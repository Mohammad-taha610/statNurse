<?php

namespace App\Controller\Provider;

use App\Controller\AbstractController;
use App\DTO\Member\NstMemberUserDTO;
use App\DTO\Member\NurseDTO;
use App\Service\NurseService;
use App\Service\Provider\ProviderService;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProviderNurseController extends AbstractController
{
    private ProviderService $providerService;
    private NurseService $nurseService;

    public function __construct(
        InertiaInterface $inertia,
        ProviderService  $providerService,
        NurseService     $nurseService
    ) {
        parent::__construct($inertia);
        $this->providerService = $providerService;
        $this->nurseService = $nurseService;
    }

    #[Route('/providers/nurse_list', name: 'app_provider_nurse_list', methods: ['GET'])]
    public function getProviderDashboard(#[CurrentUser] $user, Request $request): Response
    {
        $search = $request->query->get('search');
        $userDTO = new NstMemberUserDTO($user);
        $nurses = $this->providerService->loadNursesForUser($user, false, $search);
        return $this->inertia->render('ProviderNurseList', [
            'user' => $userDTO,
            'provider_nurse_list' => $nurses
        ]);
    }

    #[Route('/nurse/{id}', name: 'app_provider_nurse', methods: ['GET'])]
    public function getProviderNurse(#[CurrentUser] $user, int $id): Response
    {
        $userDTO = new NstMemberUserDTO($user);
        $nurse = $this->nurseService->getNurseById($id);
        $nurseDTO = new NurseDTO($nurse);
        $files = $this->nurseService->getProviderNurseFiles($nurse);
        return $this->inertia->render('ProviderNurse', [
            'user' => $userDTO,
            'files' => $files,
            'nurse' => $nurseDTO
        ]);
    }

    #[Route('/nurse/{nurseId}/block', name: 'app_provider_nurse_block', methods: ['POST'])]
    public function blockNurse(#[CurrentUser] $user, int $nurseId, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $providers = $data['providers'];
        $this->providerService->blockNurse($user, $providers, $nurseId);
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    #[Route('/providers/{providerId}/nurse/{nurseId}/unblock', name: 'app_provider_nurse_unblock', methods: ['POST'])]
    public function unblockNurse(#[CurrentUser] $user, int $providerId, int $nurseId): Response
    {
        $this->providerService->unblockNurse($user, $providerId, $nurseId);
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    #[Route('/providers/dnr_list', name: 'app_executive_providers_dnr_list', methods: ['GET'])]
    public function getBlockedNurses(#[CurrentUser] $user): Response
    {
        $nurses = $this->providerService->getBlockedNursesForUser($user);
        return $this->inertia->render('ProviderDnrList', [
            'user' => new NstMemberUserDTO($user),
            'provider_nurse_list' => $nurses
        ]);
    }
}
