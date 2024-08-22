<?php

namespace App\Controller\Provider;

use App\Controller\AbstractController;
use App\DTO\Member\NstMemberUserDTO;
use App\Service\Payroll\InvoiceService;
use App\Service\Provider\ProviderService;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProviderInvoiceController extends AbstractController
{
    private ProviderService $providerService;
    private InvoiceService $invoiceService;
    public function __construct(
        InertiaInterface $inertia,
        ProviderService $providerService,
        InvoiceService $invoiceService
    ) {
        parent::__construct($inertia);
        $this->providerService = $providerService;
        $this->invoiceService = $invoiceService;
    }

    #[Route('/providers/invoices', name: 'app_provider_invoices', methods: ['GET'])]
    public function getProviderNurses(#[CurrentUser] $user, Request $request): Response
    {
        $nstMemberUserDTO  = new NstMemberUserDTO($user);
        $invoices = $this->invoiceService->getInvoicesForUser($user);
        return $this->inertia->render('ProviderInvoices', [
            'user' => $nstMemberUserDTO,
            'data' => $invoices,
        ]);
    }
}
