<?php

/** @noinspection DuplicatedCode */


namespace nst\payroll;

use nst\events\SaShiftLogger;
use nst\member\NstFile;
use sacore\application\modRequest;
use sa\files\saImage;
use sa\member\auth;
use Doctrine\Common\Collections\ArrayCollection;
use Matrix\Exception;
use nst\events\Shift;
use nst\events\ShiftService;
use nst\member\Nurse;
use nst\member\Provider;
use nst\member\ProviderPayRate;
use nst\member\ProviderService;
use nst\quickbooks\QuickbooksLine;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\ValidateException;
use mikehaertl\wkhtmlto\Pdf;
use sacore\application\responses\File;
use sacore\application\responses\View;
use sa\files\saFile;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use sa\member\saMemberAddress;
use sa\system\saPostalCode;
use sacore\utilities\doctrineUtils;
use Carbon\Carbon;
use sacore\utilities\url;
use sacore\application\responses\Redirect;

class PayrollService
{
    /** @var PayrollRepository */
    protected $payrollRepository;

    /** @var PayrollPaymentRepository */
    protected $paymentRepository;

    /** @var \DateTimeZone $timezone */
    protected $timezone;

    public function __construct()
    {
        $this->payrollRepository = ioc::getRepository('Payroll');
        $this->paymentRepository = ioc::getRepository('PayrollPayment');
        $this->timezone = app::getInstance()->getTimeZone();
    }

    /**
     * @param DateTime $date
     * @returns array
     */
    public function calculatePayPeriodFromDate($date)
    {
        $period = [];

        if ($date->format('l') == 'Monday') {
            $startDate = $date;
        } else {
            $startDate = new DateTime('last Monday ' . $date->format('m/d/Y'), app::getInstance()->getTimeZone());
        }
        if ($date->format('l') == 'Sunday') {
            $endDate = $date;
        } else {
            $endDate = new DateTime('next Sunday ' . $date->format('m/d/Y'), app::getInstance()->getTimeZone());
        }

        $period['start'] = $startDate;
        $period['end'] = $endDate;
        $period['combined'] = $startDate->format('Ymd') . '_' . $endDate->format('Ymd');
        $period['display'] = $startDate->format('m/d/Y') . ' - ' . $endDate->format('m/d/Y');
        return $period;
    }


    public function getPayPeriods($data)
    {
        $response = ['success' => false];
        $providerId = $data['provider_id'];

        $periods = [];

        $periods[] = [
            'combined' => 'all',
            'display' => 'All Pay Periods'
        ];

        $stopDate = new DateTime('2022/01/02 00:00:00', app::getInstance()->getTimeZone());
        $stopDateTimestamp = $stopDate->getTimestamp();

        // Just setting to 200 for some sort of limit
        for ($i = 0; $i < 200; $i++) {
            $date = new DateTime('now', app::getInstance()->getTimeZone());
            if ($i > 0) {
                $days = $i * 7;
                $modifier = '-' . $days . ' days';
                $date->modify($modifier);
            }

            // 1/3/2022 - 1/10/2022 was the first pay period in the system
            if ($date->getTimestamp() < $stopDateTimestamp) {
                break;
            }

            $period = self::calculatePayperiodFromDate($date);
            $periods[] = $period;
        }

        if ($periods) {
            $response['success'] = true;
            $response['periods'] = $periods;
        }

        return $response;
    }

    /**
     * @throws \Exception
     */
    public function getShiftPayments($data)
    {
        $response = ['success' => false];

        // Set query variables
        $providerId = (int)$data['provider_id'];
        $nurseId = (int)$data['nurse_id'];
        $status = null;
        if ($data['unresolved_only'] == "true") {
            $status = 'Unresolved';
        }
        $data['get_zero_hour_payments'] = $data['get_zero_hour_payments'] == "true" ? true : false;

        // Single-day date-range for single day filtering
        $dateRange = null;
        if ($data['date']) {
            $date = new DateTime($data['date']);
            $dateRange[] = new DateTime($date->format('Y-m-d') . " 00:00:00");
            $dateRange[] = new DateTime($date->format('Y-m-d') . " 23:59:59");
        }

        // Run queries
        // First convert pay period start/end date strings to dates
        $startDate = new DateTime(explode('_', $data['pay_period'])[0], app::getInstance()->getTimeZone());
        $endDate = new DateTime(explode('_', $data['pay_period'])[1], app::getInstance()->getTimeZone());
        // Now we convert those dates into accurate ranges to get the complete list of payments in this daterange
        $startDate = new DateTime($startDate->format('Y-m-d') . " 00:00:00");
        $endDate = new Datetime($endDate->format('Y-m-d') . " 23:59:59");
        $payments = $this->paymentRepository->getPaymentsBetweenDates($providerId, $startDate, $endDate, false, false, false, $data['return_payments'] ? $nurseId : null, $status, $dateRange);

        if ($data['return_payments']) {
            return $payments;
        }

        if ($payments) {
            /** @var PayrollPayment $payment */
            foreach ($payments['shifts'] as $payment) {

                if ($data['unresolved_only'] == "true" && !in_array($payment->getStatus(), ['Unresolved', 'Change Requested'])) {
                    $response['payment-status-wrong'][$payment->getId()] = $payment->getId();
                    continue;
                }

                $shift = $payment->getShift();
                if (!$shift) {
                    $response['shift-missing'][$payment->getId()] = $payment->getId();
                    continue;
                }

                $nurse = $shift->getNurse();
                if (!$nurse) {
                    $response['shift-missing-nurse'][$shift->getId()] = $payment->getId();
                    continue;
                }
                $member = $nurse->getMember();

                $shiftStart = $shift->getStart();
                $shiftEnd = $shift->getEnd();

                $clockInTime = $shift->getClockInTime();
                if (!$clockInTime) {
                    $clockInTime = $shift->getStartTime();
                    $response['missing_clockin_time'] = $shift->getId();
                }

                $clockOutTime = $shift->getClockOutTime();
                if (!$clockOutTime) {
                    $clockOutTime = $shift->getEndTime();
                    $response['missing_clockout_time'] = $shift->getId();
                }

                if ($clockInTime > $clockOutTime) {
                    $clockOutTime->modify('+1 days');
                }

                $clockedHours = $payment->getClockedHours();

                if ($clockedHours < 0) {
                    
                    $fixedInfo = static::fixNegativeClockedHours($payment, $shift);

                    $shiftStart = $fixedInfo['clockInTime'];
                    $clockInTime = $fixedInfo['clockInTime'];
                    $shiftEnd = $fixedInfo['clockOutTime'];
                    $clockOutTime = $fixedInfo['clockOutTime'];
                    $clockedHours = $fixedInfo['clocked_hours'];
                }

                $date = $shiftStart->format('m/d/Y');
                if ($shift->getIsEndDateEnabled()) {
                    $shiftEndDate = new DateTime($shift->getEnd(), $this->timezone);
                    $date .= ' - ' . $shiftEndDate->format('m/d/Y');
                }
                $shiftOverride = $shift->getShiftOverride();
                $supervisorName = "";
                $supervisorCode = "";
                $supervisorSignature = null;
                if ($shiftOverride != null) {
                    $supervisorName = $shiftOverride->getSupervisorName();
                    $supervisorCode = $shiftOverride->getSupervisorCode();
                    /** @var NstFile  $supervisorSignature */
                    $supervisorSignature = $shiftOverride->getSupervisorSignature();
                    if ($supervisorSignature != null) {
                      $supervisorSignatureFilePath = '/assets/files/id/' . $supervisorSignature->getId();
                    }
                }
                $timeslip = "";
                if ($shift->getTimeslip()) {
                    $timeslip = "/assets/files/id/{$shift->getTimeslip()->getId()}";
                }
                if ($clockedHours > 0 || $clockedHours < 0 || $data['get_zero_hour_payments']) {
                    $response['shift_payments'][] = [
                        'id' => $payment->getId(),
                        'nurse_name' => $member->getFirstName() . ' ' . $member->getLastName() . ' (' . $nurse->getCredentials() . ')',
                        'shift_id' => $shift->getId(),
                        'resolved_by' => $payment->getResolvedBy(),
                        'payment_id' => $payment->getId(),
                        'shift_name' => $shift->getName(),
                        'shift_time' => $shiftStart->format('g:ia') . ' - ' . $shiftEnd->format('g:ia'),
                        'clocked_hours' => $clockedHours,
                        'clock_times' => $clockInTime->format('g:ia') . ' - ' . $clockOutTime->format('g:ia'),
                        'clock_in' => $clockInTime->format('h:i A'),
                        'clock_out' => $clockOutTime->format('h:i A'),
                        'rate' => number_format($payment->getPayRate(), 2, '.', ''),
                        'bill_rate' => number_format($payment->getBillRate(), 2, '.', ''),
                        'date' => $date,
                        'amount' => number_format($payment->getPayTotal(), 2, '.', ''),
                        'bill_amount' => number_format($payment->getBillTotal(), 2, '.', ''),
                        'type' => $payment->getType(),
                        'description' => $payment->getType() == 'Bonus' ? $payment->getDescription() : '',
                        'request_description' => $payment->getRequestDescription() ?: '',
                        'request_clock_in' => $payment->getRequestClockIn() ?: '',
                        'request_clock_out' => $payment->getRequestClockOut() ?: '',
                        'status' => $payment->getStatus(),
                        'supervisor_name' => $supervisorName,
                        'supervisor_code' => $supervisorCode,
                        'supervisor_signature' => $supervisorSignatureFilePath,
                        'timeslip' => $timeslip,
                        'clock_in_type' => $shift->getClockInType(),
                        'shift_route' => app::get()->getRouter()->generate('edit_shift', ['id' => $shift->getId()]),
                        'nurse_route' => app::get()->getRouter()->generate('nurse_profile', ['id' => $nurse->getId()]),
                        'pay_holiday' => $payment->getPayHoliday(),
                        'bill_holiday' => $payment->getBillHoliday(),
                    ];
                }
            }

            $response['success'] = true;
        }
        return $response;
    }

    public static function fixNegativeClockedHours($payment, $shift) {

        $clockInTime = $shift->getClockInTime();
        $clockOutTime = $shift->getClockOutTime();
        $clockedHours = (float) number_format(($clockOutTime->getTimestamp() - $clockInTime->getTimestamp()) / 3600, 2);

        if ($clockInTime > $clockOutTime) {
            
            $clockOutTime->modify('+1 days');
            $shift->setClockOutTime($clockOutTime);
            $clockedHours = (float) number_format(($clockOutTime->getTimestamp() - $clockInTime->getTimestamp()) / 3600, 2);
        }

        $lunchBreakTime = $shift->getLunchOverride();
        if ($lunchBreakTime > $clockedHours) {
            
            $shift->setLunchOverride($clockedHours);
            $clockedHours = 0;
        }
        app::$entityManager->flush($shift);
        
        $payment->setClockedHours($clockedHours);
        $payment->setPayTotal($payment->getCalculatedPayTotal());
        $payment->setBillTotal($payment->getCalculatedBillTotal());        
        app::$entityManager->flush($payment);
        
        $return['clockInTime'] = $clockInTime;
        $return['clockOutTime'] = $clockOutTime;
        $return['clocked_hours'] = $clockedHours;

        return $return;
    }

    public function getNursePayments($data)
    {
        $response = ['success' => false];
        $providerId = (int)$data['provider_id'];

        if ($data['pay_period'] == 'all') {
            $payments = $this->paymentRepository->getPayments($providerId);
        } else {
            $startDate = new DateTime(explode('_', $data['pay_period'])[0], app::getInstance()->getTimeZone());
            $endDate = new DateTime(explode('_', $data['pay_period'])[1], app::getInstance()->getTimeZone());
            $payments = $this->paymentRepository->getPaymentsBetweenDates($providerId, $startDate, $endDate);
        }

        $provider = ioc::get('Provider', ['id' => $providerId]);
        if ($payments) {
            /** @var PayrollPayment $payment */
            foreach ($payments['shifts'] as $payment) {
                if ($data['unresolved_only'] == "true" && $payment->getStatus() != 'Unresolved') {
                    continue;
                }
                $shift = $payment->getShift();
                if (!$shift) {
                    continue;
                }

                $nurse = $shift->getNurse();
                if (!is_object($nurse)) {
                    continue;
                }

                $member = $nurse->getMember();

                if (!$shift->getProvider()) {
                    $response['missing_provider'] = $shift->getId();
                    continue;
                }
                if (!$shift->getClockOutTime()) {
                    $shift->setClockOutTime($shift->getEndTime());
                    $response['missing_clockout_time'] = $shift->getId();
                }
                if (!$shift->getClockInTime()) {
                    $shift->setClockInTime($shift->getStartTime());
                    $response['missing_clockin_time'] = $shift->getId();
                }

                $clockedHours = number_format(($shift->getClockOutTime()->getTimestamp() - $shift->getClockInTime()->getTimestamp()) / 3600, 2);

                $hasUnresolvedPayments = $payment->getStatus() == 'Unresolved';

                if (!$response['nurse_payments'][$nurse->getId()]) {
                    $response['nurse_payments'][$nurse->getId()] = [
                        'nurse_name' => $member->getFirstName() . ' ' . $member->getLastName(),
                        'nurse_route' => app::get()->getRouter()->generate('nurse_profile', ['id' => $nurse->getId()]),
                        'provider_name' => $shift->getProvider()->getMember()->getCompany(),
                        'clocked_hours' => $clockedHours,
                        'pay_rate' => $payment->getPayRate(),
                        'bill_rate' => $payment->getBillRate(),
                        'pay_bonus' => $payment->getPayBonus(),
                        'bill_bonus' => $payment->getBillBonus(),
                        'pay_travel' => $payment->getPayTravel(),
                        'bill_travel' => $payment->getBillTravel(),
                        'pay_holiday' => $payment->getPayHoliday(),
                        'bill_holiday' => $payment->getBillHoliday(),
                        'pay_total' => $payment->getPayTotal(),
                        'bill_total' => $payment->getBillTotal(),
                        'has_unresolved_payments' => $hasUnresolvedPayments ? 'Yes' : 'No'
                    ];
                } else {
                    $response['nurse_payments'][$nurse->getId()]['clocked_hours'] += (float)$clockedHours;
                    $response['nurse_payments'][$nurse->getId()]['pay_total'] += $payment->getPayTotal();
                    $response['nurse_payments'][$nurse->getId()]['pay_bonus'] += $payment->getPayBonus();
                    $response['nurse_payments'][$nurse->getId()]['has_unresolved_payments'] = $hasUnresolvedPayments ? 'Yes' : $response['nurse_payments'][$nurse->getId()]['has_unresolved_payments'];
                }
            }
        }

        $response['success'] = true;
        return $response;
    }

    public function getNursePaymentsForReports($data)
    {
        $response = ['success' => false];
        $providerId = (int)$data['provider_id'];
        $paymentMethod = $data['payment_method'];
        $paymentStatus = $data['payment_status'];
        $status = $data['status'];
        $forExcel = $data['for_excel'];

        // This is a chalked performance fix for searching on the nurse for payroll reports
        $searchNurse = null;
        if ($data['nurse_name']) {
            $searchNurse = ioc::get('Nurse', ['first_name' => explode(" ", $data['nurse_name'])[0], 'last_name' => explode(" ", $data['nurse_name'])[1]]);
        }

        if ($forExcel) {
            $payments = [
                'payments' => explode(',', $data['nurse_shift_payments'])
            ];
        } else if ($data['pay_period'] == 'all') {
            $payments = $this->paymentRepository->getPayments($providerId);
        } else {
            $startDate = new DateTime(explode('_', $data['pay_period'])[0] . ' 00:00:00', app::getInstance()->getTimeZone());
            $endDate = new DateTime(explode('_', $data['pay_period'])[1] . ' 23:59:59', app::getInstance()->getTimeZone());
            $payments = $this->paymentRepository->getPaymentsBetweenDates($providerId, $startDate, $endDate, false, $paymentMethod, $paymentStatus, $searchNurse?->getId());
        }

        if ($payments) {
            /** @var PayrollPayment $payment */
            foreach ($payments['payments'] as $_payment) {
                $payment = $forExcel ? ioc::get('PayrollPayment', ['id' => $_payment]) : $_payment;

                $shift = $payment->getShift();
                if (!$shift) {
                    continue;
                }

                if ($data['unresolved_only'] == "true" && $payment->getStatus() != 'Unresolved') {
                    continue;
                }

                $nurse = $shift->getNurse();
                if (!$nurse) {
                    continue;
                }
                $member = $nurse->getMember();
                if ($payment->getStatus() != 'Unresolved') {
                    $clockedHours = $payment->getClockedHours();
                    if ($payment->getPayTotal() > 0 || $payment->getBillTotal() > 0) {
                        $hasUnresolvedPayments = $payment->getStatus() == 'Unresolved';
                        $response['nurse_shift_payments'][] = [
                            'payment_id' => $payment->getId(),
                            'nurse_name' => $member->getFirstName() . ' ' . $member->getLastName(),
                            'nurse_route' => app::get()->getRouter()->generate('member_sa_account_edit', ['id' => $nurse->getMember()->getId()]),
                            'provider_name' => $shift->getProvider() ? $shift->getProvider()->getMember()->getCompany() : '#ERROR#',
                            'clocked_hours' => $clockedHours,
                            'pay_rate' => $payment->getPayRate(),
                            'bill_rate' => $payment->getBillRate(),
                            'pay_bonus' => $payment->getPayBonus(),
                            'bill_bonus' => $payment->getBillBonus(),
                            'pay_travel' => $payment->getPayTravel(),
                            'bill_travel' => $payment->getBillTravel(),
                            'pay_total' => $payment->getPayTotal(),
                            'bill_total' => $payment->getBillTotal(),
                            'pay_holiday' => $payment->getPayHoliday(),
                            'bill_holiday' => $payment->getBillHoliday(),
                            'payment_method' => $payment->getPaymentMethod(),
                            'payment_status' => $payment->getPaymentStatus(),
                            'status' => $payment->getStatus(),
                            'type' => $payment->getType(),
                            'has_unresolved_payments' => $hasUnresolvedPayments ? 'Yes' : 'No',
                            'date' => $shift->getStart()->format('m/d/Y'),
                            'nurse_credentials' => $payment?->getShift()?->getNurse()?->getCredentials(),
                            'corrected_comment' => $payment?->getCorrectedComment(),
                            'quickbooks_purchase_id' => $payment?->getQuickbooksPurchaseId(),
                            'quickbooks_bill_id' => $payment?->getQuickbooksBillId(),
                            'quickbooks_bill_payment_id' => $payment?->getQuickbooksBillPaymentId(),
                        ];
                    }
                }
            }
            $response['success'] = true;
        }
        return $response;
    }

    public function getReportPdf($data)
    {
        $response = ['success' => false];
        $pdf = new Pdf;

        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = $this->getSheetforReportPrint($data);

        $writer = IOFactory::createWriter($spreadsheet, 'Html');
        //    $writer->setEmbedImages(true);
        //  $writer->writeAllSheets();
        //   $html_writer->setIncludeCharts(true);
        $writer->save(app::get()->getConfiguration()->get('tempDir') . "/" . "tmpHtml.html");
        $html = file_get_contents(app::get()->getConfiguration()->get('tempDir') . "/" . "tmpHtml.html");
        $pdf->addPage($html);
        if (!$pdf->saveAs(app::get()->getConfiguration()->get('uploadsDir') . "/" . $data['pdf_file_name'])) {
            $error = $pdf->getError();
            $response['failurereason'] = $error;
        } else {
            $pdfFolder = app::get()->getConfiguration()->get('uploadsDir');
            if (!is_dir($pdfFolder)) {
                mkdir($pdfFolder, 0777, true);
            }
            $path = $pdfFolder . "/" . $data['pdf_file_name'];

            $pathArr = explode('/', $path);
            $filename = $pathArr[count($pathArr) - 1];

            $folder = 'PayrollReports';
            /** @var saFile $file */
            $file = ioc::resolve('saFile');
            $file->setFolder($folder);
            $file->setDiskFileName($filename);
            $file->setFilename($filename);
            $file->setDateCreated(new \sacore\application\DateTime());
            $file->setCompleteFile(true);
            $file->setFileSize(filesize($path));

            app::$entityManager->persist($file);
            app::$entityManager->flush($file);

            $route = app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $folder, 'file' => $filename]);
            $response['file_route'] = $route;
            $response['pdf'] = $path;
            $response['success'] = true;
        }
        return $response;
    }

    private function getSheetforReportPrint($data)
    {
        $data['for_excel'] = true;
        $paymentResponse = $this->getNursePaymentsForReports($data);

        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->setActiveSheetIndex(0);
        $columnIndex = 1;
        foreach ($data['nurse_headers'] as $nurse_headers) {
            if (isset($nurse_headers['printable']) && $nurse_headers['printable'] == 'false') {
                continue;
            }
            $sheet->getCellByColumnAndRow($columnIndex, 1)->setValue($nurse_headers['text']);
            $columnIndex++;
        }
        $nurseCount = count($paymentResponse['nurse_shift_payments']);
        $rowIndex = 2;
        for ($i = 1; $i <= $nurseCount; $i++) {
            $columnIndex = 1;
            foreach ($data['nurse_headers'] as $nurse_headers) {
                if (isset($nurse_headers['printable']) && $nurse_headers['printable'] == 'false') {
                    continue;
                }
                $sheet->getCellByColumnAndRow($columnIndex, $rowIndex)->setValue($paymentResponse['nurse_shift_payments'][$rowIndex - 2][$nurse_headers['value']]);
                $columnIndex++;
            }
            $rowIndex++;
        }

        return $spreadsheet;
    }

    public function getReportInExcel($data)
    {
        $response = ['success' => false];
        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = $this->getSheetforReportPrint($data);
        $path = app::get()->getConfiguration()->get('uploadsDir')->getValue() . '/' . $data['excel_filename'];
        $writer = IOFactory::createWriter($spreadsheet, $data['excel_type']);
        $writer->save($path);
        $excelFolder = app::get()->getConfiguration()->get('uploadsDir')->getValue();
        if (!is_dir($excelFolder)) {
            mkdir($excelFolder, 0775, true);
        }
        $pathArr = explode('/', $path);
        $filename = $pathArr[count($pathArr) - 1];

        $folder = 'PayrollReports';
        /** @var saFile $file */
        $file = ioc::resolve('saFile');
        $file->setFolder($folder);
        $file->setDiskFileName($filename);
        $file->setFilename($filename);
        $file->setDateCreated(new \sacore\application\DateTime());
        $file->setCompleteFile(true);
        $file->setFileSize(filesize($path));

        app::$entityManager->persist($file);
        app::$entityManager->flush($file);

        $route = app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $folder, 'file' => $filename]);
        $response['file_route'] = $route;
        $response['success'] = true;

        return $response;
    }

    /**
     * Returns the filename that should be used for sample output.
     *
     * @param string $filename
     * @param string $extension
     *
     * @return string
     */
    public function getFilename($filename, $extension = 'xlsx')
    {
        $originalExtension = pathinfo($filename, PATHINFO_EXTENSION);

        return $this->getTemporaryFolder() . '/' . str_replace('.' . $originalExtension, '.' . $extension, basename($filename));
    }

    public function resolvePayment($data)
    {
        $response = ['success' => false];
        $id = $data['payment_id'];
        $saShiftLogger = new SaShiftLogger();
        $currentUser = modRequest::request('sa.user');

        // if not SA user then grab portal side authUser
        if ($currentUser == null) {
            $currentUser = auth::getAuthUser();
        }

        $payment = ioc::get('PayrollPayment', ['id' => $id]);

        if ($payment) {
            $resolved_by_name = $currentUser->getLastName() . ', ' . $currentUser->getFirstName();
            $payment->setStatus('Resolved');
            $payment->setResolvedBy($resolved_by_name);
            $shift = $payment->getShift();
            $dates = $shift->getStart() . '- ' . $shift->getEnd();
            $nurse = $shift->getNurse();
            $nurseName = $nurse->getLastName() . ', ' . $nurse->getFirstName();
            $saShiftLogger->log('shift for  ' . $dates . ' Has been resolved by ' . $resolved_by_name . ' for ' . $nurseName, ['action' => "RESOLVED"]);

            app::$entityManager->flush();
            $response['success'] = true;
        }

        return $response;
    }

    public function requestChange($data)
    {
        $response = ['success' => false];
        $id = $data['payment_id'];
        $description = $data['request_description'];
        $clockIn = $data['request_clock_in'];
        $clockOut = $data['request_clock_out'];

        /** @var PayrollPayment $payment */
        $payment = ioc::get('PayrollPayment', ['id' => $id]);
        if ($payment) {
            $payment->setStatus('Change Requested');
            $payment->setRequestDescription($description);

            if ($clockIn) {
                $clockInTime = new DateTime($clockIn, $this->timezone);
                $payment->setRequestClockIn($clockInTime);
            }
            if ($clockOut) {
                $clockOutTime = new DateTime($clockOut, $this->timezone);
                $payment->setRequestClockOut($clockOutTime);
            }

            app::$entityManager->flush();

            $response['success'] = true;
        }

        return $response;
    }

    public function savePaymentChanges($data)
    {
        $response = ['success' => false];
        $shiftData = $data['shift'];
        $standardPaymentData = $data['standard_payment'];
        $overtimePaymentData = $data['overtime_payment'];

        // -- Handle shift changes
        if (!is_array($shiftData) || !count($shiftData)) {
            $response['message'] = 'Could not retrieve shift';
            return $response;
        }

        $shift = ioc::get('Shift', ['id' => $shiftData['id']]);
        $clockInTime = new DateTime($shiftData['clock_in_time_picker'], $this->timezone);
        $clockOutTime = new DateTime($shiftData['clock_out_time_picker'], $this->timezone);
        if ($clockInTime > $clockOutTime) {
            $clockOutTime->modify('+1 days');
        }
        if ($shiftData['lunch_override']) {
            $shift->setLunchOverride($shiftData['lunch_override']);
        }
        $shift->setClockInTime($clockInTime);
        $shift->setClockOutTime($clockOutTime);
        $shift->setIsCovid($shiftData['is_covid'] == "false" || !$shiftData['is_covid'] ? false : true);
        app::$entityManager->flush($shift);
        //  -- End handle shift changes

        // -- Handle standard payment changes
        $standardPayment = ioc::get('PayrollPayment', ['id' => $standardPaymentData['id']]);
        if ($standardPayment) {
            $standardPayment->setPayHoliday($standardPaymentData['pay_holiday']);
            $standardPayment->setBillHoliday($standardPaymentData['bill_holiday']);
            $standardPayment->setClockedHours($standardPaymentData['clocked_hours']);
            $standardPayment->setPayTotal($standardPayment->getCalculatedPayTotal());
            $standardPayment->setBillTotal($standardPayment->getCalculatedBillTotal());
            $standardPayment->setStatus('Resolved');
            app::$entityManager->flush($standardPayment);
        }
        // -- End handle standard payment changes

        // -- Handle overtime payment changes
        // Make sure we have overtime payment data to work with
        if (is_array($overtimePaymentData) && count($overtimePaymentData)) {
            // check if we are working with an existing OT payment or generating a new one
            if ($overtimePaymentData['id'] > 0) {
                $overtimePayment = ioc::get('PayrollPayment', ['id' => $overtimePaymentData['id']]);
                if ($overtimePayment) {
                    $overtimePayment->setClockedHours($overtimePaymentData['clocked_hours']);
                    $overtimePayment->setPayTotal($overtimePayment->getCalculatedPayTotal());
                    $overtimePayment->setBillTotal($overtimePayment->getCalculatedBillTotal());
                    $overtimePayment->setStatus('Resolved');
                    app::$entityManager->flush($overtimePayment);
                }
            } else {
                $newOvertimePayment = ioc::resolve('PayrollPayment');
                doctrineUtils::setEntityData($overtimePaymentData, $newOvertimePayment);
                $newOvertimePayment->setClockedHours($overtimePaymentData['clocked_hours']);
                $newOvertimePayment->setPayTotal($newOvertimePayment->getCalculatedPayTotal());
                $newOvertimePayment->setBillTotal($newOvertimePayment->getCalculatedBillTotal());
                $newOvertimePayment->setStatus('Resolved');
                app::$entityManager->persist($newOvertimePayment);
                $newOvertimePayment?->setShift($shift);
                app::$entityManager->flush();
            }
        }
        // -- End handle overtime payment changes

        $response['success'] = true;
        return $response;
    }

    public function savePaymentChangesForReports($data)
    {
        $response = ['success' => false];
        $nurse_shift_payment = $data['nurse_shift_payment'];
        $id = $nurse_shift_payment['payment_id'];
        $clockedHours = $data['clocked_hours'];

        /** @var PayrollPayment $payment */
        $payment = ioc::get('PayrollPayment', ['id' => $id]);
        $payTotal = $nurse_shift_payment['pay_rate'] * $clockedHours + $nurse_shift_payment['pay_bonus'] + $nurse_shift_payment['pay_travel'] + $nurse_shift_payment['pay_holiday'];
        $billTotal = $nurse_shift_payment['bill_rate'] * $clockedHours + $nurse_shift_payment['bill_bonus'] + $nurse_shift_payment['bill_travel'] + $nurse_shift_payment['bill_holiday'];
        if ($payment) {
            $payment->setClockedHours($clockedHours);
            $payment->setPayRate($nurse_shift_payment['pay_rate']);
            $payment->setBillRate($nurse_shift_payment['bill_rate']);
            $payment->setPayBonus($nurse_shift_payment['pay_bonus']);
            $payment->setBillBonus($nurse_shift_payment['bill_bonus']);
            $payment->setPayTravel($nurse_shift_payment['pay_travel']);
            $payment->setBillTravel($nurse_shift_payment['bill_travel']);
            $payment->setPayHoliday($nurse_shift_payment['pay_holiday']);
            $payment->setBillHoliday($nurse_shift_payment['bill_holiday']);
            $payment->setPaymentMethod($nurse_shift_payment['payment_method']);
            $payment->setPaymentStatus($nurse_shift_payment['payment_status']);
            $payment->setPayTotal($payTotal);
            $payment->setBillTotal($billTotal);
            $payment->setCorrectedComment($nurse_shift_payment['corrected_comment']);
            app::$entityManager->flush($payment);
            $response['success'] = true;
        }

        return $response;
    }

    public function getSingleNursePaymentsForReports($data)
    {
        $response = ['success' => false];
        $nurse_payment = $data['nurse_shift_payment'];
        $id = $nurse_payment['payment_id'];

        /** @var PayrollPayment $payment */
        $payment = ioc::get('PayrollPayment', ['id' => $id]);

        if ($payment) {
            $response['success'] = true;
            $hasUnresolvedPayments = $payment->getStatus() == 'Unresolved';
            $response['nurse_shift_payment'] = [
                'payment_id' => $payment->getId(),
                'clocked_hours' => $payment->getClockedHours(),
                'pay_rate' => $payment->getPayRate(),
                'bill_rate' => $payment->getBillRate(),
                'pay_bonus' => $payment->getPayBonus(),
                'bill_bonus' => $payment->getBillBonus(),
                'pay_travel' => $payment->getPayTravel(),
                'bill_travel' => $payment->getBillTravel(),
                'pay_total' => $payment->getPayTotal(),
                'bill_total' => $payment->getBillTotal(),
                'pay_holiday' => $payment->getPayHoliday(),
                'bill_holiday' => $payment->getBillHoliday(),
                'payment_method' => $payment->getPaymentMethod(),
                'payment_status' => $payment->getPaymentStatus(),
                'has_unresolved_payments' => $hasUnresolvedPayments ? 'Yes' : 'No'
            ];
        }
        return $response;
    }

    public function cancelChangeRequest($data)
    {
        $response = ['success' => false];
        $id = $data['payment_id'];

        /** @var PayrollPayment $payment */
        $payment = ioc::get('PayrollPayment', ['id' => $id]);
        if ($payment) {
            $payment->setStatus('Unresolved');
            $payment->setRequestDescription(null);
            app::$entityManager->flush();
            $response['success'] = true;
        }
        return $response;
    }

    public function getHoursForPayPeriod($nurse, $period)
    {
        $payments = $this->paymentRepository->getPaymentsForNurseInPeriod($nurse, $period['start'], $period['end'])['payments'];

        $hours = 0;
        /** @var PayrollPayment $payment */
        foreach ($payments as $payment) {
            $hours += $payment->getClockedHours();
        }

        return $hours;
    }

    public function getHoursForPayPeriodForProvider($nurse, $period, $provider)
    {
        $start = new DateTime(explode('_', $period['combined'])[0] . ' 00:00:00', app::getInstance()->getTimeZone());
        $end = new DateTime(explode('_', $period['combined'])[1] . ' 23:59:59', app::getInstance()->getTimeZone());
        $payments = $this->paymentRepository->getPaymentsForNurseInPeriod($nurse, $start, $end)['payments'];

        $hours = 0;
        /** @var PayrollPayment $payment */
        foreach ($payments as $payment) {
            // Overtime is per provider
            if (($payment->getShift() && $payment->getShift()->getProvider() == $provider)
                && ($payment->getPayTotal() > 0 || $payment->getBillTotal() > 0)
            ) {
                $hours += $payment->getClockedHours();
            }
        }

        return $hours;
    }

    /**
     * @param Shift $shift
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \sacore\application\IocDuplicateClassException
     * @throws \sacore\application\IocException
     */
    public function createShiftPayment($shift)
    {
        /** @var PayrollPayment $payment */
        $payment = ioc::resolve('PayrollPayment');
        $shiftService = new ShiftService();
        $nurse = $shift->getNurse();
        if (!is_object($nurse)) {
            return null;
        }
        $provider = $shift->getProvider();
        $hasOtPay = $provider->getHasOtPay();

        // Calculate Clocked Hours
        $clockedHours = $shiftService->getShiftClockedHours($shift);

        $payPeriod = $this->calculatePayPeriodFromDate($shift->getStart());

        $nurseHours = $this->getHoursForPayPeriodForProvider($nurse, $payPeriod, $provider);
        $overtimeHours = 0;
        $standardHours = $clockedHours;
        if ($nurseHours >= 40) {
            $overtimeHours = $clockedHours;
            $standardHours = 0;
        } else {
            $difference = 40 - (abs($nurseHours) + abs($clockedHours));
            if ($difference < 0) {
                $overtimeHours = abs($difference);
                $standardHours = abs(abs($clockedHours) - abs($overtimeHours));
            }
        }
        if (!$hasOtPay && $overtimeHours > 0) {

            $standardHours += $overtimeHours;
            $overtimeHours = 0;
        }
        $standardHours = number_format($standardHours, 2);
        $overtimeHours = number_format($overtimeHours, 2);

        $payment->setClockedHours($standardHours);

        // check for holiday pay
        $additionalHolidayPay['pay_rate'] = 0;
        $additionalHolidayPay['bill_rate'] = 0;
        if (!$standardHours == 0) {

            $additionalHolidayPay = $this->getHolidayPay($shift, $overtimeHours);
        }
        $payment->setPayHoliday($additionalHolidayPay['pay_rate']);
        $payment->setBillHoliday($additionalHolidayPay['bill_rate']);

        // Hourly Rates
        $payment->setPayRate($shift->getHourlyRate() ?: 0);
        $payment->setBillRate($shift->getBillingRate() ?: 0);

        // Calculate Travel Pay
        $payTravel = 0;
        $billTravel = 0;
        if ($provider->getUsesTravelPay()) {
            /** @var saPostalCode $providerZip */
            $providerZip = ioc::get('saPostalCode', ['code' => $provider->getZipcode()]);
            /** @var saPostalCode $nurseZip */
            $nurseZip = ioc::get('saPostalCode', ['code' => $nurse->getZipcode()]);
            if ($nurseZip && $providerZip) {
                $meters = $providerZip->getDistance($nurseZip);
                $distance = $meters * 0.00062137;
                if ($distance > 35) {
                    $providerService = new ProviderService();
                    $payTravel = $providerService->calculatePayRate([
                        'shift' => $shift,
                        'provider' => $shift->getProvider(),
                        'nurse_type' => $nurse->getCredentials(),
                        'rate_type' => 'Standard',
                        'is_covid' => $shift->getIsCovid(),
                        'incentive' => 1,
                        'pay_or_bill' => 'Pay'
                    ]) * 2;

                    $billTravel = $providerService->calculatePayRate([
                        'shift' => $shift,
                        'provider' => $shift->getProvider(),
                        'nurse_type' => $nurse->getCredentials(),
                        'rate_type' => 'Standard',
                        'is_covid' => $shift->getIsCovid(),
                        'incentive' => 1,
                        'pay_or_bill' => 'Bill'
                    ]) * 2;
                }
            }
        }
        $payment->setPayTravel($payTravel);
        $payment->setBillTravel($billTravel);

        // Bonus
        $payBonus = $shift->getBonusAmount();
        $billBonus = $shift->getBonusAmount();
        $payment->setPayBonus($payBonus);
        $payment->setDescription($shift->getBonusDescription());
        $payment->setBillBonus($billBonus);

        // Calculate Total
        $payTotal =
            ($standardHours * $payment->getPayRate())
            + $payBonus
            + $additionalHolidayPay['pay_rate']
            + $payTravel;
        $billTotal =
            ($standardHours * $payment->getBillRate())
            + $billBonus
            + $additionalHolidayPay['bill_rate']
            + $billTravel;
        $payment->setPayTotal($payTotal);
        $payment->setBillTotal($billTotal);

        $overtimePayment = null;
        if ($overtimeHours > 0) {
            $overtimePayment = ioc::resolve('PayrollPayment');
            $overtimePayment->setType('Overtime');
            $overtimePayment->setClockedHours($overtimeHours);
            $payment->setPaymentStatus('Unpaid');
            $payment->setPaymentMethod($nurse->getPaymentMethod());
            $overtimePayment->setPaymentStatus('Unpaid');
            $overtimePayment->setPaymentMethod($nurse->getPaymentMethod());

            // Overtime Rates
            $overtimePayment->setPayRate($shift->getHourlyOvertimeRate() ?: 0);
            $overtimePayment->setBillRate($shift->getBillingOvertimeRate() ?: 0);

            $overtimePayment->setPayTravel(0);
            $overtimePayment->setBillTravel(0);
            $overtimePayment->setPayBonus(0);
            $overtimePayment->setBillBonus(0);

            // Calculate Total
            $overtimePayTotal = $overtimeHours * $shift->getHourlyOvertimeRate();
            $overtimeBillTotal = $overtimeHours * $shift->getBillingOvertimeRate();
            $overtimePayment->setPayTotal($overtimePayTotal);
            $overtimePayment->setBillTotal($overtimeBillTotal);
        }

        // Status
        $diff = date_diff($shift->getClockOutTime(), $shift->getEnd());
        $minutes = $diff->days * 24 * 60;
        $minutes += $diff->h * 60;
        $minutes += $diff->i;
        if (abs($minutes) < 60) {
            $payment->setStatus('Approved');
            $overtimePayment?->setStatus('Approved');
        } else {
            $payment->setStatus('Unresolved');
            $overtimePayment?->setStatus('Unresolved');
        }
        $payment->setType('Standard');
        $payment->setStatus('Approved');
        $payment->setPaymentStatus('Unpaid');
        $nursePaymentMethod = $nurse->getPaymentMethod() == 'Check' ? 'Paper Check' : $nurse->getPaymentMethod();
        $payment->setPaymentMethod($nursePaymentMethod);

        app::$entityManager->persist($payment);
        $shift->setPayrollPayment($payment);
        if ($overtimePayment) {
            app::$entityManager->persist($overtimePayment);
            $shift->setOvertimePayment($overtimePayment);
        }

        $payment->setShift($shift);
        $overtimePayment?->setShift($shift);

        app::$entityManager->flush();

        $payments = [];
        $payments[] = $payment;
        if ($overtimePayment) {
            $payments[] = $overtimePayment;
        }

        return $payments;
    }

    public function loadPaymentData($data)
    {
        $response = ['success' => false];

        /** @var PayrollPayment $payment */
        $payment = ioc::get('PayrollPayment', ['id' => $data['payment_id']]);
        if ($payment) {
            $shift = $payment->getShift();
            $clockIn = new DateTime($shift->getClockInTime(), $this->timezone);
            $clockOut = new DateTime($shift->getClockOutTime(), $this->timezone);
            $lunchStart = new DateTime($shift->getStart(), $this->timezone);
            $lunchEnd = new DateTime($shift->getEnd(), $this->timezone);

            $response['form'] = [
                'provider_id' => $shift->getProvider()->getId(),
                'nurse_id' => $shift->getNurse()->getId(),
                'shift_date' => $shift->getStart()->format('Y-m-d'),
                'clock_in_string' => $shift->getClockInTime()->format('h:i:s A'),
                'clock_out_string' => $shift->getClockOutTime()->format('h:i:s A'),
                'lunch_start_string' => $shift->getLunchStart()->format('h:i:s A'),
                'lunch_end_string' => $shift->getLunchEnd()->format('h:i:s A'),
                'clock_in' => [
                    'hh' => $clockIn->format('h'),
                    'mm' => $clockIn->format('i'),
                    'ss' => '00',
                    'A' => $clockIn->format('A')
                ],
                'clock_out' => [
                    'hh' => $clockOut->format('h'),
                    'mm' => $clockOut->format('i'),
                    'ss' => '00',
                    'A' => $clockOut->format('A')
                ],
                'lunch_start' => [
                    'hh' => $lunchStart->format('h'),
                    'mm' => $lunchStart->format('i'),
                    'ss' => '00',
                    'A' => $lunchStart->format('A')
                ],
                'lunch_end' => [
                    'hh' => $lunchEnd->format('h'),
                    'mm' => $lunchEnd->format('i'),
                    'ss' => '00',
                    'A' => $lunchEnd->format('A')
                ],
                'pay_rate' => $payment->getPayRate(),
                'bill_rate' => $payment->getBillRate(),
                'pay_bonus' => $payment->getPayBonus(),
                'bill_bonus' => $payment->getBillBonus(),
                'pay_travel' => $payment->getPayTravel(),
                'bill_travel' => $payment->getBillTravel(),
                'pay_travel_checked' => $payment->getPayTravel() > 0,
                'bill_travel_checked' => $payment->getBillTravel() > 0,
                'pay_total' => $payment->getPayTotal(),
                'bill_total' => $payment->getBillTotal()
            ];
        }
        $response['success'] = true;
        return $response;
    }

    public function saveManualPayment($data)
    {
        $response = ['success' => false];

        $form = $data['form'];

        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $form['provider_id']]);
        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $form['nurse_id']]);
        /** @var Shift $shift */
        $shift = ioc::resolve('Shift');


        if ($form['payment_id']) {
            /** @var PayrollPayment $payment */
            $payment = ioc::get('PayrollPayment', ['id' => $form['payment_id']]);
        } else {
            /** @var PayrollPayment $payment */
            $payment = ioc::resolve('PayrollPayment');
            app::$entityManager->persist($payment);
        }


        $clockIn = new DateTime($form['shift_date'] . ' ' . $form['clock_in_string'], $this->timezone);
        $clockOut = new DateTime($form['shift_date'] . ' ' . $form['clock_out_string'], $this->timezone);
        $lunchStart = new DateTime($form['shift_date'] . ' ' . $form['lunch_start_string'], $this->timezone);
        $lunchEnd = new Datetime($form['shift_date'] . ' ' . $form['lunch_end_string'], $this->timezone);

        if ($form['clock_in']['A'] == 'PM') {

            if ($form['clock_in']['hh'] < 12) {
                $clockIn->modify('+12 Hours');
            } else if ($form['clock_in']['hh'] == 12 && $form['clock_in']['mm'] == 0) {
                $clockIn->modify('+12 Hours');
            }
        }
        if ($form['clock_out']['A'] == 'PM') {

            if ($form['clock_out']['hh'] < 12) {
                $clockOut->modify('+12 Hours');
            } else if ($form['clock_out']['hh'] == 12 && $form['clock_out']['mm'] == 0) {
                $clockOut->modify('+12 Hours');
            }
        }

        $shift->setProvider($provider);
        $shift->setNurse($nurse);
        $shift->setStartDate($clockIn);
        $shift->setEndDate($clockIn);
        $shift->setStart($clockIn);
        $shift->setEnd($clockOut);

        if ($clockOut < $clockIn) {
            $clockOut->modify('+1 day');
        }
        if ($lunchStart < $clockIn) {
            $lunchStart->modify('+1 day');
        }
        if ($lunchEnd < $clockIn) {
            $lunchEnd->modify('+1 day');
        }

        $shift->setClockInTime($clockIn);
        $shift->setClockOutTime($clockOut);
        $shift->setStatus('Completed');
        $shift->setLunchOverride($form['lunch_minutes'] ?: 0);
        $shift->setBonusAmount($form['pay_bonus'] ?: 0);
        $shift->setHourlyRate($form['pay_rate'] ?: 0);
        $shift->setBillingRate($form['bill_rate'] ?: 0);
        $shift->setIsNurseApproved(true);
        $shift->setIsProviderApproved(true);
        $shift->setNurseType($nurse->getCredentials());
        app::$entityManager->persist($shift);

        // Calculate Overtime
        $payPeriod = $this->calculatePayPeriodFromDate($clockIn);
        $nurseHours = $this->getHoursForPayPeriodForProvider($nurse, $payPeriod, $provider);
        $overtimeHours = 0;
        $standardHours = $form['clocked_hours'];
        if ($nurseHours >= 40 && $provider->getHasOtPay()) {
            $overtimeHours = $form['clocked_hours'];
            $standardHours = 0;
        } else if ($provider->getHasOtPay()) {
            $difference = 40 - ($nurseHours + $form['clocked_hours']);
            if ($difference < 0) {
                $overtimeHours = -$difference;
                $standardHours = $form['clocked_hours'] - $overtimeHours;
            }
        }
        $standardHours = number_format($standardHours, 2);
        $overtimeHours = number_format($overtimeHours, 2);

        file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/manual_payment_overtime.txt', '-------------BEGIN PAYMENT---------------' . PHP_EOL, FILE_APPEND);
        file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/manual_payment_overtime.txt', 'Creating Standard Payment for ' . $form['clocked_hours'] . ' hours.' . PHP_EOL, FILE_APPEND);
        file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/manual_payment_overtime.txt', 'Total Hours: ' . $nurseHours . PHP_EOL, FILE_APPEND);
        file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/manual_payment_overtime.txt', 'Standard Hours: ' . $standardHours . PHP_EOL, FILE_APPEND);
        file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/manual_payment_overtime.txt', 'Overtime Hours: ' . $overtimeHours . PHP_EOL, FILE_APPEND);


        $payRate = $form['pay_rate'];
        $billRate = $form['bill_rate'];

        $payTotal =
            ($standardHours * $form['pay_rate'])
            + $form['pay_bonus']
            + $form['pay_travel']
            + $form['pay_holiday'];
        $billTotal =
            ($standardHours * $form['bill_rate'])
            + $form['bill_bonus']
            + $form['bill_travel']
            + $form['bill_holiday'];
        file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/manual_payment_overtime.txt', 'Pay Total: ' . $payTotal . PHP_EOL, FILE_APPEND);
        file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/manual_payment_overtime.txt', 'Bill Total: ' . $billTotal . PHP_EOL, FILE_APPEND);

        $payment->setClockedHours($standardHours ?: 0);
        $payment->setPayRate($payRate ?: 0);
        $payment->setPayBonus($form['pay_bonus'] ?: 0);
        $payment->setPayTravel($form['pay_travel'] ?: 0);
        $payment->setPayHoliday($form['pay_holiday'] ?: 0);
        $payment->setPayTotal($payTotal ?: 0);
        $payment->setBillRate($billRate ?: 0);
        $payment->setBillBonus($form['bill_bonus'] ?: 0);
        $payment->setBillTravel($form['bill_travel'] ?: 0);
        $payment->setBillHoliday($form['bill_holiday'] ?: 0);
        $payment->setBillTotal($billTotal ?: 0);
        $payment->setPaymentMethod($form['payment_method']);
        $payment->setStatus('Custom');
        $payment->setPaymentStatus('Unpaid');
        $payment->setType('Standard');
        app::$entityManager->persist($payment);

        $payment->setShift($shift);
        $shift->setPayrollPayment($payment);

        if ($overtimeHours > 0) {
            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/manual_payment_overtime.txt', 'Creating Overtime Payment for ' . $overtimeHours . ' hours' . PHP_EOL, FILE_APPEND);
            /** @var PayrollPayment $overtimePayment */
            $overtimePayment = ioc::resolve('PayrollPayment');
            $overtimePayment->setType('Overtime');
            $overtimePayment->setClockedHours($overtimeHours);
            $overtimePayment->setPaymentMethod($form['payment_method']);
            $overtimePayment->setStatus('Custom');
            $overtimePayment->setPaymentStatus('Unpaid');


            // Overtime Rates
            $payRate *= 1.5;
            $billRate *= 1.5;
            $overtimePayment->setPayRate($payRate ?: 0);
            $overtimePayment->setBillRate($billRate ?: 0);
            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/manual_payment_overtime.txt', 'Overtime Pay Rate: ' . $payRate . PHP_EOL, FILE_APPEND);
            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/manual_payment_overtime.txt', 'Overtime Bill Rate: ' . $billRate . PHP_EOL, FILE_APPEND);

            $overtimePayment->setPayTravel(0);
            $overtimePayment->setBillTravel(0);
            $overtimePayment->setPayBonus(0);
            $overtimePayment->setBillBonus(0);

            // Calculate Total
            $overtimePayTotal = $overtimeHours * $payRate;
            $overtimeBillTotal = $overtimeHours * $billRate;
            $overtimePayment->setPayTotal($overtimePayTotal);
            $overtimePayment->setBillTotal($overtimeBillTotal);

            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/manual_payment_overtime.txt', 'Overtime Pay Total: ' . $overtimePayTotal . PHP_EOL, FILE_APPEND);
            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/manual_payment_overtime.txt', 'Overtime Bill Total: ' . $overtimeBillTotal . PHP_EOL, FILE_APPEND);

            $overtimePayment->setShift($shift);
            app::$entityManager->persist($overtimePayment);
            $shift->setOvertimePayment($overtimePayment);
        }

        file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/manual_payment_overtime.txt', '--------------END PAYMENT----------------' . PHP_EOL, FILE_APPEND);


        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }

    /**
     * @param Shift $shift
     */
    public function initializeShiftRates($shift)
    {
        $provider = $shift->getProvider();
        $nurse = $shift->getNurse();

        /** @var ProviderService $providerService */
        $providerService = new ProviderService();

        $payRate = $providerService->calculatePayRate([
            'shift' => $shift,
            'provider' => $provider,
            'nurse_type' => $nurse?->getCredentials(),
            'rate_type' => 'Standard',
            'is_covid' => $shift->getIsCovid(),
            'incentive' => $shift->getIncentive(),
            'pay_or_bill' => 'Pay'
        ]);
        $billRate = $providerService->calculatePayRate([
            'shift' => $shift,
            'provider' => $provider,
            'nurse_type' => $nurse?->getCredentials(),
            'rate_type' => 'Standard',
            'is_covid' => $shift->getIsCovid(),
            'incentive' => $shift->getIncentive(),
            'pay_or_bill' => 'Bill'
        ]);
        $payOvertimeRate = $providerService->calculatePayRate([
            'shift' => $shift,
            'provider' => $provider,
            'nurse_type' => $nurse?->getCredentials(),
            'rate_type' => 'Overtime',
            'is_covid' => $shift->getIsCovid(),
            'incentive' => $shift->getIncentive(),
            'pay_or_bill' => 'Pay'
        ]);
        $billOvertimeRate = $providerService->calculatePayRate([
            'shift' => $shift,
            'provider' => $provider,
            'nurse_type' => $nurse?->getCredentials(),
            'rate_type' => 'Overtime',
            'is_covid' => $shift->getIsCovid(),
            'incentive' => $shift->getIncentive(),
            'pay_or_bill' => 'Bill'
        ]);

        $shift->setHourlyRate($payRate);
        $shift->setBillingRate($billRate);

        $shift->setHourlyOvertimeRate($payOvertimeRate);
        $shift->setBillingOvertimeRate($billOvertimeRate);
        app::$entityManager->flush();
    }

    public function generateNachaFile($data)
    {
        $response = ['success' => false];

        $nacha = new NachaFile();
        $nacha->setBankRT('042104168')
            ->setCompanyId('27-0728996')
            ->setSettlementAccount('44007028')
            ->setFileID('27-0728996')
            ->setOriginatingBank('WHITAKER BANK')
            ->setFileModifier('A')
            ->setCompanyName('NURSESTAT LLC')
            ->setBatchInfo('Shift Payments')
            ->setDescription('Nurse Payment')
            ->setEntryDate(date('m/d/Y'));

        $payments = explode(',', $data['payments']);
        $nachaPayments = 0;
        foreach ($payments as $paymentInfo) {
            try {
                /** @var PayrollPayment $payment */
                $payment = ioc::get('PayrollPayment', ['id' => $paymentInfo]);
                if (!$payment || $payment->getPaymentMethod() != 'Direct Deposit') continue;

                if ($payment->getShift() != null) {
                    $nurse = $payment->getShift()->getNurse();
                    $firstName = str_replace(' ', '', $nurse->getFirstName());
                    $lastName = str_replace(' ', '', $nurse->getLastName());
                    $lastName = str_replace('\'', '', $lastName);
                    $total = number_format($payment->getPayTotal(), 2);
                    $total = str_replace(',', '', $total);
                    if ($total <= 0) {
                        throw new \Exception('Total is equal to $0.00 for ' . $firstName . ' ' . $lastName);
                    }
                    $nachaPayment = [
                        'AccountNumber' => $nurse->getId(),
                        'TotalAmount' => $total,
                        'BankAccountNumber' => $nurse->getAccountNumber(),
                        'RoutingNumber' => $nurse->getRoutingNumber(),
                        'FormattedName' => $firstName . ' ' . $lastName,
                        'AccountType' => 'CHECKING'
                    ];
                    if (!$this->validateRoutingNumber($nurse->getRoutingNumber())) {
                        throw new \Exception('ROUTING NUMBER INVALID: Unable to send payment of $' . number_format($payment->getPayTotal(), 2) . ' to ' . $firstName . ' ' . $lastName);
                    }
                    if (!$nacha->addCredit($nachaPayment)) {
                        throw new \Exception('Unable to send payment of $' . number_format($payment->getPayTotal(), 2) . ' to ' . $firstName . ' ' . $lastName);
                    }
                    $nachaPayments++;
                    $payment->setPaymentStatus('Pending');
                    app::$entityManager->flush($payment);
                }
            } catch (\Exception $e) {
                $response['messages'][] = $e->getMessage();
            }
        }

        //
        //        $payment1 = [
        //            'AccountNumber' => '123',
        //            'TotalAmount' => 11.22,
        //            'BankAccountNumber' => '61834053',
        //            'RoutingNumber' => '283978425',
        //            'FormattedName' => 'Tanner Doughty',
        //            'AccountType' => 'CHECKING'
        //        ];
        //        if(!$nacha->addDebit($payment1)) {
        //            echo "error 111 ";exit;
        //        }
        //        if(!$nacha->addCredit($payment1)) {
        //            echo "error 112 ";exit;
        //        }
        //        $payment2 = [
        //            'AccountNumber' => '123',
        //            'TotalAmount' => 12.34,
        //            'BankAccountNumber' => '61834053',
        //            'RoutingNumber' => '283978425',
        //            'FormattedName' => 'Tanner Doughty',
        //            'AccountType' => 'CHECKING'
        //        ];
        //        if(!$nacha->addDebit($payment2)) {
        //            echo "error 222 ";exit;
        //        }
        //        if(!$nacha->addCredit($payment2)) {
        //            echo "error 223 ";exit;
        //        }

        try {
            if (!$nachaPayments) {
                throw new \Exception("Unable to create NACHA file");
            }
            $nacha->generateFile();
            $filename = 'TWO_BOTH_NACHA_' . time() . '.txt';
            $path = app::get()->getConfiguration()->get('uploadsDir')->getValue() . '/' . $filename;
            if (!file_put_contents($path, $nacha->fileContents)) {
                throw new \Exception('Unable to save NACHA file');
            } else {
                $folder = 'NachaFiles';
                /** @var saFile $file */
                $file = ioc::resolve('saFile');
                $file->setFolder($folder);
                $file->setDiskFileName($filename);
                $file->setFilename($filename);
                $file->setDateCreated(new \sacore\application\DateTime());
                $file->setCompleteFile(true);
                $file->setFileSize(filesize($path));
                app::$entityManager->persist($file);
                app::$entityManager->flush();

                $route = app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $folder, 'file' => $filename]);
                $response['file_route'] = $route;
                $response['success'] = true;
            }
        } catch (\Exception $e) {
            $response['messages'][] = $e->getMessage();
        }

        return $response;
    }

    /**
     * 
     * NOT USED AS OF NOW - USING CSV INSTEAD
     */
    public function generatePaycardFileXlsx($data)
    {
        $response = ['success' => false];
        
        $filename = 'paycard_upload.xlsx';
        $path = app::get()->getConfiguration()->get('tempDir')->getValue() . DIRECTORY_SEPARATOR . $filename;
        // Check to make sure we dont' have an existing file. 
        if (file_exists($path)) {
            unlink($path);
        }

        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = $this->getSheetForGeneratePaycardFileXlsx($data, $response);
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $route = app::get()->getRouter()->generate('sa_download_paycard_upload_xlsx');
        $response['file_route'] = $route;
        $response['success'] = true;
        return $response;
    }

    /**
     * 
     * NOT USED AS OF NOW - USING CSV INSTEAD
     */
    private function getSheetForGeneratePaycardFileXlsx($data, &$response)
    {
        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->setActiveSheetIndex(0);
        $sheet->setTitle('Paycard export', true);
        $spreadsheet->getActiveSheet()
            ->getStyle('E')
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
        $spreadsheet->getActiveSheet()
            ->getStyle('D')
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
        

        $columnIndex = 1;
        $headers = ['Funding Card ID', 'Funding Card Passcode', 'Reserved', 'Cardholder Account', 'Amount', 'Reserved', 'Reserved', 'Reserved', 'Reference'];
        foreach ($headers as $header) {
            $sheet->getCellByColumnAndRow($columnIndex, 1)->setValue($header);
            $columnIndex++;
        }
        $payments = $this->paymentRepository->findBy(['id' => $data['ids']]);
        $rowIndex = 2;

        /** @var PayrollPayment $payment */
        foreach ($payments as $payment) {
            if (in_array($payment->getPaymentStatus(), ['Paid', 'Corrected'])) {
                continue;
            }

            $nurse = $payment->getNurseFromShift();
            $nursePayCardAccontNumber = $nurse?->getPayCardAccountNumber();
            if (!$nursePayCardAccontNumber) {
                $response['messages'][] = "No Pay Card Account Number for " . $nurse->getFirstName() . ' ' . $nurse->getLastName();
                continue;
            }

            $payment->setPaymentStatus('Pending');
            $response['fundingFileIds'][] = $payment->getId();

            $sheet->getCellByColumnAndRow(1, $rowIndex)->setValue('3806959619');
            $sheet->getCellByColumnAndRow(2, $rowIndex)->setValue('4047');
            $sheet->getCellByColumnAndRow(4, $rowIndex)->setValue($nurse?->getPayCardAccountNumber());
            $sheet->getCellByColumnAndRow(5, $rowIndex)->setValue(number_format($payment?->getPayTotal(), 2));
            $sheet->getCellByColumnAndRow(9, $rowIndex)->setValue('Payroll for ' . $payment?->getShift()?->getStart()?->format('Y-m-d'));
            
            $rowIndex++;
        }
        app::$entityManager->flush();

        return $spreadsheet;
    }    

    public function generatePaycardFileCsv($data)
    {
        $response = ['success' => false];
        
        $filename = 'paycard_upload.csv';
        $path = app::get()->getConfiguration()->get('tempDir')->getValue() . DIRECTORY_SEPARATOR . $filename;
        // Check to make sure we dont' have an existing file. 
        if (file_exists($path)) {
            unlink($path);
        }

        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = $this->getSheetForGeneratePaycardFileCsv($data, $response);
        $writer = new Csv($spreadsheet);
        $writer->save($path);

        $route = app::get()->getRouter()->generate('sa_download_paycard_upload_csv');
        $response['file_route'] = $route;
        $response['success'] = true;
        return $response;
    }
        
    private function getSheetForGeneratePaycardFileCsv($data, &$response)
    {
        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->setActiveSheetIndex(0);
        $sheet->setTitle('Paycard export', true);
        

        $columnIndex = 1;
        $headers = ['Funding Card ID', 'Funding Card Passcode', 'Reserved', 'Cardholder Account', 'Amount', 'Reserved', 'Reserved', 'Reserved', 'Reference'];
        foreach ($headers as $header) {
            $sheet->getCellByColumnAndRow($columnIndex, 1)->setValue($header);
            $columnIndex++;
        }
        $payments = $this->paymentRepository->findBy(['id' => $data['ids']]);
        $rowIndex = 2;

        /** @var PayrollPayment $payment */
        foreach ($payments as $payment) {
            if (in_array($payment->getPaymentStatus(), ['Paid', 'Corrected'])) {
                continue;
            }

            $nurse = $payment->getNurseFromShift();
            $nursePayCardAccontNumber = $nurse?->getPayCardAccountNumber();
            if (!$nursePayCardAccontNumber) {
                $response['messages'][] = "No Pay Card Account Number for " . $nurse->getFirstName() . ' ' . $nurse->getLastName();
                continue;
            }

            $payment->setPaymentStatus('Pending');
            $response['fundingFileIds'][] = $payment->getId();

            $sheet->getCellByColumnAndRow(1, $rowIndex)->setValue('3806959619');
            $sheet->getCellByColumnAndRow(2, $rowIndex)->setValue('4047');
            $sheet->getCellByColumnAndRow(4, $rowIndex)->setValue($nurse?->getPayCardAccountNumber());
            $sheet->getCellByColumnAndRow(5, $rowIndex)->setValue(number_format($payment?->getPayTotal(), 2));
            $sheet->getCellByColumnAndRow(9, $rowIndex)->setValue('Payroll for ' . $payment?->getShift()?->getStart()?->format('Y-m-d'));
            
            $rowIndex++;
        }
        app::$entityManager->flush();

        return $spreadsheet;
    }    


		public function generateCheckrPayCsv($data)
    {
        $response = ['success' => false];
        
        $filename = 'checkr_pay_upload.csv';
        $path = app::get()->getConfiguration()->get('tempDir')->getValue() . DIRECTORY_SEPARATOR . $filename;
        // Check to make sure we dont' have an existing file. 
        if (file_exists($path)) {
            unlink($path);
        }

        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = $this->getSheetForGenerateCheckrPayCsv($data, $response);
        $writer = new Csv($spreadsheet);
        $writer->save($path);

        $route = app::get()->getRouter()->generate('sa_download_checkr_pay_upload_csv');
        $response['file_route'] = $route;
        $response['success'] = true;
        return $response;
    }
        
    private function getSheetForGenerateCheckrPayCsv($data, &$response)
    {
        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->setActiveSheetIndex(0);
        $sheet->setTitle('Checkr Pay Export', true);
        

        $columnIndex = 1;
        $headers = ['workerIdType', 'workerCheckrPayId', 'workerMetadata', 'amountCents', 'description', 'payoutMetadata'];
        foreach ($headers as $header) {
            $sheet->getCellByColumnAndRow($columnIndex, 1)->setValue($header);
            $columnIndex++;
        }
        $payments = $this->paymentRepository->findBy(['id' => $data['ids']]);
        $rowIndex = 2;

        /** @var PayrollPayment $payment */
        foreach ($payments as $payment) {
            if (in_array($payment->getPaymentStatus(), ['Paid', 'Corrected'])) {
                continue;
            }

            $nurse = $payment->getNurseFromShift();
            $nurseCheckrPayId = $nurse?->getCheckrPayId();
            if (!$nurseCheckrPayId) {
                $response['messages'][] = "No Checkr Pay ID for " . $nurse->getFirstName() . ' ' . $nurse->getLastName();
                continue;
            }

            $payment->setPaymentStatus('Pending');
            $response['fundingFileIds'][] = $payment->getId();

            $sheet->getCellByColumnAndRow(1, $rowIndex)->setValue('checkrPayId');
            $sheet->getCellByColumnAndRow(2, $rowIndex)->setValue($nurseCheckrPayId);
            $sheet->getCellByColumnAndRow(4, $rowIndex)->setValue(number_format($payment?->getPayTotal(), 2) * 100); // Multiply by 100 for cents to be paid.
            $sheet->getCellByColumnAndRow(5, $rowIndex)->setValue('Payroll for ' . $payment?->getShift()?->getStart()?->format('Y-m-d'));
            $sheet->getCellByColumnAndRow(6, $rowIndex)->setValue($payment?->getId());
            
            $rowIndex++;
        }
        app::$entityManager->flush();

        return $spreadsheet;
    }    


    public function markAllAsPaid($data)
    {
        $response = ['success' => false];

        $payments = explode(',', $data['payments']);
        foreach ($payments as $paymentId) {
            /** @var PayrollPayment $payment */
            $payment = ioc::get('PayrollPayment', ['id' => $paymentId]);

            if (!$payment) {
                continue;
            }

            $payment->setPaymentStatus('Paid');
            app::$entityManager->flush();
        }

        $response['success'] = true;
        return $response;
    }

    public function findConflictingPayments($data)
    {
        $response = ['success' => false];

        $nurse = ioc::get('Nurse', ['id' => $data['nurse_id']]);
        if ($nurse) {
            $date = new DateTime($data['date'], app::getInstance()->getTimeZone());
            foreach ($nurse->getShifts() as $shift) {
                if (
                    $shift->getStart()->format('Y-m-d') == $date->format('Y-m-d') &&
                    $shift->getClockInTime() && $shift->getClockOutTime()
                ) {
                    $response['has_conflicting_payments'] = true;
                    $response['already_clocked_in_time'] = $shift->getClockInTime()->format('g:i a');
                    $response['already_clocked_out_time'] = $shift->getClockOutTime()->format('g:i a');
                    break;
                }
            }
        }

        $response['success'] = true;
        return $response;
    }

    public function deletePayment($data)
    {
        $response = ['success' => false];

        /** @var PayrollPayment $payment */
        $payment = ioc::get('PayrollPayment', ['id' => $data['id']]);

        if ($payment) {
            $shift = $payment->getShift();

            // If shift has a payroll payment ID remove it
            // payroll_payment field on Shift model is not used anymore
            if ($shift->getPayrollPayment()) {
                $shift->clearPayrollPayment();
                app::$entityManager->flush();
            }
            
            app::$entityManager->remove($shift);
            app::$entityManager->remove($payment);
            app::$entityManager->flush();
            $response['success'] = true;
        } else {
            $response['message'] = 'Cannot find payment with id: ' . $data['id'];
        }

        return $response;
    }

    public function softDeletePayment($data)
    {
        $response = ['success' => false];

        /** @var PayrollPayment $payment */
        $payment = ioc::get('PayrollPayment', ['id' => $data['id']]);

        if ($payment) {
            $payment->setIsDeleted(true);
            $payment->setDateDeleted(new DateTime('now'));
            app::$entityManager->persist($payment);
            app::$entityManager->flush();
            $response['success'] = true;
        } else {
            $response['message'] = 'Cannot find payment with id: ' . $data['id'];
        }

        return $response;
    }

    public function validateRoutingNumber($routingNumber)
    {
        if (!is_numeric($routingNumber) || strlen($routingNumber) != 9) {
            return false;
        }

        $sum = (3 * ((int)substr($routingNumber, -9, 1) + (int)substr($routingNumber, -6, 1) + (int)substr($routingNumber, -3, 1))) +
            (7 * ((int)substr($routingNumber, -8, 1) + (int)substr($routingNumber, -5, 1) + (int)substr($routingNumber, -2, 1))) +
            (1 * ((int)substr($routingNumber, -7, 1) + (int)substr($routingNumber, -4, 1) + (int)substr($routingNumber, -1, 1)));

        $mod = $sum % 10;
        if ($mod == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function fixShiftTimezones()
    {
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', '0');
    }

    public function migratePayments()
    {
        ini_set("memory_limit", "1G");
        $shifts = ioc::getRepository('Shift')->findBy(['status' => 'Completed']);
        $i = 0;
        /** @var Shift $shift */
        foreach ($shifts as $shift) {
            try {
                $i++;
                echo "\n";
                echo "Shift Id: " . $shift->getId() . "\n";;
                $payments = new ArrayCollection();
                if ($payment = $shift->getPayrollPayment()) {
                    echo "--Standard" . "\n";
                    $payments->add($payment);
                    $payment->setShift($shift);
                }
                if ($payment = $shift->getOvertimePayment()) {
                    echo "--Overtime" . "\n";
                    $payments->add($payment);
                    $payment->setShift($shift);
                }
                $shift->setPayrollPayments($payments);
                if ($i % 50 == 0) {
                    app::$entityManager->flush();
                }
            } catch (\Throwable $e) {
                echo "EXCEPTION: " . "\n";
                echo $e->getMessage() . "\n";
                echo $shift->getId() . "\n";
            }
        }
        app::$entityManager->flush();

        echo "finished";
        exit;
    }

    public function createStipendPaymentsFromExcel()
    {
        $response = ['success' => false];

        $path = app::get()->getConfiguration()->get('tempDir') . '/Stipends 4-10.xlsx';

        set_time_limit(7200);

        ini_set('memory_limit', '512M');

        if (!$handle = fopen($path, 'r')) {
            echo "Unable to open excel file\n\r";
            exit;
        }

        /** @var Reader\Xlsx $reader */
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = $reader->load($path);

        $worksheet = $spreadsheet->getActiveSheet();

        echo "Running" . "\n";
        for ($i = 1; $i < 109; $i++) {
            $nurseName = trim($worksheet->getCell('A' . $i)->getFormattedValue());
            $providerName = ($worksheet->getCell('B' . $i)->getFormattedValue());
            $total = (int)$worksheet->getCell('C' . $i)->getFormattedValue();

            //            echo $nurseName . ' - ' . $providerName . ' : ' . $total . "\n";
            $firstName = trim(explode(' ', $nurseName)[0]);
            $lastName = (explode(' ', $nurseName)[1]);

            /** @var Nurse $nurse */
            $nurse = ioc::get('Nurse', ['first_name' => $firstName, 'last_name' => $lastName]);
            if (!$nurse) {
                echo $i . " Cannot find nurse: " . $nurseName . "\n";
                continue;
            }

            $providerMember = ioc::get('saMember', ['company' => $providerName]);
            if (!$providerMember) {
                echo $i . " Cannot find provider member: " . $providerName . "\n";
                continue;
            }

            $provider = $providerMember->getProvider();
            if (!$provider) {
                echo $i . " Cannot find provider: " . $providerName . "\n";
                continue;
            }

            $paymentData = [
                'form' => [
                    'provider_id' => $provider->getId(),
                    'nurse_id' => $nurse->getId(),
                    'pay_bonus' => $total,
                    'bill_bonus' => $total,
                    'shift_date' => '2022-04-10 08:00:00',
                    'clocked_hours' => 0,
                    'pay_travel' => 0,
                    'bill_travel' => 0,
                    'pay_rate' => 0,
                    'bill_rate' => 0,
                    'payment_method' => $nurse->getPaymentMethod()
                ]
            ];
            try {

                $this->saveManualPayment($paymentData);
            } catch (\Throwable $e) {
                echo "EXCEPTION (" . $i . "): ";
                echo $e->getMessage() . "\n";
            }
        }
        echo "finished";
        exit;
    }

    public function getPayStubPDF($data) // Trice said here to reference for PDF function
    {
        try {
            $nurseId = $data['id'];
            /** @var Nurse $nurse */
            $nurse = ioc::get('Nurse', ['id' => $nurseId]);


            $payPeriod = $data['pay_period'];
            $startDate = new DateTime(explode('_', $payPeriod)[0] . ' 00:00:00', app::getInstance()->getTimeZone());
            $endDate = new DateTime(explode('_', $payPeriod)[1] . ' 23:59:59', app::getInstance()->getTimeZone());


            $payments = $this->paymentRepository->getPaymentsBetweenDates(null, $startDate, $endDate, false, null, null, $nurseId);
            //            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/pdf_testing.txt', \Doctrine\Common\Util\Debug::dump($payments, 3) . PHP_EOL, FILE_APPEND);


            $view = new View('nurse_pay_stub_template', PayrollController::viewLocation());
            $view->data['startDate'] = $startDate;
            $view->data['endDate'] = $endDate;
            $view->data['name'] = $nurse->getFirstName() . ' ' . $nurse->getLastName();
            $view->data['credentials'] = $nurse->getCredentials();

            // If payment address is used, then uncomment this. But for right now there's nowhere for them to update it.
            //            $usePaymentAddress = $nurse->getPaymentStreetAddress() && $nurse->getPaymentCity() && $nurse->getPaymentState() && $nurse->getPaymentZipcode();
            //            $hasAddress = $nurse->getCity() && $nurse->getState() && $nurse->getZipcode();
            //            $view->data['address1'] = $usePaymentAddress ? $nurse->getPaymentStreetAddress() : ($nurse->getStreetAddress() ?? '');
            //            $view->data['address2'] = $usePaymentAddress ?
            //                $nurse->getPaymentCity() . ', ' . $nurse->getPaymentState() . ' ' . $nurse->getPaymentZipcode() :
            //                ($hasAddress ? $nurse->getCity() . ', ' . $nurse->getState() . ' ' . $nurse->getZipcode() : '');

            $hasAddress = $nurse->getCity() && $nurse->getState() && $nurse->getZipcode();
            $view->data['address1'] = $nurse->getStreetAddress() ?? '';
            $view->data['address2'] = $hasAddress ? $nurse->getCity() . ', ' . $nurse->getState() . ' ' . $nurse->getZipcode() : '';


            $cipher = "AES-128-CTR";
            $key = $nurse->getMember()->getUsers()[0]->getUserKey();
            $ssn = openssl_decrypt($nurse->getSSN(), $cipher, $key, 0, ord($key));
            $view->data['SSN'] = 'XXX-XX-' . substr($ssn, strlen($ssn) - 4);
            $view->data['phone'] = $nurse->getPhoneNumber();



            $total = 0;
            $totalHours = 0;
            /** @var PayrollPayment $payment */
            foreach ($payments['payments'] as $payment) {
                $shift = $payment->getShift() ?: $payment->getShiftRecurrence();

                if (
                    $payment->getStatus() == 'Unresolved'
                    || !$shift
                    || !$shift->getProvider()
                    || $payment->getPayTotal() <= 0
                ) {

                    continue;
                }
                $providerName = $shift->getProvider()->getMember()->getCompany();

                // Regular payment
                $description = $shift->getStart()->format('m/d/Y') . ' ' . $providerName;
                $description .= $shift->getIsCovid() ? ' Covid ' : ' ';
                $description .= $payment->getType() . ' Rate ';
                if ($shift->getIncentive() > 1) {
                    $description .= ' (*' . $shift->getIncentive() . ' incentive)';
                }
                $times = ' [' . $shift->getClockInTime()->format('g:ia') . ' - ' . $shift->getClockOutTime()->format('g:ia') . ']';

                $view->data['payments'][] = [
                    'type' => 'payment',
                    'hours' => number_format($payment->getClockedHours(), 2),
                    'description' => $description,
                    'times' => $times,
                    'rate' => number_format($payment->getPayRate(), 2) . '/hr',
                    'amount' => number_format($payment->getPayTotal(), 2)
                ];

                // Bonus payment
                if ($payment->getPayBonus()) {
                    $bonusDescription = " - " . $shift->getStart()->format('m/d/Y') . ' ' . $providerName . ' Facility Incentive Bonus';
                    $view->data['payments'][] = [
                        'type' => 'details',
                        'hours' => '',
                        'description' => $bonusDescription,
                        'rate' => number_format($payment->getPayBonus(), 2),
                        'amount' => number_format($payment->getPayBonus(), 2)
                    ];
                }

                // Travel payment
                if ($payment->getPayTravel()) {
                    $travelDescription = " - " . $shift->getStart()->format('m/d/Y') . ' ' . $providerName . ' Standard Rate Travel Pay';
                    $view->data['payments'][] = [
                        'type' => 'details',
                        'hours' => '',
                        'description' => $travelDescription,
                        'rate' => number_format($payment->getPayTravel(), 2),
                        'amount' => number_format($payment->getPayTravel(), 2)
                    ];
                }


                $total += (float)number_format($payment->getPayTotal(), 2, '.', '');
                $totalHours += (float)$payment->getClockedHours();
            }

            $view->data['total'] = number_format($total, 2);
            $view->data['totalHours'] = number_format($totalHours, 2);

            $ytd = $this->paymentRepository->getNurseYTDHoursAndTotal($nurse, $endDate);
            $view->data['ytd_amount'] = number_format($ytd['total'], 2);
            $view->data['ytd_hours'] = number_format($ytd['hours'], 2);

            $pdf = new Pdf($view->getHTML());

            $fileName = 'NursePayStub_' . $nurseId . '_' . time() . '.pdf';
            if (!$pdf->saveAs(app::get()->getConfiguration()->get('tempDir') . '/NursePayStubs/' . $fileName)) {
                throw new \Exception($pdf->getError());
            }

            $response = new File(app::get()->getConfiguration()->get('tempDir') . '/NursePayStubs/' . $fileName);
            $response->setDownloadable(true);
            return $response;
        } catch (\Throwable $e) {
            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/pdf_testing.txt', 'Exception: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            return null;
        }
    }

    public function getPayStubSAPDF($data) // Trice said here to reference for PDF function
    {
        try {
            $nurseId = $data['id'];
            /** @var Nurse $nurse */
            $nurse = ioc::get('Nurse', ['id' => $nurseId]);


            $payPeriod = $data['pay_period'];
            $startDate = new DateTime(explode('_', $payPeriod)[0] . ' 00:00:00', app::getInstance()->getTimeZone());
            $endDate = new DateTime(explode('_', $payPeriod)[1] . ' 23:59:59', app::getInstance()->getTimeZone());


            $payments = $this->paymentRepository->getPaymentsBetweenDates(null, $startDate, $endDate, false, null, null, $nurseId);
            //            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/pdf_testing.txt', \Doctrine\Common\Util\Debug::dump($payments, 3) . PHP_EOL, FILE_APPEND);


            $view = new View('nurse_pay_stub_template', PayrollController::viewLocation());
            $view->data['startDate'] = $startDate;
            $view->data['endDate'] = $endDate;
            $view->data['name'] = $nurse->getFirstName() . ' ' . $nurse->getLastName();
            $view->data['credentials'] = $nurse->getCredentials();

            // If payment address is used, then uncomment this. But for right now there's nowhere for them to update it.
            //            $usePaymentAddress = $nurse->getPaymentStreetAddress() && $nurse->getPaymentCity() && $nurse->getPaymentState() && $nurse->getPaymentZipcode();
            //            $hasAddress = $nurse->getCity() && $nurse->getState() && $nurse->getZipcode();
            //            $view->data['address1'] = $usePaymentAddress ? $nurse->getPaymentStreetAddress() : ($nurse->getStreetAddress() ?? '');
            //            $view->data['address2'] = $usePaymentAddress ?
            //                $nurse->getPaymentCity() . ', ' . $nurse->getPaymentState() . ' ' . $nurse->getPaymentZipcode() :
            //                ($hasAddress ? $nurse->getCity() . ', ' . $nurse->getState() . ' ' . $nurse->getZipcode() : '');

            $hasAddress = $nurse->getCity() && $nurse->getState() && $nurse->getZipcode();
            $view->data['address1'] = $nurse->getStreetAddress() ?? '';
            $view->data['address2'] = $hasAddress ? $nurse->getCity() . ', ' . $nurse->getState() . ' ' . $nurse->getZipcode() : '';


            $cipher = "AES-128-CTR";
            $key = $nurse->getMember()->getUsers()[0]->getUserKey();
            $ssn = openssl_decrypt($nurse->getSSN(), $cipher, $key, 0, ord($key));
            $view->data['SSN'] = 'XXX-XX-' . substr($ssn, strlen($ssn) - 4);
            $view->data['phone'] = $nurse->getPhoneNumber();



            $total = 0;
            $totalHours = 0;
            /** @var PayrollPayment $payment */
            foreach ($payments['payments'] as $payment) {
                $shift = $payment->getShift() ?: $payment->getShiftRecurrence();

                if (
                    $payment->getStatus() == 'Unresolved'
                    || !$shift
                    || !$shift->getProvider()
                    || $payment->getPayTotal() <= 0
                ) {

                    continue;
                }
                $providerName = $shift->getProvider()->getMember()->getCompany();

                // Regular payment
                $description = $shift->getStart()->format('m/d/Y') . ' ' . $providerName;
                $description .= $shift->getIsCovid() ? ' Covid ' : ' ';
                $description .= $payment->getType() . ' Rate ';
                if ($shift->getIncentive() > 1) {
                    $description .= ' (*' . $shift->getIncentive() . ' incentive)';
                }
                $times = ' [' . $shift->getClockInTime()->format('g:ia') . ' - ' . $shift->getClockOutTime()->format('g:ia') . ']';

                $view->data['payments'][] = [
                    'type' => 'payment',
                    'hours' => number_format($payment->getClockedHours(), 2),
                    'description' => $description,
                    'times' => $times,
                    'rate' => number_format($payment->getPayRate(), 2) . '/hr',
                    'amount' => number_format($payment->getPayTotal(), 2)
                ];

                // Bonus payment
                if ($payment->getPayBonus()) {
                    $bonusDescription = " - " . $shift->getStart()->format('m/d/Y') . ' ' . $providerName . ' Facility Incentive Bonus';
                    $view->data['payments'][] = [
                        'type' => 'details',
                        'hours' => '',
                        'description' => $bonusDescription,
                        'rate' => number_format($payment->getPayBonus(), 2),
                        'amount' => number_format($payment->getPayBonus(), 2)
                    ];
                }

                // Travel payment
                if ($payment->getPayTravel()) {
                    $travelDescription = " - " . $shift->getStart()->format('m/d/Y') . ' ' . $providerName . ' Standard Rate Travel Pay';
                    $view->data['payments'][] = [
                        'type' => 'details',
                        'hours' => '',
                        'description' => $travelDescription,
                        'rate' => number_format($payment->getPayTravel(), 2),
                        'amount' => number_format($payment->getPayTravel(), 2)
                    ];
                }


                $total += (float)number_format($payment->getPayTotal(), 2, '.', '');
                $totalHours += (float)$payment->getClockedHours();
            }

            $view->data['total'] = number_format($total, 2);
            $view->data['totalHours'] = number_format($totalHours, 2);

            $ytd = $this->paymentRepository->getNurseYTDHoursAndTotal($nurse, $endDate);
            $view->data['ytd_amount'] = number_format($ytd['total'], 2);
            $view->data['ytd_hours'] = number_format($ytd['hours'], 2);

            $pdf = new Pdf($view->getHTML());

            
            $fileName = 'NursePayStub_' . $nurseId . '_' . time() . '.pdf';
            if (!$pdf->saveAs(app::get()->getConfiguration()->get('uploadsDir') . DIRECTORY_SEPARATOR .  $fileName)) {
                throw new \Exception($pdf->getError());
            }
            $pdf->saveAs(app::get()->getConfiguration()->get('uploadsDir') . DIRECTORY_SEPARATOR . $fileName);
            $filePath = app::get()->getConfiguration()->get('uploadsDir') . DIRECTORY_SEPARATOR . $fileName;

            /** @var saFile $file */
            $file = ioc::resolve('saFile');
            $file->setFolder('uploads');
            $file->setDiskFileName($fileName);
            $file->setFilename($fileName);
            $file->setDateCreated(new \sacore\application\DateTime());
            $file->setCompleteFile(true);
            $file->setFileSize(filesize($filePath));
             app::$entityManager->persist($file);
            app::$entityManager->flush($file);
             $route = app::get()->getRouter()->generate('files_browser_view_file', ['folder' => 'uploads', 'file' => $fileName]);
            $response['file_route'] = $route;
            $response['pdf'] = $filePath;
            $response['success'] = true;
            
            return $response;
        } catch (\Throwable $e) {
            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . '/pdf_testing.txt', 'Exception: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            return null;
        }
    }

    public function getHolidayPay($shift, $overtimeHours)
    {
        $start = $shift->getClockInTime()->format('Y-m-d');
        $startYear = date('Y', strtotime($start));

        $end = $shift->getClockOutTime()->format('Y-m-d');
        $endYear = date('Y', strtotime($end));

        $federalHolidays = [];
        $startHoliday = false;
        $endHoliday = false;
        $additionalHolidayPay['pay_rate'] = 0;
        $additionalHolidayPay['bill_rate'] = 0;

        $startFilePath = app::get()->getConfiguration()->get('tempDir') . '/federalHolidays' . $startYear . '.txt';
        if (!file_exists($startFilePath)) {

            $startHolidaysFile = fopen(app::get()->getConfiguration()->get('tempDir') . '/federalHolidays' . $startYear . '.txt', 'a');

            if ($startHolidaysFile) {
                
                $url = "https://date.nager.at/api/v3/PublicHolidays/{$startYear}/US";
                $response = file_get_contents($url);
                $startHolidays = json_decode($response, true);

                foreach ($startHolidays as $holiday) {

                    if (in_array('Public', $holiday['types'])) {
                        
                        if (
                            $holiday['name'] == "New Year's Day" ||
                            $holiday['name'] == "Memorial Day" ||
                            $holiday['name'] == "Independence Day" ||
                            $holiday['name'] == "Labor Day" ||
                            $holiday['name'] == "Labour Day" || // it sometimes comes up as Labour Day and sometimes its labor day. I don't know why.
                            $holiday['name'] == "Thanksgiving Day" ||
                            $holiday['name'] == "Christmas Day"
                        ) {
                            fwrite($startHolidaysFile, $holiday['date'] . PHP_EOL);
                        }
                    }
                }

                fclose($startHolidaysFile);
            }
                
        }

        if ($startYear != $endYear) {

            $endFilePath = app::get()->getConfiguration()->get('tempDir') . '/federalHolidays' . $endYear . '.txt';

            if (!file_exists($endFilePath)) {
                
                $endHolidaysFile = fopen(app::get()->getConfiguration()->get('tempDir') . '/federalHolidays' . $endYear . '.txt', 'a');

                if ($endHolidaysFile) {
                
                    $url = "https://date.nager.at/api/v3/PublicHolidays/{$endYear}/US";
                    $response = file_get_contents($url);
                    $endHolidays = json_decode($response, true);

                    foreach ($endHolidays as $holiday) {

                        if (in_array('Public', $holiday['types'])) {
                            
                            if (
                                $holiday['name'] == "New Year's Day" ||
                                $holiday['name'] == "Memorial Day" ||
                                $holiday['name'] == "Independence Day" ||
                                $holiday['name'] == "Labor Day" ||
                                $holiday['name'] == "Labour Day" || // it sometimes comes up as Labour Day and sometimes its labor day. I don't know why.
                                $holiday['name'] == "Thanksgiving Day" ||
                                $holiday['name'] == "Christmas Day"
                            ) {
                                fwrite($endHolidaysFile, $holiday['date'] . PHP_EOL);
                            }
                        }
                    }

                    fclose($endHolidaysFile);
                }
            }

            $startHolidaysFile = fopen($startFilePath, 'r');
            if ($startHolidaysFile) {
                while (($line = fgets($startHolidaysFile)) !== false) {
                    array_push($federalHolidays, trim($line));
                }
                fclose($startHolidaysFile);
            } else {
                throw new ValidateException('Could not open file for reading');
            }
            
            $endHolidaysFile = fopen($endFilePath, 'r');
            if ($endHolidaysFile) {
                while (($line = fgets($endHolidaysFile)) !== false) {
                    array_push($federalHolidays, trim($line));
                }
                fclose($endHolidaysFile);
            } else {
                throw new ValidateException('Could not open file for reading');
            }

        } else {

            $startHolidaysFile = fopen($startFilePath, 'r');
            if ($startHolidaysFile) {
                while (($line = fgets($startHolidaysFile)) !== false) {
                    array_push($federalHolidays, trim($line));
                }
                fclose($startHolidaysFile);
            } else {
                throw new ValidateException('Could not open file for reading');
            }
        }

        foreach ($federalHolidays as $holiday) {
            if ($start == $holiday) {
                
                $startHoliday = true;
            }

            if ($end == $holiday) {

                $endHoliday = true;
            }
        }

        // entire shift is on a holiday
        // additional holiday pay is hours that are not overtime * .5 * pay rate
        if ($startHoliday && $endHoliday) {

            $startTime = $shift->getClockInTime();
            $endTime = $shift->getClockOutTime();

            $diff = $endTime->diff($startTime);
            
            $hours = $diff->h;
            $minutes = ($diff->i) / 60;
            $seconds = ($diff->s) / 3600;

            $totalHours = $hours + $minutes + $seconds;

            $holidayHours = $totalHours - $overtimeHours;
            $additionalHolidayPay['pay_rate'] = $holidayHours * $shift->getHourlyRate() * .5;
            $additionalHolidayPay['bill_rate'] = $holidayHours * $shift->getBillingRate() * .5;

        // shift is over two days and the first is a holiday
        // additional holiday pay is hours of the first date - overtime hours over the length of the time clocked in on the second day * .5 * pay rate
        } else if ($startHoliday) {
            
            $startTime = $shift->getClockInTime();
            $endTime = $shift->getClockOutTime();

            $diff = $endTime->diff($startTime);
            $totalHours = $diff->h + ($diff->i / 60) + ($diff->s / 3600);

            $midnight = new DateTime($endTime->format('Y-m-d') . ' 00:00:00');
            $diff1 = $midnight->diff($startTime);
            $diff2 = $endTime->diff(new DateTime($endTime->format('Y-m-d') . ' 00:00:00'));

            $hoursDay1 = $diff1->h + ($diff1->i / 60) + ($diff1->s / 3600);
            $hoursDay2 = $diff2->h + ($diff2->i / 60) + ($diff2->s / 3600);

            if ($overtimeHours > 0) {

                $remainderOvertimeHours = $overtimeHours - $hoursDay2;

                if ($remainderOvertimeHours > 0) {

                    $additionalHolidayPay['pay_rate'] = ($hoursDay1 - $remainderOvertimeHours) * $shift->getHourlyRate() * .5;
                    $additionalHolidayPay['bill_rate'] = ($hoursDay1 - $remainderOvertimeHours) * $shift->getBillingRate() * .5;
                } else {

                    $additionalHolidayPay['pay_rate'] = $hoursDay1 * $shift->getHourlyRate() * .5;
                    $additionalHolidayPay['bill_rate'] = $hoursDay1 * $shift->getBillingRate() * .5;
                }

            } else {

                $additionalHolidayPay['pay_rate'] = $hoursDay1 * $shift->getHourlyRate() * .5;
                $additionalHolidayPay['bill_rate'] = $hoursDay1 * $shift->getBillingRate() * .5;
            }

        // shift is over two days and the second is a holiday
        // additional holiday pay is hours of the second date - overtime hours * .5 * pay rate
        } else if ($endHoliday) {
            
            $startTime = $shift->getClockInTime();
            $endTime = $shift->getClockOutTime();

            $diff = $endTime->diff($startTime);
            $totalHours = $diff->h + ($diff->i / 60) + ($diff->s / 3600);

            $midnight = new DateTime($startTime->format('Y-m-d') . ' 23:59:59');
            $diff1 = $midnight->diff($startTime);
            $diff2 = $endTime->diff(new DateTime($endTime->format('Y-m-d') . ' 00:00:00'));

            $hoursDay1 = $diff1->h + ($diff1->i / 60) + ($diff1->s / 3600);
            $hoursDay2 = $diff2->h + ($diff2->i / 60) + ($diff2->s / 3600);
        
            if ($overtimeHours > 0) {

                $holidayHours = $hoursDay2 - $overtimeHours;
                $holidayHours = $holidayHours < 0 ? 0 : $holidayHours;
                
                $additionalHolidayPay['pay_rate'] = $holidayHours * $shift->getHourlyRate() * .5;
                $additionalHolidayPay['bill_rate'] = $holidayHours * $shift->getBillingRate() * .5;

            } else {

                $additionalHolidayPay['pay_rate'] = $hoursDay2 * $shift->getHourlyRate() * .5;
                $additionalHolidayPay['bill_rate'] = $hoursDay2 * $shift->getBillingRate() * .5;
            }
        }
        
        if ($additionalHolidayPay < 0) {

            $additionalHolidayPay['pay_rate'] = 0;
            $additionalHolidayPay['bill_rate'] = 0;
        }

        $additionalHolidayPay['pay_rate'] = round($additionalHolidayPay['pay_rate'], 2);
        $additionalHolidayPay['bill_rate'] = round($additionalHolidayPay['bill_rate'], 2);
        return $additionalHolidayPay;
    }

    public function getInactiveNurses($data){
        $nurseReturn = ioc::getRepository('Nurse')->getNursesThatDidNotWorkInQuarter($data);
        $returnArray = [];
        foreach($nurseReturn as $nurse){
            $shift = ioc::getRepository('Shift')->getNurseMostRecentShift($nurse->getId());
            $combinedReturn = (object)[
                'fullname' => $nurse->getFirstName() . ' ' . $nurse->getLastName(),
                'nurse' => doctrineUtils::getEntityArray($nurse),
                'shift' => ['start'=>$shift?->getStart()?->format('Y-m-d h:i:s'),
                            'end' =>$shift?->getEnd()?->format('Y-m-d h:i:s')]
            ];
            $returnArray[]= $combinedReturn;
        }

        $response['returnArray'] = $returnArray;
        $response['success'] = true;
        return $response;

    }

    public function getInactiveProviders($data){
        $providers = ioc::getRepository('Provider')->getProvidersThatDidNotWorkInQuarter($data);
        $returnArray = [];
        foreach($providers as $provider){
            $shift = ioc::getRepository('Shift')->getProviderMostRecentShift($provider->getId());
            $combinedReturn = (object)[
            'provider' => doctrineUtils::getEntityArray($provider),
            'company'  => $provider->getMember()->getCompany(),
            'shift' => ['start'=>$shift?->getStart()?->format('Y-m-d h:i:s'),
            'end' =>$shift?->getEnd()?->format('Y-m-d h:i:s')]
            ];
            $returnArray[]= $combinedReturn;
        }
        $response['returnArray'] = $returnArray;
        $response['success'] = true;
        return $response;

    }

    public function getDnrNurseReport($data){
        $nurseReturn = ioc::getRepository('Nurse')->findAll();
        $returnArray = [];
        foreach($nurseReturn as $nurse){
            $providers = $nurse->getBlockedProviders();
            $providerNames = [];
            foreach($providers as $provider){
                $providername = $provider->getMember()->getCompany();
                $providerNames[]= $providername;
            }
            $combinedReturn = (object)[
                'fullname' => $nurse->getFirstName() . ' ' . $nurse->getLastName(),
                'blocked' => doctrineUtils::getEntityCollectionArray($nurse->getBlockedProviders()),
                'blockedNames' => $providerNames,
                'nurse' => doctrineUtils::getEntityArray($nurse)
            ];
            $returnArray[]= $combinedReturn;
        }
        $response['returnArray'] = $returnArray;
        $response['success'] = true;
        return $response;
    }

    public function getDnrProviderReport($data){
        $providerReturn = ioc::getRepository('Provider')->findAll();
        $returnArray = [];
        foreach($providerReturn as $provider){
            $combinedReturn = (object)[
                'blocked' => doctrineUtils::getEntityCollectionArray($provider->getBlockedNurses()),
                'provider' => doctrineUtils::getEntityArray($provider),
                'company'  => $provider->getMember()->getCompany(),

            ];
            $returnArray[]= $combinedReturn;
        }
        $response['returnArray'] = $returnArray;
        $response['success'] = true;
        return $response;
    }

    public function getEarningsReport($data){
        $nurses = ioc::getRepository('Nurse')->getNursesActivePayBetween($data);
        $returnArray = $nurses;
        $response['returnArray'] = $returnArray;
        $response['success'] = true;
        return $response;

    }            

    public function getEarningsReportState($data){
        $nurses = ioc::getRepository('Nurse')->getNursesActivePayBetweenState($data);
        $returnArray = $nurses;
        $response['returnArray'] = $returnArray;
        $response['success'] = true;
        return $response;
    }

    public function getShiftsReport($data)
    {
        $nurses = ioc::getRepository('Shift')->getBackendShiftsBetweenTwoDates($data);
        foreach($nurses as $item){
            $startDateTime = new DateTime($item['start']);
            $item['start']->date1 = $startDateTime->format('Y-m-d H:i:s');
    
            $endDateTime = new DateTime($item['end']);
            $item['end']->date1 = $endDateTime->format('Y-m-d H:i:s');
        }
        $returnArray = $nurses;
        $response['returnArray'] = $returnArray;
        $response['success'] = true;
        return $response;

    }            

    public function getShiftsReportNurse($data)
    {
        $nurses = ioc::getRepository('Shift')->getBackendShiftsBetweenTwoDatesForNurse($data);
        foreach($nurses as $item){
            $startDateTime = new DateTime($item['start']);
            $item['start']->date1 = $startDateTime->format('Y-m-d H:i:s');
    
            $endDateTime = new DateTime($item['end']);
            $item['end']->date1 = $endDateTime->format('Y-m-d H:i:s');
        }
        $returnArray = $nurses;
        $response['returnArray'] = $returnArray;
        $response['success'] = true;
        return $response;
    }

    public function getAllNurseNames()
    {
        $nurses = ioc::getRepository('Nurse')->findAll();
        $returnArray = [];
        foreach($nurses as $nurse){
            $combinedReturn = (object)[
                'fullname' => $nurse->getFirstName() . ' ' . $nurse->getLastName(),
                'id' => $nurse->getId(),
            ];
            $returnArray[]= $combinedReturn;
        }
        $response['returnArray'] = $returnArray;
        $response['success'] = true;
        return $response;
    }

    public function getQuarterlyStartEnd($data)
    {
        $year = $data['year'];
        $quarter = $data['quarter'];

        $date = Carbon::create($year, (substr($quarter, 1, 1) - 1) * 3 + 1, 1);

        $start = $date->copy()->startOfQuarter();
        $end = $date->copy()->endOfQuarter();

        return ['start' => $start, 'end' => $end];
    }

    public function getScheduleReport($data)
    {
        if($data['timeComponent'] == 'Pay Period'){
            $datesArray = explode('_', $data['start']);
            $date1 = DateTime::createFromFormat('Ymd', $datesArray[0]);
            $date2 = DateTime::createFromFormat('Ymd', $datesArray[1]);
            $formattedStart = $date1->format('Y-m-d');
            $formattedEnd = $date2->format('Y-m-d');
            $startDate = $formattedStart;
            $endDate = $formattedEnd;
        } else if($data['timeComponent'] == 'Year and Quarter') {
            $yearAndQuarter = [ 
                'year' => $data['start'],
                'quarter' => $data['end']
            ];
            $dateRange = $this->getQuarterlyStartEnd($yearAndQuarter);
            // $response['daterange'] = $dateRange;
            $date1 = $dateRange['start'];
            $date2 = $dateRange['end'];
            $formattedStart = $date1->format('Y-m-d');
            $formattedEnd = $date2->format('Y-m-d');
            $startDate = $formattedStart;
            $endDate = $formattedEnd;
        } else {
            $startDate = $data['start'];
            $endDate = $data['end'];
        }
        $data['startDate'] = $startDate;
        $data['endDate'] = $endDate;
        // $response['data'] = $data;
        $nurses = ioc::getRepository('Shift')->getBackendScheduleBetweenTwoDates($data);
        foreach($nurses as $item){
            $startDateTime = new DateTime($item['start']);
            $item['start']->date1 = $startDateTime->format('Y-m-d H:i:s');
            $endDateTime = new DateTime($item['end']);
            $item['end']->date1 = $endDateTime->format('Y-m-d H:i:s');
        }
        $returnArray = $nurses;
        $response['returnArray'] = $returnArray;
        $response['success'] = true;
        return $response;
    }
       

    public function getScheduleReportNurse($data)
    {
        if($data['timeComponent'] == 'Pay Period'){
            $datesArray = explode('_', $data['start']);
            $date1 = DateTime::createFromFormat('Ymd', $datesArray[0]);
            $date2 = DateTime::createFromFormat('Ymd', $datesArray[1]);
            $formattedStart = $date1->format('Y-m-d');
            $formattedEnd = $date2->format('Y-m-d');
            $startDate = $formattedStart;
            $endDate = $formattedEnd;
        } else if($data['timeComponent'] == 'Year and Quarter') {
            $yearAndQuarter = [ 
                'year' => $data['start'],
                'quarter' => $data['end']
            ];
            $dateRange = $this->getQuarterlyStartEnd($yearAndQuarter);
            // $response['daterange'] = $dateRange;
            $date1 = $dateRange['start'];
            $date2 = $dateRange['end'];
            $formattedStart = $date1->format('Y-m-d');
            $formattedEnd = $date2->format('Y-m-d');
            $startDate = $formattedStart;
            $endDate = $formattedEnd;
        } else {
            $startDate = $data['start'];
            $endDate = $data['end'];
        }
        $data['startDate'] = $startDate;
        $data['endDate'] = $endDate;
        // $response['data'] = $data;
        $nurses = ioc::getRepository('Shift')->getBackendScheduleBetweenTwoDatesForNurse($data);
        foreach($nurses as $item){
            $startDateTime = new DateTime($item['start']);
            $item['start']->date1 = $startDateTime->format('Y-m-d H:i:s');
            $endDateTime = new DateTime($item['end']);
            $item['end']->date1 = $endDateTime->format('Y-m-d H:i:s');
        }
        $returnArray = $nurses;
        $response['returnArray'] = $returnArray;
        $response['success'] = true;
        return $response;
    }

}


