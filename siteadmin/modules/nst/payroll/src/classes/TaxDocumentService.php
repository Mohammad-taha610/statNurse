<?php

namespace nst\payroll;

use sacore\application\app;
use sacore\application\responses\File;
use sacore\application\ioc;

class TaxDocumentService
{
    /**
     * This method is meant to be sent batches througha  modrequest to build the CSV file incrimentally
     * @param mixed $data
     * @return File|string
     * @throws \sacore\application\Exception
     */
    public function gen1099Csv($data) {
        $file = app::get()->getConfiguration()->get('tempDir')->getValue().'/1099_export.csv';

        // Check if this is the start of building the CSV and make sure a file does not already exist
        if ($data['counter'] == 0 && file_exists($file)) {
            unlink($file);
            $fp = fopen($file, 'w');
        } else {
            $fp = fopen($file, 'a');
        }

        // create headers for columns
        $headings = array(
              'Payer Type' //a
            , 'Payer TIN Type' //b
            , 'Payer TIN' //c
            , 'P Business Name or Last Name' //d
            , 'P First Name' //e
            , 'P Middle Name' //f
            , 'P Suffix' //g
            , 'P Disregarded Entity' //h
            , 'P Address 1' //i
            , 'P Address 2 (Optional)' //j
            , 'P City' //k
            , 'P State' //l
            , 'P ZIP or Foreign Postal Code' //m
            , 'P Country' //n
            , 'P Phone Number' //o
            , 'P Email Address (optional)' //p
            , 'P State id (optional)' //q
            , 'Recipient Attention To (Optional)' //r
            , 'Recipient Type' //s
            , 'Recipient TIN Type' //t
            , 'Recipient TIN' //u
            , 'R Business Name or Last Name' //v
            , 'R First Name' //w
            , 'R Middle Name' //x
            , 'R Suffix' //y
            , 'R Address 1' //z
            , 'R Address 2 (Optional)' //aa
            , 'R City' //ab
            , 'R State' //ac
            , 'R ZIP or Foreign Postal Code' //ad
            , 'R Country' //ae
            , 'R Phone Number (Optional)' //af
            , 'R Email Address (optional)' //ag
            , 'Acct No (optional)' //ah
            , 'Box 1 Nonemployee Compensation' //ai
            , 'Box 2 Payer made direct sales totaling $5,000 or more' //aj
            , 'Box 4 Fed Income Tax withheld' //ak
            , 'Box 5a State Tax withheld' //al
            , 'Box 5b State Tax Withheld' //am
            , 'Box 6a State' //an
            , 'Box 6a State No' //ao
            , 'Box 6b State' //ap
            , 'Box 6b State No' //aq
            , 'Box 7a State Income' //ar
            , 'Box 7b State Income' //as
            , 'Client ID' //at
            , 'Exclude State Filing' //au
            , 'Recipient ClientId' //av
            , 'Group ID' //aw
        );

        if ($data['counter'] == 0 ) {
            fputcsv($fp, $headings);
        }
        
        $nurses = $data['nurses'];

        // Static Nursestat business data
        $payerBusinessName = "NurseStat LLC";
        $payerAddressOne = "226 Morris Drive";
        $payerCity = "Harrodsburg";
        $payerState = "KY";
        $payerZip = "40330";
        $payerPhone = "859-748-9600";
        $payerTin = "27-0728996";

        // Loop through each nurse in the batch
        // Retrieve necessary information and fill out information per row and append to csv file
        for ($i = 0; $i < count($nurses); $i++) {
            $nurseInfo = ioc::getRepository('Nurse')->getNurse1099Info($nurses[$i]['id']);
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
            $data1099['compensation'] = $nurses[$i]['total_comp'];
            
            $nurseSSN = $nurseInfo['ssn'];
            $key = $nurseInfo['user_key'];
            $cipher = "AES-128-CTR";
            if (!is_string($key)) {
                continue;
            }

            // decrypt only if alphabet letters detected
            if(preg_match("/[a-z]/i", $nurseSSN)){
                do {
                    $nurseSSN = openssl_decrypt($nurseSSN, $cipher, (string)$key, 0, ord($key));
                } while (strlen($nurseSSN) > 11);
            }
            
            // insert dashes to split apart number
            if (!str_contains($nurseSSN, "-")) {
                $first3 = substr($nurseSSN, 0, 3);
                $middle2 = substr($nurseSSN, 3, 2);
                $last4 = substr($nurseSSN, 5, 4);

                $nurseSSN = $first3 ."-". $middle2 ."-". $last4;
            }
            $nurseStreetAddAndAptNum = $data1099['street_and_apt'];
            $compensation = $data1099['compensation'];

        
            $row = [
                'Business' //Payer Type REQUIRED
              , 'EIN' //Payer TIN Type REQUIRED
              , $payerTin //Payer TIN
              , $payerBusinessName //P Business Name or Last Name
              , '' //P First Name
              , '' //P Middle Name
              , '' //P Suffix
              , '' //P Disregarded Entity
              , $payerAddressOne //P Address 1
              , '' //P Address 2 (Optional)
              , $payerCity //P City
              , $payerState //P State
              , $payerZip //P ZIP or Foreign Postal Code
              , 'USA' //P Country
              , $payerPhone //P Phone Number
              , '' //P Email Address (optional)
              , '' //P State id (optional)
              , '' //Recipient Attention To (Optional)
              , 'Individual' //Recipient Type REQUIRED
              , 'SSN' //Recipient TIN Type REQUIRED 
              , $nurseSSN //Recipient TIN
              , $nurseInfo['last_name'] //R Business Name or Last Name
              , $nurseInfo['first_name'] //R First Name
              , $nurseInfo['middle_name'] //R Middle Name
              , '' //R Suffix
              , $nurseStreetAddAndAptNum //R Address 1
              , '' //R Address 2 (Optional)
              , $nurseInfo['city'] //R City
              , $nurseInfo['state'] //R State
              , $nurseInfo['zipcode'] //R ZIP or Foreign Postal Code
              , 'USA' //R Country
              , '' //R Phone Number (Optional)
              , '' //R Email Address (optional)
              , '' //Acct No (optional)
              , $compensation //Box 1 Nonemployee Compensation
              , '' //Box 2 Payer made direct sales totaling $5,000 or more
              , '' //Box 4 Fed Income Tax withheld
              , '' //Box 5a State Tax withheld
              , '' //Box 5b State Tax Withheld
              , '' //Box 6a State
              , '' //Box 6a State No
              , '' //Box 6b State
              , '' //Box 6b State No
              , '' //Box 7a State Income
              , '' //Box 7b State Income
              , '' //Client ID
              , '' //Exclude State Filing
              , '' //Recipient ClientId
              , '' //Group ID
            ];
            
            fputcsv($fp, $row);
        }

        fclose($fp);

        return;
    }
}