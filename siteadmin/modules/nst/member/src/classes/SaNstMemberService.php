<?php


namespace nst\member;


use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use nst\applications\NurseApplicationController;
use nst\applications\NurseApplicationService;
use nst\payroll\PayrollService;
use nst\events\Shift;
use nst\events\ShiftService;
use nst\events\PresetShiftTime;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\Request;
use sacore\application\responses\Redirect;
use sacore\application\ValidateException;
use sa\system\saUser;
use sa\member\auth;
use sa\member\saMember;
use sa\member\getRepository;
use sacore\utilities\doctrineUtils;
use sacore\utilities\notification;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MigratingSessionHandler;
use TypeError;
use NurseCredential;

class SaNstMemberService
{
    protected $saMemberRepository;


    public function __construct()
    {
        $this->saMemberRepository = ioc::getRepository('NstMember');
    }

    /**
     * @param Request $request
     */
    public function saveMember($request)
    {

        $id = $request->getRouteParams()->get('id');
        $notify = new notification();

        if ($id > 0) {
            /** @var NstMember $member */
            $member = ioc::get('NstMember', ['id' => $id]);
        } else {
            /** @var saMember $member */
            $member = ioc::resolve('NstMember');
            $member->setIsActive(true);
        }

        $request->query->remove('avatar');
        doctrineUtils::setEntityData($request->request->all(), $member, true);

        try {

            app::$entityManager->persist($member);
            $type = $request->request->get('member_type');
            switch ($type) {
                case 'Provider':
                    if (!$member->getProvider()) {
                        $provider = ioc::resolve('Provider');

                        $member->setProvider($provider);
                        $provider->setMember($member);

                        app::$entityManager->persist($provider);
                    }

                    $member->getProvider()->setAdministrator($request->request->get('administrator'));
                    $member->getProvider()->setSchedulerName($request->request->get('scheduler_name'));
                    $member->getProvider()->setDirectorOfNursing($request->request->get('director_of_nursing'));
                    $member->getProvider()->setFacilityPhoneNumber($request->request->get('facility_phone_number'));
                    $member->getProvider()->setUsesTravelPay($request->request->get('uses_travel_pay'));
                    $member->getProvider()->setRequiresCovidVaccine($request->request->get('requires_covid_vaccine'));

                    $request->return_route = $id > 0 ? 'manage_providers' : 'edit_provider';
                    break;
                case 'Nurse':
                    $nurse = $member->getNurse();
                    if (!$nurse) {

                        $nurse = ioc::resolve('Nurse');
                        app::$entityManager->persist($nurse);

                        $member->setNurse($nurse);
                        $nurse->setMember($member);
                    }
                    $credentials = $request->request->get('credentials');
                    $nurse->setCredentials($credentials);

                    $nurse->setReceivesSMS(false);
                    $nurse->setReceivesPushNotification(false);

                    app::$entityManager->flush();
                    $request->return_route = $id > 0 ? 'manage_nurses' : 'edit_nurse';
                    break;
                case 'Executive':
                    $executive = $member->getExecutive();
                    if (!$executive) {

                        $Executive = ioc::resolve('Executive');
                        app::$entityManager->persist($Executive);

                        $member->setExecutive($Executive);
                        $Executive->setMember($member);
                    }


                    // $member->getProvider()->setAdministrator($request->request->get('administrator'));
                    // $member->getProvider()->setSchedulerName($request->request->get('scheduler_name'));
                    // $member->getProvider()->setDirectorOfNursing($request->request->get('director_of_nursing'));
                    // $member->getProvider()->setFacilityPhoneNumber($request->request->get('facility_phone_number'));
                    // $member->getProvider()->setUsesTravelPay($request->request->get('uses_travel_pay'));
                    // $member->getProvider()->setRequiresCovidVaccine($request->request->get('requires_covid_vaccine'));

                    // $credentials = $request->request->get('credentials');
                    // $nurse->setCredentials($credentials);

                    // $nurse->setReceivesSMS(false);
                    // $nurse->setReceivesPushNotification(false);

                    // app::$entityManager->flush();
                    // $request->return_route = $id > 0 ? 'manage_nurses' : 'edit_nurse';
                    break;
            }
            app::$entityManager->flush();
            modRequest::request('sa.member.postSave', null, array('member' => $member, 'post' => $request->request->all()));

            $notify->addNotification('success', 'Success', 'Member saved successfully.');

            if ($id) {
                return new Redirect(app::get()->getRouter()->generate($request->return_route ? $request->return_route : 'member_sa_accounts'));
            } else {
                return new Redirect(app::get()->getRouter()->generate($request->return_route ? $request->return_route : 'member_sa_account_edit', ['id' => $member->getId()]));
            }
        } catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />' . $e->getMessage());
            // have to return this due to editMember returning new View obj.

            return new Redirect(app::get()->getRouter()->generate($request->return_route ? $request->return_route : 'member_sa_accounts'));
        }

    }

    public static function loadProviders($data)
    {
        $response = ['success' => false];

        $providers = ioc::getRepository('Provider')->findAll();

        if ($providers) {
            /** @var Provider $provider */
            foreach ($providers as $provider) {

                if ($provider->getMember()->getIsDeleted()) {
                    continue;
                }

                $member = $provider->getMember();
                $memberId = $member->getId();
                $emails = [];
                $phoneNumbers = [];
                /** @var NstContact $contact */
                foreach ($provider->getContacts() as $contact) {

                    if ($contact->getReceivesInvoices()) {
                        $emails[] = $contact->getEmailAddress();
                    }

                    $phoneNumber = $contact->getPhoneNumber();
                    if ($phoneNumber != "") {
                        $phoneNumbers[] = $phoneNumber;
                    }
                }
                $additionalPhoneNumbers = $member->getPhones();
                foreach ($additionalPhoneNumbers as $additionalPhoneNumber) {

                    $phoneNumbers[] = $additionalPhoneNumber->getPhone();
                }
                array_unique($emails);
                array_unique($phoneNumbers);

                /** @var saMemberUsers $user */
                $users = $member->getUsers();
                $login_dates = [];
                $usernames = [];
                foreach($users as $user) {

                    $login_dates[] = $user->getLastLogin();
                    $usernames[] = $user->getUsername();
                }
                $lastLogin = ($login_dates!=[])?max($login_dates):null;
                $lastLoginFormatted = $lastLogin ? $lastLogin->format('m/d/Y g:i a') : null;

                $response['providers'][] = [

                    'first_name' => $member->getFirstName(),
                    'last_name' => $member->getLastName(),
                    'company' => $member->getCompany(),
                    'member_id' => $memberId,
                    'id' => $provider->getId(),
                    'emails' => $emails,
                    'phone_numbers' => $phoneNumbers,
                    'usernames' => $usernames,
                    'state' => $provider->getStateAbbreviation(),
                    'member_since' => $member->getDateCreated()->format('m/d/Y'),
                    'last_login' => $lastLoginFormatted,
                    'user_actions' => [

                        'edit_user' => app::get()->getRouter()->generate('member_sa_account_edit', ['id' => $memberId]),
                        'delete_user' => app::get()->getRouter()->generate('member_sa_account_delete', ['id' => $memberId]),
                        'login_user' => app::get()->getRouter()->generate('member_sa_account_superuser_login', ['id' => $memberId])
                    ]
                ];
            }

            $response['links'][] = [

                'export' => app::get()->getRouter()->generate('member_sa_export'),
                'create_account' => app::get()->getRouter()->generate('create_provider')
            ];

            $response['success'] = true;
        }

        return $response;
    }

    public static function loadNurses($data)
    {
        $response = ['success' => false];
        $nurses = ioc::getRepository('Nurse')->manageNurses();

        /** @var Nurse $nurse */
        foreach ($nurses as $nurse) {

            if ($nurse['is_deleted']) {
                continue;
            }

            $response['nurses'][] = [

                'id' => $nurse['nurse_id'],
                'member_id' => $nurse['member_id'],
                'username' => $nurse['username'],
                'date_created' => $nurse['date_created']?->format('Y-m-d H:i:s'),
                'last_login' => $nurse['last_login']?->format('Y-m-d H:i:s'),
                'first_name' => $nurse['first_name'],
                'middle_name' => $nurse['middle_name'],
                'last_name' => $nurse['last_name'],
                'email' => $nurse['email_address'],
                'phone' => $nurse['phone_number'],
								'city' => $nurse['city'],
                'state' => $nurse['state'],
                'credentials' => $nurse['credentials'],
                'user_actions' => [

                    'edit_user' => app::get()->getRouter()->generate('member_sa_account_edit', ['id' => $nurse['member_id']]),
                    'delete_user' => app::get()->getRouter()->generate('member_sa_account_delete', ['id' => $nurse['member_id']])
                ]
            ];
        }

        $response['links'][] = [

            'export' => app::get()->getRouter()->generate('member_sa_export'),
            'create_account' => app::get()->getRouter()->generate('create_nurse')
        ];

        $response['success'] = true;
        return $response;
    }

    public static function loadExecutives($data)
    {
        $response = ['success' => false];

        $executives = ioc::getRepository('Executive')->findAll();

        if ($executives) {
            /** @var Provider $provider */
            foreach ($executives as $executive) {

                if ($executive->getMember()->getIsDeleted()) {
                    continue;
                }

                $member = $executive->getMember();
                $memberId = $member->getId();
                $emails = [];
                $phoneNumbers = [];
                /** @var NstContact $contact */
                // foreach ($executive->getContacts() as $contact) {

                //     if ($contact->getReceivesInvoices()) {
                //         $emails[] = $contact->getEmailAddress();
                //     }

                //     $phoneNumber = $contact->getPhoneNumber();
                //     if ($phoneNumber != "") {
                //         $phoneNumbers[] = $phoneNumber;
                //     }
                // }
                $additionalPhoneNumbers = $member->getPhones();
                foreach ($additionalPhoneNumbers as $additionalPhoneNumber) {

                    $phoneNumbers[] = $additionalPhoneNumber->getPhone();
                }
                array_unique($emails);
                array_unique($phoneNumbers);

                /** @var saMemberUsers $user */
                $users = $member->getUsers();
                $login_dates = [];
                $usernames = [];
                foreach($users as $user) {

                    $login_dates[] = $user->getLastLogin();
                    $usernames[] = $user->getUsername();
                }
                $lastLogin = ($login_dates!=[])?max($login_dates):null;
                $lastLoginFormatted = $lastLogin ? $lastLogin->format('m/d/Y g:i a') : null;

                $response['executives'][] = [

                    'first_name' => $member->getFirstName(),
                    'last_name' => $member->getLastName(),
                    'company' => $member->getCompany(),
                    'member_id' => $memberId,
                    'id' => $executive->getId(),
                    'emails' => $emails,
                    'phone_numbers' => $phoneNumbers,
                    'usernames' => $usernames,
                    'state' => $executive->getStateAbbreviation(),
                    'member_since' => $member->getDateCreated()->format('m/d/Y'),
                    'last_login' => $lastLoginFormatted,
                    'user_actions' => [

                        'edit_user' => app::get()->getRouter()->generate('member_sa_account_edit', ['id' => $memberId]),
                        'delete_user' => app::get()->getRouter()->generate('member_sa_account_delete', ['id' => $memberId]),
                        'login_user' => app::get()->getRouter()->generate('member_sa_account_superuser_login', ['id' => $memberId])
                    ]
                ];
            }

            $response['links'][] = [

                'export' => app::get()->getRouter()->generate('member_sa_export'),
                'create_account' => app::get()->getRouter()->generate('create_executive')
            ];

            $response['success'] = true;
        }

        return $response;
    }

    public static function loadExecutiveBasicInfo($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        /** @var Executive $executive */
        $executive = ioc::get('Executive', ['id' => $id]);

        if ($executive) {
            $member = $executive->getMember();
            $response['executive'] = [
                'name' => $member->getCompany(),
                'administrator' => $executive->getAdministrator(),
                'facility_phone' => $executive->getFacilityPhoneNumber(),
                'primary_email_address' => $executive->getPrimaryEmailAddress(),
                'scheduler' => $executive->getSchedulerName(),
                'director_of_nursing' => $executive->getDirectorOfNursing(),
                // 'uses_travel_pay' => $executive->getUsesTravelPay(),
                // 'has_covid_pay' => $executive->getHasCovidPay(),
                // 'has_ot_pay' => $executive->getHasOtPay(),
                // 'requires_covid_vaccine' => $executive->getRequiresCovidVaccine(),
                'street_address' => $executive->getStreetAddress(),
                'zipcode' => $executive->getZipcode(),
                'city' => $executive->getCity(),
                'state_abbreviation' => $executive->getStateAbbreviation()
            ];

        }
        $response['success'] = true;

        return $response;
    }

    public static function saveExecutiveBasicInfo($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        $providerData = $data['provider'];
        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $id]);

        if ($provider) {
            $member = $provider->getMember();
            $member->setCompany($providerData['name']);
            $provider->setAdministrator($providerData['administrator']);
            $provider->setFacilityPhoneNumber($providerData['facility_phone']);
            $provider->setPrimaryEmailAddress($providerData['primary_email_address']);
            $provider->setSchedulerName($providerData['scheduler']);
            $provider->setDirectorOfNursing($providerData['director_of_nursing']);
            //For when "true" isn't the same as true
            $provider->setUsesTravelPay($providerData['uses_travel_pay'] == 'false' ? false : true);
            $provider->setHasCovidPay($providerData['has_covid_pay'] == 'false' ? false : true);
            $provider->setHasOtPay($providerData['has_ot_pay'] == 'false' ? false : true);
            $provider->setRequiresCovidVaccine($providerData['requires_covid_vaccine'] == 'false' ? false : true);
            $provider->setStreetAddress($providerData['street_address']);
            $provider->setZipcode($providerData['zipcode']);
            $provider->setCity($providerData['city']);
            $provider->setStateAbbreviation($providerData['state_abbreviation']);

            app::$entityManager->flush();
            $response['success'] = true;
        }
        $response['success'] = true;
        return $response;
    }

    public static function loadNurseFiles($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $id]);

        if (!$nurse) {
            return $response;
        }

        // get all nurses files as array
        $files = $nurse->getNurseFiles();
        $response['files'] = [];
        /** @var NstFile $file */
        foreach ($files as $file) {

            $response['files'][] = [

                'id' => $file->getId(),
                'filename' => $file->getFilename(),
                'type' => $file->getFileType(),
                'route' => app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id' => $file->getId()]),
                'tag' => $file->getTag() ? [
                    'id' => $file->getTag()->getId(),
                    'name' => $file->getTag()->getName()
                ] : []
            ];
        }

        // get all nurse file tags as array
        $response['tags'] = [];
        $tags = ioc::getRepository('NstFileTag')->findBy(['type' => 'Nurse']);
        /** @var NstFileTag $tag */
        foreach ($tags as $tag) {
            $response['tags'][] = [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
                'description' => $tag->getDescription(),
            ];
        }
        $response['success'] = true;

        return $response;
    }

    public static function saveNurseFiles($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        $fileIds = $data['file_ids'] ?: [];
        $tags = $data['file_tags'];
        $fileTags = [];

        foreach ($tags as $tag) {
            $fileTags[(int)$tag['file_id']] = $tag['tag'];
        }

        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $id]);

        if ($nurse) {
            /** @var NstFile $file */
            foreach ($nurse->getNurseFiles() as $file) {
                if (!in_array($file->getId(), $fileIds)) {
                    $nurse->removeNurseFile($file);
                    $file->setNurse(null);
                }
            }

            foreach ($fileIds as $fileId) {
                $fileId = (int) $fileId;
                /** @var NstFile $nurseFile */
                $nurseFile = ioc::get('saFile', ['id' => $fileId]);
                if ($nurseFile) {
                    $tagData = $fileTags[$nurseFile->getId()];
                    if ($tagData) {
                        $tag = ioc::get('NstFileTag', ['name' => $tagData]);
                    } else {
                        /** @var NstFileTag $tag */
                        $tag = ioc::get('NstFileTag', ['id' => $tagData['id']]);
                        $tag = ioc::resolve('NstFileTag');
                        $tag->setName($fileTags[$nurseFile->getId()]);
                        $tag->setDescription($tagData['description']);
                        $tag->setType('Nurse');
                        app::$entityManager->persist($tag);
                    }
                    $nurseFile->setTag($tag);

                    if (!$nurse->getNurseFiles()->contains($nurseFile)) {
                        $nurse->addNurseFile($nurseFile);
                        $nurseFile->setNurse($nurse);
                    }
                } else {
                    $response['message'] = 'Cannot find file: ' . $fileId;
                    return $response;
                }
            }

            app::$entityManager->flush();

            $response['success'] = true;
        }

        return $response;
    }


    public static function loadProviderFiles($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $id]);

        if ($provider) {
            $files = $provider->getProviderFiles();
            $response['files'] = [];
            /** @var NstFile $file */
            foreach ($files as $file) {
                $response['files'][] = [
                    'id' => $file->getId(),
                    'filename' => $file->getFilename(),
                    'type' => $file->getFileType(),
                    'route' => app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $file->getFolder(), 'file' => $file->getFilename()]),
                    'tag' => $file->getTag() ? [
                        'id' => $file->getTag()->getId(),
                        'name' => $file->getTag()->getName()
                    ] : []
                ];
            }
            $response['tags'] = [];
            $tags = ioc::getRepository('NstFileTag')->findBy(['type' => 'Provider']);
            /** @var NstFileTag $tag */
            foreach ($tags as $tag) {
                $response['tags'][] = [
                    'id' => $tag->getId(),
                    'name' => $tag->getName(),
                    'description' => $tag->getDescription(),
                ];
            }
            $response['success'] = true;
        }

        return $response;
    }

    public static function saveProviderFiles($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        $fileIds = $data['file_ids'];
        $tags = $data['file_tags'];

        $fileTags = [];
        foreach ($tags as $tag) {
            $fileTags[$tag['file_id']] = $tag['tag'];
        }

        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $id]);

        if ($provider) {
            /** @var NstFile $file */
            foreach ($provider->getProviderFiles() as $file) {
                if (!in_array($file->getId(), $fileIds)) {
                    $provider->removeProviderFile($file);
                    $file->setProvider(null);
                }
            }
            foreach ($fileIds as $fileId) {
                /** @var NstFile $providerFile */
                $providerFile = ioc::get('saFile', ['id' => $fileId]);
                if ($providerFile) {
                    if ($tagData = $fileTags[$providerFile->getId()]) {
                        if ($tagData['id']) {
                            $tag = ioc::get('NstFileTag', ['id' => $tagData['id']]);
                        } else {
                            /** @var NstFileTag $tag */
                            $tag = ioc::resolve('NstFileTag');
                            $tag->setName($tagData['name']);
                            $tag->setDescription($tagData['name']);
                            $tag->setType('Provider');
                            app::$entityManager->persist($tag);
                        }
                        $providerFile->setTag($tag);
                    }
                    if (!$provider->getProviderFiles()->contains($providerFile)) {
                        $provider->addProviderFile($providerFile);
                        $providerFile->setProvider($provider);
                    }
                    app::$entityManager->flush();
                } else {
                    $response['message'] = 'Cannot find file: ' . $fileId;
                    return $response;
                }
            }
            app::$entityManager->flush();


            $response['success'] = true;
        }

        return $response;
    }

    public static function loadProviderFileTags($data)
    {
        $response = ['success' => false];

        /** @var NstFileTagRepository $fileTagRepo  */
        $fileTagRepo = ioc::getRepository('NstFileTag');

        /** @var NstFileTag $nurseTags[] */
        $nurseTags = $fileTagRepo->findBy(['type' => 'Nurse']);
        $allNurseTags = array();
        foreach($nurseTags as $nurseTag) {
            if(!empty($nurseTag->getName())){
                $tagInfo = doctrineUtils::getEntityArray($nurseTag);
                array_push($allNurseTags, $tagInfo);
            } else {
                // If name is empty move all files for this tag to Default tag and delete this tag
                $fileTagRepo->assignToDefault($nurseTag);
                $fileTagRepo->deleteTag($nurseTag);
            }
        }
        $response['tags'] = $allNurseTags;

        $response['success'] = true;
        return $response;
    }

    public static function saveProviderFileTags($data)
    {
        $response = ['success' => false];

        /** @var NstFileTagRepository $fileTagRepo  */
        $fileTagRepo = ioc::getRepository('NstFileTag');

        if ($data['tags_to_delete']) {
            foreach($data['tags_to_delete'] as $tagId) {
                $fileTagRepo->deleteTag($fileTagRepo->findOneBy(['id' => $tagId]));
            }
        }

        foreach ($data['tags'] as $tag) {
            if ($tag['id'] == 'New') {
                $fileTagRepo->createNewTagByName($tag['name'], $tag['name'], 'Nurse', $tag['show_in_provider_portal']);
            } else if (!$fileTagRepo->updateTag($tag['id'], $tag['name'], $tag['name'], null, $tag['show_in_provider_portal'])) {
                $response['error_message'] = 'Cannot find file tag(s).';

                return $response;
            }
        }

        $response['success'] = true;
        return $response;
    }

    public static function checkIfTagCanBeDeleted($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        $canDelete = true;

        if ($id == -1) {
            $response['can_delete'] = $canDelete;
            $response['success'] = true;
            return $response;
        }

        $tagsWithId = ioc::getRepository('NstFile')->findOneBy(['tag' => $id]);
        if ($tagsWithId) {
            $canDelete = false;
        }

        $response['can_delete'] = $canDelete;
        $response['success'] = true;
        return $response;
    }

    public static function loadNurseBasicInfo($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $id]);
        if ($nurse) {
            /** @var NstMember $member */
            $member = $nurse->getMember();

            if ($member) {
                $ssn = $nurse->getSSN();
                $cipher = "AES-128-CTR";
                /** @var saUser $user */
                $samember = ioc::getRepository('saMember')->findOneBy(['id' => $member]);
                $user = $samember->getUsers()[0];
                $key = $user->getUserKey();

                do {
                    $ssn = openssl_decrypt($ssn, $cipher, (string)$key, 0, ord($key));
                } while (strlen($ssn) > 11);
            }

            $response['nurse'] = [
                'first_name' => $member->getFirstName(),
                'middle_name' => $member->getMiddleName(),
                'last_name' => $member->getLastName(),
                'credentials' => $nurse->getCredentials(),
                'phone_number' => $nurse->getPhoneNumber(),
                'ssn' => preg_replace("/[^A-Za-z0-9- ]/", '', $ssn),
                'email_address' => $nurse->getEmailAddress(),
                'skin_test_expiration' => $nurse->getSkinTestExpirationDate() ? $nurse->getSkinTestExpirationDate()->format('Y-m-d') : '',
                'license_expiration' => $nurse->getLicenseExpirationDate() ? $nurse->getLicenseExpirationDate()->format('Y-m-d') : '',
                'cpr_expiration' => $nurse->getCprExpirationDate() ? $nurse->getCprExpirationDate()->format('Y-m-d') : '',
                'street_address' => $nurse->getStreetAddress(),
                'street_address_2' => $nurse->getStreetAddress2(),
                'zipcode' => $nurse->getZipcode(),
                'city' => $nurse->getCity(),
                'state' => $nurse->getState(),
                'birthday' => $nurse->getDateOfBirth() ? $nurse->getDateOfBirth()->format('Y-m-d') : '',
                'receives_sms' => $nurse->getReceivesSMS(),
                'receives_push_notification' => $nurse->getReceivesPushNotification(),
                'app_version' => $nurse->getAppVersion()
            ];

            $response['states'] = ioc::getRepository('NstState')->getAllStateNames();
        }

        $response['success'] = true;
        return $response;
    }

    public static function saveNurseBasicInfo($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        $nurseData = $data['nurse'];
        /** @var Nurse $nurse */
        $nurse = null;
        if (!$id) {
            $response['message'] = 'Cannot create member in this function.';
            return $response;
        } else {
            $nurse = ioc::get('Nurse', ['id' => $id]);
        }

        /** @var Nurse $nurse */
        if ($nurse) {
            $member = $nurse->getMember();
            $member->setFirstName($nurseData['first_name']);
            $member->setMiddleName($nurseData['middle_name']);
            $member->setLastName($nurseData['last_name']);
            doctrineUtils::setEntityData($nurseData, $nurse);
            $nurse->setDateOfBirth($nurseData['birthday'] ? new DateTime($nurseData['birthday'], app::getInstance()->getTimeZone()) : null);
            $nurse->setSkinTestExpirationDate($nurseData['skin_test_expiration'] ? new DateTime($nurseData['skin_test_expiration'], app::getInstance()->getTimeZone()) : null);
            $nurse->setLicenseExpirationDate($nurseData['license_expiration'] ? new DateTime($nurseData['license_expiration'], app::getInstance()->getTimeZone()) : null);
            $nurse->setCprExpirationDate($nurseData['cpr_expiration'] ? new DateTime($nurseData['cpr_expiration'], app::getInstance()->getTimeZone()) : null);

            $state = ioc::getRepository('saState')->findOneBy(['name' => $nurseData['state']]);
            if ($nurseData['state'] && !$state) {
                $response['message'] = 'Cannot find state with name: ' . $nurseData['state'];
                return $response;
            }
            $nurse->setState($nurseData['state']);
            $nurse->setReceivesSMS($nurseData['receives_sms'] === 'true');
            $nurse->setReceivesPushNotification($nurseData['receives_push_notification'] === 'true');

            // encrypt social security number
            /** @var saUser $user */
            $samember = ioc::getRepository('saMember')->findOneBy(['id' => $member]);
            $user = $samember->getUsers()[0];
            $ss = $data['nurse']['ssn'];
            $cipher = "AES-128-CTR";
            $key = $user->getUserKey();
            $encrypted_ss = openssl_encrypt($ss, $cipher, $key, 0, ord($key));
            $nurse->setSSN($encrypted_ss);
            // encrypt social security number

            app::$entityManager->flush();
            $response['success'] = true;
        }

        return $response;
    }

    public static function loadProviderBasicInfo($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $id]);

        if ($provider) {
            $member = $provider->getMember();
            $response['provider'] = [
                'name' => $member->getCompany(),
                'administrator' => $provider->getAdministrator(),
                'facility_phone' => $provider->getFacilityPhoneNumber(),
                'primary_email_address' => $provider->getPrimaryEmailAddress(),
                'scheduler' => $provider->getSchedulerName(),
                'director_of_nursing' => $provider->getDirectorOfNursing(),
                'uses_travel_pay' => $provider->getUsesTravelPay(),
                'has_covid_pay' => $provider->getHasCovidPay(),
                'has_ot_pay' => $provider->getHasOtPay(),
                'requires_covid_vaccine' => $provider->getRequiresCovidVaccine(),
                'street_address' => $provider->getStreetAddress(),
                'zipcode' => $provider->getZipcode(),
                'city' => $provider->getCity(),
                'state_abbreviation' => $provider->getStateAbbreviation()
            ];

        }
        $response['success'] = true;

        return $response;
    }

    public static function saveProviderBasicInfo($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        $providerData = $data['provider'];
        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $id]);

        if ($provider) {
            $member = $provider->getMember();
            $member->setCompany($providerData['name']);
            $provider->setAdministrator($providerData['administrator']);
            $provider->setFacilityPhoneNumber($providerData['facility_phone']);
            $provider->setPrimaryEmailAddress($providerData['primary_email_address']);
            $provider->setSchedulerName($providerData['scheduler']);
            $provider->setDirectorOfNursing($providerData['director_of_nursing']);
            //For when "true" isn't the same as true
            $provider->setUsesTravelPay($providerData['uses_travel_pay'] == 'false' ? false : true);
            $provider->setHasCovidPay($providerData['has_covid_pay'] == 'false' ? false : true);
            $provider->setHasOtPay($providerData['has_ot_pay'] == 'false' ? false : true);
            $provider->setRequiresCovidVaccine($providerData['requires_covid_vaccine'] == 'false' ? false : true);
            $provider->setStreetAddress($providerData['street_address']);
            $provider->setZipcode($providerData['zipcode']);
            $provider->setCity($providerData['city']);
            $provider->setStateAbbreviation($providerData['state_abbreviation']);

            app::$entityManager->flush();
            $response['success'] = true;
        }
        $response['success'] = true;
        return $response;
    }

    public static function loadProviderContacts($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $id]);

        $response['contacts'] = [];
        if ($provider) {
            /** @var NstContact $contact */
            foreach ($provider->getContacts() as $contact) {
                $response['contacts'][] = [
                    'id' => $contact->getId(),
                    'first_name' => $contact->getFirstName(),
                    'last_name' => $contact->getLastName(),
                    'email_address' => $contact->getEmailAddress(),
                    'phone_number' => $contact->getPhoneNumber(),
                    'receives_invoices' => $contact->getReceivesInvoices(),
                    'receives_sms' => $contact->getReceivesSMS()
                ];
            }
            $response['success'] = true;
        }

        return $response;
    }

    public static function saveProviderContact($data)
    {
        $response = ['success' => false];
        $id = $data['provider_id'];
        $contactId = $data['contact']['id'];
        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $id]);

        if ($provider) {
            $shouldPersist = false;
            if ($contactId > 0) {
                /** @var NstContact $contact */
                $contact = ioc::get('NstContact', ['id' => $contactId]);
            } else {
                /** @var NstContact $contact */
                $contact = ioc::resolve('NstContact');
                $shouldPersist = true;
            }
            $contact->setFirstName($data['contact']['first_name']);
            $contact->setLastName($data['contact']['last_name']);
            $contact->setEmailAddress($data['contact']['email_address']);
            $contact->setPhoneNumber($data['contact']['phone_number']);
            $contact->setReceivesInvoices($data['contact']['receives_invoices'] == "true");
            $contact->setReceivesSMS($data['contact']['receives_sms'] == "true");
            $contact->setProvider($provider);


            if ($shouldPersist) {
                app::$entityManager->persist($contact);
            }
            app::$entityManager->flush();

            $response['id'] = $contact->getId();
            $response['success'] = true;
        }

        return $response;
    }

    public static function deleteProviderContact($data)
    {
        $response = ['success' => false];
        $id = $data['provider_id'];
        $contactId = $data['contact_id'];
        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $id]);

        if ($provider) {
            $contact = ioc::get('NstContact', ['id' => $contactId]);
            if ($contact) {
                $provider->removeContact($contact);
                app::$entityManager->remove($contact);
                app::$entityManager->flush();
                $response['success'] = true;
            }
        }

        return $response;
    }

    public static function loadProviderPayRates($data): array
    {
        $response = ['success' => false];
        $id = $data['id'];
        $nurseType = null;
        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $id]);

        if ($provider) {
            $response['success'] = true;
            $response['provider'] = $provider;
            $response['pay_rates'] = [];

            if ($provider->getPayRates()) {
                /** @var ProviderPayRate $rate */
                foreach ($provider->getPayRates() as $key => $rate) {
                    $response['pay_rates'][] = [
                        'name' => $key,
                        'standard_pay' => $rate['standard_pay'],
                        'standard_bill' => $rate['standard_bill'],
                    ];
                }
            } else {
                $response['pay_rates'] = [
                    [
                        'name' => 'CNA',
                        'standard_pay' => 0,
                        'standard_bill' => 0,
                    ],
                    [
                        'name' => 'CMT',
                        'standard_pay' => 0,
                        'standard_bill' => 0,
                    ],
                    [
                        'name' => 'LPN',
                        'standard_pay' => 0,
                        'standard_bill' => 0,
                    ],
                    [
                        'name' => 'RN',
                        'standard_pay' => 0,
                        'standard_bill' => 0,
                    ],
                ];

            }
            $response['covid_pay_amount'] = $provider->getCovidPayAmount();
            $response['covid_bill_amount'] = $provider->getCovidBillAmount();

        } else {
            $response['success'] = false;
        }

        return $response;
    }

    public static function saveProviderPayRates($data)
    {
        $response = ['success' => false];

        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $data['id']]);

        $payRates = $data['pay_rates'];
        $providerPayRates = [];
        foreach ($payRates as $rate) {
            $providerPayRates[$rate['name']] = [
                'standard_pay' => $rate['standard_pay'],
                'standard_bill' => $rate['standard_bill'],
            ];
        }
        $provider->setCovidPayAmount($data['covid_pay_amount']);
        $provider->setCovidBillAmount($data['covid_bill_amount']);

        if ($providerPayRates && $provider) {
            $provider->setPayRates($providerPayRates);
            app::$entityManager->flush();
            $response['success'] = true;
        }

        self::updateUpcomingShiftRates($provider);

        // update shifts from

        return $response;
    }

		public static function loadNurseCheckrPayInfo($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        $appCheckrPay = [];

        /** @var Nurse $nurse */
        $nurse = null;

        if (!$id) {
            $response['message'] = 'Cannot find nurse.';
            return $response;
        } else {
            $nurse = ioc::get('Nurse', ['id' => $id]);
        }

        /** @var Nurse $nurse */
        if ($nurse) {
            $member = $nurse->getMember();
            if ($member && !$nurse->getCheckrPayId()) {
                /** @var NurseApplication $app */
                $app = $member->getNurseApplication();
                if ($app && !empty(json_decode($app->getCheckrPayId())->checkr_pay_id)) {
                    $appCheckrPay = json_decode($app->getCheckrPayId());
                }
            }
            $response['checkr_pay'] =
            array(
                'checkr_pay_id' => $appCheckrPay->checkr_pay_id ?: ($nurse->getCheckrPayId() ?: ''),
            );
            app::$entityManager->flush();
            $response['success'] = true;
        } else {
            $response['message'] = 'Could not find the nurse with the provided ID';
        }

        return $response;
    }

    public static function saveNurseCheckrPayInfo($data)
    {
        $response = ['success' => false];
        $id = $data['id'];

        /** @var Nurse $nurse */
        $nurse = null;

        if (!$id) {
            $response['message'] = 'Cannot find nurse.';
            return $response;
        } else {
            $nurse = ioc::get('Nurse', ['id' => $id]);
        }

        /** @var Nurse $nurse */
        if ($nurse && $data['checkr_pay_id']) {
            $nurse->setCheckrPayId($data['checkr_pay_id']);

            app::$entityManager->flush();
            $response['success'] = true;
        } else {
            // error finding checkr pay data
            $response['message'] = 'Error saving Checkr Pay data.';
        }

        return $response;
    }

		public static function createCheckrPayWorker($data)
		{
				$response = ['success' => false];
				$nurse = ioc::get('Nurse', ['id' => $data['id']]);
				if (!$nurse) {
					$response['message'] = 'Cannot find nurse';
					return $response;
				}
        $newPayWorker = (new CheckrPayService)->createWorker($nurse);
				if (!isset($newPayWorker['id'])) {
					$reponse['message'] = 'Failed to create new worker.';
					return $response;
				}
        $checkrPayId = $newPayWorker["id"];
        $nurse->setCheckrPayId($checkrPayId);
				app::$entityManager->flush();
				$response = ['success' => true];
				return $response;
		}

		public static function listCheckrPayWorkers($data)
		{
				$response = ['success' => false];
				$workers = (new CheckrPayService)->listWorkers();
				$response = ['success' => true];
				$response['message'] = json_encode($workers);
				return $response;
		}

    public static function loadNurseDirectDepositInfo($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        $appDirectDeposit = [];

        /** @var Nurse $nurse */
        $nurse = null;

        if (!$id) {
            $response['message'] = 'Cannot find nurse.';
            return $response;
        } else {
            $nurse = ioc::get('Nurse', ['id' => $id]);
        }

        /** @var Nurse $nurse */
        if ($nurse) {
            $member = $nurse->getMember();
            if ($member && !$nurse->getAccountHolderName() && !$nurse->getAccountNumber() && !$nurse->getRoutingNumber() && !$nurse->getBankAccountType() && !$nurse->getBankName()) {
                /** @var NurseApplication $app */
                $app = $member->getNurseApplication();
                if ($app && !empty(json_decode($app->getDirectDeposit())->bank_account_holder_name)) {
                    $appDirectDeposit = json_decode($app->getDirectDeposit());
                }
            }
            $response['direct_deposit'] =
            array(
                'bank_account_holder_name' => $appDirectDeposit->bank_account_holder_name ?: ($nurse->getAccountHolderName() ?: ''),
                'bank_account_number' => $appDirectDeposit->bank_account_number ?: ($nurse->getAccountNumber() ?: ''),
                'bank_routing_number' => $appDirectDeposit->bank_routing_number ?: ($nurse->getRoutingNumber() ?: ''),
                'bank_account_type' => $appDirectDeposit->bank_account_type ?: ($nurse->getBankAccountType() ?: ''),
                'bank_name' => $appDirectDeposit->bank_name ?: ($nurse->getBankName() ?: ''),
            );
            app::$entityManager->flush();
            $response['success'] = true;
        } else {
            $response['message'] = 'Could not find the nurse with the provided ID';
        }

        return $response;
    }

    public static function saveNurseDirectDepositInfo($data)
    {
        $response = ['success' => false];
        $id = $data['id'];

        /** @var Nurse $nurse */
        $nurse = null;

        if (!$id) {
            $response['message'] = 'Cannot find nurse.';
            return $response;
        } else {
            $nurse = ioc::get('Nurse', ['id' => $id]);
        }

        /** @var Nurse $nurse */
        if ($nurse && $data['direct_deposit']) {
            $nurse->setAccountHolderName($data['direct_deposit']['bank_account_holder_name']);
            $nurse->setAccountNumber($data['direct_deposit']['bank_account_number']);
            $nurse->setRoutingNumber($data['direct_deposit']['bank_routing_number']);
            $nurse->setBankAccountType($data['direct_deposit']['bank_account_type']);
            $nurse->setBankName($data['direct_deposit']['bank_name']);

            app::$entityManager->flush();
            $response['success'] = true;
        } else {
            // error finding direct_deposit data
            $response['message'] = 'Error finding direct_deposit data.';
        }

        return $response;
    }

    /**
     * Only a single attribute for pay card data right now
     */
    public function loadNursePayCardInfo($data)
    {
        $response = ['success' => false];
        $id = $data['id'];

        /** @var Nurse $nurse */
        $nurse = null;

        if (!$id) {
            $response['message'] = 'Cannot find nurse.';
            return $response;
        } else {
            $nurse = ioc::get('Nurse', ['id' => $id]);
            if ($nurse) {
                $response['payCardAccountNumber'] = $nurse->getPayCardAccountNumber();
                $response['success'] = true;
            }
        }

        return $response;
    }

    /**
     * Only a single attribute for pay card data right now
     */
    public function saveNursePayCardInfo($data)
    {
        $response = ['success' => false];
        $id = $data['id'];

        /** @var Nurse $nurse */
        $nurse = null;

        if (!$id) {
            $response['message'] = 'Cannot find nurse.';
            return $response;
        } else {
            $nurse = ioc::get('Nurse', ['id' => $id]);
            if ($nurse) {
                $nurse->setPayCardAccountNumber($data['payCardAccountNumber']);
                app::$entityManager->flush();
                $response['success'] = true;
            }
        }

        return $response;
    }

    public static function loadNurseEmergencyContacts($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        // $appEmergencyContacts = [];

        /** @var Nurse $nurse */
        $nurse = null;

        if (!$id) {
            $response['message'] = 'Cannot find nurse.';
            return $response;
        } else {
            $nurse = ioc::get('Nurse', ['id' => $id]);
        }

        /** @var NstMember $member */
        $member = $nurse->getMember();

        /** @var NurseApplicationPartTwo $app2 */
        $app2 = $member->getNurseApplicationPartTwo();
        if (!$app2) {

            /** @var ApplicationPart2 */
            $app2 = $member->getApplicationPart2();
        }

        /** @var Nurse $nurse */
        if ($nurse) {
            $member = $nurse->getMember();

            /** @var NurseApplication $app */
            $app = $member->getNurseApplication();
            if (!$app) {
                static::createApplicationForExistingNurse($member);
            }
            try {
                $appEmergencyContactOne = json_decode($app?->getEmergencyContactOne(), true);

                if ($appEmergencyContactOne == null) {

                    $appEmergencyContactOne = [

                        'first_name' => $app2?->getEmergencyContactOneFirstName(),
                        'last_name' => $app2?->getEmergencyContactOneLastName(),
                        'phone_number' => $app2?->getEmergencyContactOnePhoneNumber(),
                        'relationship' => $app2?->getEmergencyContactOneRelationship(),
                    ];
                }
                $response['emergency_contact_one'] = $appEmergencyContactOne;

            } catch (TypeError) {
                if ($app && !empty($app->getEmergencyContactOne())) {
                    $appEmergencyContactOne = $app->getEmergencyContactOne();
                    $response['emergency_contact_one'] = $appEmergencyContactOne;

                } else {
                    $response['message'] = 'Could not load emergency contact one';
                    return $response;
                }
            }

            try {
                $appEmergencyContactTwo = json_decode($app?->getEmergencyContactTwo(), true);

                if ($appEmergencyContactTwo == null) {

                    $appEmergencyContactTwo = [

                        'first_name' => $app2?->getEmergencyContactTwoFirstName(),
                        'last_name' => $app2?->getEmergencyContactTwoLastName(),
                        'phone_number' => $app2?->getEmergencyContactTwoPhoneNumber(),
                        'relationship' => $app2?->getEmergencyContactTwoRelationship(),
                    ];
                }
                $response['emergency_contact_two'] = $appEmergencyContactTwo;
            } catch (TypeError) {
                if ($app && !empty($app->getEmergencyContactTwo())) {
                    $appEmergencyContactTwo = $app->getEmergencyContactTwo();
                    $response['emergency_contact_two'] = $appEmergencyContactTwo;

                } else {
                    $response['message'] = 'Could not load emergency contact two';
                    return $response;
                }
            }

            app::$entityManager->flush();
            $response['success'] = true;
        } else {
            $response['message'] = 'Could not find the nurse with the provided ID';
        }

        return $response;
    }

    public static function saveNurseEmergencyContacts($data)
    {
        $response = ['success' => false];
        $id = $data['id'];

        /** @var Nurse $nurse */
        $nurse = null;

        if (!$id) {
            $response['message'] = 'Cannot find nurse.';
            return $response;
        } else {
            $nurse = ioc::get('Nurse', ['id' => $id]);
        }

        /** @var Nurse $nurse */
        $member = $nurse->getMember();

        /** @var NurseApplication $app */
        $app = $member->getNurseApplication();

        $app->setEmergencyContactOne($data['emergency_contact_one']);
        $app->setEmergencyContactTwo($data['emergency_contact_two']);

        app::$entityManager->flush();
        $response['success'] = true;

        return $response;
    }

    public static function createApplicationForExistingNurse($member)
    {
        $data['member_id'] = $member;
        $applicationJSON = NurseApplicationController::store($data);
        return $applicationJSON;
    }

    public static function loadNurseContactInfo($data)
    {
        $response = ['success' => false];
        $id = $data['id'];

        /** @var Nurse $nurse */
        $nurse = null;

        if (!$id) {
            $response['message'] = 'Cannot find nurse.';
            return $response;
        } else {
            $nurse = ioc::get('Nurse', ['id' => $id]);
        }
        $member = $nurse->getMember();

        /** @var saMember $user */
        $user = ioc::get('saMember', $member);

        /** @var saMemberEmail $allEmails[] */
        $allEmails = $user->getEmails();

        $returnEmails = array();
        for ($i = 0; $i < count($allEmails); $i++) {
            $returnEmails[$i]['name'] = $allEmails[$i]->getEmail();
            $returnEmails[$i]['type'] = ucfirst($allEmails[$i]->getType());
            $primary = $allEmails[$i]->getIsPrimary();
            if ($primary == 1) { $returnEmails[$i]['primary'] = 'Yes'; }
            else { $returnEmails[$i]['primary'] = 'No'; }
            $active = $allEmails[$i]->getIsActive();
            if ($active == 1) { $returnEmails[$i]['active'] = 'Yes'; }
            else { $returnEmails[$i]['active'] = 'No'; }
        }

        /** @var saMemberPhone $allPhones[] */
        $allPhones = $user->getPhones();

        $returnNumbers = array();
        for ($i = 0; $i < count($allPhones); $i++) {
            $returnNumbers[$i]['phone'] = $allPhones[$i]->getPhone();
            $returnNumbers[$i]['type'] = ucfirst($allPhones[$i]->getType());
            $primary = $allPhones[$i]->getIsPrimary();
            if ($primary == 1) { $returnNumbers[$i]['primary'] = 'Yes'; }
            else { $returnNumbers[$i]['primary'] = 'No'; }
            $active = $allPhones[$i]->getIsActive();
            if ($active == 1) { $returnNumbers[$i]['active'] = 'Yes'; }
            else { $returnNumbers[$i]['active'] = 'No'; }
        }

        $contact['phone_numbers'] = $returnNumbers;
        $contact['emails'] = $returnEmails;
        $contact['success'] = true;

        return $contact;
    }

    public static function saveNurseContactInfo($data)
    {
        $response = ['success' => false];
        $id = $data['id'];

        /** @var Nurse $nurse */
        $nurse = null;

        if (!$id) {
            $response['message'] = 'Cannot find nurse.';
            return $response;
        } else {
            $nurse = ioc::get('Nurse', ['id' => $id]);
        }
        $member = $nurse->getMember();

        /** @var saMember $user */
        $user = ioc::get('saMember', $member);

        /** @var saMemberEmail $allEmails[] */
        $allEmails = $user->getEmails();

        $emailsToKeep = array();
        for ($i = 0; $i < count($data['emails']); $i++) {

            // convert primary, active to 1/0's
            if ($data['emails'][$i]['primary'] == 'Yes') { $data['emails'][$i]['primary'] = "1"; }
            else { $data['emails'][$i]['primary'] = "0"; }
            if ($data['emails'][$i]['active'] == 'Yes') { $data['emails'][$i]['active'] = "1"; }
            else { $data['emails'][$i]['active'] = "0"; }

            $matchedEmail = false;
            for ($j = 0; $j < count($allEmails); $j++) {
                if ($allEmails[$j]->getEmail() == $data['emails'][$i]['name']) {

                    // mark that email exists in both
                    array_push($emailsToKeep, $j);

                    // make edits where necessary
                    if ($allEmails[$j]->getType() != $data['emails'][$i]['type']) {
                        $allEmails[$j]->setType($data['emails'][$i]['type']);
                        app::$entityManager->persist($allEmails[$j]);
                        app::$entityManager->flush($allEmails[$j]);
                    }
                    if ($allEmails[$j]->getIsPrimary() != $data['emails'][$i]['primary']) {
                        $allEmails[$j]->setIsPrimary($data['emails'][$i]['primary']);
                        app::$entityManager->persist($allEmails[$j]);
                        app::$entityManager->flush($allEmails[$j]);
                    }
                    if ($allEmails[$j]->getIsActive() != $data['emails'][$i]['active']) {
                        $allEmails[$j]->setIsActive($data['emails'][$i]['active']);
                        app::$entityManager->persist($allEmails[$j]);
                        app::$entityManager->flush($allEmails[$j]);
                    }

                    $matchedEmail = true;
                }
            }

            // if incoming email does not exist already, create
            if (!$matchedEmail) {
                /** @var saMemberEmail $email */
                $email = ioc::resolve('saMemberEmail');

                $email->setEmail($data['emails'][$i]['name']);
                $email->setType($data['emails'][$i]['type']);
                $email->setIsPrimary($data['emails'][$i]['primary']);
                $email->setIsActive($data['emails'][$i]['active']);
                $email->setMember($member);
                $user->addEmail($email);

                app::$entityManager->persist($email);
                app::$entityManager->flush();
            }
        }

        // delete emails that no longer exist
        for ($i = 0; $i < count($allEmails); $i++) {
            if (!in_array($i, $emailsToKeep)) {
                app::$entityManager->remove($allEmails[$i]);
                app::$entityManager->flush();
            }
        }

        /** @var saMemberPhone $allPhones[] */
        $allPhones = $user->getPhones();

        $phonesToKeep = array();
        for ($i = 0; $i < count($data['phone_numbers']); $i++) {

            // convert primary, active to 1/0's
            if ($data['phone_numbers'][$i]['primary'] == 'Yes') { $data['phone_numbers'][$i]['primary'] = "1"; }
            else { $data['phone_numbers'][$i]['primary'] = "0"; }
            if ($data['phone_numbers'][$i]['active'] == 'Yes') { $data['phone_numbers'][$i]['active'] = "1"; }
            else { $data['phone_numbers'][$i]['active'] = "0"; }

            $matchedPhone = false;
            for ($j = 0; $j < count($allPhones); $j++) {
                if ($allPhones[$j]->getPhone() == $data['phone_numbers'][$i]['phone']) {

                    // mark that phone exists in both
                    array_push($phonesToKeep, $j);

                    // make edits where necessary
                    if ($allPhones[$j]->getType() != $data['phone_numbers'][$i]['type']) {
                        $allPhones[$j]->setType($data['phone_numbers'][$i]['type']);
                        app::$entityManager->persist($allPhones[$j]);
                        app::$entityManager->flush($allPhones[$j]);
                    }
                    if ($allPhones[$j]->getIsPrimary() != $data['phone_numbers'][$i]['primary']) {
                        $allPhones[$j]->setIsPrimary($data['phone_numbers'][$i]['primary']);
                        app::$entityManager->persist($allPhones[$j]);
                        app::$entityManager->flush($allPhones[$j]);
                    }
                    if ($allPhones[$j]->getIsActive() != $data['phone_numbers'][$i]['active']) {
                        $allPhones[$j]->setIsActive($data['phone_numbers'][$i]['active']);
                        app::$entityManager->persist($allPhones[$j]);
                        app::$entityManager->flush($allPhones[$j]);
                    }

                    $matchedPhone = true;
                }
            }

            // if incoming phone does not exist already, create
            if (!$matchedPhone) {
                /** @var saMemberPhone $phone */
                $phone = ioc::resolve('saMemberPhone');

                $phone->setPhone($data['phone_numbers'][$i]['phone']);
                $phone->setType($data['phone_numbers'][$i]['type']);
                $phone->setIsPrimary($data['phone_numbers'][$i]['primary']);
                $phone->setIsActive($data['phone_numbers'][$i]['active']);
                $phone->setMember($member);
                $user->addPhone($phone);

                app::$entityManager->persist($phone);
                app::$entityManager->flush();
            }
        }

        // delete phones that no longer exist
        for ($i = 0; $i < count($allPhones); $i++) {
            if (!in_array($i, $phonesToKeep)) {
                app::$entityManager->remove($allPhones[$i]);
                app::$entityManager->flush();
            }
        }

        $response['success'] = true;
        return $response;
    }

    public static function loadNurseNotes($data)
    {
        $response = ['success' => false];
        $id = $data['id'];

        /** @var Nurse $nurse */
        $nurse = null;

        if (!$id) {
            $response['message'] = 'Cannot find nurse.';
            return $response;
        } else {
            $nurse = ioc::get('Nurse', ['id' => $id]);
            $member = $nurse->getMember();
        }

        $allNotes = $member->getNotes();

        $returnNotesArray = array();
        foreach($allNotes as $noteObject) {
            $note['id'] = $noteObject->getId();
            $note['note'] = $noteObject->getNote();
            $note['date'] = $noteObject->getDate();
            $note['time'] = $noteObject->getTime();
            $note['admin'] = $noteObject->getAdmin();
            array_push($returnNotesArray, $note);
        }

        $return['notes'] = $returnNotesArray;
        $return['success'] = true;

        return $return;
    }

    public static function saveNurseNotes($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        $nurse = null;

        if (!$id) {
            $response['message'] = 'Cannot find nurse.';
            return $response;
        } else {
            /** @var Nurse $nurse */
            $nurse = ioc::get('Nurse', ['id' => $id]);
            /** @var NstMember $member */
            $member = $nurse->getMember();
        }

        $allNotes = $member->getNotes();
        $idArray = array();
        foreach ($data['notes'] as $note) {
            array_push($idArray, $note['id']);
        }
        foreach ($allNotes as $note) {
            if (!in_array($note->getId(), $idArray)) {
                $member->removeNote($note);

                app::$entityManager->remove($note);
                app::$entityManager->flush();
            }
        }

        if ($nurse && $data['notes']) {

            foreach ($data['notes'] as $note) {

                if ($note['id']) {
                    /** @var NurseNote */
                    $savedNote = ioc::get('NurseNote', ['id' => $note['id']]);
                    if ($savedNote->getNote() != $note['note']) {
                        $savedNote->setNote($note['note']);
                        $savedNote->setDate($note['date']);
                        $savedNote->setTime($note['time']);
                        $savedNote->setAdmin($note['admin']);

                        app::$entityManager->persist($savedNote);
                        app::$entityManager->flush();
                    }
                } else {
                    // must be new note if no id, save new note
                    /** @var NurseNote */
                    $nurseNote = ioc::resolve('NurseNote');
                    $nurseNote->setMember($member);
                    $nurseNote->setNote($note['note']);
                    $nurseNote->setDate($note['date']);
                    $nurseNote->setTime($note['time']);
                    $nurseNote->setAdmin($note['admin']);

                    app::$entityManager->persist($nurseNote);
                    app::$entityManager->flush();

                    $member->addNote($nurseNote);
                }
            }
        }

        $response['success'] = true;

        return $response;
    }

    public static function getAdminNameForNurseNote()
    {
        /** @var saUser $currentUser */
        $currentUser = modRequest::request('sa.user');
        $return['first_name'] = $currentUser->getFirstName();
        $return['last_name'] = $currentUser->getLastName();

        $return['success'] = true;
        return $return;
    }

    public static function updateUpcomingShiftRates($provider)
    {
        $response = ['success' => false];

        $shiftRepo = ioc::getRepository('Shift');
        $shifts = $shiftRepo->getShiftsForProviderRateUpdates($provider);
        $payrollService = new PayrollService();
        foreach($shifts as $shift){
            // May need to notify nurses tied to shift that their rate has been updated in the future
            $payrollService->initializeShiftRates($shift);
        }

        $response['success'] = true;
        return $response;
    }

    public function getProviderNurseCredentialsList($data)
    {
        $credentials = ['success' => false];
        $allCredentials = ioc::getRepository('NurseCredential')->findAll();
        $providerCredentials = ioc::get('Provider', ['id' => $data['provider_id']])->getNurseCredentials();

        foreach($allCredentials as $key => $cred)
        {
            $credentials['credentials'][$key] = doctrineUtils::getEntityArray($cred);
            $credentials['credentials'][$key]['value'] = $providerCredentials->contains($cred);
        }

        $credentials['success'] = true;
        return $credentials;
    }

    public function saveProviderNurseCredentialsList($data)
    {
        $response = ['success' => false];

        $allCredentials = ioc::getRepository('NurseCredential')->findAll();
        $credentials = new ArrayCollection($data['credentials']);

        $provider = ioc::get('Provider', ['id' => $data['provider_id']]);
        $providerCredentials = $provider->getNurseCredentials();

        foreach ($allCredentials as $cred)
        {
            $credentialId = $cred->getId();

            $isEnabled = filter_var($credentials->filter(fn($c) => $credentialId == $c['id'])->first()['value'], FILTER_VALIDATE_BOOLEAN);

            if ($isEnabled) {
                if (!$providerCredentials->contains($cred)) {
                    $provider->addNurseCredential($cred);
                }
            } else {
                $provider->removeNurseCredential($cred);
            }
        }

        app::$entityManager->persist($provider);
        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }

    public function saveProviderPresetShiftTime($data)
    {
        $response = ['success' => false];


        $provider = ioc::get('Provider', ['id' => $data['provider_id']]);

        /** @var NstCategory $category */
        $category = ioc::get('NstCategory', ['id' => $data['preset_time']['category_id']]);

        // if id > 0 we are updating
        /** @var PresetShiftTime $presetShiftTime */
        if ($data['preset_time']['id'] && (int)$data['preset_time']['id'] > 0) {
            $presetShiftTime = ioc::get('PresetShiftTime', ['id' => $data['preset_time']['id']]);
        } else {
            $presetShiftTime = ioc::resolve('PresetShiftTime');
            $provider->addPresetShiftTime($presetShiftTime);
            $category->addPresetShiftTime($presetShiftTime);
            app::$entityManager->persist($presetShiftTime);
        }

        $presetShiftTime->setStartTime($data['preset_time']['start_time']);
        $presetShiftTime->setEndTime($data['preset_time']['end_time']);
        $presetShiftTime->setHumanReadable($data['preset_time']['human_readable']);
        $presetShiftTime->setCategory($category);
        $presetShiftTime->setProvider($provider);

        // app::$entityManager->persist($presetShiftTime);
        app::$entityManager->flush();

        $response['presetShiftId'] = $presetShiftTime->getId();
        $response['success'] = true;
        return $response;
    }

    public function getProviderPresetShiftTimes($data)
    {
        $response = ['success' => false];
        $providerPresetShiftTimes = ioc::get('Provider', ['id' => $data['provider_id']])->getPresetShiftTimes();

        try {
            foreach ($providerPresetShiftTimes as $presetShiftTime) {
                $presetShiftTimeArray = doctrineUtils::getEntityArray($presetShiftTime);
                $presetShiftTimeArray['category_id'] = $presetShiftTime->getCategory()?->getId();
                $presetShiftTimeArray['category_name'] = $presetShiftTime->getCategory()?->getName();
                $presetShiftTimeArray['full_time'] = $presetShiftTime->getStartTime() . ' - ' . $presetShiftTime->getEndTime();
                $response['presetShiftTimes'][] = $presetShiftTimeArray;
            }
        } catch(\Throwable $t) {
            $response['message'] = $t->getMessage();
            return $response;
        }

        $response['success'] = true;
        return $response;
    }

    public function deleteProviderPresetShiftTime($data)
    {
        $response = ['success' => false];
        try {
            $presetShiftTime = ioc::get('PresetShiftTime', ['id' => $data['preset_shift_time_id']]);

            if($presetShiftTime) {
                app::$entityManager->remove($presetShiftTime);
                app::$entityManager->flush();
            } else {
                $response['message'] = 'Could not locate preset shift time to delete.';
                return $response;
            }
        } catch (\Throwable $t) {
            $response['message'] = $t->getMessage();
            return $response;
        }

        $response['success'] = true;
        return $response;
    }


    public function saveNurseStates($data)
    {
        $response = ['success' => false];

        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $data['nurse_id']]);

        $nurseStates = $nurse->getStatesAbleToWork();
        $response['beforeCount'] = count($nurseStates);

        $states = $data['states'];
        foreach ($states as $state) {
            $stateObj = ioc::get('NstState', ['name' => $state['name']]);
            if (filter_var($state['selected'], FILTER_VALIDATE_BOOLEAN)) {
                if (!$nurseStates->contains($stateObj)) {
                    $response['statesToAdd'][] = $stateObj?->getName() ?: 'NO STATE HHAA';
                    $nurse->addStateAbleToWork($stateObj);
                }
            } else {
                if ($nurseStates->contains($stateObj)) {
                    $response['statesToRemove'][] = $stateObj?->getName() ?: 'NO STATE HHAA';
                    $nurse->removeStateAbleToWork($stateObj);
                }
            }
        }

        $response['afterCount'] = count($nurse->getStatesAbleToWork());

        app::$entityManager->persist($nurse);
        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }


    public function getNurseStates($data)
    {
        $response = ['success' => false];
        $nurseStates = ioc::get('Nurse', ['id' => $data['nurse_id']])->getStatesAbleToWork();
        $allStates = ioc::getRepository('NstState')->getAllStates();

        foreach ($allStates as $state) {
            $stateArray = doctrineUtils::getEntityArray($state);
            if ($nurseStates->contains($state)) {
                $stateArray['selected'] = true;
            } else {
                $stateArray['selected'] = false;
            }

            $response['states'][] = $stateArray;
        }

        $response['success'] = true;
        return $response;
    }

    public function getBreakDurationForProvider($providerId) {
        $response = ['success' => false];
        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $providerId]);
        $response['break_duration'] = $provider->getBreakLengthInMinutes();
        $response['success'] = true;
        return $response;
    }

    public function saveBreakDurationForProvider($data) {
        $response = ['success' => false];
        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $data['provider_id']]);
        $provider->setBreakLengthInMinutes($data['break_duration']);
        app::$entityManager->persist($provider);
        app::$entityManager->flush();
        $response['success'] = true;
        return $response;
    }


    public function loadExecutiveFacilities($data) {
        $executiveId = $data['id'];
        /** @var Executive $executive */
        $executive = ioc::get('Executive', ['id' => $executiveId]);

        $providers = $executive->getProviders();
        return array_map(function ($provider) {
            /** @var NstMember $member */
            $member = $provider->getMember();
            return [
                'id' => $provider->getId(),
                'company' => $member->getCompany(),
            ];
        }, $providers->toArray());
    }

    public function addFacilityToExecutive($data) {
        $response = ['success' => false];
        $executiveId = $data['id'];
        $facilityId = $data['facility_id'];
        /** @var Executive $executive */
        $executive = ioc::get('Executive', ['id' => $executiveId]);
        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $facilityId]);
        $executive->addProvider($provider);
        app::$entityManager->persist($executive);
        app::$entityManager->flush();
        $response['success'] = true;
        return $response;
    }

    public function removeFacilityFromExecutive($data) {
        $response = ['success' => false];
        $executiveId = $data['id'];
        $facilityId = $data['facility_id'];
        /** @var Executive $executive */
        $executive = ioc::get('Executive', ['id' => $executiveId]);
        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $facilityId]);
        $executive->removeProvider($provider);
        app::$entityManager->persist($executive);
        app::$entityManager->flush();
        $response['success'] = true;
        return $response;
    }

    public function loadFacilities() {
        $response = ['success' => false];
        $providers = ioc::getRepository('Provider')->findAll();
        return array_map(function ($provider) {
            /** @var NstMember $member */
            $member = $provider->getMember();
            return [
                'id' => $provider->getId(),
                'company' => $member->getCompany(),
            ];
        }, $providers);
    }
}
