<?php

namespace nst\member;

use Doctrine\Common\Collections\ArrayCollection;
use nst\events\Shift;
use nst\events\ShiftService;
use nst\messages\NstPushNotificationService;
use nst\payroll\PayrollService;
use nst\system\NstStateRepository;
use sa\api\Responses\ApiJsonResponse;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\responses\File;
use sa\events\Category;
use sa\member\MemberApiV1Controller;
use sa\member\saMember;
use sa\member\saMemberEmail;
use sa\member\saMemberUsers;
use sa\messages\saEmail;
use sa\system\saState;
use sacore\utilities\doctrineUtils;
use sacore\application\Request;
use sacore\application\modRequest;
use sacore\application\responses\Json;
use mikehaertl\pdftk\Pdf;
use Throwable;

/**
 * @IOC_NAME="MemberApiV1Controller"
 */
class NstMemberApiV1Controller extends \sa\member\MemberApiV1Controller
{
    /**
     * @param Request $request
     * @return boolean
     */
    private static function loginAttemptCheck(Request $request, bool $validLogin) {
        $ip = $request->getClientIp();
        $username = self::getRequestJsonArrayBody($request)["username"];

        // add new attempt
        $loginAttemptService = new LoginAttemptService();
        $loginAttemptService->newLoginAttempt($username);


        // check if they are locked out
        $isLockedOut = $loginAttemptService->isNurseLockedOut($username);

        if (!$isLockedOut && $validLogin) {
            $loginAttemptService->clearLoginAttempts($username);
        }

        return $isLockedOut;
    }

    /**
     * @param Request $request
     */
    public function login(Request $request): Json
    {
        $response = parent::login($request);
        if(!$response->data['success']) {
            return $response;
        }

        $lockedOut = self::loginAttemptCheck($request, $response->data['success']);
        // if they have logged in too many times, return a 429 code
        if ($lockedOut) {
            // error code 429 for too many login attempts
            $response->setResponseCode(429);
            return $response;
        }

        /** @var NstMember $member */
        $member = ioc::get('NstMember', ['id' => $response->data['response']['member']['id']]);
        $nurse = $member->getNurse();

        $data = json_decode($request->getContent(), true);

        if ($data['firebase_token']) {
            $nurse->setFirebaseToken($data['firebase_token']);
            app::$entityManager->flush($nurse);
        }

        /** @var saMemberUsers $user */
        $user = $member->getUsers()[0];
        if (!$user->getEmail()) {
            if (count($member->getEmails()) > 0) {
                $user->setEmail($member->getEmails()[0]);
            } else {
                /** @var saMemberEmail $email */
                $email = ioc::resolve('saMemberEmail');
                $email->setEmail($nurse->getEmailAddress() ?? $member->getFirstName() . '_' . $member->getLastName() . '@nursestatky.com');
                $email->setIsActive(true);
                $email->setIsPrimary(true);
                $email->setMember($member);
                $email->setType('Personal');
                app::$entityManager->persist($email);
                $user->setEmail($email);
            }
            app::$entityManager->flush();
        }

        $phoneNumber = $nurse->getPhoneNumber() ?: (count($nurse->getMember()->getPhones()) ? $nurse->getMember()->getPhones()[0]->getPhone() : '');
        $numberDisplay = preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $phoneNumber);
        $response->data['response']['nurse'] = [
            'id' => $nurse->getId(),
            'credentials' => $nurse->getCredentials(),
            'full_name' => $nurse->getMember()->getFirstName() . ' ' . $nurse->getMember()->getLastName(),
            'phone_number' => $phoneNumber,
            'phone_number_display' => $numberDisplay,
            'email' => $nurse->getEmailAddress() ?: (count($nurse->getMember()->getEmails()) > 0 ? $nurse->getMember()->getEmails()[0]->getEmail() : ''),
            'license_expiration_date' => $nurse->getLicenseExpirationDate() ? $nurse->getLicenseExpirationDate()?->format('m/d/Y') : '...',
            'tb_skintest_expiration_date' => $nurse->getSkinTestExpirationDate() ? $nurse->getSkinTestExpirationDate()?->format('m/d/Y') : '...',
            'cpr_expiration_date' => $nurse->getCprExpirationDate() ? $nurse->getCprExpirationDate()?->format('m/d/Y') : '...',
            'date_of_hire' => $nurse->getDateOfHire()
        ];

        // Load all shift categories
        $categories = ioc::getRepository(ioc::staticResolve('\sa\events\Category'))->findAll();
        /** @var Category $category */
        foreach ($categories as $category) {
            $response->data['response']['categories'][] = [
                'id' => $category->getId(),
                'name' => $category->getName()
            ];
        }

        $preferredProviders = $nurse->getPreferredProviders();

        file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/nstmemberapiv1.txt', 'hello' . PHP_EOL, FILE_APPEND);
        // Load all providers
        $providers = ioc::getRepository('Provider')->findAll();
        /** @var Provider $provider */
        foreach ($providers as $provider) {
            $response->data['response']['providers'][] = [
                'id' => $provider->getId(),
                'name' => $provider->getMember()->getCompany(),
                'is_preferred' => $preferredProviders->contains($provider),
                'address_1' => $provider->getStreetAddress(),
                'zipcode' => $provider->getZipcode(),
                'city' => $provider->getCity(),
                'state' => $provider->getStateAbbreviation(),
                'break_duration_in_minutes' => $provider->getBreakLengthInMinutes(),
            ];
        }

        // Data for the current shift
        $shiftService = new ShiftService();
        /** @var Shift $shift */
        $shift = $shiftService->findCurrentShiftForNurse($nurse);

        if ($shift) {
            $response->data['response']['current_shift'] = [
                'id' => $shift->getId(),
                'start' => $shift->getStart()->format('Y-m-d\TH:i:s'),
                'end' => $shift->getEnd()->format('Y-m-d\TH:i:s'),
                'provider_name' => $shift->getProvider()->getMember()->getCompany(),
                'provider_id' => $shift->getProvider()->getId(),
                'clock_in_type' => $shift->getClockInType()
            ];
        }

        return $response;
    }

    public function getAllStateNames($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);

        $service = new NurseService();
        $response->data['response'] = $service->getAllStateNames($data);

        return $response;
    }

    /**
     * @param Request $request
     */
    public function getNotificationsForNurse($request)
    {
        $response = new ApiJsonResponse();

        $data = json_decode($request->getContent(), true);

        $service = new NstPushNotificationService();
        $response->data['response'] = $service->getNotificationsForNurse($data);

        return $response;
    }

    public function markNotificationAsRead($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);
        $markAll = $data['mark_all'];

        $service = new NstPushNotificationService();
        if ($markAll) {
            $nurse_id = $data['nurse_id'];
            $response->data['response'] = $service->markAllNotificationsAsRead($nurse_id);
        } else {
            $response->data['response'] = $service->markNotificationAsRead($data);
        }
        return $response;
    }

    public function getAccountData($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);

        $service = new NurseService();
        $response->data['response'] = $service->getAccountData($data);

        return $response;
    }

    public function saveAccountData($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);

        $service = new NurseService();
        $response->data['response'] = $service->saveAccountData($data);

        return $response;
    }

    public static function savePayrollConfigurationData($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);

        $service = new NurseService();
        $response->data['response'] = $service->savePayrollConfigurationData($data);

        return $response;
    }

    public static function getPayrollConfigurationData($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);

        $service = new NurseService();
        $response->data['response'] = $service->getPayrollConfigurationData($data);

        return $response;
    }

    public static function getPayReportsData($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);

        $service = new NurseService();
        $response->data['response'] = $service->getPayReportsData($data);

        return $response;
    }

    public static function getPayStubPDF($request)
    {
        $data = json_decode($request->getContent(), true);

        $service = new PayrollService();
        $response = $service->getPayStubPDF($data);

        return $response;
    }

    public static function getMyProfile($request = null): Json
    {
        $response = new ApiJsonResponse();

        /** @var saMember $me */
        $me = modRequest::request('auth.member');
        if ($me === null) {
            return new Json();
        }
        $return_array = [];
        $users = $me->getUsers();

        /** @var saMemberUsers $user */
        foreach ($users as $user) {
            $user_array = doctrineUtils::getEntityArray($user);
            //            $user_array['email'] = doctrineUtils::getEntityCollectionArray($user->getEmail());
            $user_array['email'] = doctrineUtils::getEntityArray($user->getEmail());
            $return_array['users'][] = $user_array;
        }

        $memberArray = doctrineUtils::convertEntityToArray($me);

        foreach ($return_array['users'] as &$userArray) {
            if (!$userArray['email']) {
                continue;
            }

            $userArray['email']['is_active'] = $userArray['email']['is_active'] ? true : false;
            $userArray['email']['is_primary'] = $userArray['email']['is_primary'] ? true : false;
        }

        foreach ($memberArray['phones'] as &$phoneArray) {
            $phoneArray['is_active'] = $phoneArray['is_active'] ? true : false;
            $phoneArray['is_primary'] = $phoneArray['is_primary'] ? true : false;
        }

        foreach ($memberArray['emails'] as &$emailArray) {
            $emailArray['is_active'] = $emailArray['is_active'] ? true : false;
            $emailArray['is_primary'] = $emailArray['is_primary'] ? true : false;
        }

        foreach ($memberArray['addresses'] as &$addressArray) {
            $addressArray['is_active'] = $addressArray['is_active'] ? true : false;
            $addressArray['is_primary'] = $addressArray['is_primary'] ? true : false;
        }

        $response->data['response'] = [
            'success' => true,
            'profile' => $memberArray,
            'profileUsers' => $return_array
        ];

        return $response;
    }

    public static function getCountries(): Json
    {
        $response = new ApiJsonResponse();
        /** @var saCountryRepository $countries */
        $countries = ioc::getRepository('saCountry')->findAll();

        $arr = [];
        foreach ($countries as $country) {
            $arr[] = [
                'id' => $country->getId(),
                'abbreviation' => $country->getAbbreviation(),
                'name' => $country->getName()
            ];
        }
        usort($arr, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        $response->data['response'] = [
            'success' => true,
            'countries' => $arr
        ];
        return $response;
    }

    public static function get1099PDF($request)
    {
        $res = new ApiJsonResponse();
        try {
            $data = json_decode($request->getContent(), true);

            /* @var \nst\member\Nurse $nurse */
            $nurse = ioc::getRepository('Nurse')->find($data['id']);
            if (!$nurse) {
                return $res;
            }

            $fileName = '';
            $files = $nurse->getNurseFiles();

            /* @var NstFile $file */
            foreach ($files as $file) {
                if ($file->getTag()->getName() == '1099') {
                    $fileName = $file->getDiskFileName();
                    break;
                }
            }

            $res->data['response'] = [
                'nurse' => $nurse
            ];

            $uploadsDir = app::get()->getConfiguration()->get('uploadsDir')->getValue();
            $filePath = $uploadsDir . DIRECTORY_SEPARATOR . $fileName;

            // break the pdf up into multiple files with burst
            $pdf = new Pdf($filePath);
            $basePath = $uploadsDir . DIRECTORY_SEPARATOR . strtok($fileName, '.');
            $result = $pdf?->burst($basePath . 'page_%d.pdf');
            if ($result !== false) {
                // return the 4th file
                $res = new File($filePath);
            }
            else {
                $res = new File($filePath);
            }
            $res->setDownloadable(true);
        } catch (\Throwable $e) {
            $apiJsonResponseRef = ioc::staticGet('ApiJsonResponse');

            /** @var ApiJsonResponse $return */
            $return = new $apiJsonResponseRef(500);
            $return->data['message'] = '';
            $return->data['success'] = false;

            return $return;
        } catch (\Exception $e) {
            $apiJsonResponseRef = ioc::staticGet('ApiJsonResponse');

            /** @var ApiJsonResponse $return */
            $return = new $apiJsonResponseRef(500);
            $return->data['message'] = '';
            $return->data['success'] = false;

            return $return;
        }

        return $res;
    }

    public static function sendSmsCode($request) {
        try {

        $data = json_decode($request->getContent(), true);
        $phone_number = $data['phone_number'];

        $service = new NurseService();
        $code = $service->sendSmsCodeToNurse($phone_number);

        $response = new ApiJsonResponse();
        $response->data['response'] = [
            'success' => true,
            'code' => $code
        ];

        }
        catch (\Throwable $e) {
            file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/sendsmscode.txt', $e->getMessage(), FILE_APPEND);
        }
        return $response;
    }

    public static function updateNursePhone($request) {
        try {

        $data = json_decode($request->getContent(), true);
        $nurse_id = $data['id'];
        $phone_number = $data['phone_number'];
        $code = $data['code'];

        $service = new NurseService();
        $was_updated = $service->updateNursePhone($phone_number, $code, $nurse_id);

        }
        catch (\Throwable $e) {
            file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/sendsmscode.txt', $e->getMessage(), FILE_APPEND);
        }
        $response = new ApiJsonResponse();
        $response->data['response'] = [
            'success' => $was_updated
        ];
        return $response;
    }

    public static function updatePushToken(Request $request): Json
    {
        $response = new ApiJsonResponse();
        $data = self::getRequestJsonArrayBody($request);

        /** @var saMemberUsers $user */
        $user = modRequest::request('auth.user');
        if (!$user) {
            $response->data['response'] = [
                'success' => false,
                'message' => 'Unauthorized',
            ];
            return $response;
        }

        if (empty($data['token']) || empty($data['platform']) || empty($data['device_uuid'])) {
            $response->data['response'] = [
                'success' => false,
                'message' => 'Missing data',
            ];
            return $response;
        }

        $pushToken = ioc::getRepository('PushToken')->findOneBy([
            'user_id' => $user->getId()
        ]);

        if (!$pushToken) {
            $pushToken = ioc::resolve('PushToken');
        }


        $pushToken->setToken($data['token']);
        $pushToken->setDeviceUuid($data['device_uuid']);
        $pushToken->setUserId($user->getId());
        $pushToken->setPlatform($data['platform']);

        app::$entityManager->persist($pushToken);
        app::$entityManager->flush($pushToken);

        $response->data['response'] = [
            'success' => true,
            'message' => 'Success',
        ];
        return $response;
    }

    public static function getConfiguration(Request $request): Json
    {
        $response = new ApiJsonResponse();

        $response->data['response'] = [
            'success' => true,
            'enforce_gps' => app::get()->getConfiguration()->get('enforce_gps')->getValue(),
            'provider_time' => app::get()->getConfiguration()->get('provider_time')->getValue()
        ];
        return $response;
    }

    public static function updateDeviceSettings(Request $request): Json
    {
        $response = new APiJsonResponse();


        $data = self::getRequestJsonArrayBody($request);

        /* @var \nst\member\Nurse $nurse */
        $nurse = ioc::getRepository('Nurse')->find($data['id']);
        if (!$nurse) {
            $response->data['response'] = [
                'success' => false,
            ];
            return $response;
        }
        $appVersion = $data['app_version'];
        $nurse->setAppVersion($appVersion);
        app::$entityManager->flush($nurse);
        $response->data['response'] = [
            'success' => true
        ];
        return $response;
    }
}
