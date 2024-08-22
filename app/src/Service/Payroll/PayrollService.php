<?php

namespace App\Service\Payroll;

use App\DTO\Payroll\NursePayrollPaymentDTO;
use App\DTO\Payroll\PayrollPaymentDTO;
use App\Entity\Nst\Events\Shift;
use App\Entity\Nst\Member\NstMemberUsers;
use App\Entity\Nst\Member\Nurse;
use App\Entity\Nst\Member\Provider;
use App\Entity\Nst\Payroll\PayrollPayment;
use App\Repository\Nst\Payroll\PayrollPaymentRepository;
use App\Service\Provider\ProviderService;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use PhpOffice\PhpSpreadsheet\Shared\TimeZone;

class PayrollService
{
    private PayrollPaymentRepository $payrollPaymentRepository;
    private EntityManagerInterface $entityManager;
    private ProviderService $providerService;

    public function __construct(
        PayrollPaymentRepository $payrollPaymentRepository,
        EntityManagerInterface   $entityManager,
        ProviderService          $providerService
    )
    {
        $this->payrollPaymentRepository = $payrollPaymentRepository;
        $this->entityManager = $entityManager;
        $this->providerService = $providerService;
    }

    /**
     * @throws \Exception
     */
    public function getShiftPaymentsForUser(
        NstMemberUsers $user,
        DateTime       $date = null,
        DateTime       $payPeriodStart = null,
        DateTime       $payPeriodEnd = null,
        Nurse          $nurse = null,
        bool           $unresolvedOnly = false,
        bool           $getZeroHourPayments = false,
        bool           $returnPayments = false,
    )
    {
        $providers = $this->providerService->getProvidersForMember($user);

        $payments = [];
        foreach ($providers as $provider) {
            $paymentsForProvider = $this->getShiftPaymentsForProvider(
                $provider,
                payPeriodStart: $payPeriodStart,
                payPeriodEnd: $payPeriodEnd,
                unresolvedOnly: $unresolvedOnly,
                getZeroHourPayments: $getZeroHourPayments,
                returnPayments: $returnPayments,
            );
            if (isset($paymentsForProvider['shift_payments'])) {
                $payments = array_merge($payments, $paymentsForProvider['shift_payments']);
            }
        }
        // sort them by start time
        usort($payments, function ($a, $b) {
            $aStart = Carbon::parse($a->shift->start);
            $bStart = Carbon::parse($b->shift->start);
            if ($aStart == $bStart) {
                return 0;
            }
            return $aStart < $bStart ? -1 : 1;
        });
        return $payments;
    }

    /**
     * @throws \Exception
     */
    public function getShiftPaymentsForProvider(
        Provider $provider,
        DateTime $date = null,
        DateTime $payPeriodStart = null,
        DateTime $payPeriodEnd = null,
        Nurse    $nurse = null,
        bool     $unresolvedOnly = false,
        bool     $getZeroHourPayments = false,
        bool     $returnPayments = false,
    )
    {
        // TODO handle case when nurse is selected
        //$nurseId = (int)$data['nurse_id'];
        $response = [];
        $status = null;
        if ($unresolvedOnly) {
            $status = 'Unresolved';
        }

        // Single-day date-range for single day filtering
        $dateRange = null;
        if ($date) {
            $date = new DateTime($date);
            $dateRange[] = new DateTime($date->format('Y-m-d') . " 00:00:00");
            $dateRange[] = new DateTime($date->format('Y-m-d') . " 23:59:59");
        }

        $payPeriodStart->setTime(0, 0, 0);
        $payPeriodEnd->setTime(23, 59, 59);
        $payments = $this->payrollPaymentRepository->getPaymentsBetweenDates(
            $provider->getId(),
            $payPeriodStart,
            $payPeriodEnd,
            false,
            false,
            false,
            $returnPayments ? $nurse->getId() : null,
            $status,
            $dateRange
        );

        if ($returnPayments) {
            return $payments;
        }

        if ($payments) {
            /** @var PayrollPayment $payment */
            foreach ($payments as $payment) {
                if ($unresolvedOnly == "true" && !in_array($payment->getStatus(), ['Unresolved', 'Change Requested'])) {
                    $response['payment-status-wrong'][$payment->getId()] = $payment->getId();
                    continue;
                }

                $shift = $this->payrollPaymentRepository->getShiftForPayment($payment);
                if (!$shift) {
                    $response['shift-missing'][$payment->getId()] = $payment->getId();
                    continue;
                }

                $nurse = $shift->getNurse();
                if (!$nurse) {
                    $response['shift-missing-nurse'][$shift->getId()] = $payment->getId();
                    continue;
                }
                $member = $nurse->getMember();

                $shiftStart = $shift->getStart();
                $shiftEnd = $shift->getEnd();

                $clockInTime = $shift->getClockInTime();
                if (!$clockInTime) {
                    $clockInTime = $shift->getStartTime();
                    $response['missing_clockin_time'] = $shift->getId();
                }

                $clockOutTime = $shift->getClockOutTime();
                if (!$clockOutTime) {
                    $clockOutTime = $shift->getEndTime();
                    $response['missing_clockout_time'] = $shift->getId();
                }

                if ($clockInTime > $clockOutTime) {
                    $clockOutTime->modify('+1 days');
                }

                $clockedHours = $payment->getClockedHours();

                if ($clockedHours < 0) {
                    $fixedInfo = static::fixNegativeClockedHours($payment, $shift);
                    $shiftStart = $fixedInfo['clockInTime'];
                    $clockedHours = $fixedInfo['clocked_hours'];
                }

                $date = $shiftStart->format('m/d/Y');
                if ($shift->getIsEndDateEnabled()) {
                    $shiftEndDate = new DateTime($shift->getEnd(), $this->timezone);
                    $date .= ' - ' . $shiftEndDate->format('m/d/Y');
                }
                if ($clockedHours > 0 || $clockedHours < 0 || $getZeroHourPayments) {
                    $response['shift_payments'][] = new PayrollPaymentDTO($payment, $provider);
                }
            }

        }
        return $response;
    }


    public function fixNegativeClockedHours(PayrollPayment $payment, Shift $shift)
    {

        $clockInTime = $shift->getClockInTime();
        $clockOutTime = $shift->getClockOutTime();
        $clockedHours = (float)number_format(($clockOutTime->getTimestamp() - $clockInTime->getTimestamp()) / 3600, 2);

        if ($clockInTime > $clockOutTime) {
            $clockOutTime->modify('+1 days');
            $shift->setClockOutTime($clockOutTime);
            $clockedHours = (float)number_format(($clockOutTime->getTimestamp() - $clockInTime->getTimestamp()) / 3600, 2);
        }

        $lunchBreakTime = $shift->getLunchOverride();
        if ($lunchBreakTime > $clockedHours) {
            $shift->setLunchOverride($clockedHours);
            $clockedHours = 0;
        }
        $this->entityManager->flush($shift);

        $payment->setClockedHours($clockedHours);
        $payment->setPayTotal($payment->getCalculatedPayTotal());
        $payment->setBillTotal($payment->getCalculatedBillTotal());

        $this->entityManager->flush($payment);

        $return['clockInTime'] = $clockInTime;
        $return['clockOutTime'] = $clockOutTime;
        $return['clocked_hours'] = $clockedHours;

        return $return;
    }

    public function getNursePaymentsForProvider(
        Provider $provider,
        bool $allPayPeriods = false,
        DateTime $payPeriodStart = null,
        DateTime $payPeriodEnd = null,
        bool $unresolvedOnly = false
    )
    {
        if ($allPayPeriods) {
            $payments = $this->payrollPaymentRepository->getPayments($provider->getId());
        } else {
            $payments = $this->payrollPaymentRepository->getPaymentsBetweenDates($provider->getId(), $payPeriodStart, $payPeriodEnd);
        }
        $response = [
            'nurse_payments' => [],
        ];
        if ($payments) {
            /** @var PayrollPayment $payment */
            foreach ($payments as $payment) {
                if ($unresolvedOnly && $payment->getStatus() != 'Unresolved') {
                    continue;
                }
                $shift = $payment->getShift();
                if (!$shift) {
                    continue;
                }

                $nurse = $shift->getNurse();
                if (!is_object($nurse)) {
                    continue;
                }

                $member = $nurse->getMember();
                if (!$shift->getProvider()) {
                    $response['missing_provider'] = $shift->getId();
                    continue;
                }
                if (!$shift->getClockOutTime()) {
                    $shift->setClockOutTime($shift->getEndTime());
                    $response['missing_clockout_time'] = $shift->getId();
                }
                if (!$shift->getClockInTime()) {
                    $shift->setClockInTime($shift->getStartTime());
                    $response['missing_clockin_time'] = $shift->getId();
                }

                $clockedHours = number_format(($shift->getClockOutTime()->getTimestamp() - $shift->getClockInTime()->getTimestamp()) / 3600, 2);

                $hasUnresolvedPayments = $payment->getStatus() == 'Unresolved';


                if (!isset($response['nurse_payments'][$nurse->getId()])) {
                    $response['nurse_payments'][$nurse->getId()] =
                        new NursePayrollPaymentDTO($payment, $provider, $nurse, $hasUnresolvedPayments);
                } else {
                    $response['nurse_payments'][$nurse->getId()]->clockedHours += (float)$clockedHours;
                    $response['nurse_payments'][$nurse->getId()]->payTotal += $payment->getPayTotal();
                    $response['nurse_payments'][$nurse->getId()]->payBonus += $payment->getPayBonus();
                    $response['nurse_payments'][$nurse->getId()]->hasUnresolvedPayments = $hasUnresolvedPayments ? 'Yes' : $response['nurse_payments'][$nurse->getId()]->hasUnresolvedPayments;
                }
            }
        }

        $response['success'] = true;
        return $response;
    }
    public function requestChange(
        $paymentId,
        $description,
        $clockIn,
        $clockOut
    )
    {
        $response = ['success' => false];
        /** @var PayrollPayment $payment */
        $payment = $this->payrollPaymentRepository->find($paymentId);
        if ($payment) {
            $payment->setStatus('Change Requested');
            $payment->setRequestDescription($description);

            if ($clockIn) {
                $clockInTime = Carbon::parse($clockIn);
                $payment->setRequestClockIn($clockInTime);
            }
            if ($clockOut) {
                $clockOutTime = Carbon::parse($clockOut);
                $payment->setRequestClockOut($clockOutTime);
            }

            $this->entityManager->flush();
            $response['success'] = true;
        }

        return $response;
    }

    public function getNursePaymentsForUser(
        NstMemberUsers $user,
        bool $allPayPeriods = false,
        DateTime $payPeriodStart = null,
        DateTime $payPeriodEnd = null,
        bool $unresolvedOnly = false
    ) {
        $providers = $this->providerService->getProvidersForMember($user);
        $response = [
            'nurse_payments' => [],
        ];
        foreach ($providers as $provider) {
            $paymentsForProvider = $this->getNursePaymentsForProvider(
                $provider,
                $allPayPeriods,
                $payPeriodStart,
                $payPeriodEnd,
                $unresolvedOnly
            );
            if (isset($paymentsForProvider['nurse_payments'])) {
                $response['nurse_payments'] = array_merge($response['nurse_payments'], array_values($paymentsForProvider['nurse_payments']));
            }
        }
        return $response;
    }
}
