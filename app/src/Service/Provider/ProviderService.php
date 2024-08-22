<?php

namespace App\Service\Provider;

use App\DTO\Member\NurseCredentialDTO;
use App\DTO\Member\NurseDTO;
use App\DTO\Member\ProviderDTO;
use App\DTO\Member\ProviderLocationDTO;
use App\DTO\Shift\PresetShiftTimeDTO;
use App\DTO\Shift\ShiftDTO;
use App\DTO\Shift\ShiftPayrollPaymentAggregateDTO;
use App\Entity\Nst\Events\Shift;
use App\Entity\Nst\Member\NstMemberUsers;
use App\Entity\Nst\Member\Nurse;
use App\Entity\Nst\Member\NurseCredential;
use App\Entity\Nst\Member\Provider;
use App\Entity\Nst\Payroll\PayrollPayment;
use App\Enum\MemberType;
use App\Repository\Nst\Member\NurseRepository;
use App\Repository\Nst\Member\ProviderRepository;
use App\Repository\Nst\Shift\ShiftRepository;
use App\Service\SaCommandService;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use function _PHPStan_dfcaa3082\React\Promise\all;

class ProviderService
{
    private ProviderRepository $providerRepository;
    private NurseRepository $nurseRepository;
    private ShiftRepository $shiftRepository;
    private SaCommandService $saCommandService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ProviderRepository     $providerRepository,
        NurseRepository        $nurseRepository,
        ShiftRepository        $shiftRepository,
        SaCommandService       $saCommandService,
        EntityManagerInterface $entityManager,
    )
    {
        $this->providerRepository = $providerRepository;
        $this->nurseRepository = $nurseRepository;
        $this->shiftRepository = $shiftRepository;
        $this->saCommandService = $saCommandService;
        $this->entityManager = $entityManager;
    }

    /**
     * @param NstMemberUsers $user
     * @return array<Provider>
     * @throws Exception
     */
    public function getProvidersForMember(NstMemberUsers $user): array
    {
        $memberType = $user->getMember()->getMemberType();
        if ($memberType == MemberType::Executive->name) {
            $providers = $user->getMember()->getExecutive()->getProviders()->toArray();
        } elseif ($memberType == MemberType::Provider->name) {
            $providers = [$user->getMember()->getProvider()];
        } else {
            throw new Exception("Invalid member type");
        }

        // sort them by company
        usort($providers, function ($a, $b) {
            return $a->getMember()->getCompany() <=> $b->getMember()->getCompany();
        });

        return $providers;
    }

    /**
     * @param NstMemberUsers $user
     * @param DateTime $start
     * @param DateTime $end
     * @param string $nurseType
     * @return array<Nurse>
     * @throws Exception
     */
    public function loadAssignableNurses(
        NstMemberUsers $user,
        string         $nurseType,
        int            $providerId,
        DateTime       $start = null,
        DateTime       $end = null,
    )
    {
        $types = strlen($nurseType) ? explode('/', $nurseType) : [];
        $allAvailability = !$start && !$end;

        if (!$allAvailability) {
            if ($end < $start) {
                $end->modify('+1 day');
            }
        }

        /** @var Provider $provider */
        $provider = $this->providerRepository->findOneBy(['id' => $providerId]);
        // aggregate blocked and previous nurses
        $blockedNurses = $provider->getBlockedNurses();
        $previousNurses = $provider->getPreviousNurses();

        $nurses = [];
        /** @var Nurse $nurse */
        foreach ($previousNurses as $nurse) {
            $nurseCreds = $nurse->getCredentials();
            if (!$blockedNurses->contains($nurse) && !$nurse->getIsDeleted()) {
                if (is_array($types) && in_array('CNA', $types)) {
                    if (!in_array('CMT', $types)) {
                        $types[] = 'CMT';
                    }
                } elseif ($types == 'CNA') {
                    $types = ['CMT', 'CNA'];
                } elseif ($types == 'CMT') {
                    $types = ['CNA', 'CMT'];
                }
                // Nurse is disabled if they have a shift in the time period.
                if (in_array($nurseCreds, $types)) {
                    if ($allAvailability) {
                        $nurses[] = $nurse;
                    } else {
                        $isAvailable = $this->nurseRepository->getNurseAvailability($start, $end, $nurse);
                        if ($isAvailable) {
                            $nurses[] = $nurse;
                        }
                    }
                }
            }
        }

        return $nurses;
    }

    /**
     * @param NstMemberUsers $user
     * @return array<ShiftDTO>
     * @throws Exception
     */
    public function loadShiftRequests(NstMemberUsers $user): array
    {
        $providers = $this->getProvidersForMember($user);
        $shifts = $this->shiftRepository->getShiftRequestsForProviders($providers);

        $shiftDTOs = array_map(function ($shift) {
            return ShiftDTO::fromEntity($shift);
        }, $shifts);

        usort($shiftDTOs, function ($a, $b) {
            return $a->date <=> $b->date;
        });

        return $shiftDTOs;
    }

    public function approveShiftRequest(int $id): void
    {
        /** @var Shift $shift */
        $shift = $this->shiftRepository->findOneBy(['id' => $id]);
        $is_recurrence = $shift->getIsRecurrence();
        $params = [
            'id' => $shift->getId(),
            'is_recurrence' => $is_recurrence,
        ];
        $this->saCommandService->executeSaCommand('shifts:approve', $params);
    }

    public function denyShiftRequest(int $id): void
    {
        /** @var Shift $shift */
        $shift = $this->shiftRepository->findOneBy(['id' => $id]);
        $params = [
            'id' => $shift->getId(),
        ];
        $this->saCommandService->executeSaCommand('shifts:deny', $params);
    }

    public function loadNursesForUser(NstMemberUsers $user, bool $blocked = false, string $search = null)
    {
        $providers = $this->getProvidersForMember($user);
        return array_map(
            function ($providers) {
                return [
                    'provider_id' => $providers['provider_id'],
                    'nurses' =>
                        array_map(function ($nurse) {
                            return new NurseDTO($nurse);
                        }, $providers['nurses'])
                ];
            },
            array_map(
                function ($provider) use ($blocked, $search) {
                    return [
                        'nurses' => array_values(array_filter(
                            $provider->getPreviousNurses()->toArray(),
                            function ($nurse) use ($blocked, $provider, $search) {
                                $isValid = true;
                                if ($search) {
                                    $isValid = str_contains(strtolower($nurse->getFirstName()), strtolower($search))
                                        || str_contains(strtolower($nurse->getLastName()), strtolower($search))
                                        || str_contains(strtolower($nurse->getFirstName() . ' ' . $nurse->getLastName()), strtolower($search));

                                }
                                if ($blocked) {
                                     // $provider->getBlockedNurses()->contains($nurse) && !$nurse->getIsDeleted();
                                    return $isValid && $provider->getBlockedNurses()->contains($nurse);
                                }

                                return $isValid && !$nurse->getIsDeleted();
                            }
                        )),
                        'provider_id' => $provider->getId(),
                    ];
                },
                $providers
            )
        );

        // TODO filters
        /*
            switch ($filters['worked_with']) {
                case 'Yes':
                    $validNurse = $prevNurses->contains($nurse);
                    break;
                case 'No':
                    $validNurse = !$prevNurses->contains($nurse);
                    break;
                default:
                    break;
            }

            if ($validNurse) {
                switch ($filters['unresolved_pay']) {
                    case 'Yes':
                        $validNurse = self::hasUnresolvedPay($nurse);
                        break;
                    case 'No':
                        $validNurse = !self::hasUnresolvedPay($nurse);
                        break;
                    default:
                        break;
                }
            }

            if ($validNurse) {
                switch ($filters['blocked']) {
                    case 'Yes':
                        $validNurse = $provider->getBlockedNurses()->contains($nurse);
                        break;
                    case 'No':
                        $validNurse = !$provider->getBlockedNurses()->contains($nurse);
                        break;
                    default:
                        break;
                }
            }

            if (!$validNurse) {
                continue;
            }
        */
    }

    public function blockNurse(NstMemberUsers $user, array $providerIds, int $nurseId): void
    {
        // make sure $user has access to $providerId
        $managedProviders = $this->getProvidersForMember($user);
        $managedProviderIds = array_map(function ($provider) {
            return $provider->getId();
        }, $managedProviders);

        foreach ($providerIds as $providerId) {
            if (!in_array($providerId, $managedProviderIds)) {
                throw new Exception("User does not have access to provider");
            }
            var_dump("blocking nurse $nurseId for provider $providerId");
            /** @var Provider $provider */
            $provider = $this->providerRepository->findOneBy(['id' => $providerId]);

            /** @var Nurse $nurse */
            $nurse = $this->nurseRepository->findOneBy(['id' => $nurseId]);

            if (!$provider->getBlockedNurses()->contains($nurse)) {
                $provider->addBlockedNurse($nurse);
                $nurse->addBlockedProvider($provider);
            }

            $this->entityManager->persist($provider);
            $this->entityManager->persist($nurse);
            $this->entityManager->flush();
        }
        die();
    }

    public function unblockNurse(NstMemberUsers $user, int $providerId, int $nurseId): void
    {
        // make sure $user has access to $providerId
        $providers = $this->getProvidersForMember($user);
        $providerIds = array_map(function ($provider) {
            return $provider->getId();
        }, $providers);

        if (!in_array($providerId, $providerIds)) {
            throw new Exception("User does not have access to provider");
        }

        /** @var Provider $provider */
        $provider = $this->providerRepository->findOneBy(['id' => $providerId]);

        /** @var Nurse $nurse */
        $nurse = $this->nurseRepository->findOneBy(['id' => $nurseId]);

        if ($provider->getBlockedNurses()->contains($nurse)) {
            $provider->removeBlockedNurse($nurse);
            $nurse->removeBlockedProvider($provider);
        }

        $this->entityManager->persist($provider);
        $this->entityManager->persist($nurse);
        $this->entityManager->flush();
    }

    public function getBlockedNursesForUser(NstMemberUsers $user): array
    {
        return $this->loadNursesForUser($user, true);
    }

    /**
     * @param NstMemberUsers $user
     * @param DateTime $start
     * @param DateTime $end
     * @return Array<ShiftPayrollPaymentAggregateDTO>
     * @throws Exception
     */
    public function getPbjReport(NstMemberUsers $user, DateTime $start, DateTime $end): array
    {
        $providers = $this->getProvidersForMember($user);
        $end->modify('+1 day');

        $returnShifts = array();
        foreach ($providers as $provider) {
            /** @var Shift $shifts [] */
            $shifts = $this->shiftRepository->providerShiftsInTimeFrame($start, $end, $provider);

            /** @var Shift $shift */
            foreach ($shifts as $shift) {
                $shiftPayrollPaymentAggregateDTO = new ShiftPayrollPaymentAggregateDTO();
                $shiftPayrollPaymentAggregateDTO->provider = new ProviderDTO($provider);
                $shiftPayrollPaymentAggregateDTO->date = $shift->getStart()->format('Y-m-d H:i:s');
                $payrollPayments = $shift?->getPayrollPayments();

                /** @var PayrollPayment $payrollPayment [] */
                foreach ($payrollPayments as $payment) {
                    $shiftPayrollPaymentAggregateDTO->clockedHours[] = round($payment->getClockedHours(), 2);
                    $shiftPayrollPaymentAggregateDTO->billRate[] = round($payment->getBillRate(), 2);
                    $shiftPayrollPaymentAggregateDTO->billTotal[] = round($payment->getBillTotal(), 2);
                    $shiftPayrollPaymentAggregateDTO->bonus += $payment->getBillBonus();
                    // TODO why is this like this?
                    $shiftPayrollPaymentAggregateDTO->travelPay = $payment->getBillTravel();
                    $shiftPayrollPaymentAggregateDTO->holidayPay = $payment->getBillHoliday();
                }

                // skip shifts that do not have a bill total
                if (empty(array_filter($shiftPayrollPaymentAggregateDTO->billTotal, function ($bill_total) {
                    return $bill_total != 0;
                }))) {
                    continue;
                }

                /** @var Nurse $nurse */
                $nurse = $shift?->getNurse();
                if ($nurse) {
                    $nurseDTO = new NurseDTO($nurse);
                    $shiftPayrollPaymentAggregateDTO->nurse = $nurseDTO;
                }

                $returnShifts[] = $shiftPayrollPaymentAggregateDTO;
            }

        }
        return $returnShifts;
    }

    public function getPayPeriods()
    {
        $periods = [];
        $stopDate = new DateTime('2022/01/02 00:00:00');
        $stopDateTimestamp = $stopDate->getTimestamp();

        // Just setting to 200 for some sort of limit
        for ($i = 0; $i < 200; $i++) {
            $date = Carbon::now();
            if ($i > 0) {
                $days = $i * 7;
                $modifier = '-' . $days . ' days';
                $date->modify($modifier);
            }

            // 1/3/2022 - 1/10/2022 was the first pay period in the system
            if ($date->getTimestamp() < $stopDateTimestamp) {
                break;
            }

            $period = self::calculatePayperiodFromDate($date);
            $periods[] = $period;
        }

        return $periods;
    }

    public function calculatePayPeriodFromDate($date)
    {
        $period = [];

        if ($date->format('l') == 'Monday') {
            $startDate = $date;
        } else {
            $startDate = new Carbon('last Monday ' . $date->format('m/d/Y'));
        }

        if ($date->format('l') == 'Sunday') {
            $endDate = $date;
        } else {
            $endDate = new Carbon('next Sunday ' . $date->format('m/d/Y'));
        }

        $period['start'] = $startDate;
        $period['end'] = $endDate;
        $period['combined'] = $startDate->format('Ymd') . '_' . $endDate->format('Ymd');
        $period['display'] = $startDate->format('m/d/Y') . ' - ' . $endDate->format('m/d/Y');
        return $period;
    }

    public function getProviderById(int $providerId): Provider
    {
        return $this->providerRepository->findOneBy(['id' => $providerId]);
    }

    public function getUserCanAccessProvider(NstMemberUsers $executive, int $providerId)
    {
        // make sure user owns provider
        $providers = $this->getProvidersForMember($executive);
        $providerIds = array_map(function ($provider) {
            return $provider->getId();
        }, $providers);

        return in_array($providerId, $providerIds);
    }

    public function loadProviderTimeslots(NstMemberUsers $user, int $providerId)
    {
        $execCanManageProvider = $this->getUserCanAccessProvider($user, $providerId);
        if (!$execCanManageProvider) {
            throw new Exception("User does not have access to provider");
        }

        /** @var Provider $provider */
        $provider = $this->providerRepository->findOneBy(['id' => $providerId]);
        $presetShiftTimes = $provider->getPresetShiftTimes();
        return array_map(function ($shiftTime) {
            return new PresetShiftTimeDTO($shiftTime);
        }, $presetShiftTimes->toArray());
    }

    public function providerCreateShift($data)
    {
        return $this->saCommandService->executeSaCommandWithJson('provider:create_shift', json_encode($data));
    }

    public function loadProviderCredentials(NstMemberUsers $user, int $providerId)
    {
        $execCanManageProvider = $this->getUserCanAccessProvider($user, $providerId);
        if (!$execCanManageProvider) {
            throw new Exception("User does not have access to provider");
        }
        /** @var Provider $provider */
        $provider = $this->providerRepository->findOneBy(['id' => $providerId]);
        return array_map(function (NurseCredential $credential) {
            return new NurseCredentialDTO($credential);
        }, $provider->getNurseCredentials()->toArray());
    }

    public function getProviderData(NstMemberUsers $user, Provider|int $provider)
    {
        if (is_int($provider)) {
            $provider = $this->providerRepository->findOneBy(['id' => $provider]);
        }

        $providerId = $provider->getId();
        $userCanAccessProvider = $this->getUserCanAccessProvider($user, $providerId);
        if (!$userCanAccessProvider) {
            throw new Exception("User does not have access to provider");
        }

        $previousNurses = $provider->getPreviousNurses()->toArray();
        $unclaimedShiftCount = $this->providerRepository->getUnclaimedShiftsCount($providerId);
        $shiftRequestCount = $this->providerRepository->getShiftRequestsCount($providerId);
        $unresolvedPaymentCount = $this->providerRepository->getUnresolvedPaymentsCount($providerId);
        $currentPayPeriod = $this->calculatePayPeriodFromDate(new DateTime('now'));

        return new ProviderLocationDTO(
            $provider,
            $shiftRequestCount,
            $unresolvedPaymentCount,
            $unclaimedShiftCount,
            $currentPayPeriod['display'],
            $previousNurses
        );
    }

    public function getProvidersData(NstMemberUsers $user)
    {
        $providers = $this->getProvidersForMember($user);
        return array_map(function ($provider) use ($user) {
            return $this->getProviderData($user, $provider);
        }, $providers);
    }

    public function getProviderRates(NstMemberUsers $user, int $providerId)
    {
        $execCanManageProvider = $this->getUserCanAccessProvider($user, $providerId);
        if (!$execCanManageProvider) {
            throw new Exception("User does not have access to provider");
        }
        /** @var Provider $provider */
        $provider = $this->providerRepository->findOneBy(['id' => $providerId]);
        return $provider->getPayRates();
    }
}
