<?php

namespace nst\applications;

use DoctrineProxies\__CG__\sa\member\saMember;
use nst\applications\ApplicationStatus;
use nst\member\CheckrPayService;
use nst\member\NstFileTag;
use nst\member\NstMember;
use nst\member\Nurse;
use nst\member\SaNstMemberService;
use nst\messages\SmsService;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\modRequest;
use sa\files\saFile;
use sacore\utilities\Cookie;
use sa\member\auth;
use sa\system\saUser;
use sa\system\saUserRepository;
use sacore\utilities\doctrineUtils;
use nst\member\NurseApplication;
use nst\member\NurseApplicationPartTwo;
use nst\member\NstFile;

class NurseApplicationService
{
    public function getFullApplication(NurseApplication $application)
    {
        /*$applicationPartTwo = ioc::getRepository('NurseApplicationPartTwo')->findOneBy(
            ['member' => $application->getMember()->getId()]
        );*/

        return [
            'part_one' => doctrineUtils::getEntityArray($application),
            //'part_two' => doctrineUtils::getEntityArray($applicationPartTwo),
        ];
    }

    public function approveNurseDeprecated($data)
    {
        $response = ['success' => false];
        /** @var NurseApplication $application */
        $application = ioc::getRepository('NurseApplication')->find($data['id']);
        doctrineUtils::setEntityData($data['data'], $application);

        /** @var NstMember $member */
        $member = $application->getMember();
        if (!$member) {
            $response['message'] = "No Member found with id: " . $data['id'];
            return $response;
        }

        /** @var Nurse $nurse */
        $nurse = $member->getNurse();
        if (!$nurse) {
            $nurse = ioc::resolve('Nurse');
            app::$entityManager->persist($nurse);

            $nurse->setMember($member);
            $member->setNurse($nurse);
        }

        $nurseData = json_decode($application->getNurse(), true);

        $emailInfo['registration_info']['email'] =  $nurseData['email'];
        $directDepositData = json_decode($application->getDirectDeposit(), true);

        // encrypt social security number
        /** @var saUser $user */
        $user = $member->getUsers()[0];
        $cipher = "AES-128-CTR";
        $key = $user->getUserKey();
        $encrypted_ss = openssl_encrypt($nurseData['socialsecurity_number'], $cipher, $key, 0, ord($key));
        // encrypt social security number

        $member->setFirstName($nurseData['first_name']);
        $member->setMiddleName($nurseData['middle_name']);
        $member->setLastName($nurseData['last_name']);
        $member->setMemberType('Nurse');
        $nurse->setFirstName($nurseData['first_name']);
        $nurse->setMiddleName($nurseData['middle_name']);
        $nurse->setLastName($nurseData['last_name']);
        $nurse->setCredentials($nurseData['position']);
        $nurse->setPhoneNumber($nurseData['phone_number']);
        $nurse->setEmailAddress($nurseData['email']);
        $nurse->setStreetAddress($nurseData['street_address']);
        $nurse->setStreetAddress2($nurseData['street_address_two']);
        $nurse->setCity($nurseData['city']);
        $nurse->setState($nurseData['state']);
        $nurse->setZipcode($nurseData['zip_code']);
        $nurse->setSSN($encrypted_ss); // encrypt social security number
        $nurse->setDateOfBirth(new DateTime($nurseData['date_of_birth'], app::getInstance()->getTimeZone()));
        $nurse->setDateOfHire(new DateTime('now', app::getInstance()->getTimeZone()));
        $nurse->setReceivesSMS(false);
        $nurse->setReceivesPushNotification(false);

        if ($directDepositData) {
            $nurse->setPaymentMethod('Direct Deposit');
            $nurse->setAccountNumber($directDepositData['bank_account_number']);
            $nurse->setRoutingNumber($directDepositData['bank_routing_number']);
        }

        app::$entityManager->flush();

        $files = json_decode($application->getFiles(), true);
        if ($files) {
            $file_ids = [];
            $file_tags = [];
            for ($i = 0; $i < count($files); $i++) {
                $file_ids[] = $files[$i]['id'];
                $file_tags[] = [
                    'file_id' => $files[$i]['id'],
                    'tag' => $files[$i]['tag']
                ];
            }

            if ($nurse->getId()) {
                $file_data = [
                    'id' => $nurse->getId(),
                    'file_ids' => $file_ids,
                    'file_tags' => $file_tags
                ];
                SaNstMemberService::saveNurseFiles($file_data);
                $response['success'] = true;
            } else {
                $response['message'] = "No Nurse found!";
            }
        }
        return $response;
    }

    public function approveNurse($data)
    {
        $response = ['success' => false];

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['application_id']]);

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var NurseApplicationStatus $applicationStatus */
        $applicationStatus = $application2->getApplicationStatus();
        $applicationStatus->setApplicationAccepted(new DateTime('now', app::getInstance()->getTimeZone()));
        
        // Create Checkr Pay Account for the nurse
				// We're not currently sending Checkr invites here.
        //$checkrId = $applicationStatus?->getCheckrId();


        /** @var Nurse $nurse */
        $nurse = ioc::resolve('Nurse');
        app::$entityManager->persist($nurse);

        $nurse->setMember($member);
        $member->setNurse($nurse);

        /** @var saUser $user */
        $user = $member->getUsers()[0];

        $state = static::convertToFullStateName($application->getState());
        $dob = static::fixDOB($application->getDOB());

        $member->setMemberType('Nurse');
        $nurse->setFirstName($member->getFirstName());
        $nurse->setMiddleName($member->getMiddleName());
        $nurse->setLastName($member->getLastName());
        $nurse->setCredentials($application->getPosition());
        $nurse->setPhoneNumber($application->getPhoneNumber());
        $nurse->setEmailAddress($user->getUsername());
        $nurse->setPhoneNumber($application->getPhoneNumber());
        $nurse->setStreetAddress($application->getStreetAddress());
        $nurse->setStreetAddress2($application->getStreetAddress2());
        $nurse->setCity($application->getCity());
        $nurse->setState($state);
        $nurse->setZipcode($application->getZipcode());
        $nurse->setSSN($application->getSocSec());
        $nurse->setDateOfBirth(new DateTime($dob, app::getInstance()->getTimeZone()));
        $nurse->setDateOfHire(new DateTime('now', app::getInstance()->getTimeZone()));
        $nurse->setLicenseExpirationDate($application2->getLicense1Expiration());
        $nurse->setReceivesSMS(true);
        $nurse->setReceivesPushNotification(false);

        if ($application2->getPayType() == 'direct_deposit') {

            $nurse->setPaymentMethod('Direct Deposit');
            $nurse->setAccountNumber($application2->getAccountNumber());
            $nurse->setRoutingNumber($application2->getRoutingNumber());
        }

        $files = $application2->getApplicationFiles();
        foreach ($files as $file) {

            $file->setNurse($nurse);
            $nurse->addNurseFile($file);
        }

        $application->setApprovedAt(new DateTime('now', app::getInstance()->getTimeZone()));

        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }

    public static function convertToFullStateName($abbreviatedStateName)
    {
        $stateMap = [

            "AL" => "Alabama",
            "AK" => "Alaska",
            "AZ" => "Arizona",
            "AR" => "Arkansas",
            "CA" => "California",
            "CO" => "Colorado",
            "CT" => "Connecticut",
            "DE" => "Delaware",
            "FL" => "Florida",
            "GA" => "Georgia",
            "HI" => "Hawaii",
            "ID" => "Idaho",
            "IL" => "Illinois",
            "IN" => "Indiana",
            "IA" => "Iowa",
            "KS" => "Kansas",
            "KY" => "Kentucky",
            "LA" => "Louisiana",
            "ME" => "Maine",
            "MD" => "Maryland",
            "MA" => "Massachusetts",
            "MI" => "Michigan",
            "MN" => "Minnesota",
            "MS" => "Mississippi",
            "MO" => "Missouri",
            "MT" => "Montana",
            "NE" => "Nebraska",
            "NV" => "Nevada",
            "NH" => "New Hampshire",
            "NJ" => "New Jersey",
            "NM" => "New Mexico",
            "NY" => "New York",
            "NC" => "North Carolina",
            "ND" => "North Dakota",
            "OH" => "Ohio",
            "OK" => "Oklahoma",
            "OR" => "Oregon",
            "PA" => "Pennsylvania",
            "RI" => "Rhode Island",
            "SC" => "South Carolina",
            "SD" => "South Dakota",
            "TN" => "Tennessee",
            "TX" => "Texas",
            "UT" => "Utah",
            "VT" => "Vermont",
            "VA" => "Virginia",
            "WA" => "Washington",
            "WV" => "West Virginia",
            "WI" => "Wisconsin",
            "WY" => "Wyoming"
        ];

        if (array_key_exists($abbreviatedStateName, $stateMap)) {
            return $stateMap[$abbreviatedStateName];
        } else {
            return "Kentucky";
        }
    }

    public static function fixDOB($dob)
    {
        if (!substr_count($dob, '/') == 2) {

            $dob = str_replace('-', '/', $dob);
            $dob = substr_replace($dob, '/', 2, 0);
            $dob = substr_replace($dob, '/', 5, 0);
        }

        return $dob;
    }

    public function deleteApplicationFile($data)
    {
        /** @var NstFile $fileToDelete */
        $fileToDelete = ioc::getRepository('NstFile')->findOneBy(['id' => $data['id']]);

        if (!$fileToDelete) {
            return false;
        }
        app::$entityManager->remove($fileToDelete);
        app::$entityManager->flush();

        return true;
    }

    public function createLogin($data)
    {
        $emailAlreadyInUse = ioc::getRepository('saMemberUsers')->findOneBy(
            ['username' => $data['username']]
        );
        if ($emailAlreadyInUse !== null) {

            $response['message'] = "Email already in use";
            return $response;

        } else {

            /** @var NstMember $member */
            $data['email'] = $data['username'];
            $data['email2'] = $data['username'];
            $data['password2'] = $data['password'];
            $member = ioc::resolve('saMember')->saveMember($data);

            $auth = auth::getInstance();
            $auth->logon($data['registration_info']['email'], $data['registration_info']['password']);

            /** @var ApplicationPart1 $application */
            $application = new ApplicationPart1();

            app::$entityManager->persist($application);

            $application->setMember($member);
            $member->setApplicationPart1($application);

            /** @var ApplicationPart2 $application2 */
            $application2 = new ApplicationPart2();

            app::$entityManager->persist($application2);

            $application2->setMember($member);
            $member->setApplicationPart2($application2);

            /** @var ApplicationStatus $applicationStatus */
            $applicationStatus = new ApplicationStatus();

            app::$entityManager->persist($applicationStatus);

            $applicationStatus->setApplication2($application2);
            $application2->setApplicationStatus($applicationStatus);
            $applicationStatus->setIsActive(true);
            $applicationStatus->setLastActive(new DateTime('now', app::getInstance()->getTimeZone()));
        }
        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }

    public function loginApplicant($data, $sessionLogin = false)
    {
        /** @var saMemberUsers $memberUser */
        $memberUser = ioc::getRepository('saMemberUsers')->findOneBy(
            ['username' => $data['username']]
        );

        if (!$memberUser) {

            $response['message'] = "No application found with username: " . $data['username'];
            return $response;
        }

        $auth = auth::getInstance();
        if (!$auth->logon($data['username'], $data['password']) && !$sessionLogin) {

            $response['message'] = "Incorrect password";
            return $response;
        }

        /** @var NstMember $member */
        $member = $memberUser->getMember();
        $application = $member->getApplicationPart1();
        Cookie::setCookie('rememberme_applicant', $application->getId(), time() + 1814400, '/nurse-application');

        $response['login'] = [

            'application_id' => $application->getId(),
            'first_name' => $member->getFirstName(),
            'middle_name' => $member?->getMiddleName(),
            'last_name' => $member->getLastName(),
        ];

        return $response;
    }

    public function checkSession()
    {
        try {

            $auth = auth::getInstance();
            if (!$auth) {
                $response['message'] = "Not logged in";
                return $response;
            }

            $applicationId = $_COOKIE['rememberme_applicant'];
            if (!$applicationId) {
                $response['message'] = "Not logged in";
                return $response;
            }

            /** @var ApplicationPart1 $application */
            $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $applicationId]);

            /** @var NstMember $member */
            $member = $application->getMember();
            $response['login'] = [

                'application_id' => $application->getId(),
                'first_name' => $member->getFirstName(),
                'middle_name' => $member?->getMiddleName(),
                'last_name' => $member->getLastName(),
            ];
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function logoutApplicant()
    {
        Cookie::setCookie('rememberme_applicant', false, -1, '/nurse-application');
        $response['success'] = true;
        return $response;
    }

    public function saveApplicationProgress($data)
    {
        $response['success'] = false;

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['application_id']]);

        if (!$application) {
            $response['message'] = "No application found with id: " . $data['application_id'];
            return $response;
        }

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var NurseApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var ApplicationStatus $applicationStatus */
        $applicationStatus = $application2->getApplicationStatus();

        /** @var saMemberUsers $memberUser */
        $memberUser = $member->getUsers()[0];

        $pageNum = (int)$data['pageNum'];
        switch ($pageNum) {

            case 1:

                // encrypt social security number
                $cipher = "AES-128-CTR";
                $key = $memberUser->getUserKey();
                $encrypted_ss = openssl_encrypt($data['page1Data']['social_security_number'], $cipher, $key, 0, ord($key));
                // encrypt social security number

                $application->setPhoneNumber($data['page1Data']['phone']);
                $application->setStreetAddress($data['page1Data']['street_address']);
                $application->setStreetAddress2($data['page1Data']['street_address_line2']);
                $application->setCity($data['page1Data']['city']);
                $application->setState($data['page1Data']['state']);
                $application->setZipcode($data['page1Data']['zipcode']);

                $application->setDOB($data['page1Data']['dob']);
                $application->setPosition($data['page1Data']['position']);
                $application->setExplanation($data['page1Data']['explanation']);
                $application->setIsCitizen($data['page1Data']['citizen_of_the_us']);
                $application->setIsAllowedToWork($data['page1Data']['allowed_to_work']);

                if ($data['page1Data']['soc_sec_saved'] === 'false') { $application->setSocSec($encrypted_ss); }

                $firstAndLastName = $member->getFirstName() . ' ' . $member->getLastName();
                $message = "Hi $firstAndLastName!\nI'm in Human Resources here at NurseStat, and I'm excited to hear that you're considering applying to join the NurseStat team! 
                At NurseStat we're passionate about helping medical professionals like yourself find the jobs they love. If you're interested in learning more about NurseStat and 
                the open positions we have, please visit our website. You can also contact us directly (call or text) at 859-748-9600 or by email at apply@nursestat.com.";
                $smsData = [

                    'application_id' => $data['application_id'],
                    'message' => $message,
                ];

                $response['sms_attempt'] = SmsService::sendApplicantSMS($smsData);

                break;

            case 2:

                $application->setOneYearLTCExperience($data['page2Data']['one_year_ltc_experience']);
                $application->setOneYearExplanation($data['page2Data']['one_year_experience_explanation']);
                $application->setCurrentlyEmployed($data['page2Data']['currently_employed']);

                $application->setCompany1CompanyName($data['page2Data']['company1']['company_name']);
                $application->setCompany1SupervisorName($data['page2Data']['company1']['supervisor_name']);
                $application->setCompany1CompanyAddress($data['page2Data']['company1']['company_address']);
                $application->setCompany1CompanyCity($data['page2Data']['company1']['company_city']);
                $application->setCompany1CompanyState($data['page2Data']['company1']['company_state']);
                $application->setCompany1CompanyZip($data['page2Data']['company1']['company_zip']);
                $application->setCompany1CompanyPhone($data['page2Data']['company1']['company_phone']);
                $application->setCompany1CompanyEmail($data['page2Data']['company1']['email']);
                $application->setCompany1JobTitle($data['page2Data']['company1']['job_title']);
                $application->setCompany1StartDate($data['page2Data']['company1']['start_date']);
                $application->setCompany1EndDate($data['page2Data']['company1']['end_date']);
                $application->setCompany1Responsibilites($data['page2Data']['company1']['responsibilities']);
                $application->setCompany1ReasonForLeaving($data['page2Data']['company1']['reason_for_leaving']);
                $application->setCompany1MayWeContactEmployer($data['page2Data']['company1']['may_we_contact_employer']);

                $application->setCompany2CompanyName($data['page2Data']['company2']['company_name']);
                $application->setCompany2SupervisorName($data['page2Data']['company2']['supervisor_name']);
                $application->setCompany2CompanyAddress($data['page2Data']['company2']['company_address']);
                $application->setCompany2CompanyCity($data['page2Data']['company2']['company_city']);
                $application->setCompany2CompanyState($data['page2Data']['company2']['company_state']);
                $application->setCompany2CompanyZip($data['page2Data']['company2']['company_zip']);
                $application->setCompany2CompanyPhone($data['page2Data']['company2']['company_phone']);
                $application->setCompany2CompanyEmail($data['page2Data']['company2']['email']);
                $application->setCompany2JobTitle($data['page2Data']['company2']['job_title']);
                $application->setCompany2StartDate($data['page2Data']['company2']['start_date']);
                $application->setCompany2EndDate($data['page2Data']['company2']['end_date']);
                $application->setCompany2Responsibilites($data['page2Data']['company2']['responsibilities']);
                $application->setCompany2ReasonForLeaving($data['page2Data']['company2']['reason_for_leaving']);
                $application->setCompany2MayWeContactEmployer($data['page2Data']['company2']['may_we_contact_employer']);

                $application->setCompany3CompanyName($data['page2Data']['company3']['company_name']);
                $application->setCompany3SupervisorName($data['page2Data']['company3']['supervisor_name']);
                $application->setCompany3CompanyAddress($data['page2Data']['company3']['company_address']);
                $application->setCompany3CompanyCity($data['page2Data']['company3']['company_city']);
                $application->setCompany3CompanyState($data['page2Data']['company3']['company_state']);
                $application->setCompany3CompanyZip($data['page2Data']['company3']['company_zip']);
                $application->setCompany3CompanyPhone($data['page2Data']['company3']['company_phone']);
                $application->setCompany3CompanyEmail($data['page2Data']['company3']['email']);
                $application->setCompany3JobTitle($data['page2Data']['company3']['job_title']);
                $application->setCompany3StartDate($data['page2Data']['company3']['start_date']);
                $application->setCompany3EndDate($data['page2Data']['company3']['end_date']);
                $application->setCompany3Responsibilites($data['page2Data']['company3']['responsibilities']);
                $application->setCompany3ReasonForLeaving($data['page2Data']['company3']['reason_for_leaving']);
                $application->setCompany3MayWeContactEmployer($data['page2Data']['company3']['may_we_contact_employer']);

                break;
            case 3:

                $application->setHSorGED($data['page3Data']['hs_or_ged']);

                $application->setCollegeName($data['page3Data']['college']['name']);
                $application->setCollegeCity($data['page3Data']['college']['city']);
                $application->setCollegeState($data['page3Data']['college']['state']);
                $application->setCollegeGraduated($data['page3Data']['college']['year_graduated']);
                $application->setCollegeSubjects($data['page3Data']['college']['subjects_major_degree']);

                $application->setGEDName($data['page3Data']['ged']['name']);
                $application->setGEDCity($data['page3Data']['ged']['city']);
                $application->setGEDState($data['page3Data']['ged']['state']);
                $application->setGEDYearGraduated($data['page3Data']['ged']['year_graduated']);

                $application->setHSName($data['page3Data']['high_school']['name']);
                $application->setHSCity($data['page3Data']['high_school']['city']);
                $application->setHSState($data['page3Data']['high_school']['state']);
                $application->setHSYearGraduated($data['page3Data']['high_school']['year_graduated']);

                $application->setOtherEducationName($data['page3Data']['other']['name']);
                $application->setOtherEducationCity($data['page3Data']['other']['city']);
                $application->setOtherEducationState($data['page3Data']['other']['state']);
                $application->setOtherEducationYearGraduated($data['page3Data']['other']['year_graduated']);
                $application->setOtherEducationSubjects($data['page3Data']['other']['subjects_major_degree']);

                break;
            case 4:

                $application->setProfessionalReferenceOneName($data['page4Data']['reference1']['name']);
                $application->setProfessionalReferenceOneRelationship($data['page4Data']['reference1']['relationship']);
                $application->setProfessionalReferenceOneCompany($data['page4Data']['reference1']['company']);
                $application->setProfessionalReferenceOnePhone($data['page4Data']['reference1']['phone_number']);

                $application->setProfessionalReferenceTwoName($data['page4Data']['reference2']['name']);
                $application->setProfessionalReferenceTwoRelationship($data['page4Data']['reference2']['relationship']);
                $application->setProfessionalReferenceTwoCompany($data['page4Data']['reference2']['company']);
                $application->setProfessionalReferenceTwoPhone($data['page4Data']['reference2']['phone_number']);

                $application->setProfessionalReferenceThreeName($data['page4Data']['reference3']['name']);
                $application->setProfessionalReferenceThreeRelationship($data['page4Data']['reference3']['relationship']);
                $application->setProfessionalReferenceThreeCompany($data['page4Data']['reference3']['company']);
                $application->setProfessionalReferenceThreePhone($data['page4Data']['reference3']['phone_number']);

                $licensesAndCerts = [];
                foreach ($data['page4Data']['license_and_certifications'] as $license => $hasLicense) {

                    if ($hasLicense === 'true') {
                        array_push($licensesAndCerts, $license);
                    }
                }
                $application->setLicenseAndCertifications($licensesAndCerts);

                break;
            case 5:

                $application2->setTermsSignature($data['page5Data']['signature']);
                if ($data['page5Data']['timestamp'] == 'update') {
                    $application2->setTermsDate(new DateTime('now', app::getInstance()->getTimeZone()));
                }
                $application2->setTermsIpAddress($data['page5Data']['ip']);

                break;
            case 6:

                $medicalHistory = [];
                foreach ($data['page6Data']['medical_history'] as $condition => $hasCondition) {

                    if ($hasCondition === '1') {
                        array_push($medicalHistory, $condition);
                    }
                }
                $application2->setMedicalHistory($medicalHistory);

                $application2->setInjuryExplanation($data['page6Data']['injury_explanation']);
                $application2->setRoutineVaccinations($data['page6Data']['routine_vaccinations']);
                $application2->setHepatitisBVaccination($data['page6Data']['hepatitis_b']);
                $application2->setHepatitisAVaccination($data['page6Data']['hepatitis_a']);
                $application2->setCovidVaccination($data['page6Data']['covid_19']);
                $application2->setCovidVaccinationExemption($data['page6Data']['covid_19_exemption']);
                $application2->setPreviousPositiveTBScreening($data['page6Data']['positive_tb_screening']);
                $application2->setPositiveTBDate($data['page6Data']['positive_tb_date']);
                $application2->setHadChestXRay($data['page6Data']['xray']);
                $application2->setChestXRayDate($data['page6Data']['xray_date']);
                $application2->setMedicalHistorySignature($data['page6Data']['signature']);

                break;
            case 7:

                $application2->setPayType($data['page7Data']['pay_type']);
                $application2->setAccountType($data['page7Data']['account_type']);
                $application2->setAccountNumber($data['page7Data']['account_number']);
                $application2->setRoutingNumber($data['page7Data']['routing_number']);
                $application2->setBankName($data['page7Data']['bank_name']);

                $application2->setHeardAboutUs($data['page7Data']['heard_about_us']);
                $application2->setHeardAboutUsOther($data['page7Data']['heard_about_us_other']);
                $application2->setReferrer($data['page7Data']['referrer']);

                $applicationStatus->setApplicationSubmitted(true);
                $applicationStatus->setLastActive(new DateTime('now', app::getInstance()->getTimeZone()));

                break;
        }

        app::$entityManager->flush();

        if ($data['progressPage'] == false || $data['progressPage'] == 'false') {

            $firstAndLastName = $member->getFirstName() . ' ' . $member->getLastName();
            $message = "Hi $firstAndLastName!\n I noticed that you started your application to join the team here at NurseStat, but haven't had a chance to finish it yet.
                I wanted to follow up and remind you that we're still accepting applications and we'd love to have you join our team! Please reach out anytime should you 
                have any questions! Call/text 859-748-9600.";
            $smsData = [

                'application_id' => $data['application_id'],
                'message' => $message,
            ];

            $response['sms_attempt'] = SmsService::sendApplicantSMS($smsData);
        }

        $response['data'] = $data;
        $response['success'] = true;
        return $response;
    }

    public function loadApplicationProgress($data)
    {
        $response['success'] = false;

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['application_id']]);

        if (!$application) {
            $response['message'] = "No application found with id: " . $data['application_id'];
            return $response;
        }

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var NurseApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var saMemberUsers $memberUser */
        $memberUser = $member->getUsers()[0];

        $return['application']['completed'] = false;
        $return['application']['show'] = true;

        $socSecSaved = false;
        if ($application->getSocSec()) { $socSecSaved = true; }

        $return['app_step'] = 0;
        $return['application']['show'] = true;
        $return['application']['completed'] = false;
        $return['application']['application_id'] = $data['application_id'];

        $return['application']['page1Data'] = [

            'street_address' => $application->getStreetAddress(),
            'street_address_line2' => $application->getStreetAddress2(),
            'city' => $application->getCity(),
            'state' => $application->getState(),
            'zipcode' => $application->getZipcode(),
            'dob' => $application->getDOB(),
            'phone' => $application->getPhoneNumber(),
            'position' => $application->getPosition(),
            'explanation' => $application->getExplanation(),
            'citizen_of_the_us' => (string) $application->getIsCitizen(),
            'allowed_to_work' => (string) $application->getIsAllowedToWork(),
            'social_security_number' => '',
            'soc_sec_saved' => $socSecSaved,
        ];

        if (   $return['application']['page1Data']['street_address']
            && $return['application']['page1Data']['city']
            && $return['application']['page1Data']['state']
            && $return['application']['page1Data']['zipcode']
            && $return['application']['page1Data']['dob']
            && $return['application']['page1Data']['phone']
            && $return['application']['page1Data']['position']
            && $socSecSaved
        ) { $return['app_step'] = 1; }

        $return['application']['page2Data'] = [

            'one_year_ltc_experience' => (string) $application->getOneYearLTCExperience(),
            'one_year_experience_explanation' => $application->getOneYearExplanation(),
            'currently_employed' => (string) $application->getCurrentlyEmployed(),

            'company1' => [

                'show' => true,
                'company_name' => $application->getCompany1CompanyName(),
                'supervisor_name' => $application->getCompany1SupervisorName(),
                'company_address' => $application->getCompany1CompanyAddress(),
                'company_city' => $application->getCompany1CompanyCity(),
                'company_state' => $application->getCompany1CompanyState(),
                'company_zip' => $application->getCompany1CompanyZip(),
                'company_phone' => $application->getCompany1CompanyPhone(),
                'email' => $application->getCompany1CompanyEmail(),
                'job_title' => $application->getCompany1JobTitle(),
                'start_date' => $application->getCompany1StartDate(),
                'end_date' => $application->getCompany1EndDate(),
                'responsibilities' => $application->getCompany1Responsibilites(),
                'reason_for_leaving' => $application->getCompany1ReasonForLeaving(),
                'may_we_contact_employer' => (string) $application->getCompany1MayWeContactEmployer(),
            ],

            'company2' => [

                'show' => false,
                'company_name' => $application->getCompany2CompanyName(),
                'supervisor_name' => $application->getCompany2SupervisorName(),
                'company_address' => $application->getCompany2CompanyAddress(),
                'company_city' => $application->getCompany2CompanyCity(),
                'company_state' => $application->getCompany2CompanyState(),
                'company_zip' => $application->getCompany2CompanyZip(),
                'company_phone' => $application->getCompany2CompanyPhone(),
                'email' => $application->getCompany2CompanyEmail(),
                'job_title' => $application->getCompany2JobTitle(),
                'start_date' => $application->getCompany2StartDate(),
                'end_date' => $application->getCompany2EndDate(),
                'responsibilities' => $application->getCompany2Responsibilites(),
                'reason_for_leaving' => $application->getCompany2ReasonForLeaving(),
                'may_we_contact_employer' => (string) $application->getCompany2MayWeContactEmployer(),
            ],

            'company3' => [

                'show' => false,
                'company_name' => $application->getCompany3CompanyName(),
                'supervisor_name' => $application->getCompany3SupervisorName(),
                'company_address' => $application->getCompany3CompanyAddress(),
                'company_city' => $application->getCompany3CompanyCity(),
                'company_state' => $application->getCompany3CompanyState(),
                'company_zip' => $application->getCompany3CompanyZip(),
                'company_phone' => $application->getCompany3CompanyPhone(),
                'email' => $application->getCompany3CompanyEmail(),
                'job_title' => $application->getCompany3JobTitle(),
                'start_date' => $application->getCompany3StartDate(),
                'end_date' => $application->getCompany3EndDate(),
                'responsibilities' => $application->getCompany3Responsibilites(),
                'reason_for_leaving' => $application->getCompany3ReasonForLeaving(),
                'may_we_contact_employer' => (string) $application->getCompany3MayWeContactEmployer(),
            ],
        ];

        if (   $return['application']['page2Data']['one_year_ltc_experience'] === null
            || $return['application']['page2Data']['one_year_ltc_experience'] === ""
        ) { $return['application']['page2Data']['one_year_ltc_experience'] = '0'; }
        if (   $return['application']['page2Data']['currently_employed'] === null
            || $return['application']['page2Data']['currently_employed'] === ""
        ) { $return['application']['page2Data']['currently_employed'] = '0'; }
        if (   $return['application']['page2Data']['company1']['may_we_contact_employer'] === null
            || $return['application']['page2Data']['company1']['may_we_contact_employer'] === ""
        ) { $return['application']['page2Data']['company1']['may_we_contact_employer'] = '1'; }
        if (   $return['application']['page2Data']['company2']['may_we_contact_employer'] === null
            || $return['application']['page2Data']['company2']['may_we_contact_employer'] === ""
        ) { $return['application']['page2Data']['company2']['may_we_contact_employer'] = '1'; }
        if (   $return['application']['page2Data']['company3']['may_we_contact_employer'] === null
            || $return['application']['page2Data']['company3']['may_we_contact_employer'] === ""
        ) { $return['application']['page2Data']['company3']['may_we_contact_employer'] = '1'; }

        if (   $return['application']['page2Data']['one_year_ltc_experience']
            && $return['application']['page2Data']['currently_employed']
            && $return['application']['page2Data']['company1']['company_name']
            && $return['application']['page2Data']['company1']['supervisor_name']
            && $return['application']['page2Data']['company1']['company_address']
            && $return['application']['page2Data']['company1']['company_city']
            && $return['application']['page2Data']['company1']['company_state']
            && $return['application']['page2Data']['company1']['company_zip']
            && $return['application']['page2Data']['company1']['company_phone']
            && $return['application']['page2Data']['company1']['email']
            && $return['application']['page2Data']['company1']['job_title']
            && $return['application']['page2Data']['company1']['start_date']
            // && $return['application']['page2Data']['company1']['end_date']
            && $return['application']['page2Data']['company1']['responsibilities']
            // && $return['application']['page2Data']['company1']['reason_for_leaving']
            && $return['application']['page2Data']['company1']['may_we_contact_employer']
        ) {
            if (  $return['application']['page2Data']['currently_employed'] != '0'
               && $return['application']['page2Data']['currently_employed'] != ''
               && $return['application']['page2Data']['currently_employed'] != null
               ) { $return['app_step'] = 2; }
            else {

                if (   $return['application']['page2Data']['company1']['end_date'] !== null
                    && $return['application']['page2Data']['company1']['end_date'] !== ''
                    && $return['application']['page2Data']['company1']['reason_for_leaving']
                    && $return['application']['page2Data']['company1']['may_we_contact_employer']
                ) { $return['app_step'] = 2; }
            }
        }

        $return['application']['page3Data'] = [

            'hs_or_ged' => $application->getHSorGED(),

            'college' => [

                'show' => false,
                'name' => $application->getCollegeName(),
                'city' => $application->getCollegeCity(),
                'state' => $application->getCollegeState(),
                'year_graduated' => $application->getCollegeGraduated(),
                'subjects_major_degree' => $application->getCollegeSubjects(),
            ],

            'ged' => [

                'show' => true,
                'name' => $application->getGEDName(),
                'city' => $application->getGEDCity(),
                'state' => $application->getGEDState(),
                'year_graduated' => $application->getGEDYearGraduated(),
            ],

            'high_school' => [

                'show' => true,
                'name' => $application->getHSName(),
                'city' => $application->getHSCity(),
                'state' => $application->getHSState(),
                'year_graduated' => $application->getHSYearGraduated(),
            ],

            'other' => [

                'show' => false,
                'name' => $application->getOtherEducationName(),
                'city' => $application->getOtherEducationCity(),
                'state' => $application->getOtherEducationState(),
                'year_graduated' => $application->getOtherEducationYearGraduated(),
                'subjects_major_degree' => $application->getOtherEducationSubjects(),
            ],
        ];

        if (   $return['application']['page3Data']['hs_or_ged']
            && $return['application']['page3Data']['high_school']['name']
            && $return['application']['page3Data']['high_school']['city']
            && $return['application']['page3Data']['high_school']['state']
            && $return['application']['page3Data']['high_school']['year_graduated']
        ) { $return['app_step'] = 3; }

        $licensesAndCerts = [];
        $allAttributes = [

            'rn_long_term_care',
            'rn_hospital',
            'rn_home_health',
            'rn_hospice',
            'rn_homecare_sitter',
            'lpn_long_term_care',
            'lpn_hospital',
            'lpn_home_health',
            'lpn_hospice',
            'lpn_homecare_sitter',
            'ckc_long_term_care',
            'ckc_hospital',
            'ckc_home_health',
            'ckc_hospice',
            'ckc_homecare_sitter',
            'cna_long_term_care',
            'cna_hospital',
            'cna_home_health',
            'cna_hospice',
            'cna_homecare_sitter',
            'sitter_long_term_care',
            'sitter_hospital',
            'sitter_home_health',
            'sitter_hospice',
            'sitter_homecare_sitter',
        ];
        foreach ($allAttributes as $attribute) {
            $licensesAndCerts[$attribute] = false;
        }
        foreach ($application->getLicenseAndCertifications() as $attribute) {
            $licensesAndCerts[$attribute] = true;
        }

        $return['application']['page4Data'] = [

            'reference1' => [

                'show' => true,
                'name' => $application->getProfessionalReferenceOneName(),
                'relationship' => $application->getProfessionalReferenceOneRelationship(),
                'company' => $application->getProfessionalReferenceOneCompany(),
                'phone_number' => $application->getProfessionalReferenceOnePhone(),
            ],

            'reference2' => [

                'show' => false,
                'name' => $application->getProfessionalReferenceTwoName(),
                'relationship' => $application->getProfessionalReferenceTwoRelationship(),
                'company' => $application->getProfessionalReferenceTwoCompany(),
                'phone_number' => $application->getProfessionalReferenceTwoPhone(),
            ],

            'reference3' => [

                'show' => false,
                'name' => $application->getProfessionalReferenceThreeName(),
                'relationship' => $application->getProfessionalReferenceThreeRelationship(),
                'company' => $application->getProfessionalReferenceThreeCompany(),
                'phone_number' => $application->getProfessionalReferenceThreePhone(),
            ],

            'license_and_certifications' => $licensesAndCerts,
        ];

        if ( $return['application']['page4Data']['reference1']['name'] ) { $return['app_step'] = 4; }

        $return['application']['page5Data'] = [

            'signature' => $application2->getTermsSignature(),
            'timestamp' => $application2->getTermsDate(),
            'ip' => $application2->getTermsIpAddress(),
        ];

        if ($return['application']['page5Data']['signature']) {
            $return['app_step'] = 5;
        }

        $medicalHistory = [];
        $medicalHistoryItems = [

            'anemia',
            'smallpox',
            'diabetes',
            'diptheria',
            'epilepsy',
            'heart_disease',
            'kidney_trouble',
            'mononucleosis',
            'scarlet_fever',
            'typhoid',
            'hypertension',
            'latex_allergy',
            'hernia',
            'depression',
            'measles',
            'hepatitis',
            'mumps',
            'pleurisy',
            'pneumonia',
            'chicken_pox',
            'emphysema',
            'tuberculosis',
            'whopping_cough',
            'rheumatic_fever',
            'carpal_tunnel',
            'sight_hearing_problems',
            'color_blindness',
        ];
        foreach ($medicalHistoryItems as $attribute) {
            $medicalHistory[$attribute] = null;
        }
        foreach ($application2->getMedicalHistory() as $attribute) {
            $medicalHistory[$attribute] = "1";
        }

        $return['application']['page6Data'] = [

            'medical_history_show' => true,
            'injury_history_show' => true,
            'vaccination_history_show' => true,
            'tuberculosis_screening_show' => true,

            'medical_history' => $medicalHistory,

            'injury_explanation' => $application2->getInjuryExplanation(),
            'routine_vaccinations' => $application2->getRoutineVaccinations(),
            'hepatitis_b' => $application2->getHepatitisBVaccination(),
            'hepatitis_a' => $application2->getHepatitisAVaccination(),
            'covid_19' => $application2->getCovidVaccination(),
            'covid_19_exemption' => $application2->getCovidVaccinationExemption(),
            'positive_tb_screening' => $application2->getPreviousPositiveTBScreening(),
            'positive_tb_date' => $application2->getPositiveTBDate(),
            'xray' => $application2->getHadChestXRay(),
            'xray_date' => $application2->getChestXRayDate(),
            'signature' => $application2->getMedicalHistorySignature(),
        ];

        if ($return['application']['page6Data']['routine_vaccinations'] === true) {
            $return['application']['page6Data']['routine_vaccinations'] = '1';
        } else if ($return['application']['page6Data']['routine_vaccinations'] === false) {
            $return['application']['page6Data']['routine_vaccinations'] = '0';
        }
        if ($return['application']['page6Data']['hepatitis_b'] === true) {
            $return['application']['page6Data']['hepatitis_b'] = '1';
        } else if ($return['application']['page6Data']['hepatitis_b'] === false) {
            $return['application']['page6Data']['hepatitis_b'] = '0';
        }
        if ($return['application']['page6Data']['hepatitis_a'] === true) {
            $return['application']['page6Data']['hepatitis_a'] = '1';
        } else if ($return['application']['page6Data']['hepatitis_a'] === false) {
            $return['application']['page6Data']['hepatitis_a'] = '0';
        }
        if ($return['application']['page6Data']['covid_19'] === true) {
            $return['application']['page6Data']['covid_19'] = '1';
        } else if ($return['application']['page6Data']['covid_19'] === false) {
            $return['application']['page6Data']['covid_19'] = '0';
        }
        if ($return['application']['page6Data']['covid_19_exemption'] === true) {
            $return['application']['page6Data']['covid_19_exemption'] = '1';
        } else if ($return['application']['page6Data']['covid_19_exemption'] === false) {
            $return['application']['page6Data']['covid_19_exemption'] = '0';
        }
        if ($return['application']['page6Data']['positive_tb_screening'] === true) {
            $return['application']['page6Data']['positive_tb_screening'] = '1';
        } else if ($return['application']['page6Data']['positive_tb_screening'] === false) {
            $return['application']['page6Data']['positive_tb_screening'] = '0';
        }
        if ($return['application']['page6Data']['xray'] === true) {
            $return['application']['page6Data']['xray'] = '1';
        } else if ($return['application']['page6Data']['xray'] === false) {
            $return['application']['page6Data']['xray'] = '0';
        }

        if ( $return['application']['page6Data']['signature'] ) { $return['app_step'] = 6; }

        $return['application']['page7Data'] = [

            'pay_type' => $application2->getPayType(),
            'account_type' => $application2->getAccountType(),
            'account_number' => $application2->getAccountNumber(),
            'routing_number' => $application2->getRoutingNumber(),
            'bank_name' => $application2->getBankName(),

            'heard_about_us' => $application2->getHeardAboutUs(),
            'heard_about_us_other' => $application2->getHeardAboutUsOther(),
            'referrer' => $application2->getReferrer(),

            'license1' => [

                'state' => $application2->getLicense1State(),
                'license_number' => $application2->getLicense1Number(),
                'full_name' => $application2->getLicense1FullName(),
            ],

            'license2' => [

                'state' => $application2->getLicense2State(),
                'license_number' => $application2->getLicense2Number(),
                'full_name' => $application2->getLicense2FullName(),
            ],

            'license3' => [

                'state' => $application2->getLicense3State(),
                'license_number' => $application2->getLicense3Number(),
                'full_name' => $application2->getLicense3FullName(),
            ],
        ];

        if (   $return['application']['page7Data']['pay_type']
            && $return['application']['page7Data']['heard_about_us']
        ) {
            $return['app_step'] = 7;
        }

        if ($return['app_step'] === 7) {

            $return['application']['completed'] = true;
            $return['application']['show'] = false;
        }

        $return['success'] = true;
        return $return;
    }

    public static function loadApplications()
    {
        $response['success'] = false;
        $applicants = ioc::getRepository('ApplicationPart1')->manageApplications();

        $nurseApplicationService = new NurseApplicationService();

        foreach ($applicants as $applicant) {

            if ($applicant['is_active'] && $applicant['license_verified'] && $applicant['background_screen_status'] != 'completed') {

                if ($applicant['drug_screen_status'] == 'pending' || ($applicant['drug_screen_status'] == null && $applicant['license_verified']) || ($applicant['drug_screen_status'] == '' && $applicant['license_verified'])) {

                    //$nurseApplicationService->loadDrugScreenProgress($applicant);

                } else {

                    // check if background check should be done if it hasn't been initiated yet
                    if (!$applicant['background_check_status'] && !$applicant['drug_screen_accepted'] && $applicant['drug_screen_report']['result'] != 'clear') {
                        
                        continue;

                    } else {

                        // check if drug screen report is current and update if not
                        if ($applicant['drug_screen_report']['status'] == 'pending') {
                            //$nurseApplicationService->loadDrugScreenProgress($applicant);
                        }

                        //$nurseApplicationService->loadBackgroundCheckProgress($applicant);
                    }
                }
            }
        }
        
        // get applicants again to get updated info
        $applicants = ioc::getRepository('ApplicationPart1')->manageApplications();

        foreach ($applicants as $applicant) {

            $response['applicants'][] = [

                'applicant_id' => $applicant['applicant_id'],
                'member_id' => $applicant['member_id'],
                'first_name' => $applicant['first_name'],
                'last_name' => $applicant['last_name'],
                'email' => $applicant['username'],
                'phone' => $applicant['phone_number'],
                'city' => $applicant['city'],
		'state' => $applicant['state'],

                'submitted_at' => $applicant['submitted_at']?->format('m/d/y g:i'),
                'approved_at' => $applicant['approved_at']?->format('m/d/y g:i'),
                'declined_at' => $applicant['declined_at']?->format('m/d/y g:i'),

                'is_active' => $applicant['is_active'],
                'last_active' => $applicant['last_active']?->format('m/d/y g:i'),
                'application_submitted' => $applicant['application_submitted'],

                'license_submitted' => $applicant['license_submitted'],
                'license_verified' => $applicant['license_verified'],

                'files_submitted' => $applicant['files_submitted'],

                'drug_screen_status' => $applicant['drug_screen_status'],
                'drug_screen_accepted' => $applicant['drug_screen_accepted'],
                'drug_screen_report' => $applicant['drug_screen_report'],

                'background_check_started_date' => $applicant['background_check_started_date']?->format('m/d/y g:i'),
                'background_check_status' => $applicant['background_check_status'],
                'background_check_accepted' => $applicant['background_check_accepted'],

                'completed_at' => $applicant['background_check_signed_time']?->format('m/d/y g:i'),

                'link' => app::get()->getRouter()->generate('nurse_applications_view', ['application' => $applicant['applicant_id']]),
            ];
        }

        $response['success'] = true;
        return $response;
    }

    public static function saveFilesProgress($data)
    {
        $response['success'] = false;

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['application_id']]);

        if (!$application) {
            $response['message'] = "No application found with id: " . $data['application_id'];
            return $response;
        }

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var ApplicationStatus $applicationStatus */
        $applicationStatus = $application2->getApplicationStatus();

        /** @var saMemberUsers $memberUser */
        $memberUser = $member->getUsers()[0];

        /** @var NstFileTagRepository $fileTagRepo */
        $fileTagRepo = ioc::getRepository('NstFileTag');

        // NURSE LICENSES
        /** @var NstFileTag $nurseLicense1FileTag */
        $nurseLicense1FileTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => 'Nurse License 1']);
        if (!$nurseLicense1FileTag) {

            $nurseLicense1FileTag = $fileTagRepo->createNewTagByName('Nurse License 1', 'Nurse License 1', 'Nurse', false);
            app::$entityManager->persist($nurseLicense1FileTag);
        }
        $nurseLicense2FileTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => 'Nurse License 2']);
        if (!$nurseLicense2FileTag) {

            $nurseLicense2FileTag = $fileTagRepo->createNewTagByName('Nurse License 2', 'Nurse License 2', 'Nurse', false);
            app::$entityManager->persist($nurseLicense2FileTag);
        }
        $nurseLicense3FileTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => 'Nurse License 3']);
        if (!$nurseLicense3FileTag) {

            $nurseLicense3FileTag = $fileTagRepo->createNewTagByName('Nurse License 3', 'Nurse License 3', 'Nurse', false);
            app::$entityManager->persist($nurseLicense3FileTag);
        }

        $applicationFiles = $application2?->getApplicationFiles();
        $savedFiles = [];
        try {

            if (count($applicationFiles) > 0) {

                foreach ($applicationFiles as $file) {

                    $savedFile['tag'] = $file->getTag()->getName();
                    $savedFile['id'] = $file->getId();
                    array_push($savedFiles, $savedFile);
                }
            }
        } catch (\Throwable $e) {
            $savedFiles = [];
        }

        $savedNurseLicense1 = array_values(array_filter($savedFiles, function($file) {
            return $file['tag'] == 'Nurse License 1';
        }));
        $savedNurseLicense1 = $savedNurseLicense1[0];
        if ($savedNurseLicense1) {

            if ($data['files']['nursing_license_1']['id'] && $savedNurseLicense1['id'] != $data['files']['nursing_license_1']['id']) {

                // get new file
                $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['nursing_license_1']['id']]);
                // set tag
                $newFile->setTag($nurseLicense1FileTag);
                // remove old file
                foreach ($applicationFiles as $file) {

                    if ($file->getId() == $savedNurseLicense1['id']) {

                        $application2->removeApplicationFile($file);
                        $file->setNurseApplicationPartTwo(null);
                        app::$entityManager->remove($file);
                    }
                }
                // add new file
                $application2->addApplicationFile($newFile);
                $newFile->setNurseApplicationPartTwo($application2);
                $application2->setLicense1Expiration(null);
                $application2->setLicense1Accepted(null);
            }
        } else if ($data['files']['nursing_license_1']['id']) {

            // get new file
            $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['nursing_license_1']['id']]);
            // set tag
            $newFile->setTag($nurseLicense1FileTag);
            // add new file
            $application2->addApplicationFile($newFile);
            $newFile->setNurseApplicationPartTwo($application2);
            $application2->setLicense1Expiration(null);
            $application2->setLicense1Accepted(null);
        }
        $application2->setLicense1State($data['files']['nursing_license_1']['state']);
        $application2->setLicense1Number($data['files']['nursing_license_1']['license_number']);
        $application2->setLicense1FullName($data['files']['nursing_license_1']['full_name']);

        $savedNurseLicense2 = array_values(array_filter($savedFiles, function($file) {
            return $file['tag'] == 'Nurse License 2';
        }));
        $savedNurseLicense2 = $savedNurseLicense2[0];
        if ($savedNurseLicense2) {

            if ($data['files']['nursing_license_2']['id'] && $savedNurseLicense2['id'] != $data['files']['nursing_license_2']['id']) {

                // get new file
                $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['nursing_license_2']['id']]);
                // set tag
                $newFile->setTag($nurseLicense2FileTag);
                // remove old file
                foreach ($applicationFiles as $file) {

                    if ($file->getId() == $savedNurseLicense2['id']) {

                        $application2->removeApplicationFile($file);
                        $file->setNurseApplicationPartTwo(null);
                        app::$entityManager->remove($file);
                    }
                }
                // add new file
                $application2->addApplicationFile($newFile);
                $newFile->setNurseApplicationPartTwo($application2);
                $application2->setLicense2Expiration(null);
                $application2->setLicense2Accepted(null);
            }
        } else if ($data['files']['nursing_license_2']['id']) {

            // get new file
            $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['nursing_license_2']['id']]);
            // set tag
            $newFile->setTag($nurseLicense2FileTag);
            // add new file
            $application2->addApplicationFile($newFile);
            $newFile->setNurseApplicationPartTwo($application2);
            $application2->setLicense2Expiration(null);
            $application2->setLicense2Accepted(null);
        }
        $application2->setLicense2State($data['files']['nursing_license_2']['state']);
        $application2->setLicense2Number($data['files']['nursing_license_2']['license_number']);
        $application2->setLicense2FullName($data['files']['nursing_license_2']['full_name']);

        $savedNurseLicense3 = array_values(array_filter($savedFiles, function($file) {
            return $file['tag'] == 'Nurse License 3';
        }));
        $savedNurseLicense3 = $savedNurseLicense3[0];
        if ($savedNurseLicense3) {

            if ($data['files']['nursing_license_3']['id'] && $savedNurseLicense3['id'] != $data['files']['nursing_license_3']['id']) {

                // get new file
                $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['nursing_license_3']['id']]);
                // set tag
                $newFile->setTag($nurseLicense3FileTag);
                // remove old file
                foreach ($applicationFiles as $file) {

                    if ($file->getId() == $savedNurseLicense3['id']) {

                        $application2->removeApplicationFile($file);
                        $file->setNurseApplicationPartTwo(null);
                        app::$entityManager->remove($file);
                    }
                }
                // add new file
                $application2->addApplicationFile($newFile);
                $newFile->setNurseApplicationPartTwo($application2);
                $application2->setLicense3Expiration(null);
                $application2->setLicense3Accepted(null);
            }
        } else if ($data['files']['nursing_license_3']['id']) {

            // get new file
            $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['nursing_license_3']['id']]);
            // set tag
            $newFile->setTag($nurseLicense3FileTag);
            // add new file
            $application2->addApplicationFile($newFile);
            $newFile->setNurseApplicationPartTwo($application2);
            $application2->setLicense3Expiration(null);
            $application2->setLicense3Accepted(null);
        }
        $application2->setLicense3State($data['files']['nursing_license_3']['state']);
        $application2->setLicense3Number($data['files']['nursing_license_3']['license_number']);
        $application2->setLicense3FullName($data['files']['nursing_license_3']['full_name']);

        // REQUIRED DOCUMENTS
        $drivingLicenseTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => 'Driver License']);
        if (!$drivingLicenseTag) {

            $drivingLicenseTag = $fileTagRepo->createNewTagByName('Driver License', 'Driver License', 'Nurse', false);
            app::$entityManager->persist($drivingLicenseTag);
        }
        $socialSecurityTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => 'Social Security']);
        if (!$socialSecurityTag) {

            $socialSecurityTag = $fileTagRepo->createNewTagByName('Social Security', 'Social Security', 'Nurse', false);
            app::$entityManager->persist($socialSecurityTag);
        }
        $tbTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => 'TB Skin Test']);
        if (!$tbTag) {

            $tbTag = $fileTagRepo->createNewTagByName('TB Skin Test', 'TB Skin Test', 'Nurse', false);
            app::$entityManager->persist($tbTag);
        }

        $savedDriverLicense = array_values(array_filter($savedFiles, function($file) {
            return $file['tag'] == 'Driver License';
        }));
        $savedDriverLicense = $savedDriverLicense[0];
        if ($savedDriverLicense) {

            if ($data['files']['drivers_license']['id'] && $savedDriverLicense['id'] != $data['files']['drivers_license']['id']) {

                // get new file
                $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['drivers_license']['id']]);
                // set tag
                $newFile->setTag($drivingLicenseTag);
                // remove old file
                foreach ($applicationFiles as $file) {

                    if ($file->getId() == $savedDriverLicense['id']) {

                        $application2->removeApplicationFile($file);
                        $file->setNurseApplicationPartTwo(null);
                        app::$entityManager->remove($file);
                    }
                }
                // add new file
                $application2->addApplicationFile($newFile);
                $newFile->setNurseApplicationPartTwo($application2);
            }
        } else if ($data['files']['drivers_license']['id']) {

            // get new file
            $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['drivers_license']['id']]);
            // set tag
            $newFile->setTag($drivingLicenseTag);
            // add new file
            $application2->addApplicationFile($newFile);
            $newFile->setNurseApplicationPartTwo($application2);
        }

        $savedSocSec = array_values(array_filter($savedFiles, function($file) {
            return $file['tag'] == 'Social Security';
        }));
        $savedSocSec = $savedSocSec[0];
        if ($savedSocSec) {

            if ($data['files']['social_security']['id'] && $savedSocSec['id'] != $data['files']['social_security']['id']) {

                // get new file
                $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['social_security']['id']]);
                // set tag
                $newFile->setTag($socialSecurityTag);
                // remove old file
                foreach ($applicationFiles as $file) {

                    if ($file->getId() == $savedSocSec['id']) {

                        $application2->removeApplicationFile($file);
                        $file->setNurseApplicationPartTwo(null);
                        app::$entityManager->remove($file);
                    }
                }
                // add new file
                $application2->addApplicationFile($newFile);
                $newFile->setNurseApplicationPartTwo($application2);
            }
        } else if ($data['files']['social_security']['id']) {

            // get new file
            $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['social_security']['id']]);
            // set tag
            $newFile->setTag($socialSecurityTag);
            // add new file
            $application2->addApplicationFile($newFile);
            $newFile->setNurseApplicationPartTwo($application2);
        }

        $savedTb = array_values(array_filter($savedFiles, function($file) {
            return $file['tag'] == 'TB Skin Test';
        }));
        $savedTb = $savedTb[0];
        if ($savedTb) {

            if ($data['files']['tb_skin_test']['id'] && $savedTb['id'] != $data['files']['tb_skin_test']['id']) {

                // get new file
                $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['tb_skin_test']['id']]);
                // set tag
                $newFile->setTag($tbTag);
                // remove old file
                foreach ($applicationFiles as $file) {

                    if ($file->getId() == $savedTb['id']) {

                        $application2->removeApplicationFile($file);
                        $file->setNurseApplicationPartTwo(null);
                        app::$entityManager->remove($file);
                    }
                }
                // add new file
                $application2->addApplicationFile($newFile);
                $newFile->setNurseApplicationPartTwo($application2);
            }
        } else if ($data['files']['tb_skin_test']['id']) {

            // get new file
            $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['tb_skin_test']['id']]);
            // set tag
            $newFile->setTag($tbTag);
            // add new file
            $application2->addApplicationFile($newFile);
            $newFile->setNurseApplicationPartTwo($application2);
        }

        // OPTIONAL DOCUMENTS
        $cprCardTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => 'CPR Card']);
        if (!$cprCardTag) {

            $cprCardTag = $fileTagRepo->createNewTagByName('CPR Card', 'CPR Card', 'Nurse', false);
            app::$entityManager->persist($cprCardTag);
        }
        $blsAclCardTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => 'BLS ACL']);
        if (!$blsAclCardTag) {

            $blsAclCardTag = $fileTagRepo->createNewTagByName('BLS ACL', 'BLS ACL', 'Nurse', false);
            app::$entityManager->persist($blsAclCardTag);
        }
        $covidVaccineTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => 'Covid Vaccine']);
        if (!$covidVaccineTag) {

            $covidVaccineTag = $fileTagRepo->createNewTagByName('Covid Vaccine', 'Covid Vaccine', 'Nurse', false);
            app::$entityManager->persist($covidVaccineTag);
        }

        $savedCPR = array_values(array_filter($savedFiles, function($file) {
            return $file['tag'] == 'CPR Card';
        }));
        $savedCPR = $savedCPR[0];
        if ($savedCPR) {

            if ($data['files']['cpr_card']['id'] && $savedCPR['id'] != $data['files']['cpr_card']['id']) {

                // get new file
                $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['cpr_card']['id']]);
                // set tag
                $newFile->setTag($cprCardTag);
                // remove old file
                foreach ($applicationFiles as $file) {

                    if ($file->getId() == $savedCPR['id']) {

                        $application2->removeApplicationFile($file);
                        $file->setNurseApplicationPartTwo(null);
                        app::$entityManager->remove($file);
                    }
                }
                // add new file
                $application2->addApplicationFile($newFile);
                $newFile->setNurseApplicationPartTwo($application2);
            }
        } else if ($data['files']['cpr_card']['id']) {

            // get new file
            $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['cpr_card']['id']]);
            // set tag
            $newFile->setTag($cprCardTag);
            // add new file
            $application2->addApplicationFile($newFile);
            $newFile->setNurseApplicationPartTwo($application2);
        }

        $savedBlsAcl = array_values(array_filter($savedFiles, function($file) {
            return $file['tag'] == 'BLS ACL';
        }));
        $savedBlsAcl = $savedBlsAcl[0];
        if ($savedBlsAcl) {

            if ($data['files']['bls_acl_card']['id'] && $savedBlsAcl['id'] != $data['files']['bls_acl_card']['id']) {

                // get new file
                $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['bls_acl_card']['id']]);
                // set tag
                $newFile->setTag($blsAclCardTag);
                // remove old file
                foreach ($applicationFiles as $file) {

                    if ($file->getId() == $savedBlsAcl['id']) {

                        $application2->removeApplicationFile($file);
                        $file->setNurseApplicationPartTwo(null);
                        app::$entityManager->remove($file);
                    }
                }
                // add new file
                $application2->addApplicationFile($newFile);
                $newFile->setNurseApplicationPartTwo($application2);
            }
        } else if ($data['files']['bls_acl_card']['id']) {

            // get new file
            $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['bls_acl_card']['id']]);
            // set tag
            $newFile->setTag($blsAclCardTag);
            // add new file
            $application2->addApplicationFile($newFile);
            $newFile->setNurseApplicationPartTwo($application2);
        }

        $savedCovid = array_values(array_filter($savedFiles, function($file) {
            return $file['tag'] == 'Covid Vaccine';
        }));
        $savedCovid = $savedCovid[0];
        if ($savedCovid) {

            if ($data['files']['covid_vaccine_card']['id'] && $savedCovid['id'] != $data['files']['covid_vaccine_card']['id']) {

                // get new file
                $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['covid_vaccine_card']['id']]);
                // set tag
                $newFile->setTag($covidVaccineTag);
                // remove old file
                foreach ($applicationFiles as $file) {

                    if ($file->getId() == $savedCovid['id']) {

                        $application2->removeApplicationFile($file);
                        $file->setNurseApplicationPartTwo(null);
                        app::$entityManager->remove($file);
                    }
                }
                // add new file
                $application2->addApplicationFile($newFile);
                $newFile->setNurseApplicationPartTwo($application2);
            }
        } else if ($data['files']['covid_vaccine_card']['id']) {

            // get new file
            $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['covid_vaccine_card']['id']]);
            // set tag
            $newFile->setTag($covidVaccineTag);
            // add new file
            $application2->addApplicationFile($newFile);
            $newFile->setNurseApplicationPartTwo($application2);
        }

        // ID BADGE REQUEST
        $idBadgeTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => 'ID Badge']);
        if (!$idBadgeTag) {

            $idBadgeTag = $fileTagRepo->createNewTagByName('ID Badge', 'ID Badge', 'Nurse', false);
            app::$entityManager->persist($idBadgeTag);
        }

        $savedID = array_values(array_filter($savedFiles, function($file) {
            return $file['tag'] == 'ID Badge';
        }));
        $savedID = $savedID[0];
        if ($savedID) {

            if ($data['files']['id_badge_picture']['id'] && $savedID['id'] != $data['files']['id_badge_picture']['id']) {

                // get new file
                $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['id_badge_picture']['id']]);
                // set tag
                $newFile->setTag($idBadgeTag);
                // remove old file
                foreach ($applicationFiles as $file) {

                    if ($file->getId() == $savedID['id']) {

                        $application2->removeApplicationFile($file);
                        $file->setNurseApplicationPartTwo(null);
                        app::$entityManager->remove($file);
                    }
                }
                // add new file
                $application2->addApplicationFile($newFile);
                $newFile->setNurseApplicationPartTwo($application2);
            }
        } else if ($data['files']['id_badge_picture']['id']) {

            // get new file
            $newFile = ioc::getRepository('saFile')->findOneBy(['id' => (int) $data['files']['id_badge_picture']['id']]);
            // set tag
            $newFile->setTag($idBadgeTag);
            // add new file
            $application2->addApplicationFile($newFile);
            $newFile->setNurseApplicationPartTwo($application2);
        }

        if ($data['files']['nursing_license_1']['id']) {

            $applicationStatus->setLicenseSubmitted(true);
            $applicationStatus->setLastActive(new DateTime('now', app::getInstance()->getTimeZone()));
        }
        if ($data['files']['id_badge_picture']['id']) {

            $applicationStatus->setFilesSubmitted(true);
            $applicationStatus->setLastActive(new DateTime('now', app::getInstance()->getTimeZone()));

            $firstAndLastName = $member->getFirstName() . ' ' . $member->getLastName();
            $message = "Hi $firstAndLastName!\n Thank you for completing the online portion of your application to NurseStat. We are pleased to have received your 
                information and are now ready to proceed with the next step in the process; Drug Screen. You will receive an email where you will schedule an 
                appointment at a convenientlocation near you, please complete your drug screen as soon as possible. Once we have received the results, we will be able 
                to continue processing your application. If you have any questions or concerns please feel free to reach out (call/text) at 859-748-9600.";
            $smsData = [

                'application_id' => $data['application_id'],
                'message' => $message,
            ];

            $response['sms_attempt'] = SmsService::sendApplicantSMS($smsData);
        }

        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }

    public static function loadFilesProgress($data)
    {
        $response['success'] = false;
        $response['completed'] = false;
        $response['uploaded_files']['step'] = 0;
        $response['uploaded_files']['steps'] = ['', '', ''];

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['application_id']]);

        if (!$application) {
            $response['message'] = "No application found with id: " . $data['application_id'];
            return $response;
        }

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var saMemberUsers $memberUser */
        $memberUser = $member->getUsers()[0];

        $applicationFiles = $application2?->getApplicationFiles();
        $savedFiles = [];
        try {

            if (count($applicationFiles) > 0) {

                foreach ($applicationFiles as $file) {

                    $savedFile['fileTag'] = $file->getTag()->getName();
                    $savedFile['id'] = $file->getId();
                    $savedFile['name'] = $file->getFileName();
                    if ($file->getFileName() != '') {
                        $savedFile['url'] = app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id' => $file->getId()]);//['folder' => 'uploads', 'file' => $file->getFileName()]);
                    }
                    array_push($savedFiles, $savedFile);
                }
            }
        } catch (\Throwable $e) {
            $savedFiles = [];
        }

        $savedNurseLicense1 = array_values(array_filter($savedFiles, function($file) {
            return $file['fileTag'] == 'Nurse License 1';
        }));
        $savedNurseLicense1 = $savedNurseLicense1[0];
        if ($savedNurseLicense1) {

            $response['uploaded_files']['nursing_license_1']['state'] = $application2->getLicense1State();
            $response['uploaded_files']['nursing_license_1']['license_number'] = $application2->getLicense1Number();
            $response['uploaded_files']['nursing_license_1']['full_name'] = $application2->getLicense1FullName();

            $response['uploaded_files']['nursing_license_1']['url'] = $savedNurseLicense1['url'];
            $response['uploaded_files']['nursing_license_1']['id'] = $savedNurseLicense1['id'];
            $response['uploaded_files']['nursing_license_1']['name'] = $savedNurseLicense1['name'];
            $response['uploaded_files']['nursing_license_1']['fileTag'] = $savedNurseLicense1['fileTag'];

            $response['uploaded_files']['nursing_license_1']['file']['date'] = '';
            $response['uploaded_files']['nursing_license_1']['file']['id'] = $savedNurseLicense1['id'];
            $response['uploaded_files']['nursing_license_1']['file']['name'] = $savedNurseLicense1['name'];
            $response['uploaded_files']['nursing_license_1']['file']['size'] = '';
            $response['uploaded_files']['nursing_license_1']['file']['type'] = '';
            $response['uploaded_files']['nursing_license_1']['file']['url'] = '';
        } else {

            $response['uploaded_files']['nursing_license_1']['state'] = '';
            $response['uploaded_files']['nursing_license_1']['license_number'] = '';
            $response['uploaded_files']['nursing_license_1']['full_name'] = '';

            $response['uploaded_files']['nursing_license_1']['url'] = '';
            $response['uploaded_files']['nursing_license_1']['id'] = '';
            $response['uploaded_files']['nursing_license_1']['name'] = '';
            $response['uploaded_files']['nursing_license_1']['fileTag'] = '';

            $response['uploaded_files']['nursing_license_1']['file']['date'] = '';
            $response['uploaded_files']['nursing_license_1']['file']['id'] = '';
            $response['uploaded_files']['nursing_license_1']['file']['name'] = '';
            $response['uploaded_files']['nursing_license_1']['file']['size'] = '';
            $response['uploaded_files']['nursing_license_1']['file']['type'] = '';
            $response['uploaded_files']['nursing_license_1']['file']['url'] = '';
        }

        $savedNurseLicense2 = array_values(array_filter($savedFiles, function($file) {
            return $file['fileTag'] == 'Nurse License 2';
        }));
        $savedNurseLicense2 = $savedNurseLicense2[0];
        if ($savedNurseLicense2) {

            $response['uploaded_files']['nursing_license_2']['show'] = true;

            $response['uploaded_files']['nursing_license_2']['state'] = $application2->getLicense2State();
            $response['uploaded_files']['nursing_license_2']['license_number'] = $application2->getLicense2Number();
            $response['uploaded_files']['nursing_license_2']['full_name'] = $application2->getLicense2FullName();

            $response['uploaded_files']['nursing_license_2']['url'] = $savedNurseLicense2['url'];
            $response['uploaded_files']['nursing_license_2']['id'] = $savedNurseLicense2['id'];
            $response['uploaded_files']['nursing_license_2']['name'] = $savedNurseLicense2['name'];
            $response['uploaded_files']['nursing_license_2']['fileTag'] = $savedNurseLicense2['fileTag'];

            $response['uploaded_files']['nursing_license_2']['file']['date'] = '';
            $response['uploaded_files']['nursing_license_2']['file']['id'] = $savedNurseLicense2['id'];
            $response['uploaded_files']['nursing_license_2']['file']['name'] = $savedNurseLicense2['name'];
            $response['uploaded_files']['nursing_license_2']['file']['size'] = '';
            $response['uploaded_files']['nursing_license_2']['file']['type'] = '';
            $response['uploaded_files']['nursing_license_2']['file']['url'] = '';
        } else {

            $response['uploaded_files']['nursing_license_2']['show'] = false;

            $response['uploaded_files']['nursing_license_2']['state'] = '';
            $response['uploaded_files']['nursing_license_2']['license_number'] = '';
            $response['uploaded_files']['nursing_license_2']['full_name'] = '';

            $response['uploaded_files']['nursing_license_2']['url'] = '';
            $response['uploaded_files']['nursing_license_2']['id'] = '';
            $response['uploaded_files']['nursing_license_2']['name'] = '';
            $response['uploaded_files']['nursing_license_2']['fileTag'] = '';

            $response['uploaded_files']['nursing_license_2']['file']['date'] = '';
            $response['uploaded_files']['nursing_license_2']['file']['id'] = '';
            $response['uploaded_files']['nursing_license_2']['file']['name'] = '';
            $response['uploaded_files']['nursing_license_2']['file']['size'] = '';
            $response['uploaded_files']['nursing_license_2']['file']['type'] = '';
            $response['uploaded_files']['nursing_license_2']['file']['url'] = '';
        }

        $savedNurseLicense3 = array_values(array_filter($savedFiles, function($file) {
            return $file['fileTag'] == 'Nurse License 3';
        }));
        $savedNurseLicense3 = $savedNurseLicense3[0];
        if ($savedNurseLicense3) {

            $response['uploaded_files']['nursing_license_3']['show'] = true;

            $response['uploaded_files']['nursing_license_3']['state'] = $application2->getLicense3State();
            $response['uploaded_files']['nursing_license_3']['license_number'] = $application2->getLicense3Number();
            $response['uploaded_files']['nursing_license_3']['full_name'] = $application2->getLicense3FullName();

            $response['uploaded_files']['nursing_license_3']['url'] = $savedNurseLicense3['url'];
            $response['uploaded_files']['nursing_license_3']['id'] = $savedNurseLicense3['id'];
            $response['uploaded_files']['nursing_license_3']['name'] = $savedNurseLicense3['name'];
            $response['uploaded_files']['nursing_license_3']['fileTag'] = $savedNurseLicense3['fileTag'];

            $response['uploaded_files']['nursing_license_3']['file']['date'] = '';
            $response['uploaded_files']['nursing_license_3']['file']['id'] = $savedNurseLicense3['id'];
            $response['uploaded_files']['nursing_license_3']['file']['name'] = $savedNurseLicense3['name'];
            $response['uploaded_files']['nursing_license_3']['file']['size'] = '';
            $response['uploaded_files']['nursing_license_3']['file']['type'] = '';
            $response['uploaded_files']['nursing_license_3']['file']['url'] = '';
        } else {

            $response['uploaded_files']['nursing_license_3']['show'] = false;

            $response['uploaded_files']['nursing_license_3']['state'] = '';
            $response['uploaded_files']['nursing_license_3']['license_number'] = '';
            $response['uploaded_files']['nursing_license_3']['full_name'] = '';

            $response['uploaded_files']['nursing_license_3']['url'] = '';
            $response['uploaded_files']['nursing_license_3']['id'] = '';
            $response['uploaded_files']['nursing_license_3']['name'] = '';
            $response['uploaded_files']['nursing_license_3']['fileTag'] = '';

            $response['uploaded_files']['nursing_license_3']['file']['date'] = '';
            $response['uploaded_files']['nursing_license_3']['file']['id'] = '';
            $response['uploaded_files']['nursing_license_3']['file']['name'] = '';
            $response['uploaded_files']['nursing_license_3']['file']['size'] = '';
            $response['uploaded_files']['nursing_license_3']['file']['type'] = '';
            $response['uploaded_files']['nursing_license_3']['file']['url'] = '';
        }

        /** Proceed if uploaded license 1 OR license 2 or 3 are accepted in sa */
        if ( $response['uploaded_files']['nursing_license_1']['id'] != '' &&
             $response['uploaded_files']['nursing_license_1']['state'] != '' &&
             $response['uploaded_files']['nursing_license_1']['license_number'] != '' &&
             $response['uploaded_files']['nursing_license_1']['full_name'] != '' )
            { $response['uploaded_files']['step'] = 1; }

        if ($application2->getLicense2Accepted() || $application2->getLicense3Accepted()) { $response['uploaded_files']['step'] = 1; }

        // REQUIRED DOCUMENTS
        $savedDriverLicense = array_values(array_filter($savedFiles, function($file) {
            return $file['fileTag'] == 'Driver License';
        }));
        $savedDriverLicense = $savedDriverLicense[0];
        if ($savedDriverLicense) {

            $response['uploaded_files']['drivers_license']['url'] = $savedDriverLicense['url'];
            $response['uploaded_files']['drivers_license']['id'] = $savedDriverLicense['id'];
            $response['uploaded_files']['drivers_license']['name'] = $savedDriverLicense['name'];
            $response['uploaded_files']['drivers_license']['fileTag'] = $savedDriverLicense['fileTag'];

            $response['uploaded_files']['drivers_license']['file']['date'] = '';
            $response['uploaded_files']['drivers_license']['file']['id'] = $savedDriverLicense['id'];
            $response['uploaded_files']['drivers_license']['file']['name'] = $savedDriverLicense['name'];
            $response['uploaded_files']['drivers_license']['file']['size'] = '';
            $response['uploaded_files']['drivers_license']['file']['type'] = '';
            $response['uploaded_files']['drivers_license']['file']['url'] = '';
        } else {

            $response['uploaded_files']['drivers_license']['url'] = '';
            $response['uploaded_files']['drivers_license']['id'] = '';
            $response['uploaded_files']['drivers_license']['name'] = '';
            $response['uploaded_files']['drivers_license']['fileTag'] = '';

            $response['uploaded_files']['drivers_license']['file']['date'] = '';
            $response['uploaded_files']['drivers_license']['file']['id'] = '';
            $response['uploaded_files']['drivers_license']['file']['name'] = '';
            $response['uploaded_files']['drivers_license']['file']['size'] = '';
            $response['uploaded_files']['drivers_license']['file']['type'] = '';
            $response['uploaded_files']['drivers_license']['file']['url'] = '';
        }

        $savedSocSec = array_values(array_filter($savedFiles, function($file) {
            return $file['fileTag'] == 'Social Security';
        }));
        $savedSocSec = $savedSocSec[0];
        if ($savedSocSec) {

            $response['uploaded_files']['social_security']['url'] = $savedSocSec['url'];
            $response['uploaded_files']['social_security']['id'] = $savedSocSec['id'];
            $response['uploaded_files']['social_security']['name'] = $savedSocSec['name'];
            $response['uploaded_files']['social_security']['fileTag'] = $savedSocSec['fileTag'];

            $response['uploaded_files']['social_security']['file']['date'] = '';
            $response['uploaded_files']['social_security']['file']['id'] = $savedSocSec['id'];
            $response['uploaded_files']['social_security']['file']['name'] = $savedSocSec['name'];
            $response['uploaded_files']['social_security']['file']['size'] = '';
            $response['uploaded_files']['social_security']['file']['type'] = '';
            $response['uploaded_files']['social_security']['file']['url'] = '';
        } else {

            $response['uploaded_files']['social_security']['url'] = '';
            $response['uploaded_files']['social_security']['id'] = '';
            $response['uploaded_files']['social_security']['name'] = '';
            $response['uploaded_files']['social_security']['fileTag'] = '';

            $response['uploaded_files']['social_security']['file']['date'] = '';
            $response['uploaded_files']['social_security']['file']['id'] = '';
            $response['uploaded_files']['social_security']['file']['name'] = '';
            $response['uploaded_files']['social_security']['file']['size'] = '';
            $response['uploaded_files']['social_security']['file']['type'] = '';
            $response['uploaded_files']['social_security']['file']['url'] = '';
        }

        $savedTbSkinTest = array_values(array_filter($savedFiles, function($file) {
            return $file['fileTag'] == 'TB Skin Test';
        }));
        $savedTbSkinTest = $savedTbSkinTest[0];
        if ($savedTbSkinTest) {

            $response['uploaded_files']['tb_skin_test']['url'] = $savedTbSkinTest['url'];
            $response['uploaded_files']['tb_skin_test']['id'] = $savedTbSkinTest['id'];
            $response['uploaded_files']['tb_skin_test']['name'] = $savedTbSkinTest['name'];
            $response['uploaded_files']['tb_skin_test']['fileTag'] = $savedTbSkinTest['fileTag'];

            $response['uploaded_files']['tb_skin_test']['file']['date'] = '';
            $response['uploaded_files']['tb_skin_test']['file']['id'] = $savedTbSkinTest['id'];
            $response['uploaded_files']['tb_skin_test']['file']['name'] = $savedTbSkinTest['name'];
            $response['uploaded_files']['tb_skin_test']['file']['size'] = '';
            $response['uploaded_files']['tb_skin_test']['file']['type'] = '';
            $response['uploaded_files']['tb_skin_test']['file']['url'] = '';
        } else {

            $response['uploaded_files']['tb_skin_test']['url'] = '';
            $response['uploaded_files']['tb_skin_test']['id'] = '';
            $response['uploaded_files']['tb_skin_test']['name'] = '';
            $response['uploaded_files']['tb_skin_test']['fileTag'] = '';

            $response['uploaded_files']['tb_skin_test']['file']['date'] = '';
            $response['uploaded_files']['tb_skin_test']['file']['id'] = '';
            $response['uploaded_files']['tb_skin_test']['file']['name'] = '';
            $response['uploaded_files']['tb_skin_test']['file']['size'] = '';
            $response['uploaded_files']['tb_skin_test']['file']['type'] = '';
            $response['uploaded_files']['tb_skin_test']['file']['url'] = '';
        }

        // OPTIONAL DOCUMENTS
        $savedCPRCard = array_values(array_filter($savedFiles, function($file) {
            return $file['fileTag'] == 'CPR Card';
        }));
        $savedCPRCard = $savedCPRCard[0];
        if ($savedCPRCard) {

            $response['uploaded_files']['cpr_card']['url'] = $savedCPRCard['url'];
            $response['uploaded_files']['cpr_card']['id'] = $savedCPRCard['id'];
            $response['uploaded_files']['cpr_card']['name'] = $savedCPRCard['name'];
            $response['uploaded_files']['cpr_card']['fileTag'] = $savedCPRCard['fileTag'];

            $response['uploaded_files']['cpr_card']['file']['date'] = '';
            $response['uploaded_files']['cpr_card']['file']['id'] = $savedCPRCard['id'];
            $response['uploaded_files']['cpr_card']['file']['name'] = $savedCPRCard['name'];
            $response['uploaded_files']['cpr_card']['file']['size'] = '';
            $response['uploaded_files']['cpr_card']['file']['type'] = '';
            $response['uploaded_files']['cpr_card']['file']['url'] = '';
        } else {

            $response['uploaded_files']['cpr_card']['url'] = '';
            $response['uploaded_files']['cpr_card']['id'] = '';
            $response['uploaded_files']['cpr_card']['name'] = '';
            $response['uploaded_files']['cpr_card']['fileTag'] = '';

            $response['uploaded_files']['cpr_card']['file']['date'] = '';
            $response['uploaded_files']['cpr_card']['file']['id'] = '';
            $response['uploaded_files']['cpr_card']['file']['name'] = '';
            $response['uploaded_files']['cpr_card']['file']['size'] = '';
            $response['uploaded_files']['cpr_card']['file']['type'] = '';
            $response['uploaded_files']['cpr_card']['file']['url'] = '';
        }

        $savedBlsAcl = array_values(array_filter($savedFiles, function($file) {
            return $file['fileTag'] == 'BLS ACL';
        }));
        $savedBlsAcl = $savedBlsAcl[0];
        if ($savedBlsAcl) {

            $response['uploaded_files']['bls_acl_card']['url'] = $savedBlsAcl['url'];
            $response['uploaded_files']['bls_acl_card']['id'] = $savedBlsAcl['id'];
            $response['uploaded_files']['bls_acl_card']['name'] = $savedBlsAcl['name'];
            $response['uploaded_files']['bls_acl_card']['fileTag'] = $savedBlsAcl['fileTag'];

            $response['uploaded_files']['bls_acl_card']['file']['date'] = '';
            $response['uploaded_files']['bls_acl_card']['file']['id'] = $savedBlsAcl['id'];
            $response['uploaded_files']['bls_acl_card']['file']['name'] = $savedBlsAcl['name'];
            $response['uploaded_files']['bls_acl_card']['file']['size'] = '';
            $response['uploaded_files']['bls_acl_card']['file']['type'] = '';
            $response['uploaded_files']['bls_acl_card']['file']['url'] = '';
        } else {

            $response['uploaded_files']['bls_acl_card']['url'] = '';
            $response['uploaded_files']['bls_acl_card']['id'] = '';
            $response['uploaded_files']['bls_acl_card']['name'] = '';
            $response['uploaded_files']['bls_acl_card']['fileTag'] = '';

            $response['uploaded_files']['bls_acl_card']['file']['date'] = '';
            $response['uploaded_files']['bls_acl_card']['file']['id'] = '';
            $response['uploaded_files']['bls_acl_card']['file']['name'] = '';
            $response['uploaded_files']['bls_acl_card']['file']['size'] = '';
            $response['uploaded_files']['bls_acl_card']['file']['type'] = '';
            $response['uploaded_files']['bls_acl_card']['file']['url'] = '';
        }

        $savedCovidVaccine = array_values(array_filter($savedFiles, function($file) {
            return $file['fileTag'] == 'Covid Vaccine';
        }));
        $savedCovidVaccine = $savedCovidVaccine[0];
        if ($savedTbSkinTest) {

            $response['uploaded_files']['covid_vaccine_card']['url'] = $savedCovidVaccine['url'];
            $response['uploaded_files']['covid_vaccine_card']['id'] = $savedCovidVaccine['id'];
            $response['uploaded_files']['covid_vaccine_card']['name'] = $savedCovidVaccine['name'];
            $response['uploaded_files']['covid_vaccine_card']['fileTag'] = $savedCovidVaccine['fileTag'];

            $response['uploaded_files']['covid_vaccine_card']['file']['date'] = '';
            $response['uploaded_files']['covid_vaccine_card']['file']['id'] = $savedCovidVaccine['id'];
            $response['uploaded_files']['covid_vaccine_card']['file']['name'] = $savedCovidVaccine['name'];
            $response['uploaded_files']['covid_vaccine_card']['file']['size'] = '';
            $response['uploaded_files']['covid_vaccine_card']['file']['type'] = '';
            $response['uploaded_files']['covid_vaccine_card']['file']['url'] = '';
        } else {

            $response['uploaded_files']['covid_vaccine_card']['url'] = '';
            $response['uploaded_files']['covid_vaccine_card']['id'] = '';
            $response['uploaded_files']['covid_vaccine_card']['name'] = '';
            $response['uploaded_files']['covid_vaccine_card']['fileTag'] = '';

            $response['uploaded_files']['covid_vaccine_card']['file']['date'] = '';
            $response['uploaded_files']['covid_vaccine_card']['file']['id'] = '';
            $response['uploaded_files']['covid_vaccine_card']['file']['name'] = '';
            $response['uploaded_files']['covid_vaccine_card']['file']['size'] = '';
            $response['uploaded_files']['covid_vaccine_card']['file']['type'] = '';
            $response['uploaded_files']['covid_vaccine_card']['file']['url'] = '';
        }

        if ( $response['uploaded_files']['step'] == 1 &&
             $response['uploaded_files']['drivers_license']['id'] != '' &&
             $response['uploaded_files']['social_security']['id'] != '' &&
             $response['uploaded_files']['tb_skin_test']['id'] != '' )
            { $response['uploaded_files']['step'] = 2; }

        // ID BADGE REQUEST
        $savedIDBadge = array_values(array_filter($savedFiles, function($file) {
            return $file['fileTag'] == 'ID Badge';
        }));
        $savedIDBadge = $savedIDBadge[0];
        if ($savedIDBadge) {

            $response['uploaded_files']['id_badge_picture']['url'] = $savedIDBadge['url'];
            $response['uploaded_files']['id_badge_picture']['id'] = $savedIDBadge['id'];
            $response['uploaded_files']['id_badge_picture']['name'] = $savedIDBadge['name'];
            $response['uploaded_files']['id_badge_picture']['fileTag'] = $savedIDBadge['fileTag'];

            $response['uploaded_files']['id_badge_picture']['file']['date'] = '';
            $response['uploaded_files']['id_badge_picture']['file']['id'] = $savedIDBadge['id'];
            $response['uploaded_files']['id_badge_picture']['file']['name'] = $savedIDBadge['name'];
            $response['uploaded_files']['id_badge_picture']['file']['size'] = '';
            $response['uploaded_files']['id_badge_picture']['file']['type'] = '';
            $response['uploaded_files']['id_badge_picture']['file']['url'] = '';
        } else {

            $response['uploaded_files']['id_badge_picture']['url'] = '';
            $response['uploaded_files']['id_badge_picture']['id'] = '';
            $response['uploaded_files']['id_badge_picture']['name'] = '';
            $response['uploaded_files']['id_badge_picture']['fileTag'] = '';

            $response['uploaded_files']['id_badge_picture']['file']['date'] = '';
            $response['uploaded_files']['id_badge_picture']['file']['id'] = '';
            $response['uploaded_files']['id_badge_picture']['file']['name'] = '';
            $response['uploaded_files']['id_badge_picture']['file']['size'] = '';
            $response['uploaded_files']['id_badge_picture']['file']['type'] = '';
            $response['uploaded_files']['id_badge_picture']['file']['url'] = '';
        }

        if ($response['uploaded_files']['step'] == 2 && $response['uploaded_files']['id_badge_picture']['id'] != '') {
            $response['completed'] = true;
        }

        $response['success'] = true;
        return $response;
    }

    public static function sendMobileFileUpload($data)
    {
        $response['success'] = false;

        $domain = app::get()->getConfiguration()->get('site_url')->getValue();
        $url = $domain . '/applications/mobile-file-upload/' . $data['application_id'];

        $smsData = [

            'application_id' => $data['application_id'],
            'message' => 'Please upload your files here: ' . $url,
        ];

        $response['sms_attempt'] = SmsService::sendApplicantSMS($smsData);
        $response['url'] = $url;

        $response['success'] = true;
        return $response;
    }

    public function startDrugScreen($data)
    {
        $response['success'] = false;

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['application_id']]);

        if (!$application) {

            $response['message'] = "No application found with id: " . $data['application_id'];
            return $response;
        }

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var saMemberUsers $memberUser */
        $memberUser = $member->getUsers()[0];

        /** @var ApplicationStatus $applicationStatus */
        $applicationStatus = $application2->getApplicationStatus();

        $checkrDomain = app::get()->getConfiguration()->get('checkr_base_url')->getValue();
        $api_key = app::get()->getConfiguration()->get('checkr_api')->getValue();
        $endpoint = "$checkrDomain/v1/candidates";

        $checkrId = $applicationStatus?->getCheckrId();

        if (!$checkrId || $checkrId == '') {

            // decrypt ssn ************************
            // $encryptedSSN = $application->getSocSec();
            // $cipher = "AES-128-CTR";
            // $key = $memberUser->getUserKey();
            // $ssn = openssl_decrypt($encryptedSSN, $cipher, (string)$key, 0, ord($key));
            // ************************************

            // change dob from mm/dd/yyyy to yyyy-mm-dd
            $dob = $application->getDOB();
            $dob = explode('/', $dob);
            $dob = $dob[2] . '-' . $dob[0] . '-' . $dob[1];

            $middleName = $member?->getMiddleName();
            if (!$middleName || $middleName == null) {
                $middleName = '';
            }
            $state = $application->getState();

            $requestData = [

                'first_name' => $member->getFirstName(),
                'middle_name' => $middleName,
                'last_name' => $member->getLastName(),
                'email' => $memberUser->getUsername(),
                'phone' => $application->getPhoneNumber(),
                'zipcode' => $application->getZipcode(),
                // 'dob' => $dob,
                // 'ssn' => $ssn,
                'copy_requested' => true,
                '&work_locations[][country]' => 'US',
                '&work_locations[][state]' => $state,
            ];

            $ch = curl_init($endpoint);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [

                'Authorization: Basic ' . base64_encode($api_key . ':'),
                'Content-Type: application/x-www-form-urlencoded',
            ]);

            $apiResponse = curl_exec($ch);

            if (curl_errno($ch)) {

                $response['curl_error_1'] = curl_error($ch);
                return $response;
            }

            curl_close($ch);

            $checkrReturnArray = json_decode($apiResponse, true);
            $checkrId = $checkrReturnArray['id'];
            $applicationStatus->setCheckrId($checkrId);

            app::$entityManager->flush();
        }

        $drugScreenId = $applicationStatus?->getDrugScreenInvitationId();
        if (!$drugScreenId) {

            // this enables the api call to work in staging env and in production
            if (strpos($checkrDomain, 'staging')) {
                $package = 'test_10_panel_drug_screen';
            } else {
                $package = '10_panel_drug_screen';
            }

            $state = $application->getState();
            $ch = curl_init($endpoint);

            curl_setopt($ch, CURLOPT_URL, "$checkrDomain/v1/invitations");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
            ]);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "$api_key:");
            curl_setopt($ch, CURLOPT_POSTFIELDS, "candidate_id=$checkrId&package=$package&work_locations[][country]=US&work_locations[][state]=$state");

            $apiResponse = curl_exec($ch);

            if (curl_errno($ch)) {
                echo 'cURL Error: ' . curl_error($ch);
            }

            $apiResponseObject = json_decode($apiResponse, true);
            $drugScreenId = $apiResponseObject['id'];

            $applicationStatus->setDrugScreenInvitationId($drugScreenId);
            app::$entityManager->flush();

            curl_close($ch);
        }

        $response['success'] = true;
        $response['drug_screen_id'] = $drugScreenId;
        return $response;
    }

    public function startBackgroundCheck($data)
    {
        $response['success'] = false;

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['applicant_id']]);

        if (!$application) {

            $response['message'] = "No application found with id: " . $data['applicant_id'];
            return $response;
        }

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var saMemberUsers $memberUser */
        $memberUser = $member->getUsers()[0];

        /** @var ApplicationStatus $applicationStatus */
        $applicationStatus = $application2->getApplicationStatus();

        $checkrDomain = app::get()->getConfiguration()->get('checkr_base_url')->getValue();
        $api_key = app::get()->getConfiguration()->get('checkr_api')->getValue();
        $endpoint = "$checkrDomain/v1/candidates";

        $checkrId = $applicationStatus?->getCheckrId();
        if (!$checkrId) {

            $response['error'] = 'No checkr id found';
            return $response;
        }

        $workLocationsString = '&work_locations[][country]=US';
        $license1State = $application2->getLicense1State();
        if ($license1State && $license1State != 'Compact') {
            $workLocationsString .= "&work_locations[][state]=$license1State";
        }

        $license2State = $application2->getLicense2State();
        if ($license2State && $license2State != 'Compact') {
            $workLocationsString .= "&work_locations[][state]=$license2State";
        }

        $license3State = $application2->getLicense3State();
        if ($license3State && $license3State != 'Compact') {
            $workLocationsString .= "&work_locations[][state]=$license3State";
        }

        if ($workLocationsString == '') {

            $applicationState = $application->getState();
            $workLocationsString = "&work_locations[][state]=$applicationState";
        }

        $backgroundCheckId = $applicationStatus?->getBackgroundCheckInvitationId();

        if (!$backgroundCheckId) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "$checkrDomain/v1/invitations");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
            ]);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "$api_key:");
            curl_setopt($ch, CURLOPT_POSTFIELDS, "candidate_id=$checkrId&package=basic_plus_with_facis_3$workLocationsString");

            $apiResponse = curl_exec($ch);
            $apiResponse = json_decode($apiResponse, true);

            $applicationStatus->setBackgroundCheckInvitationId($apiResponse['id']);
            $applicationStatus->setBackgroundCheckStatus($apiResponse['status']);
            $applicationStatus->setBackgroundCheckStartedDate(new \DateTime());

            app::$entityManager->flush();
            curl_close($ch);

            $firstAndLastName = $member->getFirstName() . ' ' . $member->getLastName();
            $message = "Hi $firstAndLastName!\n Congratulations on completing your drug screen! We are now ready to proceed with the next step, a background check. 
                You will soon receive an email with instructions on how to complete this step. Please submit your information as soon as possible. Once we have 
                received the results, we will be able to finalize your onboarding and welcome you to the NurseStat team!";
            $smsData = [

                'application_id' => $data['applicant_id'],
                'message' => $message,
            ];

            $response['sms_attempt'] = SmsService::sendApplicantSMS($smsData);
        }

        $response['success'] = true;
        return $response;
    }

    public function loadDrugScreenProgress($data)
    {
        $response['success'] = false;

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['applicant_id']]);

        if (!$application) {

            $response['message'] = "No application found with id: " . $data['applicant_id'];
            return $response;
        }

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var ApplicationStatus $applicationStatus */
        $applicationStatus = $application2->getApplicationStatus();

        $nurseApplicationService = new NurseApplicationService();

        // check status of license approval
        if ($applicationStatus->getLicenseVerified()) {

            $status = $applicationStatus->getDrugScreenStatus();
            if ($status != 'completed') {

                // check status of drug screen
                $drugScreenId = $applicationStatus->getDrugScreenInvitationId();
                $checkrDomain = app::get()->getConfiguration()->get('checkr_base_url')->getValue();
                $api_key = app::get()->getConfiguration()->get('checkr_api')->getValue();
                $endpoint = "$checkrDomain/v1/invitations/$drugScreenId";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    "Authorization: Basic " . base64_encode("$api_key:")
                ]);

                $apiResponse = curl_exec($ch);

                $apiResponse = json_decode($apiResponse, true);
                $status = $apiResponse['status'];

                curl_close($ch);

                $applicationStatus->setDrugScreenStatus($status);
                app::$entityManager->flush();
            }

            // return status of drug screen
            $drugScreenReport = $applicationStatus?->getDrugScreenReport();
            if (!$drugScreenReport || $drugScreenReport['status'] != 'complete') {

                $reportId = $applicationStatus->getDrugScreenReportId();
                if (!$reportId) {

                    $reportId = $apiResponse['report_id'];
                    $applicationStatus->setDrugScreenReportId($reportId);
                    app::$entityManager->flush();
                }

                $endpoint = "$checkrDomain/v1/reports/$reportId";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [

                    'Content-Type: application/json',
                    "Authorization: Basic " . base64_encode("$api_key:")
                ]);

                $reportApiResponse = curl_exec($ch);
                $reportApiResponse = json_decode($reportApiResponse, true);

                // saves file to application2
                $nurseApplicationService->saveDrugScreenReport($data, $reportApiResponse);

                // saves json to applicationStatus
                $applicationStatus->setDrugScreenReport($reportApiResponse);
                app::$entityManager->flush();

                if ($reportApiResponse['result'] == 'clear' || $applicationStatus?->getDrugScreenAccepted()) {

                    $applicationStatus->setDrugScreenAccepted(true);
                    app::$entityManager->flush();

                    $nurseApplicationService->startBackgroundCheck($data);

                    $response['show'] = false;
                    $response['completed'] = true;
                    $response['status'] = 'completed';

                    $response['success'] = true;
                    return $response;

                } else {

                    $response['show'] = true;
                    $response['completed'] = false;
                    $response['status'] = 'pending drug screen';

                    $response['success'] = true;
                    return $response;
                }

            } else {

                if ($applicationStatus?->getDrugScreenAccepted()) {

                    $response['show'] = false;
                    $response['completed'] = true;
                    $response['status'] = 'completed';

                    $response['success'] = true;
                    return $response;

                } else {

                    $response['show'] = true;
                    $response['completed'] = false;
                    $response['status'] = 'pending drug screen';

                    $response['success'] = true;
                    return $response;
                }
            }

        } else {

            $response['show'] = true;
            $response['completed'] = false;
            $response['status'] = 'pending license';

            $response['success'] = true;
            return $response;
        }
    }

    public function saveDrugScreenReport($data, $reportApiResponse)
    {
        $saf = new NstFile();
        $file = $saf->saveStringToFile(time().'DrugScreenReport.txt', json_encode($reportApiResponse));
        app::$entityManager->persist($file);

        $fileTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => 'Drug Screen Report']);
        if (!$fileTag) {

            $fileTag = new nstFileTag();
            $fileTag->setName('Drug Screen');
            $fileTag->setShowInProviderPortal(true);
            app::$entityManager->persist($fileTag);
        }
        $file->setTag($fileTag);

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['applicant_id']]);

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        $application2->addApplicationFile($file);
        $file->setNurseApplicationPartTwo($application2);

        app::$entityManager->flush();
    }

    public function loadBackgroundCheckProgress($data)
    {
        $response['success'] = false;

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['applicant_id']]);

        if (!$application) {

            $response['message'] = "No application found with id: " . $data['application_id'];
            return $response;
        }

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var ApplicationStatus $applicationStatus */
        $applicationStatus = $application2->getApplicationStatus();

        $status = $applicationStatus->getBackgroundCheckStatus();
        if (!$status || $status != 'completed') {

            // make sure that there is a background check id
            $backgroundCheckId = $applicationStatus->getBackgroundCheckInvitationId();
            if (!$backgroundCheckId) {

                static::startBackgroundCheck($data);
                $applicationStatus->setBackgroundCheckStatus('pending');
                
                app::$entityManager->flush();

            } else {

                $checkrDomain = app::get()->getConfiguration()->get('checkr_base_url')->getValue();
                $api_key = app::get()->getConfiguration()->get('checkr_api')->getValue();
                $endpoint = "$checkrDomain/v1/invitations/$backgroundCheckId";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [

                    'Content-Type: application/json',
                    "Authorization: Basic " . base64_encode("$api_key:")
                ]);

                $apiResponse = curl_exec($ch);

                $apiResponse = json_decode($apiResponse, true);
                $status = $apiResponse['status'];

                curl_close($ch);

                $applicationStatus->setBackgroundCheckStatus($status);
                app::$entityManager->flush();
            }
        }

        if ($status == 'pending') {

            $response['show'] = true;
            $response['completed'] = false;
            $response['status'] = 'pending';
            $response['already_accepted'] = false;

            $response['success'] = true;
            return $response;

        } else if ($status == 'completed') {

            $reportId = $applicationStatus->getBackgroundCheckReportId();
            if (!$reportId) {

                $reportId = $apiResponse['report_id'];
                $applicationStatus->setBackgroundCheckReportId($reportId);
                app::$entityManager->flush();
            }

            $backgroundCheckReport = $applicationStatus?->getBackgroundCheckReport();
            if (!$backgroundCheckReport) {

                $endpoint = "$checkrDomain/v1/reports/$reportId";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [

                    'Content-Type: application/json',
                    "Authorization: Basic " . base64_encode("$api_key:")
                ]);

                $reportApiResponse = curl_exec($ch);
                $reportApiResponse = json_decode($reportApiResponse, true);

                $applicationStatus->setBackgroundCheckReport($reportApiResponse);
                app::$entityManager->flush();

                $firstAndLastName = $member->getFirstName() . ' ' . $member->getLastName();
                $message = "Hi $firstAndLastName!\n Thank you for completing your application to NurseStat! We are pleased to have received your information and are 
                excited to move forward with the next step in the process: a virtual orientation. Please schedule a date and time for your virtual orientation using 
                the link below. We look forward to meeting you and welcoming you to the NurseStat team! https://calendly.com/nursestat-orientation/interview";
                $smsData = [

                    'application_id' => $data['application_id'],
                    'message' => $message,
                ];

                $response['sms_attempt'] = SmsService::sendApplicantSMS($smsData);
            }

            if ($applicationStatus?->getBackgroundCheckAccepted()) {

                $alreadyAccepted = $applicationStatus?->getBackgroundCheckSignature();
                if ($alreadyAccepted) {

                    $response['already_accepted'] = true;
                    $response['completed'] = true;

                } else {

                    $response['already_accepted'] = false;
                    $response['completed'] = false;
                }

                $response['show'] = true;
                $response['status'] = 'accepted';

                $response['success'] = true;
                return $response;

            } else {

                $response['show'] = true;
                $response['completed'] = false;
                $response['status'] = 'pending';
                $response['already_accepted'] = false;

                $response['success'] = true;
                return $response;
            }
        }
    }

    public function signBackgroundCheckAgreement($data)
    {
        $response['success'] = false;

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['application_id']]);

        if (!$application) {

            $response['message'] = "No application found with id: " . $data['application_id'];
            return $response;
        }

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var ApplicationStatus $applicationStatus */
        $applicationStatus = $application2->getApplicationStatus();

        $applicationStatus->setBackgroundCheckSignature($data['signature']);
        $applicationStatus->setBackgroundCheckSignedTime(new \DateTime());

        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }
}
