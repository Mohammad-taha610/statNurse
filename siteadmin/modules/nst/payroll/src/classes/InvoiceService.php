<?php


namespace nst\payroll;


use Doctrine\DBAL\ParameterType;
use nst\events\Shift;
use nst\events\ShiftRepository;
use nst\events\ShiftService;
use nst\member\Provider;
use nst\payroll\Invoice;
use nst\quickbooks\QuickbooksInvoice;
use nst\quickbooks\QuickbooksLine;
use nst\quickbooks\QuickbooksService;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Customer;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\utilities\doctrineUtils;

class InvoiceService
{

    public static function loadInvoices($data) {
        $response = ['success' => false];
        $providerId = $data['provider_id'];

        $invoices = [];
        if($providerId) {
            /** @var Provider $provider */
            $provider = ioc::get('Provider', ['id' => $providerId]);
            $invoices = $provider->getInvoices();
        } else {
            $response['message'] = 'No ProviderID';
            return $response;
        }

        /** @var NstInvoice $invoice */
        foreach($invoices as $invoice) {
            if (!$invoice->getTotal() && !$invoice->getInvoiceFile()) { continue; }
            $response['invoices'][] = [
                'id' => $invoice->getId(),
                'invoice_number' => $invoice->getInvoiceNumber(),
                'pay_period' => $invoice->getPayPeriod(),
                'amount' => $invoice->getTotal(),
                'status' => $invoice->getStatus(),
                'file_url' => $invoice->getInvoiceFile() ? app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $invoice->getInvoiceFile()->getFolder(), 'file' => $invoice->getInvoiceFile()->getFilename()]) : ''
            ];
        }

        $response['success'] = true;

        return $response;
    }

    public static function loadAdminInvoices($data) {
        $response = ['success' => false];
        $providerId = $data['provider_id'];
        $payPeriod = $data['pay_period'];

        $invoices = [];
        if($providerId) {
            /** @var Provider $provider */
            $provider = ioc::get('Provider', ['id' => $providerId]);
            $invoices = $provider->getInvoices();
        } else {
            $invoices = app::$entityManager->getRepository(ioc::staticResolve('NstInvoice'))->findAll();
        }

        /** @var NstInvoice $invoice */
        foreach($invoices as $invoice) {
            if($payPeriod && $payPeriod != 'all' && $invoice->getPayPeriod() != $payPeriod) {
                continue;
            }
            $response['invoices'][] = [
                'id' => $invoice->getId(),
                'provider_name' => $invoice->getProvider() ? $invoice->getProvider()->getMember()->getCompany() : '',
                'invoice_number' => $invoice->getInvoiceNumber(),
                'pay_period' => $invoice->getPayPeriod(),
                'amount' => $invoice->getTotal(),
                'status' => $invoice->getStatus(),
                'edit_route' => app::get()->getRouter()->generate('sa_edit_invoice', ['id' => $invoice->getId()]),
                'file_url' => $invoice->getInvoiceFile() ? app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $invoice->getInvoiceFile()->getFolder(), 'file' => $invoice->getInvoiceFile()->getFilename()]) : '',
                'generate_url' => app::get()->getRouter()->generate('sa_generate_invoice_send', ['provider_id' => $invoice->getProvider()->getId(), 'pay_period' => $invoice->getPayPeriod()])
            ];
        }
        $response['success'] = true;
        return $response;
    }

    public static function loadInvoiceData($data) {
        $response = ['success' => false];
        $id = $data['id'];

        $response['statuses'] = [
            'Paid',
            'Upcoming',
        ];

        if($id) {
            /** @var NstInvoice $invoice */
            $invoice = ioc::get('Invoice', ['id' => $id]);

            if(!$invoice) {
                $response['message'] = 'Cannot find invoice: ' . $id;
                return $response;
            }

            $response['invoice'] = [
                'id' => $invoice->getId(),
                'provider_id' => $invoice->getProvider() ? $invoice->getProvider()->getId() : 0,
                'invoice_number' => $invoice->getInvoiceNumber(),
                'pay_period' => $invoice->getPayPeriod(),
                'status' => $invoice->getStatus(),
                'amount' => $invoice->getTotal(),
                'file_id' => $invoice->getInvoiceFile() ? $invoice->getInvoiceFile()->getId() : 0,
                'file_name' => $invoice->getInvoiceFile() ? $invoice->getInvoiceFile()->getFilename() : '',
                'file_url' => $invoice->getInvoiceFile() ? app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $invoice->getInvoiceFile()->getFolder(), 'file'=>$invoice->getInvoiceFile()->getFilename()]) : '',
            ];
        }

        $response['success'] = true;
        return $response;
    }

    public static function saveInvoiceData($data) {
        $response = ['success' => false];
        $id = $data['id'];
        $providerId = $data['provider_id'];
        $invoiceNumber = $data['invoice_number'];
        $payPeriod = $data['pay_period'];
        $status = $data['status'];
        $amount = $data['amount'];
        $fileId = $data['file_id'];

        $shouldPersist = false;

        /** @var NstInvoice $invoice */
        $invoice = null;
        if($id) {
            /** @var NstInvoice $invoice */
            $invoice = ioc::get('Invoice', ['id' => $id]);
        } else {
            /** @var NstInvoice $invoice */
            $invoice = ioc::resolve('Invoice');
            $shouldPersist = true;
        }

        if(!$invoice) {
            $response['message'] = 'Cannot find invoice: ' . $id;
            return $response;
        }

        if($providerId) {
            $provider = ioc::get('Provider', ['id' => $providerId]);
            if (!$provider) {
                $response['message'] = 'Cannot find provider: ' . $providerId;
                return $response;
            }
            $invoice->setProvider($provider);
        }

        if($fileId) {
            $file = ioc::get('saFile', ['id' => $fileId]);
            if(!$file) {
                $response['message'] = 'Cannot find file: ' . $fileId;
                return $response;
            }
            $invoice->setInvoiceFile($file);
        }

        $invoice->setInvoiceNumber($invoiceNumber);
        $invoice->setTotal($amount);
        $invoice->setStatus($status);
        $invoice->setPayPeriod($payPeriod);

        if($shouldPersist) {
            app::$entityManager->persist($invoice);
        }
        app::$entityManager->flush($invoice);

        $response['success'] = true;
        return $response;
    }

    /**
     * Generate an invoice for a provider given a defined pay period
     * @param array $data
     */
    public static function generateInvoice($data) {
        $payments = null;
        $providerId = $data['provider_id'];
        $pay_period = $data['pay_period'];
        $emails = $data['emails'];
        $response = ['success' => false];
        try {
            // Set Up Invoice
            /** @var Provider $provider */
            $provider = ioc::get('Provider', ['id' => $providerId]);

            if(!$provider) {
                $response['message'] = 'Cannot find provider: ' . $providerId;
                return $response;
            }

            /** @var NstInvoice $invoice */
            $invoice = ioc::resolve('NstInvoice');

            // $today = new DateTime('now', app::getInstance()->getTimeZone()); Not using this?
            $invoiceNumber = app::get()->getConfiguration()->get('quickbooks_invoice_number')->getValue();
            app::get()->getConfiguration()->get('quickbooks_invoice_number')->setValue((int)$invoiceNumber + 1);
            app::get()->getConfiguration()->persist();
            $invoice->setInvoiceNumber($invoiceNumber);
            $invoice->setProvider($provider);
            $invoice->setPayPeriod($pay_period);
            $invoice->setStatus($data['review_first'] == 'Yes' ? QuickbooksInvoice::STATUS_REVIEW : QuickbooksInvoice::STATUS_UNPAID);
            $invoice->setEmails($emails);

            // Apply rates from shifts during the pay period
            $startDate = null;
            $endDate = null;
            if($pay_period != 'all') {
                $startDate = new DateTime(explode( '_', $pay_period)[0] . ' 00:00:00', app::getInstance()->getTimeZone());
                $endDate = new DateTime(explode('_', $pay_period)[1] . ' 23:59:59', app::getInstance()->getTimeZone());
            }

            /** @var PayrollPaymentRepository $paymentRepo */
            $paymentRepo = ioc::getRepository('PayrollPayment');
            $payments = $paymentRepo->getPaymentsBetweenDates($provider->getId(), $startDate, $endDate, $pay_period == 'all', false, false, null, null, null, false)['payments'];

            app::$entityManager->persist($invoice);
        } catch(\Throwable $e) {
            $response['messages'][] = 'EXCEPTION[1]: ' . $e->getMessage();
        }
        /** @var PayrollPayment $payment */
        foreach ($payments as $payment) {
            try {
                if ($payment->getBillTotal() <= 0 || $payment->getStatus() == 'Unresolved' || (!$payment->getShift() && !$payment->getShiftRecurrence())) {
                    continue;
                }

                // need to split into two lines if there is holiday pay
                if ($payment->getBillHoliday() > 0) {

                    static::generateHolidayInvoiceLines($payment, $invoice);
                    continue;
                }

                $line = new QuickbooksLine();
                $shift = $payment->getShift() ?: $payment->getShiftRecurrence();
                $nurse = $shift->getNurse();
                $clockedHours = number_format($payment->getClockedHours(), 2, '.', '');
                $billRate = number_format($payment->getBillRate(), 2, '.', '');
                $line->setDetailType(QuickbooksLine::DETAIL_TYPE_SALES_ITEM_LINE_DETAIL);
                $line->setAmount(number_format($clockedHours * $billRate, 2, '.', ''));
                $line->setQuantity(number_format($clockedHours, 2, '.', ''));
                $line->setRate($payment->getBillRate());
                $line->setLineDetail([
                    'ItemRef' => [
                        'name' => 'Payment',
                        'value' => app::get()->getConfiguration()->get('quickbooks_base_url')->getValue() == 'Production' ? '241' : '1'
                    ],
                    'Qty' => number_format($clockedHours, 2, '.', ''),
                    'UnitPrice' => $payment->getBillRate(),
                ]);
                $invoiceDescription = $nurse->getFirstName() . ' ' . $nurse->getLastName() . ' ' . $nurse->getCredentials();
                $invoiceDescription .= ' ' . $shift->getStart()->format('m/d/y');
                $invoiceDescription .= ' [' . $shift->getClockInTime()->format('g:ia') . ' - ' . $shift->getClockOutTime()->format('g:ia') . ']';
                $invoiceDescription .=  ' ' . $payment->getType() . ' Rate';
                $invoiceDescription .= $shift->getIsCovid() ? ' (Covid) ' : ' ';
                $invoiceDescription .= $shift->getLunchOverride() ? '(Break: ' . $shift->getLunchOverride() . '-Minutes)  ' : '(No Break) ';
                if ($shift->getIncentive() > 1) {
                    $invoiceDescription .= ' (*' . $shift->getIncentive() . ' incentive)';
                }
                $line->setDescription($invoiceDescription);
                $line->setInvoice($invoice);
                app::$entityManager->persist($line);
                app::$entityManager->flush($line);

                if ($payment->getBillBonus()) {
                    $line = new QuickbooksLine();
                    $line->setDetailType(QuickbooksLine::DETAIL_TYPE_SALES_ITEM_LINE_DETAIL);
                    $line->setAmount($payment->getBillBonus());
                    $line->setQuantity(1);
                    $line->setRate($payment->getBillBonus());
                    $line->setLineDetail([
                        'ItemRef' => [
                            'name' => 'Payment',
                            'value' => app::get()->getConfiguration()->get('quickbooks_base_url')->getValue() == 'Production' ? '241' : '1'
                        ],
                        'Qty' => 1,
                        'UnitPrice' => $payment->getBillBonus(),
                    ]);
                    $bonusInvoiceDescription = 'Facility Incentive Bonus ' . $nurse->getFirstName() . ' ' . $nurse->getLastName();
                    $line->setDescription($bonusInvoiceDescription);
                    $line->setInvoice($invoice);
                    app::$entityManager->persist($line);
                    app::$entityManager->flush($line);
                }

                if ($payment->getBillTravel()) {
                    $line = new QuickbooksLine();
                    $line->setDetailType(QuickbooksLine::DETAIL_TYPE_SALES_ITEM_LINE_DETAIL);
                    $line->setAmount($payment->getBillTravel());
                    $line->setQuantity(1);
                    $line->setRate($payment->getBillTravel());
                    $line->setLineDetail([
                        'ItemRef' => [
                            'name' => 'Payment',
                            'value' => app::get()->getConfiguration()->get('quickbooks_base_url')->getValue() == 'Production' ? '241' : '1'
                        ],
                        'Qty' => 1,
                        'UnitPrice' => $payment->getBillTravel(),
                    ]);
                    $travelDescription = $nurse->getFirstName() . ' ' . $nurse->getLastName() . ' ' . $nurse->getCredentials();
                    $travelDescription .= ' Standard Rate Travel Pay';
                    $line->setDescription($travelDescription);
                    $line->setInvoice($invoice);
                    app::$entityManager->persist($line);
                    app::$entityManager->flush($line);
                }
            } catch(\Throwable $e) {
                $response['messages'][] = 'EXCEPTION[2]: ' . $e->getMessage();
            }

        }

        try {
            app::$entityManager->flush();

            $response['success'] = true;
            $response['id'] = $invoice->getId();
            $_SESSION['invoice_id'] = $invoice->getId();
            $_SESSION['provider_id'] = $providerId;

            $quickbooksService = new QuickbooksService();
            $redirectData = [
                'redirect_uri' => app::get()->getConfiguration()->get('secure_site_url') . app::get()->getRouter()->generate('sa_view_invoice')
            ];
            $authData = $quickbooksService->getAuthRoute($redirectData);
            $response['auth_url'] = $authData['url'];
        } catch(\Throwable $e) {
            $response['messages'][] = 'EXCEPTION[3]: ' . $e->getMessage();
        }
        return $response;
    }

    public static function generateHolidayInvoiceLines($payment, $invoice)
    {
        // create line for regular pay
        $line = new QuickbooksLine();
        $shift = $payment->getShift() ?: $payment->getShiftRecurrence();
        $nurse = $shift->getNurse();
        $clockedHours = number_format($payment->getClockedHours(), 2, '.', '');
        $billRate = number_format($payment->getBillRate(), 2, '.', '');
        $line->setDetailType(QuickbooksLine::DETAIL_TYPE_SALES_ITEM_LINE_DETAIL);
        $line->setAmount(number_format($clockedHours * $billRate, 2, '.', ''));
        $line->setQuantity(number_format($clockedHours, 2, '.', ''));
        $line->setRate($payment->getBillRate());
        $line->setLineDetail([
            'ItemRef' => [
                'name' => 'Payment',
                'value' => app::get()->getConfiguration()->get('quickbooks_base_url')->getValue() == 'Production' ? '241' : '1'
            ],
            'Qty' => number_format($clockedHours, 2, '.', ''),
            'UnitPrice' => $payment->getBillRate(),
        ]);
        $invoiceDescription = $nurse->getFirstName() . ' ' . $nurse->getLastName() . ' ' . $nurse->getCredentials();
        $invoiceDescription .= ' ' . $shift->getStart()->format('m/d/y');
        $invoiceDescription .= ' [' . $shift->getClockInTime()->format('g:ia') . ' - ' . $shift->getClockOutTime()->format('g:ia') . ']';
        $invoiceDescription .=  ' ' . $payment->getType() . ' Rate';
        $invoiceDescription .= $shift->getIsCovid() ? ' (Covid) ' : ' ';
        $invoiceDescription .= $shift->getLunchOverride() ? '(Break: ' . $shift->getLunchOverride() . '-Minutes)  ' : '(No Break) ';
        if ($shift->getIncentive() > 1) {
            $invoiceDescription .= ' (*' . $shift->getIncentive() . ' incentive)';
        }
        $line->setDescription($invoiceDescription);
        $line->setInvoice($invoice);
        app::$entityManager->persist($line);
        app::$entityManager->flush($line);

        // create line for holiday pay
        $holidayBillTotal = $payment->getBillHoliday();
        $line = new QuickbooksLine();
        $shift = $payment->getShift() ?: $payment->getShiftRecurrence();
        $nurse = $shift->getNurse();
        $line->setDetailType(QuickbooksLine::DETAIL_TYPE_SALES_ITEM_LINE_DETAIL);
        $line->setAmount($holidayBillTotal);
        $line->setQuantity(1);
        $line->setRate($holidayBillTotal);
        $line->setLineDetail([
            'ItemRef' => [
                'name' => 'Payment',
                'value' => app::get()->getConfiguration()->get('quickbooks_base_url')->getValue() == 'Production' ? '241' : '1'
            ],
            'Qty' => 1,
            'UnitPrice' => $holidayBillTotal,
        ]);
        $invoiceDescription = $nurse->getFirstName() . ' ' . $nurse->getLastName() . ' ' . $nurse->getCredentials();
        $invoiceDescription .= ' ' . $shift->getStart()->format('m/d/y');
        $invoiceDescription .= ' [' . $shift->getClockInTime()->format('g:ia') . ' - ' . $shift->getClockOutTime()->format('g:ia') . ']';
        $invoiceDescription .=  ' ' . $payment->getType() . ' Rate Holiday Pay';
        $line->setDescription($invoiceDescription);
        $line->setInvoice($invoice);
        app::$entityManager->persist($line);
        app::$entityManager->flush($line);

        if ($payment->getBillBonus()) {
            $line = new QuickbooksLine();
            $line->setDetailType(QuickbooksLine::DETAIL_TYPE_SALES_ITEM_LINE_DETAIL);
            $line->setAmount($payment->getBillBonus());
            $line->setQuantity(1);
            $line->setRate($payment->getBillBonus());
            $line->setLineDetail([
                'ItemRef' => [
                    'name' => 'Payment',
                    'value' => app::get()->getConfiguration()->get('quickbooks_base_url')->getValue() == 'Production' ? '241' : '1'
                ],
                'Qty' => 1,
                'UnitPrice' => $payment->getBillBonus(),
            ]);
            $bonusInvoiceDescription = 'Facility Incentive Bonus ' . $nurse->getFirstName() . ' ' . $nurse->getLastName();
            $line->setDescription($bonusInvoiceDescription);
            $line->setInvoice($invoice);
            app::$entityManager->persist($line);
            app::$entityManager->flush($line);
        }

        if ($payment->getBillTravel()) {
            $line = new QuickbooksLine();
            $line->setDetailType(QuickbooksLine::DETAIL_TYPE_SALES_ITEM_LINE_DETAIL);
            $line->setAmount($payment->getBillTravel());
            $line->setQuantity(1);
            $line->setRate($payment->getBillTravel());
            $line->setLineDetail([
                'ItemRef' => [
                    'name' => 'Payment',
                    'value' => app::get()->getConfiguration()->get('quickbooks_base_url')->getValue() == 'Production' ? '241' : '1'
                ],
                'Qty' => 1,
                'UnitPrice' => $payment->getBillTravel(),
            ]);
            $travelDescription = $nurse->getFirstName() . ' ' . $nurse->getLastName() . ' ' . $nurse->getCredentials();
            $travelDescription .= ' Standard Rate Travel Pay';
            $line->setDescription($travelDescription);
            $line->setInvoice($invoice);
            app::$entityManager->persist($line);
            app::$entityManager->flush($line);
        }
    }

    public static function showInvoice($data) {
        $response = ['success' => false];

        $quickbooksService = new QuickbooksService();
        $data['redirect_uri'] = app::get()->getConfiguration()->get('secure_site_url') . app::get()->getRouter()->generate('sa_view_invoice');

        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $data['provider_id']]);
        $data['display_name'] = $provider->getMember()->getCompany();
        $qbResponse = $quickbooksService->generateQuickbooksInvoice($data);

        return $qbResponse;
    }

    public static function deleteInvoice($data) {
        $response = ['success' => false];
        $id = $data['invoice_id'];

        $invoice = ioc::get('NstInvoice', ['id' => $id]);
        if(!$invoice) {
            $response['message'] = 'Unable to find invoice: ' . $id;
            return $response;
        }

        app::$entityManager->remove($invoice);
        app::$entityManager->flush();
        $response['success'] = true;

        return $response;
    }
}
