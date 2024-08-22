<?php
/**
 * Date: 5/2/2017
 *
 * File: ExportFactory.php
 */

namespace sa\member;


use Doctrine\ORM\QueryBuilder;
use sacore\application\app;
use sacore\application\assetView;
use sacore\application\ioc;
use sacore\application\responses\File;
use sacore\application\responses\ResponseHeader;
use sacore\utilities\doctrineUtils;

class ExportFactory
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return File|string
     * @throws \sacore\application\Exception
     */
    public static function getExportFile($queryBuilder) {

        $formheader = null;
        $questions = null;

        $file = app::get()->getConfiguration()->get('tempDir')->getValue().'/members_export'.time().rand(0,9999999).'.csv';
        $fp = fopen($file, 'w');

        $members = $queryBuilder->getQuery()->getResult();

        $header = array();

        /** @var saMember $member */
        foreach($members as $member) {

            $csv = doctrineUtils::getEntityArray($member);
            $other = $csv['other'];

            $csv['is_active'] = $member->getIsActive() ? 'Yes' : 'No';
            $csv['is_pending'] = $member->getIsPending() ? 'Yes' : 'No';
            $csv = array_merge($csv, $other);

            unset( $csv['other'] );

            $header = array_keys($csv);

            fputcsv($fp, $csv);


            $emailArray = array();
            $emailArray[] = '';
            $emailArray[] = '';
            $emailArray[] = 'Email';
            $emailArray[] = 'Type';
            $emailArray[] = 'Is Primary';
            $emailArray[] = 'Member Id';
            fputcsv($fp, $emailArray);

            $emails = $member->getEmails();
            foreach($emails as $k=>$email) {

                $emailArray = array();
                $emailArray[] = '';
                $emailArray[] = 'Email '.($k+1);
                $emailArray[] = $email->getEmail();
                $emailArray[] = $email->getType();
                $emailArray[] = $email->getIsPrimary() ? 'Yes' : 'No';
                $emailArray[] = $member->getId();
                fputcsv($fp, $emailArray);

            }

            $addressArray = array();
            $addressArray[] = '';
            $addressArray[] = '';
            $addressArray[] = 'Street One';
            $addressArray[] = 'Street Two';
            $addressArray[] = 'City';
            $addressArray[] = 'State';
            $addressArray[] = 'Postal Code';
            $addressArray[] = 'Country';
            $addressArray[] = 'Type';
            $addressArray[] = 'Is Primary';
            $addressArray[] = 'Member Id';
            fputcsv($fp, $addressArray);

            $addresses = $member->getAddresses();
            foreach($addresses as $k=>$address) {

                $addressArray = array();
                $addressArray[] = '';
                $addressArray[] = 'Address '.($k+1);
                $addressArray[] = $address->getStreetOne();
                $addressArray[] = $address->getStreetTwo();
                $addressArray[] = $address->getCity();
                $addressArray[] = $address->getState();
                $addressArray[] = $address->getPostalCode();
                $addressArray[] = $address->getCountry();
                $addressArray[] = $address->getType();
                $addressArray[] = $address->getIsPrimary() ? 'Yes' : 'No';
                $addressArray[] = $member->getId();
                fputcsv($fp, $addressArray);

            }

        }

        fclose($fp);

        $header = implode(',', $header);

        $csv = file_get_contents($file);
        file_put_contents($file, $header.PHP_EOL.$csv);

        $name = pathinfo($file, PATHINFO_BASENAME);
        $file = new File(app::get()->getConfiguration()->get('tempDir')->getValue() . DIRECTORY_SEPARATOR . $name);
        $file->setDownloadable($name);

        return $file;
    }
}