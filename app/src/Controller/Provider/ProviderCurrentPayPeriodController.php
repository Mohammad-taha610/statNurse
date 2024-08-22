<?php
namespace App\Controller\Provider;

use App\Controller\AbstractController;
use App\DTO\Member\NstMemberUserDTO;
use App\Service\Payroll\PayrollService;
use App\Service\Provider\ProviderService;
use Carbon\Carbon;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;


class ProviderCurrentPayPeriodController extends AbstractController
{
    private ProviderService $providerService;
    private PayrollService $payrollService;

    public function __construct(
        InertiaInterface $inertia,
        ProviderService $providerService,
        PayrollService $payrollService
    )
    {
        parent::__construct($inertia);
        $this->providerService = $providerService;
        $this->payrollService = $payrollService;
    }

    #[Route('/payroll/current_pay_period', name: 'app_provider_current_pay_period', methods: ['GET'])]
    public function getCurrentPayPeriod(#[CurrentUser] $user, Request $request): Response
    {
        $userDTO = new NstMemberUserDTO($user);
        $payPeriods = array_slice($this->providerService->getPayPeriods(),0, 8);
        return $this->inertia->render('ProviderCurrentPayPeriod', [
            'user' => $userDTO,
            'payPeriods' => $payPeriods,
        ]);
    }

    #[Route('/payroll/payments')]
    public function getPaymentsForPayPeriod(#[CurrentUser] $user, Request $request): Response
    {
        $userDTO = new NstMemberUserDTO($user);
        $data = $request->query->all();
        $payPeriodStart = Carbon::parse($data['payPeriodStart']);
        $payPeriodEnd = Carbon::parse($data['payPeriodEnd']);
        $unresolvedOnly = $data['unresolvedOnly'] == 'true';
        $payments = $this->payrollService->getShiftPaymentsForUser(
            $user,
            payPeriodStart: $payPeriodStart,
            payPeriodEnd: $payPeriodEnd,
            unresolvedOnly: $unresolvedOnly
        );
        return new JsonResponse([
            'payments' => $payments,
        ]);
    }

    #[Route('/payroll/nurse_payments')]
    public function getNursePaymentsForPayPeriod(#[CurrentUser] $user, Request $request): Response
    {
        $userDTO = new NstMemberUserDTO($user);
        $data = $request->query->all();
        $payPeriodStart = Carbon::parse($data['payPeriodStart']);
        $payPeriodEnd = Carbon::parse($data['payPeriodEnd']);
        $unresolvedOnly = $data['unresolvedOnly'] == 'true';
        $payments = $this->payrollService->getNursePaymentsForUser(
            $user,
            payPeriodStart: $payPeriodStart,
            payPeriodEnd: $payPeriodEnd,
            unresolvedOnly: $unresolvedOnly
        )['nurse_payments'];
        return new JsonResponse([
            'payments' => $payments,
        ]);
    }

    #[Route('/payroll/payments/{paymentId}/request_change', methods: ['POST'])]
    public function requestChange(#[CurrentUser] $user, int $paymentId, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $requestDescription = $data['description'];
        $requestClockIn = $data['clockIn'];
        $requestClockOut = $data['clockOut'];

        $response = $this->payrollService->requestChange(
            paymentId: $paymentId,
            description: $requestDescription,
            clockIn: $requestClockIn,
            clockOut: $requestClockOut
        );
        return new JsonResponse([
            'response' => $response,
        ]);
    }

}
