<?php

namespace nst\quickbooks;

use nst\events\Shift;
use nst\member\Nurse;
use nst\member\Provider;
use nst\payroll\Invoice;
use nst\payroll\PayrollPayment;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Bill;
use QuickBooksOnline\API\Facades\BillPayment;
use QuickBooksOnline\API\Facades\Purchase;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Vendor;
use sacore\application\app;
use sacore\application\ioc;
use sa\files\saFile;
use sa\member\saMemberAddress;

class QuickbooksService
{

    public static function generateQuickbooksInvoice($data) {
        $displayName = $data['display_name'];
        $realmId = $data['realmId'];
        $code = $data['code'];
        $id = $data['id'];
        $response = ['success' => false];
        $response['type'] = 'success';

        $dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => app::get()->getConfiguration()->get('quickbooks_client_id')->getValue(),
            'ClientSecret' => app::get()->getConfiguration()->get('quickbooks_client_secret')->getValue(),
            'RedirectURI' => $data['redirect_uri'],
            'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
            'baseUrl' => app::get()->getConfiguration()->get('quickbooks_base_url')->getValue()
        ]);

        $dataService->throwExceptionOnError(true);
        $oauthLoginHelper = $dataService->getOAuth2LoginHelper();

        $accessToken = $oauthLoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
        $dataService->updateOAuth2Token($accessToken);
        $dataService->setLogLocation(app::get()->getConfiguration()->get('tempDir')->getValue() . '/QuickbooksLogs');

        $customers = $dataService->Query("SELECT * FROM Customer WHERE DisplayName = '" . $displayName . "'");
        $customer = $customers[0];

        if(!$customer) {
            $customer = Customer::create([
                'DisplayName' => $displayName
            ]);
            $customer = $dataService->Add($customer);
        }

        $customerId = $customer->Id;
        /** @var QuickbooksInvoice $qbInvoice */
        $qbInvoice = ioc::get('QuickbooksInvoice', ['id' => $id]);
        if($qbInvoice->getQuickbooksId()) {
            $invoices = $dataService->Query("SELECT * FROM invoice WHERE id = '".$qbInvoice->getQuickbooksId()."'");
            $invoice = $invoices[0];

            $folder = $qbInvoice->getInvoiceFile()->getFolder();
            $filename = $qbInvoice->getInvoiceFile()->getFilename();
        } else {
            $invoice = static::convertInvoice($qbInvoice, $customerId);
            $invoice = $dataService->Add($invoice);
            $pdfFolder = app::get()->getConfiguration()->get('uploadsDir');
            if(!is_dir($pdfFolder)) {
                mkdir($pdfFolder, 0777, true);
            }
            $path = $dataService->DownloadPDF($invoice, $pdfFolder);

            $pathArr = explode('/', $path);
            $filename = $pathArr[count($pathArr) - 1];

            $folder = 'QuickbooksInvoices';
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
            $qbInvoice->setInvoiceFile($file);
            $qbInvoice->setQuickbooksId($invoice->Id);
            app::$entityManager->flush($qbInvoice);
        }
        if($qbInvoice->getStatus() != "Review") {
            foreach ($qbInvoice->getEmails() as $email) {
                $dataService->SendEmail($invoice, $email);
            }
        }

        $error = $dataService->getLastError();

        $route = app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $folder, 'file' => $filename]);
        $response['file_route'] = $route;

        $response['pdf'] = $path;
        if($error) {
            // echo "ERROR: \n<br>";
            // echo "<pre>" . \Doctrine\Common\Util\Debug::dump($error, 3) . "</pre>"; exit;
            $response['message'] = $error->getIntuitErrorDetail();
            return $response;
        }

        $qbInvoice->setQuickbooksId($invoice->Id);
        $qbInvoice->setTotal($invoice->TotalAmt);
        app::$entityManager->flush($qbInvoice);

        $response['success'] = true;
        $response['message'] = 'Successfully generated invoice';
        return $response;
    }

    public static function runTest($data) {
        $response = ['success' => false];
        $code = $data['code'];
        $realmId = $data['realmId'];

        $dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => app::get()->getConfiguration()->get('quickbooks_client_id')->getValue(),
            'ClientSecret' => app::get()->getConfiguration()->get('quickbooks_client_secret')->getValue(),
            'RedirectURI' => 'https://portal.nursestatky.com/siteadmin/quickbooks/test',
            'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
            'baseUrl' => app::get()->getConfiguration()->get('quickbooks_base_url')->getValue()
        ]);

        $dataService->throwExceptionOnError(true);

        $oauthLoginHelper = $dataService->getOAuth2LoginHelper();

        $response['type'] = 'success';
        $accessToken = $oauthLoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
        $dataService->updateOAuth2Token($accessToken);
        $dataService->setLogLocation(app::get()->getConfiguration()->get('tempDir')->getValue() . '/QuickbooksLogs');

        $customers = $dataService->Query("SELECT * FROM Customer WHERE DisplayName = 'Kelvin Test'");
        $customer = $customers[0];

        if(!$customer) {
            $customer = Customer::create([
                'DisplayName' => 'Kelvin Test'
            ]);
            $customerRef = $dataService->Add($customer);
        }

        $invoice = ioc::get('QuickbooksInvoice', ['id' => 7]);

        $qbInvoice = static::convertInvoice($invoice, 58);
//        $invoice = \QuickBooksOnline\API\Facades\Invoice::create([
//            'Line' => [
//                [
//                    'DetailType' => 'SalesItemLineDetail',
//                    'Amount' => 100.0,
//                    'SalesItemLineDetail' => [
//                        'ItemRef' => [
//                            'name' => 'Services',
//                            'value' => '1'
//                        ]
//                    ]
//                ]
//            ],
//            'CustomerRef' => [
//                'value' =>1
//            ],
//
//        ]);

        $result = $dataService->Add($qbInvoice);
        $error = $dataService->getLastError();

        if($error) {
            echo 'Error' . "<br>\n";
            echo "<pre>" . \Doctrine\Common\Util\Debug::dump($error, 3) . "</pre>"; exit;
        } else {

            echo "Created Id={$result->Id}. Reconstructed response body:\n\n";
            $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($result, $urlResource);
            echo $xmlBody . "<br>\n";
            echo "<pre>" . \Doctrine\Common\Util\Debug::dump($urlResource, 3) . "</pre>"; exit;
        }
        exit;
        $response['success'] = true;
        $response['message'] = 'test message';
        return $response;
    }

    public function getAuthRoute($data) {

        $response = ['success' => false];

        $dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => app::get()->getConfiguration()->get('quickbooks_client_id')->getValue(),
            'ClientSecret' => app::get()->getConfiguration()->get('quickbooks_client_secret')->getValue(),
            'RedirectURI' => $data['redirect_uri'],
            'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
            'baseUrl' => app::get()->getConfiguration()->get('quickbooks_base_url')->getValue()
        ]);

        $dataService->throwExceptionOnError(true);

        $oauthLoginHelper = $dataService->getOAuth2LoginHelper();

        $tokenUrl = $oauthLoginHelper->getAuthorizationCodeURL();
        $response['url'] = $tokenUrl;
        $response['type'] = 'url';
        $response['success'] = true;
        return $response;
    }

    /**
     * @param QuickbooksInvoice $invoiceObj
     * @throws \Exception
     */
    public static function convertInvoice($invoiceObj, $customerId) {

        $invoiceArray = [];
        /** @var QuickbooksLine $line */
        foreach($invoiceObj->getLines() as $line) {
            $invoiceArray['Line'][] = [
                'DetailType' => $line->getDetailType(),
                'Amount' => $line->getAmount(),
                'Description' => $line->getDescription(),
                'SalesItemLineDetail' => $line->getLineDetail(),
            ];
        }

        $invoiceArray['EmailStatus'] = 'NotSet';
        $emails = $invoiceObj->getEmails();
        if(is_array($emails)) {
            $invoiceArray['BillEmail']['Address'] = $emails[0] ?: '';
            $invoiceArray['BillEmailBcc']['Address'] = $emails[1] ?: '';
        }

        $invoiceArray['CustomerRef'] = [
            'value' => $customerId
        ];
        $invoiceArray['DocNumber'] = $invoiceObj->getInvoiceNumber();

        $invoice = \QuickBooksOnline\API\Facades\Invoice::create($invoiceArray);

        return $invoice;
    }

    public function getExportVendorsRoute($data) {
        $response = ['success' => false];

        $redirectData = [
            'redirect_uri' => app::get()->getConfiguration()->get('quickbooks_site_url') . app::get()->getRouter()->generate('export_vendors_confirmation')
        ];
        $authData = static::getAuthRoute($redirectData);
        $response['auth_url'] = $authData['url'];

        $response['success'] = true;
        return $response;
    }

    public function getExportCustomersRoute($data) {
        $response = ['success' => false];

        $redirectData = [
            'redirect_uri' => app::get()->getConfiguration()->get('quickbooks_site_url') . app::get()->getRouter()->generate('export_customers_confirmation')
        ];
        $authData = static::getAuthRoute($redirectData);
        $response['auth_url'] = $authData['url'];

        $response['success'] = true;
        return $response;
    }

    public function exportVendors($data) {
        $code = $data['code'];
        $realmId = $data['realmId'];
        $batch = $data['batch'];
        $batchSize = 10;
        $response = ['success' => false];
        $response['type'] = 'success';
        $response['messages'] = [];

        $dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => app::get()->getConfiguration()->get('quickbooks_client_id')->getValue(),
            'ClientSecret' => app::get()->getConfiguration()->get('quickbooks_client_secret')->getValue(),
            'RedirectURI' => app::get()->getConfiguration()->get('quickbooks_site_url') . app::get()->getRouter()->generate('export_vendors_confirmation'),
            'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
            'baseUrl' => app::get()->getConfiguration()->get('quickbooks_base_url')->getValue()
        ]);

        $dataService->throwExceptionOnError(true);
        $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
        $accessToken = $_SESSION['token'];

        // Check if token exists or if it has been over 30 minutes since using it
        $time = $_SESSION['time'] ?: time();
        $timeDiff = (time() - $time) / 60;
        if(!$_SESSION['token'] || $timeDiff > 30) {
            $accessToken = $oauthLoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
            $_SESSION['token'] = $accessToken;
            $_SESSION['time'] = time();
        }
        $dataService->updateOAuth2Token($accessToken);
        $dataService->setLogLocation(app::get()->getConfiguration()->get('tempDir')->getValue() . '/QuickbooksLogs');

        $nurseRepo = ioc::getRepository('Nurse');
        $nurseCount = $nurseRepo->search(null, null, null, null, true);
        $nurses = $nurseRepo->search(null, null, $batchSize, $batch * $batchSize);

        /** @var Nurse $nurse */
        foreach($nurses as $nurse) {
            try {
                $qbVendors = null;
                $firstName = str_replace(' ', '', $nurse->getMember()->getFirstName());
                $firstName = str_replace('\'', '', $firstName);
                $lastName = str_replace(' ', '', $nurse->getMember()->getLastName());
                $lastName = str_replace('\'', '', $lastName);
                $name = $firstName . ' ' . $lastName;

//                $qbVendors = $dataService->Query("Select * from Vendor where DisplayName = '" . $name . "'");
                if($nurse->getQuickbooksVendorId()) {
                    $qbVendors = $dataService->Query("Select * from Vendor where Id = '" . $nurse->getQuickbooksVendorId() . "'");
                }
                $vendorData = static::convertNurseToVendor($nurse);

                if ($qbVendors) {
                    $qbVendor = reset($qbVendors);
                    $vendorData['sparse'] = true;
                    $vendor = Vendor::update($qbVendor, $vendorData);
                    $resultingObj = $dataService->Update($vendor);
                } else {
                    $vendor = Vendor::create($vendorData);
                    $resultingObj = $dataService->Add($vendor);
                }
                $error = $dataService->getLastError();
                if ($error) {
                    $response['messages'][] = 'ERROR FOR NURSE [' . $nurse->getId() . ']: ' . $name;
                    $response['messages'][] = "---The Status code is: " . $error->getHttpStatusCode();
                    $response['messages'][] = "---The Helper message is: " . $error->getOAuthHelperError();
                    $response['messages'][] = "---The Response message is: " . $error->getResponseBody();
                } else {
                    $nurse->setQuickbooksVendorId($resultingObj->Id);
                    $response['messages'][] = 'Exported [' . $nurse->getId() . ']: ' . $name;
                }
            } catch(\Throwable $e) {
                $response['messages'][] = 'EXCEPTION: ' . $e->getMessage();
            }
        }

        app::$entityManager->flush();
        $response['export_completion_percent'] = number_format(((($batch * $batchSize) + $batchSize) / $nurseCount) * 100, 2);
        $response['total_exported'] = ($batch * $batchSize) + count($nurses);
        $response['finished'] = count($nurses) < $batchSize;
        $response['success'] = true;
        return $response;
    }

    public function exportCustomers($data) {
        $code = $data['code'];
        $realmId = $data['realmId'];
        $batch = $data['batch'];
        $batchSize = 10;
        $response = ['success' => false];
        $response['type'] = 'success';
        $response['messages'] = [];

        $dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => app::get()->getConfiguration()->get('quickbooks_client_id')->getValue(),
            'ClientSecret' => app::get()->getConfiguration()->get('quickbooks_client_secret')->getValue(),
            'RedirectURI' => app::get()->getConfiguration()->get('quickbooks_site_url') . app::get()->getRouter()->generate('export_customers_confirmation'),
            'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
            'baseUrl' => app::get()->getConfiguration()->get('quickbooks_base_url')->getValue()
        ]);

        $dataService->throwExceptionOnError(true);
        $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
        $accessToken = $_SESSION['token'];

        // Check if token exists or if it has been over 30 minutes since using it
        //TODO: move this logic into a generic getAuthToken method
        $time = $_SESSION['time'] ?: time();
        $timeDiff = (time() - $time) / 60;
        if(!$_SESSION['token'] || $timeDiff > 30) {
            $accessToken = $oauthLoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
            $_SESSION['token'] = $accessToken;
            $_SESSION['time'] = time();
        }
        $dataService->updateOAuth2Token($accessToken);
        $dataService->setLogLocation(app::get()->getConfiguration()->get('tempDir')->getValue() . '/QuickbooksLogs');

        //Get provider count, and current batch of providers
        $providerRepo = ioc::getRepository('Provider');
        $providerCount = $providerRepo->search(null, null, null, null, true);
        $providers = $providerRepo->search(null, null, $batchSize, $batch * $batchSize);

        //Run worker loop
        /** @var Provider $provider */
        foreach($providers as $provider) {
            try {
                $qbCustomers = null;
//                $qbCustomers = $dataService->Query("Select * from Customer where DisplayName = '" . $provider->getMember()->getCompany() . "'");
                if($provider->getQuickbooksCustomerId()) {
                    $qbCustomers = $dataService->Query("Select * from Customer where Id = '" . $provider->getQuickbooksCustomerId() . "'");
                }
                $customerData = self::convertProviderToCustomer($provider);

                if ($qbCustomers) {
                    $qbCustomer = reset($qbCustomers);
                    //Setting sparse to update only the fields included
                    $customerData['sparse'] = true;
                    $customer = Customer::update($qbCustomer, $customerData);
                    $resultingObj = $dataService->Update($customer);
                } else {
                    $customer = Customer::create($customerData);
                    $resultingObj = $dataService->Add($customer);
                }
                $error = $dataService->getLastError();
                if ($error) {
                    $response['messages'][] = 'ERROR FOR PROVIDER [' . $provider->getId() . ']: ' . $provider->getMember()->getCompany();
                    $response['messages'][] = "---The Status code is: " . $error->getHttpStatusCode();
                    $response['messages'][] = "---The Helper message is: " . $error->getOAuthHelperError();
                    $response['messages'][] = "---The Response message is: " . $error->getResponseBody();
                } else {
                    $provider->setQuickbooksCustomerId($resultingObj->Id);
                    $response['messages'][] = 'Exported [' . $provider->getId() . ']: ' . $provider->getMember()->getCompany() . "<br>\n";
                }
            } catch(\Throwable $e) {
                $response['messages'][] = 'EXCEPTION: ' . $e->getMessage();
            }
        }

        app::$entityManager->flush();
        $response['export_completion_percent'] = number_format(((($batch * $batchSize) + $batchSize) / $providerCount) * 100, 2);
        $response['total_exported'] = ($batch * $batchSize) + count($providers);
        $response['finished'] = count($providers) < $batchSize;
        $response['success'] = true;
        return $response;
    }

    /**
     * @param Nurse $nurse
     */
    public function convertNurseToVendor($nurse) {
        $firstName = str_replace(' ', '', $nurse->getMember()->getFirstName());
        $firstName = str_replace('\'', '', $firstName);
        $lastName = str_replace(' ', '', $nurse->getMember()->getLastName());
        $lastName = str_replace('\'', '', $lastName);
        $name = $firstName . ' ' . $lastName;

        $member = $nurse->getMember();
        if ($member) {
            /** @var saUser $user */
            $user = $member->getUsers()->first();
            $ss = $nurse->getSSN();
            $cipher = "AES-128-CTR";
            $key = $user->getUserKey();
            $ssn = openssl_decrypt($ss, $cipher, $key, 0, ord($key));
        }

        $vendorData = [
            'BillAddr' => [
                'Line1' => $name,
                'Line2' => $nurse->getStreetAddress() ?? '',
                'City' => $nurse->getCity() ?? '',
                'CountrySubDivisionCode' => $nurse->getState() ?? '',
                'Country' => 'U.S.A',
                'PostalCode' => $nurse->getZipcode() ?? ''
            ],
            'TaxIdentifier' => $ssn ?? '',
            'AcctNum' => $nurse->getAccountNumber() ?? '',
            'GivenName' => $firstName,
            'FamilyName' => $lastName,
            'CompanyName' => '',
            'DisplayName' => '',
            'PrintOnCheckName' => $name,
            'PrimaryPhone' => [
                'FreeFormNumber' => $nurse->getPhoneNumber() ?? ''
            ],
            'Mobile' => [
                'FreeFormNumber' => $nurse->getPhoneNumber() ?? ''
            ],
            'PrimaryEmailAddr' => [
                'Address' => $nurse->getEmailAddress() ?? ''
            ]
        ];

        return $vendorData;
    }

    /**
     * @param Provider $provider
     */
    public function convertProviderToCustomer($provider) {

        $customerData = [
            'DisplayName' => $provider->getMember()->getCompany(),
            'CompanyName' => $provider->getMember()->getCompany(),
            'PrintOnCheckName' => $provider->getMember()->getCompany()
        ];
        /** @var saMemberAddress $address */
        $address = $provider->getMember()->getAddresses() ? $provider->getMember()->getAddresses()[0] : null;
        if($address) {
            $customerData['BillAddr'] = [
                'City' => $address->getCity() ?? '',
                'Line1' => $address->getStreetOne(),
                'Line2' => $address->getStreetTwo(),
                'PostalCode' => $address->getPostalCode(),
                'CountrySubDivisionCode' => $address->getStateObject() ? $address->getStateObject()->getAbbreviation() : '',
            ];
        } else {
            $customerData['BillAddr'] = [
                'City' => $provider->getCity() ?? '',
                'Line1' => $provider->getStreetAddress() ?? '',
                'Line2' => '',
                'PostalCode' => $provider->getZipcode() ?? '',
                'CountrySubDivisionCode' => $provider->getStateAbbreviation() ?? '',
            ];
        }

        if($provider->getPrimaryEmailAddress()) {
            $customerData['PrimaryEmailAddr'] = [
                'Address' => $provider->getPrimaryEmailAddress()
            ];
        }

        if($provider->getFacilityPhoneNumber()) {
            $customerData['PrimaryPhone'] = [
                'FreeFormNumber' => $provider->getFacilityPhoneNumber()
            ];
        }

        return $customerData;
    }

    public function getPaymentsAuthRoute($data) {
        $response = ['success' => false];

        $redirectData = [
            'redirect_uri' => app::get()->getConfiguration()->get('quickbooks_site_url') . app::get()->getRouter()->generate('send_payments_confirmation')
        ];
        $authData = static::getAuthRoute($redirectData);
        $response['auth_url'] = $authData['url'];

        $paymentIds = [];
        $_SESSION['payment_ids'] = '';
        foreach($data['payments'] as $payment) {
            $paymentIds[] = $payment['payment_id'];
        }
        $_SESSION['payment_ids'] = implode(',', $paymentIds ?? []);

        $response['success'] = true;
        return $response;
    }

    public function sendPaymentsToQuickbooks($data) {
        $response = ['success' => false];
        $code = $data['code'];
        $realmId = $data['realmId'];
        $paymentIds = explode(',', $data['payment_ids']);

        $dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => app::get()->getConfiguration()->get('quickbooks_client_id')->getValue(),
            'ClientSecret' => app::get()->getConfiguration()->get('quickbooks_client_secret')->getValue(),
            'RedirectURI' => app::get()->getConfiguration()->get('quickbooks_site_url') . app::get()->getRouter()->generate('send_payments_confirmation'),
            'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
            'baseUrl' => app::get()->getConfiguration()->get('quickbooks_base_url')->getValue()
        ]);


        $dataService->throwExceptionOnError(true);

        $oauthLoginHelper = $dataService->getOAuth2LoginHelper();

        $response['type'] = 'success';
        $accessToken = $oauthLoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
        $dataService->updateOAuth2Token($accessToken);
        $dataService->setLogLocation(app::get()->getConfiguration()->get('tempDir')->getValue() . '/QuickbooksLogs');



        $i = 0;
        foreach($paymentIds as $paymentId) {
            try {

                $i++;
                /** @var PayrollPayment $payment */
                $payment = ioc::get('PayrollPayment', ['id' => $paymentId]);
                if(!$payment || ($payment->getBillTotal() <= 0.00) || $payment->getStatus() == 'Unresolved' || $payment->getPaymentMethod() == 'Direct Deposit') {
                    continue;
                }

                $provider = ioc::get('Provider', ['id' => $payment->getShift()->getProvider()->getId()]);
                $nurse = ioc::get('Nurse', ['id' => $payment->getShift()->getNurse()->getId()]);
            } catch(\Throwable $e) {
                $response['messages'][] = 'Error sending payment id: ' . $paymentId;
                continue;
            }
            try {

                $purchaseData = static::convertToQuickbooksPurchase($payment, $provider, $nurse);
                $qbPurchase = Purchase::create($purchaseData);
                $qbPurchase->EntityRef->type = 'Vendor';
                $purchaseObject = $dataService->Add($qbPurchase);

                $error = $dataService->getLastError();
                if ($error) {
                    $response['messages'][]= 'ERROR CREATING PURCHASE [id: ' . $payment->getId() . '] [' . $nurse->getFirstName() . ' ' . $nurse->getLastName() . ']';
                    $response['messages'][]= "---The Status code is: " . $error->getHttpStatusCode();
                    $response['messages'][]= "---The Helper message is: " . $error->getOAuthHelperError();
                    $response['messages'][]= "---The Response message is: " . $error->getResponseBody();
                }
                else {
                    $payment->setQuickbooksPurchaseId($purchaseObject->Id);
                    $payment->setPaymentStatus('Paid');
                }

                // Create a bill, then create a payment
                // $billData = static::convertToQuickbooksBill($payment, $provider, $nurse);
                // $qbBill = Bill::create($billData);
                // $billObject = $dataService->Add($qbBill);

                // $error = $dataService->getLastError();
                // if ($error) {
                //     $response['messages'][]= 'ERROR CREATING BILL FOR PAYMENT [id: ' . $payment->getId() . '] [' . $nurse->getFirstName() . ' ' . $nurse->getLastName() . ']';
                //     $response['messages'][]= "---The Status code is: " . $error->getHttpStatusCode();
                //     $response['messages'][]= "---The Helper message is: " . $error->getOAuthHelperError();
                //     $response['messages'][]= "---The Response message is: " . $error->getResponseBody();
                // }
                // else {
                //     $payment->setQuickbooksBillId($billObject->Id);
                // }

                // $paymentData = static::convertToQuickbooksPayment($payment, $billObject->Id, $nurse);
                // $qbPayment = BillPayment::create($paymentData);
                // $paymentObject = $dataService->Add($qbPayment);

                // echo '<pre>';
                // var_dump($paymentObject);
                // echo '</pre>';
                // die();

                // $error = $dataService->getLastError();
                // if ($error) {
                //     $response['messages'][]= 'ERROR CREATING BILLPAYMENT FOR PAYMENT [id: ' . $payment->getId() . '] [' . $nurse->getFirstName() . ' ' . $nurse->getLastName() . ']';
                //     $response['messages'][]= "---The Status code is: " . $error->getHttpStatusCode();
                //     $response['messages'][]= "---The Helper message is: " . $error->getOAuthHelperError();
                //     $response['messages'][]= "---The Response message is: " . $error->getResponseBody();
                // }
                // else {
                //     $payment->setPaymentStatus('Paid');
                //     $payment->setQuickbooksBillPaymentId($paymentObject->Id);
                // }

                app::$entityManager->flush();
            } catch(\Throwable $e) {
                $response['messages'][] = 'Error sending payment [id: ' . $paymentId . '] for: ' . $nurse->getFirstName() . ' ' . $nurse->getLastName() . PHP_EOL . $e->getMessage();
            }
        }


        $response['success'] = true;
        return $response;
    }

    /**
     * @deprecated
     * @param PayrollPayment $payment
     */
    public function convertToQuickbooksBill($payment, $provider, $nurse) {
        $shift = $payment->getShift() ?? $payment->getShiftRecurrence();

        $billData = [
            'domain' => 'QBO',
            'VendorRef' => [
                'name' => $nurse->getFirstName() . ' ' . $nurse->getLastName(),
                'value' => $nurse->getQuickbooksVendorId()
            ],
            'TxnDate' => $shift->getStart()->format('Y-m-d'),
            'TotalAmt' => $payment->getPayTotal(),
            'sparse' => false,
            'Line' => [
                [
                    'DetailType' => 'AccountBasedExpenseLineDetail',
                    'Amount' => $payment->getPayTotal(),
                    'AccountBasedExpenseLineDetail' => [
                        'AccountRef' => [
                        //    'name' => 'Cost of Labor',    //DEVELOPMENT
                        //    'value' => '91'
                            'name' => 'Contract Labor',     //PRODUCTION
                            'value' => '37'
                        ],
                        'CustomerRef' => [
                            'name' => $provider->getMember()->getCompany(),
                            'value' => $provider->getQuickbooksCustomerId()
                        ]
                    ]
                ]
            ],
            'CurrencyRef' => [
                'name' => 'United States Dollar',
                'value' => 'USD'
            ],
            'DocNumber' => $payment->getId(),
        ];

        return $billData;
    }

    /**
     * @deprecated
     * @param PayrollPayment $payment
     */
    public function convertToQuickbooksPayment($payment, $billId, $nurse) {
        $shift = $payment->getShift() ?? $payment->getShiftRecurrence();
        
        $paymentData = [
            'domain' => 'QBO',
            'VendorRef' => [
                'name' => $nurse->getFirstName() . ' ' . $nurse->getLastName(),
                'value' => $nurse->getQuickbooksVendorId()
            ],
            'TxnDate' => $shift->getStart()->format('Y-m-d'),
            'PayType' => 'Check',
            'TotalAmt' => $payment->getPayTotal(),
            'sparse' => false,
            'Line' => [
                [
                    'Amount' => $payment->getPayTotal(),
                    'LinkedTxn' => [
                        'TxnId' => $billId,
                        'TxnType' => 'Bill'
                    ]
                ]
            ],
            'CheckPayment' => [
                'PrintStatus' => $payment->getPaymentMethod() == 'Paper Check' ? 'NeedToPrint' : 'NotSet',         // Pay cards - NotSet
                'BankAccountRef' => [
                //    'name' => 'Checking',   // DEVELOPMENT
                //    'value' => '35'
                    'name' => $payment->getPaymentMethod() == 'Pay Card' ? 'MetaBank' : 'Whitaker Bank',         // PRODUCTION - MetaBank for paycards, Whitacre for ACH and paper check
                    'value' => $payment->getPaymentMethod() == 'Pay Card' ? '84' : '70'
                ]
            ]
        ];

        return $paymentData;
    }

        /**
     * @param PayrollPayment $payment
     */
    public function convertToQuickbooksPurchase($payment, $provider, $nurse) {
        $shift = $payment->getShift() ?? $payment->getShiftRecurrence();
        
        $purchaseData = [
            "AccountRef" => [ // DEVELOPMENT This might fix the 'Account' problem -
                // 'name' => 'Checking',   // DEVELOPMENT
                // 'value' => '35'
                'name' => $payment->getPaymentMethod() == 'Pay Card' ? 'MetaBank' : 'Whitaker Bank',         // PRODUCTION - MetaBank for paycards, Whitacre for ACH and paper check
                'value' => $payment->getPaymentMethod() == 'Pay Card' ? '84' : '70'
            ], 
            'EntityRef' => [
                'value' => $nurse->getQuickbooksVendorId(),
                'name' => $nurse->getFirstName() . ' ' . $nurse->getLastName()
            ],
            "SyncToken" => "0", 
            'domain' => 'QBO',
            'PaymentType' => "Check",  
            'TxnDate' => $shift->getStart()->format('Y-m-d'),
            'TotalAmt' => $payment->getPayTotal(),
            'sparse' => false,
            'Line' => [
                [
                    'DetailType' => 'AccountBasedExpenseLineDetail',
                    'Amount' => $payment->getPayTotal(),
                    "Id" => "0",
                    'AccountBasedExpenseLineDetail' => [
                        'AccountRef' => [
                            // 'name' => 'Contract Labor', // DEVELOPMENT
                            // 'value' => '93'
                            'name' => 'Contract Labor',     //PRODUCTION
                            'value' => '37'
                        ],
                        'CustomerRef' => [
                            'name' => $provider->getMember()->getCompany(),
                            'value' => $provider->getQuickbooksCustomerId()
                        ]
                    ]
                ]
            ],
            'CurrencyRef' => [
                'name' => 'United States Dollar',
                'value' => 'USD'
            ],

        ];

        return $purchaseData;
    }
}
