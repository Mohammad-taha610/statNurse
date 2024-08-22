<?php
/**
 * Date: 5/2/2017
 *
 * File: saMemberExportController.php
 */

namespace sa\member;

use sacore\application\ioc;

class saMemberExportController {

    public function exportAll() {

        /** @var ExportFactory $factory */
        $factory = ioc::get('ExportFactory');

        $qb = ioc::getRepository('saMember')->createQueryBuilder('m');
        return $factory::getExportFile($qb);
    }

}