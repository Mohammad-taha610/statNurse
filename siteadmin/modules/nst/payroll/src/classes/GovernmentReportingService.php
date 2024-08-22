<?php

namespace nst\payroll;

use Carbon\Carbon;
use DateTime;
use DoctrineProxies\__CG__\nst\member\Provider;
use Dompdf\Dompdf;
use Exception;
use nst\events\ShiftRepository;
use nst\member\NstFile;
use nst\member\Nurse;
use nst\member\NurseRepository;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use sacore\application\app;
use sacore\application\controller;
use sacore\application\ioc;
use sacore\application\responses\ResponseUtils;
use sacore\application\responses\View;
use sacore\utilities\doctrineUtils;
use Throwable;
use ZipArchive;
use SplFileInfo;

class GovernmentReportingService
{
    /** @var NurseRepository $nurseRepo */
    protected $nurseRepo;

    /** @var ShiftRepository $nurseRepo */
    protected $shiftRepo;
    
    public function __construct()
    {
        $this->nurseRepo = ioc::getRepository('Nurse');
        $this->shiftRepo = ioc::getRepository('Shift');
    }

    public function getBatchingData($data)
    {
        $response = ['success' => false];
        
        $dateRange = $this->getQuarterlyStartEnd($data);
        $response['count'] = $this->nurseRepo->getNursesThatWorkedInQuarter($dateRange, true);

        $this->clearReportingDirectory($data);

        $response['success'] = true;
        return $response;
    }

    public function clearReportingDirectory($data) {
        $year = $data['year'];
        $quarter = $data['quarter'];
        $yearQuarterDirPath = app::get()->getConfiguration()->get('tempDir') . DIRECTORY_SEPARATOR . 'government_reporting' . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $quarter .  DIRECTORY_SEPARATOR;
        
        // Clear files and folders in selected year & quarter
        $it = new RecursiveDirectoryIterator($yearQuarterDirPath, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator(
            $it,
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
    }

    public function getReport($data)
    {
        $response = ['success' => false];
        $year = $data['year'];
        $quarter = $data['quarter'];

        try {
            $dirPath = app::get()->getConfiguration()->get('tempDir') . DIRECTORY_SEPARATOR . 'government_reporting' . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $quarter .  DIRECTORY_SEPARATOR;
            $response['message'] = $this->reportDirExists($year, $quarter);
            $folders = array_diff(scandir($dirPath), ['.', '..']) ?: false;

            if (!is_array($folders)) {
                return $response;
            }

            foreach ($folders as $folder) {
                if (!is_dir($dirPath . $folder)) {
                    continue;
                }
                $files = array_values(array_diff(scandir($dirPath . $folder), ['.', '..'])) ?: false;
                
                $shiftsReportFile = array_pop(array_filter($files ?: [], function($fileName) {
                    return str_contains($fileName, 'shifts-report.pdf');
                }));

                if (!$shiftsReportFile) {
                    $response['report'][$folder]['actions'][] = "Issue generating nurse shift report";
                }

                $nursingLicenseFile = array_pop(array_filter($files ?: [], function($fileName) {
                    return str_contains($fileName, 'nursing-license');
                }));
                
                if (!$nursingLicenseFile) {
                    $response['report'][$folder]['actions'][] = "Add Nursing License for this nurse";
                }

                $nameAndCredentialArr = explode(' ', $folder);

                $response['report'][$folder]['name'] = str_replace('-', ' ', $nameAndCredentialArr[0]) . ' ' . str_replace('-', ' ', array_reverse($nameAndCredentialArr)[1]);
                $response['report'][$folder]['credential'] = array_reverse($nameAndCredentialArr)[0];    
                $response['report'][$folder]['lastModified'] = Carbon::createFromTimestamp(filemtime($dirPath . $folder))->toDateTimeString();    
                $response['report'][$folder]['shiftsReportFile'] = $shiftsReportFile;
                $response['report'][$folder]['nursingLicenseFile'] = $nursingLicenseFile;
                $response['report'][$folder]['status'] = $nursingLicenseFile && $shiftsReportFile ? 'Ready' : 'Incomplete';
            }
        } catch (Throwable $t) {
            $response['message'] = $t->getMessage();
        }

        $response['success'] = true;

        return $response;
    }

    public function reportDirExists($year, $quarter)
    {
        $response = [];
        $dirPathYear = app::get()->getConfiguration()->get('tempDir') . DIRECTORY_SEPARATOR . 'government_reporting' . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR;
        $dirPathQuarter = $dirPathYear . $quarter .  DIRECTORY_SEPARATOR;

        // TODO: Add some error checking in here
        if (!is_dir($dirPathYear)) {
            $response['year_dir'] = $dirPathYear;
            $response['made_year_dir'] = mkdir($dirPathYear, 0755, true);
        }

        if (!is_dir($dirPathQuarter)) {
            $response['quarter_dir'] = $dirPathQuarter;
            $response['made_quarter_dir'] = mkdir($dirPathQuarter, 0755);
        }

        return $response;
    }

    /**
     * Using generateReportBatch instead now
     * @deprecated
     */
    public function generateReport($data)
    {
        ini_set('memory_limit', '512M');
        ini_set("max_execution_time", "500");
        $response = ['success' => false];

        try {
            $year = $data['year'];
            $quarter = $data['quarter'];
            $dateRange = $this->getQuarterlyStartEnd($data);
            $yearQuarterDirPath = app::get()->getConfiguration()->get('tempDir') . DIRECTORY_SEPARATOR . 'government_reporting' . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $quarter .  DIRECTORY_SEPARATOR;
            $nurses = $this->nurseRepo->getNursesThatWorkedInQuarter($dateRange);

            // Clear files and folders in selected year & quarter
            $it = new RecursiveDirectoryIterator($yearQuarterDirPath, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator(
                $it,
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }

            if (!count($nurses)) {
                $response['errMessage'] = "No nurses worked in this quarter.";
                return $response;
            }

            /** @var NstFile $nursingLicenseFile */
            $nursingLicenseFileTag = ioc::get('NstFileTag', ['name' => 'Nursing License']);

            /** @var Nurse $nurse */
            foreach ($nurses as $nurse) {
                $nurseFolderName = str_replace(' ', '-', trim($nurse->getFirstName())) . ' ' . str_replace(' ', '-', trim($nurse->getLastName())) . ' ' . $nurse->getCredentials();
                
                $nurseFolder = $yearQuarterDirPath . $nurseFolderName . DIRECTORY_SEPARATOR;

                // Create or clear and create file directory for nurse
                if (!is_dir($nurseFolder)) {
                    $success = mkdir($nurseFolder, 0755);
                } else {
                    $dir = $nurseFolder;
                    $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
                    $files = new RecursiveIteratorIterator(
                        $it,
                        RecursiveIteratorIterator::CHILD_FIRST
                    );
                    foreach ($files as $file) {
                        if ($file->isDir()) {
                            rmdir($file->getRealPath());
                        } else {
                            unlink($file->getRealPath());
                        }
                    }
                    rmdir($dir);

                    $success = mkdir($yearQuarterDirPath . $nurseFolderName, 0755);
                }

                $nursingLicenseFile = ioc::get('NstFile', ['nurse' => $nurse->getId(), 'tag' => $nursingLicenseFileTag]);

                if ($nursingLicenseFile) {
                    $nursingLicenseFileName = trim($nurse->getFirstName()) . '-' . trim($nurse->getLastName()) . ' ' . 'nursing-license.' . $nursingLicenseFile->getFileType();
                    $success = copy($nursingLicenseFile->getPath(), $nurseFolder . $nursingLicenseFileName);
                    app::$entityManager->detach($nursingLicenseFile);
                }

                $tmp = $this->generateQuarterlyNurseShiftsReport($data, $nurseFolder, $dateRange, $nurse);
            }
        } catch (Throwable $t) {
            $response['errMessage'] = $t->getMessage();
            $response['errMessageLine'] = $t->getLine();
            $response['errMessageFile'] = $t->getFile();

            return $response;
        }
        $response['success'] = true;
        return $response;
    }
    
    public function generateReportBatch($data)
    {
        ini_set('memory_limit', '512M');
        ini_set("max_execution_time", "500");
        $response = ['success' => false];

        try {
            $year = $data['year'];
            $quarter = $data['quarter'];
            $offset = $data['offset'];
            $limit = $data['limit'];
            $dateRange = $this->getQuarterlyStartEnd($data);
            $yearQuarterDirPath = app::get()->getConfiguration()->get('tempDir') . DIRECTORY_SEPARATOR . 'government_reporting' . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $quarter .  DIRECTORY_SEPARATOR;
            $nurses = $this->nurseRepo->getNursesThatWorkedInQuarter($dateRange, false, $offset, $limit);

            if (count($nurses) < $limit) {
                $response['completed'] = true;
            }

            /** @var NstFile $nursingLicenseFile */
            $nursingLicenseFileTag = ioc::get('NstFileTag', ['name' => 'Nursing License']);

            /** @var Nurse $nurse */
            foreach ($nurses as $nurse) {
                $nurseFolderName = str_replace(' ', '-', trim($nurse->getFirstName())) . ' ' . str_replace(' ', '-', trim($nurse->getLastName())) . ' ' . $nurse->getCredentials();
                
                $nurseFolder = $yearQuarterDirPath . $nurseFolderName . DIRECTORY_SEPARATOR;

                // Create or clear and create file directory for nurse
                $it = null;
                if (!file_exists($nurseFolder)) {
                    $success = mkdir($nurseFolder, 0765);
                } else {
                    $dir = '';
                    $it = 0;
                    do {
                        $it++;
                        $dir = $yearQuarterDirPath . str_replace(' ', '-', trim($nurse->getFirstName())) . ' ' . str_replace(' ', '-', trim($nurse->getLastName())) . "-($it)" . ' ' . $nurse->getCredentials() . DIRECTORY_SEPARATOR;
                    } while(file_exists($dir));
                    $nurseFolder = $dir;
                    $success = mkdir($nurseFolder, 0765);
                }

                $nursingLicenseFile = ioc::get('NstFile', ['nurse' => $nurse->getId(), 'tag' => $nursingLicenseFileTag]);

                if ($nursingLicenseFile) {
                    $nursingLicenseFileName = trim($nurse->getFirstName()) . '-' . trim($nurse->getLastName()) . ' ' . 'nursing-license.' . $nursingLicenseFile->getFileType();
                    $success = copy($nursingLicenseFile->getPath(), $nurseFolder . $nursingLicenseFileName);
                    app::$entityManager->detach($nursingLicenseFile);
                }

                $tmp = $this->generateQuarterlyNurseShiftsReport($data, $nurseFolder, $dateRange, $nurse,  $it);
            }
        } catch (Throwable $t) {
            $response['errMessage'] = $t->getMessage();
            $response['errMessageLine'] = $t->getLine();
            $response['errMessageFile'] = $t->getFile();

            return $response;
        }
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

    public function generateQuarterlyNurseShiftsReport($data, $savePath, $dateRange, $nurse,  $it = null)
    {
        // TODO: optimize performance
        $dompdf = new Dompdf();
        $nurseShifts = $this->shiftRepo->getShiftsNurseWorkedInQuarter($dateRange, $nurse);
        $nurseCredential = $nurse->getCredentials();

        $pdfData['logoFilePath'] = ResponseUtils::filePath('PdfLogo.jpg', GovernmentReportingController::assetLocation('img'));
        $pdfData['nurseName'] = trim($nurse->getFirstName()) . ' ' . trim($nurse->getLastName());
        $pdfData['yearAndQuarter'] = $data['year'] . ' ' . $data['quarter'];
        $pdfData['columnHeaders'] = ['Date', 'Location with address', 'Total hours worked', 'Pay total', 'Bill total', 'Credentials worked as'];
        $pdfData['shiftData'] = [];

        foreach ($nurseShifts as $nurseShift) {
            $payments = $nurseShift->getPayrollPayments();
            $provider = $nurseShift->getProvider();

            if (!$provider) {
                continue;
            }

            foreach ($payments as $payment) {
                if (!$payment->getClockedHours() && !$payment->getPayTotal() && !$payment->getBillTotal()) {
                    continue;
                }
                $isOvertime = ($payment->getType() == 'Overtime') ? "<sup>ot</sup>" : '';
                $row = [];
                $row['date'] = $nurseShift->getStart()->format('Y-m-d');
                $row['location'] = $provider->getCompanyAndAddress();
                $row['hoursTotal'] = (string)(number_format($payment->getClockedHours(), 2, '.', '')) . ' ' . $isOvertime;
                $row['payTotal'] = '$' . number_format($payment->getPayTotal(), 2, '.', '');
                $row['billTotal'] = '$' . number_format($payment->getBillTotal(), 2, '.', '');

                if($nurseCredential != 'CMT') {
                    $credential = $nurseCredential;
                    
                } else {
                    $credentialsArr = explode('/', $nurseShift->getNurseType());
                    
                    if (in_array($nurseCredential, $credentialsArr)) {
                        $credential = $nurseCredential;
                    } else if (in_array('CNA', $credentialsArr)) {
                        $credential = 'CNA';
                    } else {
                        throw new Exception('Cannot determine credential worked');
                    }
                }

                $row['credentials'] = $credential;
                $pdfData['shiftData'][] = $row;
                
            }
            
            app::$entityManager->detach($payments);
            app::$entityManager->detach($provider);
            app::$entityManager->detach($nurse);
            app::$entityManager->detach($nurseShift);
        }

        app::$entityManager->clear();

        // Generate PDF with html data
        $view = new View('government_nurse_shifts_report', $data['viewLocation']);
        $view->data = $pdfData; // to be filled in with shift data
        $dompdf->loadHtml($view->getHTML());
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $output = $dompdf->output();
        $nurseShiftsReportFileName = str_replace(' ', '-', strtolower(trim($nurse->getFirstName()) . '-' . trim($nurse->getLastName()))) . '-' . 'shifts-report.pdf';
        if ($it) {
            $nurseShiftsReportFileName = str_replace(' ', '-', strtolower(trim($nurse->getFirstName()) . '-' . trim($nurse->getLastName()))) . "-($it)" . '-' . 'shifts-report.pdf';
        }
        file_put_contents($savePath . $nurseShiftsReportFileName, $output);
    }

    public function zipQuarterlyNurseShiftsReports($data)
    {
        // TODO: optimize performance
        $response = ['success' => false];
        $year = $data['year'];
        $quarter = $data['quarter'];
        $yearQuarterDirPath = app::get()->getConfiguration()->get('tempDir') . DIRECTORY_SEPARATOR . 'government_reporting' . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $quarter .  DIRECTORY_SEPARATOR;

        
        $zipLocation = app::get()->getConfiguration()->get('tempDir') . DIRECTORY_SEPARATOR . "government-quarterly-report-" . $year . "-" . $quarter . ".zip";

        // check if zipLocation file exists and delete it
        if (file_exists($zipLocation)) {
            unlink($zipLocation);
        }

        if (is_dir($yearQuarterDirPath)) {
            $rootPath = realpath($yearQuarterDirPath);

            $zip = new ZipArchive();
            $zip->open($zipLocation, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            
            // Create recursive directory iterator
            /** @var SplFileInfo[] $files */
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($rootPath),
                RecursiveIteratorIterator::LEAVES_ONLY);

            // zip all files and folders in a directory
            foreach ($files as $name => $file) {
                // Skip directories (they would be added automatically)
                if (!$file->isDir()) {
                    // Get real and relative path for current file
                    $filePath = $file->getRealPath();
                    
                    // Add current file to archive
                    // This is to add the same folders for directory separation - COMMENTED OUT RIGHT NOW, MAY BE NEEDED FOR A SETTING LATER
                    // $relativePath = substr($filePath, strlen($rootPath) + 1); 
                    // $zip->addFile($filePath, $relativePath);

                    // This is to add the file without directory separation so all files are lumped together for easy printing
                    $zip->addFile($filePath, $file->getFilename());
                }
            }
            $zip->close();
        }

        $response['fileRoute'] = app::get()->getRouter()->generate('sa_payroll_download_governement_report_zip', ['year' => $year, 'quarter' => $quarter]);
        $response['success'] = true;
        return $response;
    }
}
