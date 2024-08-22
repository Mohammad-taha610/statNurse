<?php


namespace nst\member;


use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use GuzzleHttp\Client;
use nst\events\SaShiftService;
use nst\events\ShiftService;
use sacore\application\app;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\Request;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\ValidateException;
use sa\member\saMember;
use sa\member\saMemberEmail;
use sacore\utilities\notification;
use sacore\utilities\stringUtils;
use sacore\utilities\doctrineUtils;

/**
 * Class SaNstMemberController
 * @IOC_NAME="saMemberController"
 */
class SaNstMemberController extends \sa\member\saMemberController
{
    // MEMBERS
    /**
     * @param $request
     * @return View
     */
    public function manageMembers($request): View
    {
        $view = parent::manageMembers($request);
        if ($request->extra_search_fields['member_type'] === 'Nurse')
            unset($view->data['table'][0]['actions']['user']);

        return $view;
    }

    public function editMemberUsers($request): View
    {
        $view = parent::editMemberUsers($request);

        $id = $request->getRouteParams()->get('id');
        if($id) {
            /** @var NstMemberUsers $user */
            $user = ioc::get('saMemberUsers', ['id' => $id]);

            $view->data['user_type'] = $user->getUserType();
            $view->data['bonus_allowed'] = $user->getBonusAllowed();
            $view->data['covid_allowed'] = $user->getCovidAllowed();
        }

        return $view;
    }

    public function saveMemberUsers($request): Redirect|View
    {
        $memberId = $request->getRouteParams()->get('member_id');
        $usernameId = $request->getRouteParams()->get('id');
        /** @var saMember $member */
        $member = app::$entityManager->find($this->saMember, $memberId);

        /** @var NstMemberUsers $user */
        if ($usernameId>0) {
            $saMemberUsers = ioc::staticResolve('saMemberUsers');
            $user = app::$entityManager->find($saMemberUsers, $usernameId);
        }
        else {
            $user = ioc::resolve('saMemberUsers');
            $user->setDateCreated(new \sacore\application\DateTime());
            $member->addUser($user);
        }


        if (!empty($request->request->get('password')))
            $user->setPassword($request->request->get('password'));


        if($request->request->get('unlocked_account') == "true") {
            $loginAttemptService = new LoginAttemptService();
            $loginAttemptService->clearLoginAttempts($user->getUsername());
        }

        $user->setLastName($request->request->get('last_name'));
        $user->setFirstName($request->request->get('first_name'));
        $user->setUsername($request->request->get('username'));
        $user->setIsActive($request->request->get('is_active'));
        $user->setUserType($request->request->get('user_type'));
        $user->setBonusAllowed($request->request->get('bonus_allowed'));
        $user->setCovidAllowed($request->request->get('covid_allowed'));
        $user->setMember($member);

        $user->getGroups()->clear();
        if ( is_array($request->request->get('in_groups')) ) {
            foreach($request->request->get('in_groups') as $group) {
                $group = app::$entityManager->find( ioc::staticResolve('saMemberGroup'), $group);
                if ($group)
                    $user->addGroup($group);
            }
        }

        if (!empty($request->request->get('email')) && $request->request->get('email')!='add' ) {
            /** @var saMemberEmail $email */
            $email = app::$entityManager->find( ioc::staticResolve('saMemberEmail'), $request->request->get('email'));
            $user->setEmail( $email );
        }
        elseif (!empty($request->request->get('email')) && $request->request->get('email')=='add' ) {
            /** @var saMemberEmail $email */
            $saMemberEmail = ioc::staticResolve('saMemberEmail');
            $email = new $saMemberEmail();
            $email->setEmail($request->get('email_new'));
            $email->setIsActive(true);
            $email->setIsPrimary(false);
            $email->setType('N\A');
            $email->setMember($member);
            $user->setEmail( $email );
        }
        else {
            $user->setEmail(null);
        }

        $notify = new notification();

        try {
            app::$entityManager->persist($member);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'User saved successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_sa_account_edit', ['id' => $memberId]).'#edit-usernames');
        }
        catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage());
            return $this->editMemberUsers($request);
        }
    }

    public function SaNstEditMemberUsers($request): View
    {
        $memberId = $request->getRouteParams()->get('member_id');
        $usernameId = $request->getRouteParams()->get('id');
        if (is_null($usernameId)) {
            $usernameId = 0;
        }
        /** @var NstMember $member */
        $member = ioc::get('NstMember', ['id' => $memberId]);

        $view = new View('saUsernames', $this->viewLocation(), false);
        $view->data['postRoute'] = app::get()->getRouter()->generate('member_sa_saveusernames', ['member_id' => $memberId, 'id' => $usernameId]);
        $view->data['memberId'] = $memberId;
        $view->data['usernameId'] = $usernameId;
        $view->data['in_groups'] = array();
        $view->data['member_type'] = $member->getMemberType();
        if ($usernameId>0) {
            $saMemberUsers = ioc::staticResolve('saMemberUsers');
            $user = app::$entityManager->find($saMemberUsers, $usernameId);

            $mData = doctrineUtils::convertEntityToArray( $user );
            $view->data = array_merge($view->data, $mData);
            $view->data['email'] = '';
            if ($user->getEmail()) {
                $view->data['email'] = $user->getEmail()->getId();
            }

            $view->data['in_groups'] = array();

            foreach( $user->getGroups() as $group ) {
                $view->data['in_groups'][] = $group->getId();
            }

        }

        if ($request->request->all()) {
            $view->data = array_merge($view->data, $request->request->all());
        } else {
            unset($view->data['password']);
        }

        $view->data['groups'] = $repo = app::$entityManager->getRepository( ioc::staticResolve('saMemberGroup') )->findAll();
        $view->data['emails'] = doctrineUtils::convertEntityToArray( $member->getEmails() );

        return $view;
    }


    /**
     * @param Request $request
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws \Exception
     * @throws Exception
     */
    public function editMember($request): Redirect|View
    {
        $view = parent::editMember($request);
        $view->data['dbform'][0]['exclude'][] = 'member_type';
        $view->data['dbform'][0]['exclude'][] = 'credentials';
        $view->data['dbform'][0]['exclude'][] = 'homepage';

        $id = $request->getRouteParams()->get('id');
        /** @var NstMember $member */
        $member = ioc::get('NstMember', ['id' => $id]);

        $view->data['member_id'] = $id;

        if($member && $member->getMemberType() == 'Nurse') {
            $nurse = $member->getNurse();
            $view->data['credentials'] = $nurse->getCredentials();
            $view->data['nurse_id'] = $nurse->getId();
        }
        if($member && $member->getMemberType() == 'Provider') {
            $provider = $member->getProvider();
            $view->data['pay_rates'] = $provider->getPayRates();
            $view->data['administrator'] = $provider->getAdministrator();
            $view->data['director_of_nursing'] = $provider->getDirectorOfNursing();
            $view->data['scheduler_name'] = $provider->getSchedulerName();
            $view->data['facility_phone_number'] = $provider->getFacilityPhoneNumber();
            $view->data['uses_travel_pay'] = $provider->getUsesTravelPay();
            $view->data['requires_covid_vaccine'] = $provider->getRequiresCovidVaccine();
            $view->data['provider_id'] = $provider->getId();
        }
        if($member && $member->getMemberType() == 'Executive') {
            $executive = $member->getExecutive();
            // $view->data['credentials'] = $nurse->getCredentials();
            $view->data['executive_id'] = $executive->getId();
        }

        return $view;
    }

    public function saveMember($request): Redirect|View
    {
        $saMemberService = new SaNstMemberService();
        return $saMemberService->saveMember($request);
    }

    /**
     * @param Request $request
     * @return Redirect|View
     */
    public function deleteMember($request): Redirect|View
    {
        $id = $request->getRouteParams()->get('id');

        $view = parent::deleteMember($request);
        $memberService = new NstMemberService();
        $memberService->deleteNstMember($request);
        return $view;
    }



    // PROVIDERS
    /**
     * @param $request
     * @return View
     */
    public function manageProviders($request): View
    {
        $view = new View('manage_providers');

        return $view;
    }

    public function manageExecutives($request): View
    {
        $view = new View('manage_executives');
        return $view;
    }

    /**
     * @param $request
     * @return Redirect|View
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws Exception
     */
    public function editProvider($request): Redirect|View
    {
        $view = self::editMember($request);
        $view->data['member_type'] = 'Provider';
        return $view;
    }

    /**
     * @param $request
     * @return Redirect|View
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws Exception
     */
    public function editExecutive($request): Redirect|View
    {
        $view = self::editMember($request);
        $view->data['member_type'] = 'Executive';
        return $view;
    }

    /**
     * @param $request
     * @return Redirect
     */
    public function deleteProvider($request): Redirect
    {
        $request->return_route = 'edit_provider';
        $view = parent::deleteMember($request);
        $memberService = new NstMemberService();
        $memberService->deleteNstMember($request);
        return $view;
    }

    public static function deleteNstMemberMod($data)
    {
        $return['success'] = false;

        /** @var saMember $member */
        $member = ioc::getRepository('saMember')->findOneBy(['id' => $data['member_id']]);
        try {

            $member->setIsDeleted(true);
            $member->setIsActive(false);

            if($member->getUsers()) {

                foreach($member->getUsers() as $user) {
                    $user->setIsActive(false);
                }
            }

            app::$entityManager->flush();

            $return['success'] = true;
            return $return;
        }
        catch (ValidateException $e) {

            $return['error'] = $e->getMessage();
            return $return;
        }
    }


    // NURSES
    /**
     * @param $request
     * @return View
     */
    public function manageNurses($request): View
    {
        $view = new View('manage_nurses');

        return $view;
    }

    /**
     * @param $request
     * @return Redirect|View
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws Exception
     */
    public function editNurse($request): Redirect|View
    {
        $view = self::editMember($request);
        $view->data['member_type'] = 'Nurse';

        return $view;
    }

    /**
     * @param $request
     * @return Redirect
     */
    public function deleteNurse($request): Redirect
    {
        $request->return_route = 'edit_nurse';
        $view = parent::deleteMember($request);
        $memberService = new NstMemberService();
        $memberService->deleteNstMember($request);
        return $view;
    }

    public function editTags($data)
    {
        $view = new View('edit_tags');

        return $view;
    }

    public static function loadProviders($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->loadProviders($data);
    }

    public static function loadNurses($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->loadNurses($data);
    }

    public static function loadNurseFiles($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->loadNurseFiles($data);
    }

    public static function saveNurseFiles($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->saveNurseFiles($data);
    }


    public static function loadProviderFiles($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->loadProviderFiles($data);
    }

    public static function saveProviderFiles($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->saveProviderFiles($data);
    }

    public static function loadProviderFileTags($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->loadProviderFileTags($data);
    }

    public static function saveProviderFileTags($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->saveProviderFileTags($data);
    }

    public static function checkIfTagCanBeDeleted($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->checkIfTagCanBeDeleted($data);
    }

    public static function loadNurseBasicInfo($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->loadNurseBasicInfo($data);
    }

    public static function saveNurseBasicInfo($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->saveNurseBasicInfo($data);
    }

		public static function loadNurseCheckrPayInfo($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->loadNurseCheckrPayInfo($data);
    }

    public static function saveNurseCheckrPayInfo($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->saveNurseCheckrPayInfo($data);
    }

		public static function createCheckrPayWorker($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->createCheckrPayWorker($data);
    }

		public static function listCheckrPayWorkers($data): array
		{
				$memberService = new SaNstMemberService();

				return $memberService->listCheckrPayWorkers($data);
		}

    public static function loadNurseDirectDepositInfo($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->loadNurseDirectDepositInfo($data);
    }

    public static function saveNurseDirectDepositInfo($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->saveNurseDirectDepositInfo($data);
    }

    public static function loadNursePayCardInfo($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->loadNursePayCardInfo($data);
    }

    public static function saveNursePayCardInfo($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->saveNursePayCardInfo($data);
    }

    public static function loadNurseEmergencyContacts($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->loadNurseEmergencyContacts($data);
    }

    public static function saveNurseEmergencyContacts($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->saveNurseEmergencyContacts($data);
    }

    public static function loadNurseContactInfo($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->loadNurseContactInfo($data);
    }

    public static function saveNurseContactInfo($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->saveNurseContactInfo($data);
    }

    public static function loadProviderBasicInfo($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->loadProviderBasicInfo($data);
    }

    public static function saveProviderBasicInfo($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->saveProviderBasicInfo($data);
    }

    public static function loadProviderContacts($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->loadProviderContacts($data);
    }

    public static function saveProviderContact($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->saveProviderContact($data);
    }

    public static function deleteProviderContact($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->deleteProviderContact($data);
    }

    public static function loadProviderPayRates($data): array
    {
        $memberService = new SaNstMemberService();
        return $memberService->loadProviderPayRates($data);
    }

    public static function saveProviderPayRates($data): array
    {
        $memberService = new SaNstMemberService();
        return $memberService->saveProviderPayRates($data);
    }

    public static function loadNurseNotes($data): array
    {
        $memberService = new SaNstMemberService();
        return $memberService->loadNurseNotes($data);
    }

    public static function loadExecutiveFacilities($data): array
    {
        $memberService = new SaNstMemberService();
        return $memberService->loadExecutiveFacilities($data);
    }

    public static function saveNurseNotes($data): array
    {
        $memberService = new SaNstMemberService();
        return $memberService->saveNurseNotes($data);
    }

    public static function getAdminNameForNurseNote(): array
    {
        $memberService = new SaNstMemberService();
        return $memberService->getAdminNameForNurseNote();
    }

    public function mergeNurseIndex() {
        $member = modRequest::request('auth.member');

        $view = new View('nurse_merge');

        $view->data['member'] = null;

        if ($member) {
            $view->data['member'] = doctrineUtils::getEntityArray($member);
        }

        return $view;
    }

    public static function getProviderNurseCredentialsList($data): array
    {
        $memberService = new SaNstMemberService();
        return $memberService->getProviderNurseCredentialsList($data);
    }

    public static function saveProviderNurseCredentialsList($data): array
    {
        $memberService = new SaNstMemberService();
        return $memberService->saveProviderNurseCredentialsList($data);
    }

    public static function getProviderShiftCategories($data): array
    {
        $shiftService = new ShiftService();
        return $shiftService->getAllShiftCategories($data);
    }

    public static function saveProviderPresetShiftTime($data): array
    {
        $memberService = new SaNstMemberService();
        return $memberService->saveProviderPresetShiftTime($data);
    }

    public static function getProviderPresetShiftTimes($data): array
    {
        $memberService = new SaNstMemberService();
        return $memberService->getProviderPresetShiftTimes($data);
    }

    public static function deleteProviderPresetShiftTime($data): array
    {
        $memberService = new SaNstMemberService();
        return $memberService->deleteProviderPresetShiftTime($data);
    }

    public static function saveNurseStates($data): array
    {
        try {
            $memberService = new SaNstMemberService();
            return $memberService->saveNurseStates($data);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }


    public static function getNurseStates($data): array
    {
        try {
            $memberService = new SaNstMemberService();
            return $memberService->getNurseStates($data);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function getProviderShiftBreakDuration($data) {
        $memberService = new SaNstMemberService();
        return $memberService->getBreakDurationForProvider($data['provider_id']);
    }

    public static function saveProviderShiftBreakDuration($data) {
        $memberService = new SaNstMemberService();
        return $memberService->saveBreakDurationForProvider($data);
    }

    public static function executiveAccounts($data) {
        var_dump('hfaeowwe');
        die();
    }

    public static function loadExecutives($data) {
        $memberService = new SaNstMemberService();
        return $memberService->loadExecutives($data);
    }

    public static function loadExecutiveBasicInfo($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->loadExecutiveBasicInfo($data);
    }

    public static function saveExecutiveBasicInfo($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->saveExecutiveBasicInfo($data);
    }

    public static function loadFacilities($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->loadFacilities();
    }

    public static function addExecutiveFacility($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->addFacilityToExecutive($data);
    }

    public static function removeExecutiveFacility($data): array
    {
        $memberService = new SaNstMemberService();

        return $memberService->removeFacilityFromExecutive($data);
    }
}
