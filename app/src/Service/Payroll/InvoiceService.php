<?php

namespace App\Service\Payroll;

use App\DTO\Payroll\InvoiceDTO;
use App\Entity\Nst\Member\NstMemberUsers;
use App\Entity\Nst\Member\Provider;
use App\Entity\Nst\Payroll\NstInvoice;
use App\Repository\Nst\Member\ProviderRepository;
use App\Service\Provider\ProviderService;
use Exception;

class InvoiceService
{
    private ProviderRepository $providerRepository;
    private ProviderService $providerService;

    public function __construct(ProviderRepository $providerRepository, ProviderService $providerService)
    {
        $this->providerRepository = $providerRepository;
        $this->providerService = $providerService;
    }


    /**
     * @param NstMemberUsers $user
     * @return array<InvoiceDTO>
     * @throws Exception
     */
    public function getInvoicesForUser(NstMemberUsers $user): array
    {
        $providers = $this->providerService->getProvidersForMember($user);

        return array_reduce($providers, function ($invoices, $provider) {
            return [...$invoices, ...$this->getInvoicesForProvider($provider->getId())];
        }, []);
    }

    /**
     * @param $providerId
     * @return array<InvoiceDTO>
     */
    public function getInvoicesForProvider($providerId): array
    {
        $invoices = [];
        if ($providerId) {
            /** @var Provider $provider */
            $provider = $this->providerRepository->find($providerId);
            $invoices = $provider->getInvoices();
        } else {
            $response['message'] = 'No ProviderID';
            return $response;
        }

        $invoiceDTOs = [];
        /** @var NstInvoice $invoice */
        foreach ($invoices as $invoice) {
            if (!$invoice->getTotal() && !$invoice->getInvoiceFile()) {
                continue;
            }
            $fileBase = '/assets/files';
            $fileUrl =
                $invoice->getInvoiceFile() ?
                    $fileBase . DIRECTORY_SEPARATOR .
                    $invoice->getInvoiceFile()->getFolder() . DIRECTORY_SEPARATOR .
                    $invoice->getInvoiceFile()->getFilename()
                    : '';
            $invoiceDTOs[] = new InvoiceDTO(
                $invoice->getId(),
                $invoice->getInvoiceNumber(),
                $invoice->getPayPeriod(),
                $invoice->getTotal(),
                $invoice->getStatus(),
                $fileUrl,
                $invoice->getProvider(),
            );
        }

        return $invoiceDTOs;
    }
}
