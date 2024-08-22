<?php

namespace App\Controller\Provider;

use App\Controller\AbstractController;
use App\DTO\Member\NstMemberUserDTO;
use App\Entity\Nst\Member\Provider;
use App\Service\Provider\ProviderDashboardService;
use App\Service\Provider\ProviderService;
use Carbon\Carbon;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProviderDashboardController extends AbstractController
{
    private ProviderDashboardService $providerDashboardService;
    private ProviderService $providerService;

    public function __construct(
        InertiaInterface         $inertia,
        ProviderDashboardService $providerDashboardService,
        ProviderService          $providerService
    )
    {
        parent::__construct($inertia);
        $this->providerDashboardService = $providerDashboardService;
        $this->providerService = $providerService;
    }

    #[Route('/dashboard', name: 'app_provider_dashboard', methods: ['GET'])]
    public function getProviderDashboard(#[CurrentUser] $user): Response
    {
        $userDTO = new NstMemberUserDTO($user);
        $dashboardData = $this->providerDashboardService->loadDashboardDataForUser($user);
        return $this->inertia->render('ProviderDashboard', [
            'controller_name' => 'ProviderDashboardController',
            'user' => $userDTO,
            'data' => $dashboardData
        ]);
    }

    #[Route('/dashboard/upcoming_shifts', name: 'app_provider_dashboard_upcoming_shifts', methods: ['GET'])]
    public function getUpcomingShifts(#[CurrentUser] $user, Request $request): Response
    {
        $data = $request->query->all();
        $userDTO = new NstMemberUserDTO($user);
        $providerParam = $data['provider'] ?? null;
        $providers = $providerParam ? [$providerParam] : $this->providerService->getProvidersForMember($user);
        $page = $data['page'] ?? 1;
        $tz = $data['tz'] ?? 'America/New_York';

        $upcomingShiftData = $this->providerDashboardService->getUpcomingShifts(
            $providers,
            $page,
            10,
            Carbon::now(),
            $tz,
        );

        return new Response(json_encode($upcomingShiftData), 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
