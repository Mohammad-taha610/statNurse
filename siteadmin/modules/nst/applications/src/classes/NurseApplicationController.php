<?php

namespace nst\applications;

use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Matrix\Exception;
use sacore\application\IocDuplicateClassException;
use sacore\application\IocException;
use sacore\application\ModRequestAuthenticationException;
use sacore\application\responses\Json;
use sacore\application\ValidateException;
use sa\member\auth;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\DateTime;
use sacore\application\controller;
use sacore\application\modRequest;
use sa\member\saMember;
use sa\system\saUser;
use sacore\utilities\doctrineUtils;
use nst\member\NurseApplication;
use nst\applications\NurseApplicationService;
use sacore\application\responses\View;
use nst\member\NurseApplicationPartTwo;
use nst\member\NstFile;
use sa\files\safile;

class NurseApplicationController extends controller
{
    /**
     * @throws \sacore\application\Exception
     * @throws ModRequestAuthenticationException
     */
    public function index(): View
    {
        // $_SESSION["isSubmitted"] = true;
        $isSubmitted = $_SESSION["isSubmitted"];

        if ($isSubmitted == true) {
            $view = new View('application_submitted');
            $_SESSION["isSubmitted"] = false;
        } else {
            $view = new View('nurse_application_form');
        }
        
        $member = modRequest::request('auth.member');

        $view->data['member'] = null;

        if ($member) {
            $view->data['member'] = doctrineUtils::getEntityArray($member);
        }

        return $view;
    }

    /**
     * THIS IS FOR VIEWING THE APPLICATION FORM IN A FULL-PAGE VIEW FOR PDF PRINTING
     * @throws \sacore\application\Exception
     * @throws ModRequestAuthenticationException
     */
    public function indexPdfPrint(): View
    {
        return new View('nurse_application_form_pdf_print');
    }

    public static function applicationLogin($data)
    {
        $response = ['success' => false];
        /** @var saMember $member */   
        $auth = auth::getInstance();
        $auth->logon($data['email'], $data['password']);

        $member = modRequest::request('auth.member');
        if ($member == null) {
            $response['errMessage'] = "No member";
            return $response;
        }

        /** @var NurseApplication $application */
        $application = doctrineUtils::getEntityArray(ioc::getRepository('NurseApplication')->findOneBy(['member' => $member]));
        if ($application == null) {
            $response['errMessage'] = "No saved application for this user";
            return $response;
        }

        $form['college'] =  json_decode($application['college'], true);
        if ($form['college']['shown'] == "" || $form['college']['shown'] == "false") { $form['college']['shown'] = false; }

        $form['criminal_record'] = json_decode($application['criminal_record'], true);

        $form['direct_deposit'] = json_decode($application['direct_deposit'], true);

        $form['emergency_contact_one'] = is_array($application['emergency_contact_one']) ? $application['emergency_contact_one'] : json_decode($application['emergency_contact_one'], true);

        $form['emergency_contact_two'] = is_array($application['emergency_contact_two']) ? $application['emergency_contact_two'] : json_decode($application['emergency_contact_two'], true);

        $form['employment'] = json_decode($application['employment'], true);

        $form['employment_details_one'] = json_decode($application['employment_details_one'], true);

        $form['employment_details_two'] = json_decode($application['employment_details_two'], true);
        if ($form['employment_details_two']['shown'] == "" || $form['employment_details_two']['shown'] == "false") { $form['employment_details_two']['shown'] = false; }

        $form['employment_details_three'] = json_decode($application['employment_details_three'], true);
        if ($form['employment_details_three']['shown'] == "" || $form['employment_details_three']['shown'] == "false") { $form['employment_details_three']['shown'] = false; }

        $form['files'] = json_decode($application['files'], true);

        $form['highschool'] = json_decode($application['highschool'], true);

        $id = $application['id'];
        $data['id'] = $id;

        $form['license_and_certifications'] = json_decode($application['license_and_certifications'], true);

        $form['medical_history'] = json_decode($application['medical_history'], true);

        $form['nurse'] = json_decode($application['nurse'], true);
        
        if ($member) {
            if(strlen($form['nurse']['socialsecurity_number']) > 11){
                /** @var saUser $user */
                $user = $member->getUsers()->first();
                $ss = $form['nurse']['socialsecurity_number'];
                $cipher = "AES-128-CTR";
                $key = $user->getUserKey();
                $ssn = openssl_decrypt($ss, $cipher, (string)$key, 0, ord($key));
                $form['nurse']['socialsecurity_number'] = $ssn;
            } 
        }

        $form['nurse_stat_info'] = json_decode($application['nurse_stat_info'], true);

        $form['other_education'] = json_decode($application['other_education'], true);
        if ($form['other_education']['shown'] == "" || $form['other_education']['shown'] == "false") { $form['other_education']['shown'] = false; }

        $form['professional_reference_one'] = json_decode($application['professional_reference_one'], true);

        $form['professional_reference_two'] = json_decode($application['professional_reference_two'], true);

        $form['tb'] = json_decode($application['tb'], true);

        $form['terms'] = json_decode($application['terms'], true);

        $data['form'] = $form;
        $data['success'] = true;

        return $data;
    }

    public static function store($data): Json
    {
        /** @var saMember $member */
        try {
            $existingApplication = false;
            $json = new Json();
            if ($data['member_id']) {
                $member = ioc::get('saMember', ['id' => $data['member_id']]);
                if (ioc::getRepository('NurseApplication')->findOneBy(['member' => $member])) {
                    $existingApplication = true;
                } else { $existingApplication = false; }
            } else {
                // Create member
                if ($data['registration_info']) {
                    $email = ioc::getRepository('saMemberEmail')->findOneBy(
                        ['email' => $data['registration_info']['email']]
                    );

                    if (!$email) {
                        $saMember = ioc::staticResolve('saMember');
                        $saMember::memberSignUp($data['registration_info']);

                        $auth = auth::getInstance();
                        $auth->logon($data['registration_info']['email'], $data['registration_info']['password']);
                    } else {
                        modRequest::request('sa.member.login.async', null, $data['registration_info']);
                    }
                }

                $member = modRequest::request('auth.member');
                if (ioc::getRepository('NurseApplication')->findOneBy(['member' => $member])) { $existingApplication = true; }
            }// Save application

            $data['return'] = true;
            $socAlreadyExists = static::getSocialSecurityNumber($data);
            if (!$socAlreadyExists['exists'] || strlen($data['application']['nurse']['socialsecurity_number']) == 11 ) {
                // encrypt social security number
                /** @var saUser $user */
                $user = $member->getUsers()[0];
                $ss = $data['application']['nurse']['socialsecurity_number'];
                $cipher = "AES-128-CTR";
                $key = $user->getUserKey();
                $encrypted_ss = openssl_encrypt($ss, $cipher, $key, 0, ord($key));
                // encrypt social security number
                $data['application']['nurse']['socialsecurity_number']  = $encrypted_ss; // encrypt social security number
            } else {
                $data['application']['nurse']['socialsecurity_number']  = $socAlreadyExists['ssn']; // encrypt social security number
            }

            $data['application']['nurse'] = json_encode($data['application']['nurse']);
            $data['application']['employment_details_one'] = json_encode(
                $data['application']['employment_details_one']
            );
            $data['application']['employment_details_two'] = json_encode(
                $data['application']['employment_details_two']
            );
            $data['application']['employment_details_three'] = json_encode(
                $data['application']['employment_details_three']
            );
            $data['application']['highschool'] = json_encode($data['application']['highschool']);
            $data['application']['college'] = json_encode($data['application']['college']);
            $data['application']['other_education'] = json_encode($data['application']['other_education']);
            $data['application']['professional_reference_one'] = json_encode(
                $data['application']['professional_reference_one']
            );
            $data['application']['professional_reference_two'] = json_encode(
                $data['application']['professional_reference_two']
            );
            $data['application']['employment'] = json_encode($data['application']['employment']);
            $data['application']['criminal_record'] = json_encode($data['application']['criminal_record']);
            $data['application']['nurse_stat_info'] = json_encode($data['application']['nurse_stat_info']);
            $data['application']['license_and_certifications'] = json_encode(
                $data['application']['license_and_certifications']
            );
            $data['application']['emergency_contact_one'] = json_encode($data['application']['emergency_contact_one']);
            $data['application']['emergency_contact_two'] = json_encode($data['application']['emergency_contact_two']);
            $data['application']['medical_history'] = json_encode($data['application']['medical_history']);
            $data['application']['direct_deposit'] = json_encode($data['application']['direct_deposit']);
            $data['application']['terms'] = json_encode($data['application']['terms']);
            $data['application']['tb'] = json_encode($data['application']['tb']);
            if ($data['is_submitting'] === "true") {
                $data['application']['submitted_at'] = new DateTime('now', app::getInstance()->getTimeZone());
            }
            // save files if they exist
            if ((is_array($data['application']['files']) || is_countable($data['application']['files'])) && count($data['application']['files']) > 0) {
                $data['application']['files'] = json_encode($data['application']['files']);
            }
            if ($existingApplication) {
                /** @var NurseApplication $application */
                $nurseApplication = ioc::getRepository('NurseApplication')->findOneBy(['member' => $member]);
                doctrineUtils::setEntityData(self::flatten($data['application']), $nurseApplication);
                // app::$entityManager->persist($nurseApplication);
                app::$entityManager->flush();
                $json->data['success'] = true;
                return $json;
            } else {
                /** @var NurseApplication $application */
                $nurseApplication = doctrineUtils::setEntityData(self::flatten($data['application']), new NurseApplication);
                $nurseApplication->setMember($member);
                app::$entityManager->persist($nurseApplication);
                app::$entityManager->flush();
                $json->data['success'] = true;
                return $json;
            }
        } catch (MappingException | IocException | ModRequestAuthenticationException | IocDuplicateClassException | ORMException | ValidateException $e) {
            $json->data['error'] = $e->getMessage();
            $json->data['success'] = false;

            return $json;
        } catch (ORMInvalidArgumentException $e){
            $json->data['error'] = 'Email address selected is already in use.';
            $json->data['success'] = false;

            return $json;
        }
    }

    /**
     * @throws \sacore\application\Exception
     * @throws ModRequestAuthenticationException
     */
    public function indexTwo(): View
    {
        $member = modRequest::request('auth.member');

        $view = new View('nurse_application_form_two');

        $view->data['member'] = null;

        if ($member) {
            $view->data['member'] = doctrineUtils::getEntityArray($member);
        }

        return $view;
    }

    /**
     * @throws OptimisticLockException
     * @throws MappingException
     * @throws ORMException
     * @throws ModRequestAuthenticationException
     */
    public static function storeTwo($data)
    {
        // Save second part of the application
        $member = modRequest::request('auth.member');

        $data['application']['medical_history'] = json_encode($data['application']['medical_history']);
        $data['application']['terms']['signature'] = json_encode($data['application']['tb']['signature']);
        $data['application']['tb']['signature'] = json_encode($data['application']['tb']['signature']);

        $nurseApplication = doctrineUtils::setEntityData(self::flatten($data['application']), new NurseApplicationPartTwo);
        $nurseApplication->setMember($member);

        app::$entityManager->persist($nurseApplication);
        app::$entityManager->flush();
    }

    /**
     * @throws ModRequestAuthenticationException
     */
    public static function submissionEmail($data)
    {
        if ($data['registration_info']['email']) {
            $toAddress = ioc::getRepository('saMemberEmail')->findOneBy(['email' => $data['registration_info']['email']]);

            if ($toAddress) {
                modRequest::request('messages.startEmailBatch');

                $subject = 'Your Nurse Application has been received.';
                $body = 'Thank you, your nurse application has been received.  We will review your application and contact you shortly.';

                $result = modRequest::request('messages.sendEmail', array(
                        'to' => $toAddress,
                        'body' => $body,
                        'subject' => $subject
                    )
                );

                modRequest::request('messages.commitEmailBatch');
            }
        } else {
            // shouldn't occur
            return;
        }
    }

    public static function flatten($array, $prefix = '')
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + self::flatten($value, $prefix . $key . '_');
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        return $result;
    }

    public static function navigateToSubmittedPage()
    {
        $_SESSION["isSubmitted"] = true;
    }

    public static function getSocialSecurityNumber($data)
    {
        $response = ['success' => false];
        $returnSocial = filter_var($data['return'], FILTER_VALIDATE_BOOLEAN);

        $member = modRequest::request('auth.member');

        if ($member) {
            /** @var NurseApplication $application */
            $application = ioc::getRepository('NurseApplication')->findOneBy(['member' => $member]);
            if($application) {
                $application = doctrineUtils::getEntityArray($application);
            } else {
                return $response;
            }
    
            $socialSecurityNum = json_decode($application['nurse'], true);
            $socialSecurityNum = (string)$socialSecurityNum['socialsecurity_number'];
            try {
                if (strlen($socialSecurityNum) == 11) {
                    /** @var saUser $user */
                    $user = $member->getUsers()->first();
                    $cipher = "AES-128-CTR";
                    $key = $user->getUserKey();
                    $ssn = openssl_decrypt($socialSecurityNum, $cipher, (string)$key, 0, ord($key));
                } else {
                    $ssn = $socialSecurityNum;
                }

                if ($returnSocial) {
                    $response['ssn'] = $ssn;
                }         
            } catch (Exception $e) {
                $socialExists = false;
            }
        }

        $response['success'] = true;    
        $response['exists'] = (bool)$ssn;    
        return $response;
    }

    public static function deleteApplicationFile($data)
    {
        $response = ['success' => false];

        $nurseApplicationService = new NurseApplicationService;
        $nurseApplicationService->deleteApplicationFile($data);

        $response['success'] = true;
        return $response;
    }

    public static function createLogin($data)
    {
        $response = ['success' => false];

        $nurseApplicationService = new NurseApplicationService;
        $response = $nurseApplicationService->createLogin($data);

        $response['success'] = true;
        return $response;
    }

    public static function loginApplicant($data)
    {
        $response = ['success' => false];

        $nurseApplicationService = new NurseApplicationService;
        $response = $nurseApplicationService->loginApplicant($data);

        $response['success'] = true;
        return $response;
    }

    public static function logoutApplicant()
    {
        $response = ['success' => false];

        $nurseApplicationService = new NurseApplicationService;
        $response = $nurseApplicationService->logoutApplicant();

        $response['success'] = true;
        return $response;
    }

    public static function checkSession()
    {
        $response = ['success' => false];

        $nurseApplicationService = new NurseApplicationService;
        $response = $nurseApplicationService->checkSession();

        $response['success'] = true;
        return $response;
    }

    public static function saveApplicationProgress($data)
    {
        $response = ['success' => false];

        $nurseApplicationService = new NurseApplicationService;
        $response = $nurseApplicationService->saveApplicationProgress($data);

        $response['success'] = true;
        return $response;
    }

    public static function loadApplicationProgress($data)
    {
        $response = ['success' => false];

        $nurseApplicationService = new NurseApplicationService;
        $response = $nurseApplicationService->loadApplicationProgress($data);

        $response['success'] = true;
        return $response;
    }

    public static function saveFilesProgress($data)
    {
        $response = ['success' => false];

        $nurseApplicationService = new NurseApplicationService;
        $response = $nurseApplicationService->saveFilesProgress($data);

        $response['success'] = true;
        return $response;
    }

    public static function loadFilesProgress($data)
    {
        $response = ['success' => false];

        $nurseApplicationService = new NurseApplicationService;
        $response = $nurseApplicationService->loadFilesProgress($data);

        $response['success'] = true;
        return $response;
    }

    public static function mobileFileUpload($request)
    {
        $application = ioc::getRepository('NurseApplication')->find(
            $request->getRouteParams()->get('application')
        );

        $view = new View('mobile_file_upload', static::viewLocation());
        $view->data['application'] = $application;

        return $view;
    }

    public static function sendMobileFileUpload($data)
    {
        $response = ['success' => false];

        $nurseApplicationService = new NurseApplicationService;
        $response = $nurseApplicationService->sendMobileFileUpload($data);

        $response['success'] = true;
        return $response;
    }

    public static function startDrugScreen($data)
    {
        $response = ['success' => false];

        $nurseApplicationService = new NurseApplicationService;
        $response = $nurseApplicationService->startDrugScreen($data);

        $response['success'] = true;
        return $response;
    }
    
    public static function startBackgroundCheck($data)
    {
        $response = ['success' => false];

        $nurseApplicationService = new NurseApplicationService;
        $response = $nurseApplicationService->startBackgroundCheck($data);

        $response['success'] = true;
        return $response;
    }

    public static function loadDrugScreenProgress($data)
    {
        $response = ['success' => false];

        $nurseApplicationService = new NurseApplicationService;
        $response = $nurseApplicationService->loadDrugScreenProgress($data);

        $response['success'] = true;
        return $response;
    }

    public static function loadBackgroundCheckProgress($data)
    {
        $response = ['success' => false];

        $nurseApplicationService = new NurseApplicationService;
        $response = $nurseApplicationService->loadBackgroundCheckProgress($data);

        $response['success'] = true;
        return $response;
    }

    public static function signBackgroundCheckAgreement($data)
    {
        $response = ['success' => false];

        $nurseApplicationService = new NurseApplicationService;
        $response = $nurseApplicationService->signBackgroundCheckAgreement($data);

        $response['success'] = true;
        return $response;
    }
}
