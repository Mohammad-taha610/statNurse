<?php


namespace nst\payroll;


use Doctrine\ORM\ORMException;
use nst\applications\SaNurseApplicationController;
use nst\member\NstMember;
use nst\member\Nurse;
use nst\member\Provider;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\responses\Json;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\saController;
use sacore\application\ValidateException;
use sa\files\saFile;
use sa\member\auth;
use sa\system\saUser;
use sacore\utilities\notification;
use nst\payroll\PayrollService;
use nst\events\Shift;
use nst\member\NurseRepository;
use nst\member\NstFile;
use sa\developer\saDeveloperController;
use ZipArchive;
use sacore\application\responses\File;
use sacore\application\Request;
use mikehaertl\pdftk\Pdf;
use nst\payroll\TaxDocumentService;

class SaPayrollController extends saController
{

    public function managePayrolls($request): View
    {
        $view = new View('table');

        $fieldsToSearch = array();
        foreach ($request->query->all() as $field => $value) {
            if ($field == 'q_per_page') {
                $perPage = intval($value);
            } elseif (str_starts_with($field, 'q_') && !empty($value)) {
                $fieldsToSearch[str_replace('q_', '', $field)] = $value;
            }
        }

        $currentPage = !empty($request->get('page')) ? $request->get('page') : 1;
        $sort = !empty($request->get('sort')) ? $request->get('sort') : false;
        $sortDir = !empty($request->get('sortDir')) ? $request->get('sortDir') : false;

        $defaultLimit = 20;

        [$payrolls, $totalRecords, $totalPages] = ioc::getRepository('Payroll')->paginatedSearch($fieldsToSearch, $defaultLimit, $currentPage, $sort, $sortDir);
        foreach ($payrolls as $payroll) {
            /** @var Payroll $payroll */
            $startDate = $payroll->getStart();
            $endDate = $payroll->getEnd();
            $amount = $payroll->getAmount();
            $dataSingle = ['id' => $payroll->getId(), 'start_date' => $startDate, 'end_date' => $endDate, 'dollar_amount' => $amount, 'date_created' => $payroll->getDateCreated()->format('m/d/Y')];
            $dataArray[] = $dataSingle;
        }

        $provider_table = array(
            /* SET THE HEADER OF THE TABLE UP */
            'header' => array(array('name' => 'Start Date', 'class' => '', 'searchType' => 'date'), array('name' => 'End Date', 'class' => '', 'searchType' => 'date'),
                array('name' => 'Dollar Amount', 'class' => ''), array('name' => 'Date Created', 'class' => '', 'searchType' => 'date')),
            /* SET ACTIONS ON EVERY ROW */
            'actions' => array('view' => array('name' => 'Edit', 'routeid' => 'edit_payroll', 'params' => array('id')),
                'delete' => ['name' => 'Delete', 'routeid' => 'delete_payroll', 'params' => ['id']]),
            'tableCreateRoute' => 'create_payroll',
            /* SET THE NO DATA MESSAGE */
            'noDataMessage' => 'No payrolls in the system',
            /* SET THE DATA MAP */
            'map' => array('start_date', 'end_date', 'dollar_amount', 'date_created'),
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            'data' => $dataArray,
            'searchable' => true,
            /* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'perPage' => $defaultLimit,
        );

        $view->data['table'][] = $provider_table;

        return $view;
    }

    public function editPayroll($request): View
    {
        $id = $request->getRouteParams()->get('id');
        if (is_null($id)) $id = 0;
        $view = new View('edit_payroll');
        $view->data['id'] = $id;
        return $view;
    }

    public function loadVue($data): Json
    {
        $payrollId = $data['payrollId'];
        $json = new Json();

        $itemsPerPage = 100;

        $nurses = ioc::getRepository('Nurse')->search([], 'DESC', $itemsPerPage);
        $nurseArray = [];
        foreach ($nurses as $nurse) {
            $singleArray = ['id' => $nurse->getId(), 'name' => $nurse->getMember()->getFirstName() . " " . $nurse->getMember()->getLastName()];
            $nurseArray[] = $singleArray;
        }

        $json->data['nurseOptions'] = $nurseArray;
        $json->data['totalAmount'] = 0;

        if ($payrollId > 0) {
            /** @var Payroll $payroll */
            $payroll = ioc::getRepository('Payroll')->find($payrollId);
            $startDate = $payroll->getStart();
            $endDate = $payroll->getEnd();
            $nurse = $payroll->getNurse();
            $name = $payroll->getUniqueDescriptor();
            $payrollItems = $payroll->getPayrollItems();

            $totalAmount = 0;
            foreach ($payrollItems as $payrollItem) {
                $totalAmount += $payrollItem->getAmount();
            }

            $json->data['nurse'] = ['id' => $nurse->getId(), 'name' => $nurse->getFirstName() . " " . $nurse->getLastName()];
            $json->data['startDate'] = $startDate;
            $json->data['endDate'] = $endDate;
            $json->data['payroll'] = ['id' => $payroll->getId(), 'name' => $name];
            $json->data['totalAmount'] = $totalAmount;
        }

        $json->data['success'] = true;
        return $json;
    }

    public function savePayroll($request): Redirect
    {
        $error = [];
        $id = $request->getRouteParams()->get('id');
        if (is_null($id)) $id = 0;
        $notify = new notification();

        if ($id > 0) {
            /** @var Provider $provider */
            $provider = ioc::getRepository('Payroll')->find($id);
        } else {
            /** @var Provider $provider */
            $provider = ioc::resolve('Payroll');
            $provider->setDateCreated(new DateTime('now', app::getInstance()->getTimeZone()));
        }

        if (empty($request->request->all()['name'])) {
            $error[] = "Please select a Nurse";
        }
        if (!empty($error)) {
            $notify->addNotification('error', 'Error', 'Some fields are missing.');
            return new Redirect(app::get()->getRouter()->generate('manage_payrolls'));
        }

        if (empty($request->request->all()['is_active'])) {
            $is_active = 0;
        } else {
            $is_active = 1;
        }

        $provider->setIsActive($is_active);
        $provider->getMember()->setFirstName($request->request->all()['name']);

        try {
            app::$entityManager->persist($provider);
            app::$entityManager->flush();


            if ($id > 0) {
                $notify->addNotification('success', 'Success', 'Provider saved successfully.');
                return new Redirect(app::get()->getRouter()->generate('manage_payroll'));
            } else {
                $notify->addNotification('success', 'Success', 'Provider created successfully.');
                return new Redirect(app::get()->getRouter()->generate('edit_payroll', ['id' => $provider->getId()]));
            }
        } catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />' . $e->getMessage());
            // have to return this due to editMember returning new View obj.
            return $this->editNurse($request);
        }
    }

    public function deletePayroll($request): Redirect|View
    {
        $id = $request->getRouteParams()->get('id');
        /** @var Nurse $nurse */
        $nurse = ioc::getRepository('danger')->find($id);

        $notify = new notification();

        try {
            app::$entityManager->remove($nurse);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Nurse deleted successfully.');
            return new Redirect(app::get()->getRouter()->generate('manage_payroll'));
        } catch (ValidateException|ORMException $e) {
            $notify->addNotification('danger', 'Error', 'An error occurred while deleting this nurse. <br />' . $e->getMessage());
            return $this->editPayroll($request);
        }
    }

    /**
     * @throws \Exception
     */
    public function viewCurrentPayPeriod(): View
    {
        $payrollService = new PayrollService();
        $period = $payrollService->calculatePayPeriodFromDate(new DateTime('now', app::getInstance()->getTimeZone()));

        $view = new View('sa_pay_period');
        $view->data['period'] = $period['combined'];
        $view->data['provider_id'] = 0;
        $view->data['unresolved_only'] = 0;
        return $view;
    }

    /**
     * @throws \Exception
     */
    public function viewUnresolvedPay(): View
    {
        $payrollService = new PayrollService();
        $period = $payrollService->calculatePayPeriodFromDate(new DateTime('now', app::getInstance()->getTimeZone()));

        $view = new View('sa_pay_period');
        $view->data['period'] = $period['combined'];
        $view->data['provider_id'] = 0;
        $view->data['unresolved_only'] = true;
        return $view;
    }

    /**
     * @throws \Exception
     */
    public function viewReports(): View
    {
        $payrollService = new PayrollService();
        $period = $payrollService->calculatePayPeriodFromDate(new DateTime('now', app::getInstance()->getTimeZone()));

        $view = new View('sa_reports');
        $view->data['period'] = $period['combined'];
        $view->data['provider_id'] = 0;
        $view->data['unresolved_only'] = true;
        return $view;
    }

        /**
     * @throws \Exception
     */
    public function viewAdminReports(): View
    {
        $payrollService = new PayrollService();
        $period = $payrollService->calculatePayPeriodFromDate(new DateTime('now', app::getInstance()->getTimeZone()));

        $view = new View('sa_admin_reports');
        $view->data['period'] = $period['combined'];
        $view->data['provider_id'] = 0;
        $view->data['unresolved_only'] = true;
        return $view;
    }

    /**
     * @throws \Exception
     */
    public function viewPaymentHistory(): View
    {
        $payrollService = new PayrollService();
        $period = $payrollService->calculatePayPeriodFromDate(new DateTime('now', app::getInstance()->getTimeZone()));

        $view = new View('sa_pay_period');
        $view->data['period'] = $period['combined'];
        $view->data['provider_id'] = 0;
        $view->data['unresolved_only'] = 0;
        return $view;
    }

    public static function getPayPeriods($data): array
    {
        $payrollService = new PayrollService();
        return $payrollService->getPayPeriods($data);
    }

    /**
     * @throws \Exception
     */
    public static function getShiftPayments($data): array
    {
        $payrollService = new PayrollService();
        return $payrollService->getShiftPayments($data);
    }

    public static function getNursePayments($data): array
    {
        $payrollService = new PayrollService();
        return $payrollService->getNursePayments($data);
    }

    public static function getNursePaymentsForReports($data): array
    {
        $payrollService = new PayrollService();
        return $payrollService->getNursePaymentsForReports($data);
    }

    public static function getSingleNursePaymentsForReports($data): array
    {
        $payrollService = new PayrollService();
        return $payrollService->getSingleNursePaymentsForReports($data);
    }

    public static function resolvePayment($data): array
    {
        $payrollService = new PayrollService();
        return $payrollService->resolvePayment($data);
    }

    public static function savePaymentChanges($data): array
    {
        $payrollService = new PayrollService();
        return $payrollService->savePaymentChanges($data);
    }

    public static function savePaymentChangesForReports($data): array
    {
        $payrollService = new PayrollService();
        return $payrollService->savePaymentChangesForReports($data);
    }

    public static function saveManualPayment($data): array
    {
        $payrollService = new PayrollService();
        return $payrollService->saveManualPayment($data);
    }

    public static function generateNachaFile($data): array
    {
        $service = new PayrollService();
        return $service->generateNachaFile($data);
    }

    public static function generatePaycardFileXlsx($data): array
    {
        $service = new PayrollService();
        return $service->generatePaycardFileXlsx($data);
    }

    public static function generatePaycardFileCsv($data): array
    {
        $service = new PayrollService();
        return $service->generatePaycardFileCsv($data);
    }

		public static function generateCheckrPayCsv($data): array
    {
        $service = new PayrollService();
        return $service->generateCheckrPayCsv($data);
    }

    /**
     * NOT USED AS OF NOW - USING CSV INSTEAD
     * @var \sacore\application\Request $request
     */
    public function getPaycardFileXlsx($request) {
        ini_set('memory_limit', '512M');
        $xlsxFile = app::get()->getConfiguration()->get('tempDir') ."/paycard_upload.xlsx";

        header('Content-Type: application/vnd.ms-excel');
        header('Content-disposition: attachment; filename=paycard_upload.xlsx');
        header('Content-Length: ' . (String) filesize($xlsxFile));
        readfile($xlsxFile);
        unlink($xlsxFile);

        exit();
    }

    /**
     * @var \sacore\application\Request $request
     */
    public function getPaycardFileCsv($request) {
        ini_set('memory_limit', '512M');
        $csvFile = app::get()->getConfiguration()->get('tempDir') ."/paycard_upload.csv";

        header('Content-Type: text/csv');
        header('Content-disposition: attachment; filename=paycard_upload.csv');
        header('Content-Length: ' . (String) filesize($csvFile));
        readfile($csvFile);
        unlink($csvFile);

        exit();
    }

		    /**
     * @var \sacore\application\Request $request
     */
    public function getCheckrPayFileCsv($request) {
			ini_set('memory_limit', '512M');
			$csvFile = app::get()->getConfiguration()->get('tempDir') ."/checkr_pay_upload.csv";

			header('Content-Type: text/csv');
			header('Content-disposition: attachment; filename=checkr_pay_upload.csv');
			header('Content-Length: ' . (String) filesize($csvFile));
			readfile($csvFile);
			unlink($csvFile);

			exit();
	}

    public static function loadPaymentData($data): array
    {
        $service = new PayrollService();
        return $service->loadPaymentData($data);
    }

    public static function markAllAsPaid($data): array
    {
        $service = new PayrollService();
        return $service->markAllAsPaid($data);
    }

    public static function findConflictingPayments($data): array
    {
        $service = new PayrollService();
        return $service->findConflictingPayments($data);
    }

    public static function deletePayment($data): array
    {
        $service = new PayrollService();
        return $service->deletePayment($data);
    }

    public static function softDeletePayment($data): array
    {
        $service = new PayrollService();
        return $service->softDeletePayment($data);
    }

    public static function getPaymentsByShift($data) {
        $response = ['success' => false];
        $payments = \sacore\utilities\doctrineUtils::getEntityCollectionArray(ioc::getRepository('PayrollPayment')->findBy(['shift' => $data['shift_id']]));
        $shiftRepo = ioc::getRepository('Shift');
        $shift = $shiftRepo->findOneBy(['id' => $data['shift_id']]);
        $provider = $shift->getProvider();
        $nurse = $shift->getNurse();
        if($shift) {
            $response['shift_for_edit'] = \sacore\utilities\doctrineUtils::getEntityArray($shift);
            $response['shift_for_edit']['clock_in_time_picker'] = $shift->getClockInTime()->format('h:i A');
            $response['shift_for_edit']['clock_out_time_picker'] = $shift->getClockOutTime()->format('h:i A');
            $response['shift_for_edit']['current_period_standard_hours'] = 0;
            $response['shift_for_edit']['current_period_overtime_hours'] = 0;
            if($provider) {
                $response['shift_for_edit']['provider'] = \sacore\utilities\doctrineUtils::getEntityArray($provider);
                $response['shift_for_edit']['provider']['company'] = $provider->getMember()->getCompany();
            }
            if($nurse) {
                $response['shift_for_edit']['nurse'] = \sacore\utilities\doctrineUtils::getEntityArray($nurse);
            }
        }

        if (!$payments) {
            return $response;
        }
        $response['standard_payment'] = null;
        $response['overtime_payment'] = null;
        foreach ($payments as $payment) {

            if($payment['type'] === 'Standard') {

                if ($payment['bill_holiday'] != 0) {

                    $payRate = $payment['bill_rate'];
                    $billHoliday = $payment['bill_holiday'];

                    $payment['holiday_hours'] = $billHoliday / ($payRate * .5);

                    $response['standard_payment'] = $payment;
                } else {

                    $payment['holiday_hours'] = 0;
                    $response['standard_payment'] = $payment;
                }
            } elseif ($payment['type'] === 'Overtime') {
                $response['overtime_payment'] = $payment;
            } else {
                $response['unexpected_shift_type'] = $payment;
            }
        }
        if(count($payments) > 2) {
            $response['too_many_payments'] = true;
            $response['shift_payment_count'] = count($payments);
        }
        // Get hours for pay period so far
        $payrollService = new PayrollService();
        $payPeriod = $payrollService->calculatePayPeriodFromDate($shift->getStart());
        $shiftsInPayPeriod = $shiftRepo->getShiftsBetweenDates(
            $payPeriod['start'],
            $payPeriod['end'],
            null,
            true,
            $provider->getId(),
            $nurse->getId(),
            null,
            false,
            null,
            null,
            false,
            false,
            [['column' => 'start', 'dir' => 'ASC']]
        );
        foreach ($shiftsInPayPeriod as $shiftInPayPeriod) {
            if ($shiftInPayPeriod === $shift) {
                break;
            }
            if ($shiftInPayPeriod->getStatus() != 'Completed') {
                continue;
            }

            /** @var Provider $provider */
            $provider = $shiftInPayPeriod->getProvider();

            $shiftInPayPeriodPayments = ioc::getRepository('PayrollPayment')->findBy(['shift' => $shiftInPayPeriod->getId()]);
            foreach ($shiftInPayPeriodPayments as $shiftInPayPeriodPayment) {
                if($shiftInPayPeriodPayment->getClockedHours() <= 0.00) {
                    continue;
                }
                if($shiftInPayPeriodPayment->getType() === 'Standard') {
                    $response['shift_for_edit']['current_period_standard_shift_payments'][] = [
                        'date' => $shiftInPayPeriod->getStart()->format('m-d-Y'),
                        'clocked_hours' => number_format($shiftInPayPeriodPayment->getClockedHours(), 2)
                    ];
                    if ($provider->getHasOtPay()) {
                        $response['shift_for_edit']['current_period_standard_hours'] = number_format($response['shift_for_edit']['current_period_standard_hours'] + +$shiftInPayPeriodPayment->getClockedHours(), 2);
                    } else {
                        $response['shift_for_edit']['current_period_standard_hours'] = 0;
                    }
                } else {
                    $response['shift_for_edit']['current_period_overtime_shift_payments'][] = [
                        'date' => $shiftInPayPeriod->getStart()->format('m-d-Y'),
                        'clocked_hours' => number_format($shiftInPayPeriodPayment->getClockedHours(), 2)
                    ];
                    $response['shift_for_edit']['current_period_overtime_hours'] = number_format($response['shift_for_edit']['current_overtime_hours'] + +$shiftInPayPeriodPayment->getClockedHours(), 2);
                }
            }
        }

        $response['success'] = true;
        return $response;
    }

    public static function generate1099s($data)
    {
        $response = ['success' => false];
        $year = (date('Y') - 1) . '%';

        $data1099['year'] = $year;
        /** @var NstFileTag $fileTag */
        $fileTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => '1099']);
        $tagId = $fileTag->getId();
        $data1099['tag_id'] = $tagId;
        $nurses = $data['nurses'];

        for ($i = 0; $i < count($nurses); $i++) {
            $nurseInfo = ioc::getRepository('Nurse')->getNurse1099Info($nurses[$i]['id']);

            $data1099['id'] = $nurses[$i]['id'];
            $data1099['name'] = $nurseInfo['first_name']." ".$nurseInfo['last_name'];
            $data1099['file_name'] = str_replace(" ", "_", $data1099['name']);
            $streetAddress = $nurseInfo['street_address'];
            $streetAddress2 = $nurseInfo['street_address_2'];
            $apartmentNum = $nurseInfo['apt_number'];

            if ($streetAddress2 && $apartmentNum) {
                $data1099['street_and_apt'] = $streetAddress." ".$streetAddress2." ".$apartmentNum;
            } else if ($streetAddress2) {
                $data1099['street_and_apt'] = $streetAddress." ".$streetAddress2;
            } else if ($apartmentNum) {
                $data1099['street_and_apt'] = $streetAddress." ".$apartmentNum;
            } else {
                $data1099['street_and_apt'] = $streetAddress;
            }
            $data1099['city_state_country_zip'] = $nurseInfo['city']." ".$nurseInfo['state']." USA ".$nurseInfo['zipcode'];
            $data1099['compensation'] = $nurses[$i]['total_comp'];

            $nurseSSN = $nurseInfo['ssn'];
            $key = $nurseInfo['user_key'];
            $cipher = "AES-128-CTR";
            if (!is_string($key)) {
                continue;
            }

            do {
                $nurseSSN = openssl_decrypt($nurseSSN, $cipher, (string)$key, 0, ord($key));
            } while (strlen($nurseSSN) > 11);

            if (!str_contains($nurseSSN, "-")) {
                $first3 = substr($nurseSSN, 0, 3);
                $middle2 = substr($nurseSSN, 3, 2);
                $last4 = substr($nurseSSN, 5, 4);

                $nurseSSN = $first3 ."-". $middle2 ."-". $last4;
            }
            $data1099['social_security'] = $nurseSSN;

            SaNurseApplicationController::generate1099($data1099);
        }

        $response['success'] = true;
        return $response;
    }

    public static function getNursesWithShift()
    {
        $response = ['success' => false];
        // last day of last year
        $lastYear = date('Y') - 1;
        $endDate = new DateTime($lastYear . '-12-31' . '23:59:59');

        $nurses = ioc::getRepository('Nurse')->findAll();
        $nurseArray = array();
        if ($nurses) {
            /** @var Nurse $nurse */
            foreach ($nurses as $nurse) {
                $id = $nurse->getId();
                $totalComp =  ioc::getRepository('PayrollPayment')->getNurseYTDHoursAndTotal($id, $endDate)['total'];
                if ($totalComp >= 600) {
                    $nurseWithShift['id'] = $id;
                    $nurseWithShift['total_comp'] = number_format(round($totalComp, 2), 2, '.', '');
                    array_push($nurseArray, $nurseWithShift);
                }
            }
        }

        $response['nurses'] = $nurseArray;
        $response['success'] = true;
        return $response;
    }

    public static function checkFor1099($data)
    {
        $response = ['success' => false];
        // year needs to have % character trailing as it is used in a LIKE query
        $lastYear = date('Y') - 1;
        $year = $lastYear . "%";
        $nurses = $data['nurses'];

        /** @var NstFileTag $fileTag */
        $fileTag = ioc::getRepository('NstFileTag')?->findOneBy(['name' => '1099']);
        $tagId = $fileTag->getId();

        $nurseArray = array();
        foreach ($nurses as $nurse) {
            $returnNurse['total_comp'] = $nurse['total_comp'];
            $id = $nurse['id'];
            /** @var Nurse $nurse */
            $nurse = ioc::getRepository('Nurse')->findOneBy(['id' => $id]);

            $firstName = $nurse->getFirstName();
            $lastName = $nurse->getLastName();
            $returnNurse['name'] = $firstName." ".$lastName;
            $returnNurse['id'] = $id;
            $returnNurse['has_1099'] = ioc::getRepository('saFile')->nurseHasFileWithTagInYear($year, $tagId, $id)['route'];
            $returnNurse['nurse_route'] = app::get()->getRouter()->generate('edit_nurse', ['id' => $id]);

            array_push($nurseArray, $returnNurse);
        }

        $response['nurses'] = $nurseArray;
        $response['success'] = true;
        return $response;
    }

    public static function gen1099ExportGroup($data)
    {
        $response = ['success' => false];
        if($data['pdf_pages'] == 8) {
            $taxDocService = new TaxDocumentService();
            $taxDocService->gen1099Csv($data);
            $response['success'] = true;
            return $response;
        }

        /** @var NstFileTag $fileTag */
        $fileTag = ioc::getRepository('NstFileTag')?->findOneBy(['name' => '1099']);
        $tagId = $fileTag->getId();

        $filesToZip = array();
        $nurses = $data['nurses'];
        $pdfPageNumber = $data['pdf_pages'];
        $counter = $data['counter'];

        $exampleFile = ioc::getRepository('NstFile')?->findOneBy(['tag' => $fileTag, 'nurse' => $nurses[0]['id']]);
        $exampleFilePath = $exampleFile->getPath();
        $savePathArray = explode("uploads/", $exampleFilePath);
        $savePath = $savePathArray[0];

        $tempDirectory = app::get()->getConfiguration()->get('tempDir');
        $singlePageExportDirectory = "/1099_singlepage_export";
        if (!is_dir($tempDirectory . $singlePageExportDirectory)) {
            mkdir($tempDirectory . $singlePageExportDirectory, 0777);
        }

        if ($pdfPageNumber != "all") {

            for($i = 0; $i < count($nurses); $i++) {
                $filename = $nurses[$i]['file_name'] . "_page_" . $pdfPageNumber . "_" . time() . ".pdf";
                $diskFilePath = ioc::getRepository('saFile')->filePathFor1099($nurses[$i]['id'], $tagId);
                $filePath = $savePath . "uploads/" . $diskFilePath;

                $pdf = new Pdf($filePath);
                $pdf->cat($pdfPageNumber)->flatten();

                $tempSavePath = $tempDirectory . $singlePageExportDirectory ."/". $filename;
                file_put_contents($tempSavePath, $pdf->toString(), FILE_APPEND);
            }

            $tempSinglePagePdfFiles = glob($tempDirectory . $singlePageExportDirectory . "/*");

            $zip = new ZipArchive;
            $zipName = $tempDirectory . "/1099Export.zip";
            if ($counter == 0) {
                $zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            } else {
                $zip->open($zipName, ZipArchive::CREATE);
            }
            foreach ($tempSinglePagePdfFiles as $filePath) {
                $zip->addFromString(basename($filePath), file_get_contents($filePath));
                unlink($filePath);
            }
            $zip->close();

        } else {

            for($i = 0; $i < count($nurses); $i++) {
                $file = ioc::getRepository('NstFile')?->findOneBy(['tag' => $fileTag, 'nurse' => $nurses[$i]['id']]);
                $filePath = $file->getPath();
                array_push($filesToZip, $filePath);
            }

            $zip = new ZipArchive;
            $zipName = $tempDirectory . "/1099Export.zip";
            if ($counter == 0) {
                $zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            } else {
                $zip->open($zipName, ZipArchive::CREATE);
            }
            foreach ($filesToZip as $filePath) {
                $zip->addFromString(basename($filePath), file_get_contents($filePath));
            }
            $zip->close();

        }

        $response['success'] = true;
        return $response;
    }

    /**
     * @var \sacore\application\Request $data
     */
    public static function export1099s($data)
    {
        ini_set('memory_limit', '512M');
        $zipName = app::get()->getConfiguration()->get('tempDir') ."/1099Export.zip";

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=1099Export.zip');
        header('Content-Length: ' . (String) filesize($zipName));
        readfile($zipName);
        unlink($zipName);

        exit();
    }

    /**
     * @var \sacore\application\Request $request
     */
    public function export1099csv($request) {
        ini_set('memory_limit', '512M');
        $csvFile = app::get()->getConfiguration()->get('tempDir') ."/1099_export.csv";

        header('Content-Type: application/csv');
        header('Content-disposition: attachment; filename=1099_export.csv');
        header('Content-Length: ' . (String) filesize($csvFile));
        readfile($csvFile);
        unlink($csvFile);

        exit();
    }

    public static function getInactiveNurses($data)
    {
        $payrollService = new PayrollService();
        return $payrollService->getInactiveNurses($data);
    }

    public static function getInactiveProviders($data)
    {
        $payrollService = new PayrollService();
        return $payrollService->getInactiveProviders($data);
    }

    public static function getDnrNurseReport($data)
    {
        $payrollService = new PayrollService();
        return $payrollService->getDnrNurseReport($data);
    }

    public static function getDnrProviderReport($data)
    {
        $payrollService = new PayrollService();
        return $payrollService->getDnrProviderReport($data);
    }

    public static function getEarningsReport($data)
    {
        $payrollService = new PayrollService();
        return $payrollService->getEarningsReport($data);
    }

    public static function getEarningsReportState($data)
    {
        $payrollService = new PayrollService();
        return $payrollService->getEarningsReportState($data);
    }

     public static function getShiftsReport($data)
    {
        $payrollService = new PayrollService();
        return $payrollService->getShiftsReport($data);
    }

    public static function getShiftsReportNurse($data)
    {
        $payrollService = new PayrollService();
        return $payrollService->getShiftsReportNurse($data);
    }

    public static function getScheduleReport($data)
    {
        $payrollService = new PayrollService();
        return $payrollService->getScheduleReport($data);
    }

    public static function getScheduleReportNurse($data)
    {
        $payrollService = new PayrollService();
        return $payrollService->getScheduleReportNurse($data);
    }

    public static function getAllNurseNames()
    {
        $payrollService = new PayrollService();
        return $payrollService->getAllNurseNames();
    }

    public static function getPayStubPDF($data)
    {
        $service = new PayrollService();
        return $service->getPayStubSAPDF($data);
    }

}
