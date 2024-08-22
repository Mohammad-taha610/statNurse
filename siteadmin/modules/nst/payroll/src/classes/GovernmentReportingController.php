<?php

namespace nst\payroll;

use sacore\application\controller;
use sacore\application\responses\View;
use sacore\application\app;
use sa\member\auth;

class GovernmentReportingController extends controller
{
    public static function getBatchingData($data)
    {
        $govReportingService = new GovernmentReportingService();

        $response = $govReportingService->getBatchingData($data);

        return $response;
    }

    public static function getReport($data)
    {
        $govReportingService = new GovernmentReportingService();

        $response = $govReportingService->getReport($data);

        return $response;
    }

    public static function generateReport($data)
    {
        try {
            $data['viewLocation'] = static::viewLocation();
            $govReportingService = new GovernmentReportingService();
    
            $response = $govReportingService->generateReport($data);
            return $response;
        } catch (\Throwable $th) {
            return ['success' => false, 'message' => $th->getMessage()];
        }
    }
    
    public static function generateReportBatch($data)
    {
        try {
            $data['viewLocation'] = static::viewLocation();
            $govReportingService = new GovernmentReportingService();
    
            $response = $govReportingService->generateReportBatch($data);
            return $response;
        } catch (\Throwable $th) {
            return ['success' => false, 'message' => $th->getMessage()];
        }
    }

    public static function generateReportZip($data)
    {
        $govReportingService = new GovernmentReportingService();

        $response = $govReportingService->zipQuarterlyNurseShiftsReports($data);
        return $response;
    }

    /**
     * @var \sacore\application\Request $request
     */
    public static function downloadReportZip($request)
    {
        $files = array_diff(scandir(app::get()->getConfiguration()->get('tempDir')), ['.', '..']) ?: false;
        $year = $request->getRouteParams()->get('year');
        $quarter = $request->getRouteParams()->get('quarter');

        $fileName = 'government-quarterly-report-' . $year . '-' . $quarter . '.zip';

        $zipLocation = '';

        foreach ($files as $file) {
            if (basename($file) == $fileName) {
                $zipLocation = $file;
                break;
            }
        }

        $zipPath = app::get()->getConfiguration()->get('tempDir') . DIRECTORY_SEPARATOR . $zipLocation;
        if (file_exists($zipPath)) {
            header('Content-Type: application/zip');
            header("Content-disposition: attachment; filename=$fileName");
            header('Content-Length: ' . (String) filesize($zipPath));
            readfile($zipPath);
            unlink($zipPath);
        }
    }
}
