<?php
namespace App\Controller\Provider;

use App\Controller\AbstractController;
use App\DTO\Member\NstMemberUserDTO;
use App\DTO\Member\NurseDTO;
use App\Service\Provider\ProviderService;
use Carbon\Carbon;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProviderPBJReportController extends AbstractController
{
    private ProviderService $providerService;

    public function __construct(InertiaInterface $inertia, ProviderService $providerService)
    {
        parent::__construct($inertia);
        $this->providerService = $providerService;
    }

    #[Route('/payroll/pbj_report', name: 'app_provider_pbj_report', methods: ['GET'])]
    public function getPbjReport(#[CurrentUser] $user, Request $request): Response
    {
        $userDTO = new NstMemberUserDTO($user);
        return $this->inertia->render('ProviderPbjReport', [
            'user' => $userDTO,
        ]);
    }

    #[Route('/payroll/pbj_report', name: 'app_provider_pbj_report_for_range', methods: ['POST'])]
    public function getPbjReportForRange(#[CurrentUser] $user, Request $request): JsonResponse
    {
        $start = Carbon::parse($request->query->get('start'));
        $end = Carbon::parse($request->query->get('end'));
        $pbjReport = $this->providerService->getPbjReport($user, $start, $end);
        return $this->json($pbjReport);
    }
}
