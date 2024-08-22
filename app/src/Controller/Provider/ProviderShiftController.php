<?php

namespace App\Controller\Provider;

use App\Controller\AbstractController;
use App\DTO\Member\NstMemberUserDTO;
use App\DTO\Shift\ShiftCategoryDTO;
use App\Service\Provider\ProviderService;
use App\Service\Shift\ShiftService;
use Carbon\Carbon;
use Rompetomp\InertiaBundle\Service\Inertia;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProviderShiftController extends AbstractController
{
    private ShiftService $shiftService;
    private ProviderService $providerService;

    public function __construct(Inertia $inertia, ShiftService $shiftService, ProviderService $providerService)
    {
        parent::__construct($inertia);
        $this->shiftService = $shiftService;
        $this->providerService = $providerService;
    }

    #[Route('/shifts/calendar', name: 'app_provider_shift_calendar_range', methods: ['GET'])]
    public function getShiftsInRange(#[CurrentUser] $user, Request $request): Response
    {
        $start = Carbon::parse($request->query->get('start'));
        $end = Carbon::parse($request->query->get('end'));
        $calendarMode = $request->query->get('calendarMode');

        $nurseFilter = $request->query->get('nurseFilter');
        $providerFilter = $request->query->get('providerFilter');
        $categoryFilter = $request->query->get('categoryFilter');
        $credentialFilter = $request->query->get('nurseType');
        $tz = $request->query->get('tz');

        $shiftsForThisMonth = $this->shiftService->loadShiftCalendarMonthView(
            $user,
            $start,
            $end,
            $nurseFilter,
            $providerFilter,
            $categoryFilter,
            $credentialFilter,
            $tz,
            $calendarMode
        );
        $response = new JsonResponse();
        $response->setData([
            'data' => $shiftsForThisMonth,
        ]);
        return $response;
    }

    #[Route('/shifts', name: 'app_provider_shifts', methods: ['GET', 'POST'])]
    public function getShiftCalendar(#[CurrentUser] $user, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->getContent();
            $response = $this->shiftService->saveShift($data);
            return new JsonResponse($response);
        }
        $userDTO = new NstMemberUserDTO($user);
        return $this->inertia->render('ProviderManageShiftCalendar', [
            'user' => $userDTO,
        ]);
    }

    #[Route('/shifts/create', name: 'app_provider_shift_create', methods: ['GET'])]
    public function getShiftCreate(#[CurrentUser] $user): Response
    {
        $userDTO = new NstMemberUserDTO($user);
        $categories = $this->shiftService->loadAllCategories();
        $categoryDTOs = array_map(function ($category) {
            return ShiftCategoryDTO::fromEntity($category);
        }, $categories);

        return $this->inertia->render('ProviderCreateShift', [
            'user' => $userDTO,
            'categories' => $categoryDTOs,
        ]);
    }

    #[Route('/shifts/requests', name: 'app_provider_shift_requests', methods: ['GET'])]
    public function getShiftRequests(#[CurrentUser] $user): Response
    {
        $userDTO = new NstMemberUserDTO($user);
        $shiftRequests = $this->providerService->loadShiftRequests($user);
        return $this->inertia->render('ProviderShiftRequests', [
            'user' => $userDTO,
            'shifts' => $shiftRequests,
        ]);
    }

    #[Route('/shifts/requests/{id}/approve', name: 'app_provider_shift_request_approve', methods: ['POST'])]
    public function approveShiftRequest(#[CurrentUser] $user, $id): Response
    {
        $this->providerService->approveShiftRequest($id);
        return new JsonResponse([
            'success' => true,
            'id' => $id,
        ]);
    }

    #[Route('/shifts/requests/{id}/deny', name: 'app_provider_shift_request_deny', methods: ['POST'])]
    public function denyShiftRequest(#[CurrentUser] $user, $id): Response
    {
        $this->providerService->denyShiftRequest($id);
        return new JsonResponse([
            'success' => true,
            'id' => $id,
        ]);
    }

    #[Route('/shifts/{id}/edit', name: 'app_provider_shift_edit', methods: ['GET'])]
    public function getShiftEdit(#[CurrentUser] $user, $id): Response
    {
        $userDTO = new NstMemberUserDTO($user);
        $shift = $this->shiftService->loadShiftById($id);

        return $this->inertia->render('ProviderEditShift', [
            'user' => $userDTO,
            'shift' => $shift,
        ]);
    }

    #[Route('/shifts/review', name: 'app_provider_shift_review', methods: ['GET'])]
    public function reviewShifts(#[CurrentUser] $user): Response
    {
        $userDTO = new NstMemberUserDTO($user);
        $payPeriods = $this->providerService->getPayPeriods();
        $start = Carbon::parse($payPeriods[0]['start']);
        $end = Carbon::parse($payPeriods[0]['end']);
        $shifts = $this->providerService->getPbjReport($user, $start, $end);

        return $this->inertia->render('ProviderReviewShifts', [
            'user' => $userDTO,
            'shifts' => $shifts,
            'payPeriods' => $payPeriods,
        ]);
    }

    #[Route('/shifts/pbj_report', name: 'app_provider_shift_pbj_report', methods: ['GET'])]
    public function getPbjReport(
        #[CurrentUser]              $user,
        #[MapQueryParameter] string $start,
        #[MapQueryParameter] string $end
    ): Response
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        $shifts = $this->providerService->getPbjReport($user, $start, $end);
        return new JsonResponse(
            [
                'payPeriods' => $shifts
            ]
        );
    }

    #[Route('/provider/create_shift', name: 'app_provider_create_shift', methods: ['POST'])]
    public function providerCreateShift(#[CurrentUser] $user, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $res = $this->providerService->providerCreateShift($data);
        if (str_contains($res, 'shift already exists')) {
            return new JsonResponse('Selected nurse has existing shifts for selected timeslots', 400);
        }
        return new JsonResponse($res);
    }

    #[Route('/shift/categories', name: 'app_provider_shift_categories', methods: ['GET'])]
    public function getShiftCategories(#[CurrentUser] $user): Response
    {
        $categories = $this->shiftService->loadAllCategories();
        $categoryDTOs = array_map(function ($category) {
            return ShiftCategoryDTO::fromEntity($category);
        }, $categories);

        return new JsonResponse($categoryDTOs);
    }
    #[Route("/shift/bulk", name: "app_provider_shift_bulk_delete", methods: ["DELETE"])]
    public function bulkDeleteShift(#[CurrentUser] $user, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $shiftIds = $data['shiftIds'];
        $this->shiftService->bulkDeleteShifts($user, $shiftIds);
        return new JsonResponse([
            'success' => true,
        ]);
    }

    #[Route('/shift/{id}', name: 'app_provider_shift_delete', methods: ['DELETE'])]
    public function deleteShift(#[CurrentUser] $user, $id): Response
    {
        $this->shiftService->deleteShift($id);
        return new JsonResponse([
            'success' => true,
            'id' => $id,
        ]);
    }

}
