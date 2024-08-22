<?php

namespace nst\applications;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\DateTime;
use sacore\application\modRequest;
use sacore\application\ModRequestAuthenticationException;
use sacore\utilities\doctrineUtils;
use nst\member\NurseApplication;
use nst\member\Nurse;
use nst\messages\SmsService;
use sacore\application\saController;
use sacore\application\responses\View;
use mikehaertl\pdftk\Pdf;
use nst\applications;
use nst\member\NstFileTagRepository;
use sa\files\saFile;

class SaNurseApplicationController extends saController
{
    public function index()
    {
        $view = new View('table', static::viewLocation());

        $nurseAppRepository = ioc::getRepository('NurseApplication');

        $perPage = 500;
        $fieldsToSearch = [];

        $showFirstStatus = false;

        //if ($_GET) { echo json_encode($_GET); die; }

        foreach ($_GET as $field => $value) {
            if ($value == ' ') {
                continue;
            }
            if (strpos($field, 'q_') === 0 && !empty($value)) {
                $strippedField = str_replace('q_', '', $field);

                if ($strippedField == 'first_name') {
                    if ($fieldsToSearch['nurse'] && !is_array($fieldsToSearch['nurse'])) {
                        $fieldsToSearch['nurse'] = [$fieldsToSearch['nurse']];
                        array_push($fieldsToSearch['nurse'], '"%first_name_____' . $value . '%"');
                    } else if ($fieldsToSearch['nurse'] && is_array($fieldsToSearch['nurse'])) {
                        array_push($fieldsToSearch['nurse'], '"%first_name_____' . $value . '%"');
                    } else {
                        $fieldsToSearch['nurse'] = '"%first_name_____' . $value . '%"';
                    }
                } else if ($strippedField == 'last_name') {
                    if ($fieldsToSearch['nurse'] && !is_array($fieldsToSearch['nurse'])) {
                        $fieldsToSearch['nurse'] = [$fieldsToSearch['nurse']];
                        array_push($fieldsToSearch['nurse'], '"%last_name_____' . $value . '%"');
                    } else if ($fieldsToSearch['nurse'] && is_array($fieldsToSearch['nurse'])) {
                        array_push($fieldsToSearch['nurse'], '"%last_name_____' . $value . '%"');
                    } else {
                        $fieldsToSearch['nurse'] = '"%last_name_____' . $value . '%"';
                    }
                } else if ($strippedField == 'phone_number') {
                    if ($fieldsToSearch['nurse'] && !is_array($fieldsToSearch['nurse'])) {
                        $fieldsToSearch['nurse'] = [$fieldsToSearch['nurse']];
                        if (is_numeric($value)) {
                            array_push($fieldsToSearch['nurse'], '"%phone_number_____' . $this->maskPhoneInput($value) . '%"');
                        } else {
                            array_push($fieldsToSearch['nurse'], '"%phone_number_____' . $value . '%"');
                        }
                    } else if ($fieldsToSearch['nurse'] && is_array($fieldsToSearch['nurse'])) {
                        if (is_numeric($value)) {
                            array_push($fieldsToSearch['nurse'], '"%phone_number_____' . $this->maskPhoneInput($value) . '%"');
                        } else {
                            array_push($fieldsToSearch['nurse'], '"%phone_number_____' . $value . '%"');
                        }
                    } else {
                        if (is_numeric($value)) {
                            $fieldsToSearch['nurse'] = '"%phone_number_____' . $this->maskPhoneInput($value) . '%"';
                        } else {
                            $fieldsToSearch['nurse'] = '"%phone_number_____' . $value . '%"';
                        }
                    }

                } else if ($strippedField == 'email') {
                    if ($fieldsToSearch['nurse'] && !is_array($fieldsToSearch['nurse'])) {
                        $fieldsToSearch['nurse'] = [$fieldsToSearch['nurse']];
                        array_push($fieldsToSearch['nurse'], '"%email_____' . $value . '%"');
                    } else if ($fieldsToSearch['nurse'] && is_array($fieldsToSearch['nurse'])) {
                        array_push($fieldsToSearch['nurse'], '"%email_____' . $value . '%"');
                    } else {
                        $fieldsToSearch['nurse'] = '"%email_____' . $value . '%"';
                    }
                } else if ($strippedField == 'submitted_at') {
                    //if (is_numeric($value)) {
                    //} else {
                        $fieldsToSearch[$strippedField] = $value;
                    //}
                } else if ($strippedField == 'position') {
                    if ($fieldsToSearch['nurse'] && !is_array($fieldsToSearch['nurse'])) {
                        $fieldsToSearch['nurse'] = [$fieldsToSearch['nurse']];
                        array_push($fieldsToSearch['nurse'], '"%position_____' . $value . '%"');
                    } else if ($fieldsToSearch['nurse'] && is_array($fieldsToSearch['nurse'])) {
                        array_push($fieldsToSearch['nurse'], '"%position_____' . $value . '%"');
                    } else {
                        $fieldsToSearch['nurse'] = '"%position_____' . $value . '%"';
                    }
                } else if ($strippedField == 'state') {
                    if ($fieldsToSearch['nurse'] && !is_array($fieldsToSearch['nurse'])) {
                        $fieldsToSearch['nurse'] = [$fieldsToSearch['nurse']];
                        array_push($fieldsToSearch['nurse'], '"%state_____' . $value . '%"');
                    } else if ($fieldsToSearch['nurse'] && is_array($fieldsToSearch['nurse'])) {
                        array_push($fieldsToSearch['nurse'], '"%state_____' . $value . '%"');
                    } else {
                        $fieldsToSearch['nurse'] = '"%state_____' . $value . '%"';
                    }
                }
                 else if ($strippedField == 'application_status') {
                    if ($value === 'was_declined') {
                        $fieldsToSearch['declined_at'] = '';
                    } else if ($value === 'was_approved') {
                        $fieldsToSearch['approved_at'] = '';
                    } else if ($value === 'was_saved') {
                        $fieldsToSearch['submitted_at'] = 'saved';
                    } else if ($value === 'was_submitted') {
                        $fieldsToSearch['submitted_at'] = '';
                    }
                } else {
                    $fieldsToSearch[$strippedField] = $value;
                }
            }
        }

        // echo '$fieldsToSearch: ' . json_encode($fieldsToSearch); die;

        $currentPage = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $sort = !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : false;
        $sortDir = !empty($_REQUEST['sortDir']) ? $_REQUEST['sortDir'] : false;
        $orderBy = null;

        if ($sort) {
            $orderBy = array($sort => $sortDir);
        }

        $totalRecords = $nurseAppRepository->search($fieldsToSearch, null, null, null, true);
        $results = $nurseAppRepository->search($fieldsToSearch, $orderBy, $perPage, (($currentPage - 1) * $perPage), false, ['submitted_at' => 'DESC']);

        $applications = [];

        // Set Table View Data
        foreach ($results as $application) {
            $tmpApplicationArr = [];

            $tmpApplicationArr['application'] = $application->getId();
            $nurse = json_decode($application->getNurse(), true);
            $tmpApplicationArr['first_name'] = $nurse['first_name'];
            $tmpApplicationArr['last_name'] = $nurse['last_name'];
            $tmpApplicationArr['phone_number'] = $nurse['phone_number'];
            $tmpApplicationArr['email'] = $nurse['email'];
            $tmpApplicationArr['submitted_at'] = $application->getSubmittedAt() ? $application->getSubmittedAt()->format('F j, Y') . ' at ' . $application->getSubmittedAt()->format('g:ia') : '';
            $tmpApplicationArr['state'] = $nurse['state'];
            $tmpApplicationArr['position'] = $nurse['position'];
            // $tmpApplicationArr['pending'] = (!$application->getApprovedAt() && !$application->getDeclinedAt()) ? 'Yes' : 'No';

            // if ($application->getApprovedAt()) {
            //     if ($application->getApprovedAt()) {
            //         $tmpApplicationArr['approved_at'] = 'Yes';
            //     } else if ($application->getDeclinedAt()) {
            //         $tmpApplicationArr['approved_at'] = 'No';
            //     }
            // } else {
            //     $tmpApplicationArr['approved_at'] = 'N/A';
            // }

            $applications[] = $tmpApplicationArr;
        }

        $totalPages = ceil($totalRecords / $perPage);
        $view->data['table'][] = [
            'header' => [
                ['name' => 'First Name', 'class' => '', 'map' => 'first_name'],
                ['name' => 'Last Name', 'class' => '', 'map' => 'last_name'],
                ['name' => 'Date', 'class' => '', 'map' => 'submitted_at', 'searchType' => 'date'],
                ['name' => 'Phone Number', 'class' => '', 'map' => 'phone_number'],
                ['name' => 'Email Address', 'class' => '', 'map' => 'email'],
                ['name' => 'Credentials', 'class' => '', 'map' => 'position', 'searchType' => 'select', 'values' => array(

                    'RN' => 'RN',
                    'LPN' => 'LPN',
                    'CNA' => 'CNA',
                    'CMA/KMA' => 'CMA/KMA',
                    'Homecare/Sitter' => 'Homecare/Sitter',
                    'Other' => 'Other'
                )],
                ['name' => 'State', 'class' => '', 'map' => 'state', 'searchType' => 'select', 'values' => array(

                    'Alabama' => 'Alabama', 'Alaska' => 'Alaska', 'Arizona' => 'Arizona', 'Arkansas' => 'Arkansas', 'California' => 'California', 'Colorado' => 'Colorado', 'Connecticut' => 'Connecticut', 'Delaware' => 'Delaware', 'District of Columbia' => 'District of Columbia', 'Florida' => 'Florida', 'Georgia' => 'Georgia', 'Guam' => 'Guam', 'Hawaii' => 'Hawaii', 'Idaho' => 'Idaho', 'Illinois' => 'Illinois', 'Indiana' => 'Indiana', 'Iowa' => 'Iowa', 'Kansas' => 'Kansas', 'Kentucky' => 'Kentucky', 'Louisiana' => 'Louisiana', 'Maine' => 'Maine', 'Marshall Islands' => 'Marshall Islands', 'Maryland' => 'Maryland', 'Massachusetts' => 'Massachusetts', 'Michigan' => 'Michigan', 'Minnesota' => 'Minnesota', 'Mississippi' => 'Mississippi', 'Missouri' => 'Missouri', 'Montana' => 'Montana', 'Nebraska' => 'Nebraska', 'Nevada' => 'Nevada', 'New Hampshire' => 'New Hampshire', 'New Jersey' => 'New Jersey', 'New Mexico' => 'New Mexico', 'New York' => 'New York', 'North Carolina' => 'North Carolina', 'North Dakota' => 'North Dakota', 'Ohio' => 'Ohio', 'Oklahoma' => 'Oklahoma', 'Oregon' => 'Oregon', 'Pennsylvania' => 'Pennsylvania', 'Rhode Island' => 'Rhode Island', 'South Carolina' => 'South Carolina', 'South Dakota' => 'South Dakota', 'Tennessee' => 'Tennessee', 'Texas' => 'Texas', 'Utah' => 'Utah', 'Vermont' => 'Vermont', 'Virginia' => 'Virginia', 'Washington' => 'Washington', 'West Virginia' => 'West Virginia', 'Wisconsin' => 'Wisconsin', 'Wyoming' => 'Wyoming'
                )]
            ],
            'actions' => ['edit' => ['name' => 'Edit', 'routeid' => 'nurse_applications_show', 'params' => ['application']]],
            'noDataMessage' => 'No Applications Available',
            'tableCreateRoute' => '',
            'data' => $applications,
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'searchable' => true,
            'custom_search_fields' => array(
                'radios' => array(
                    'label' => 'Application Status',
                    'name' => 'application_status',
                    'searchType' => 'radios',
                    'items' => array(
                        'was_declined' => array(
                            'label' => 'Declined'
                        ),
                        'was_approved' => array(
                            'label' => 'Approved'
                        ),
                        'was_saved' => array(
                            'label' => 'Saved'
                        ),
                        'was_submitted' => array(
                            'label' => 'Submitted (default)'
                        )
                    )
                )
            ),
            'dataRowCallback' => function ($data) {
                return $data;
            }
        ];

        return $view;
    }

    public function manageApplications(): View
    {
        $view = new View('sa_nurse_applications', static::viewLocation());
        return $view;
    }

    public static function loadApplications()
    {
        $service = new NurseApplicationService();
        return $service->loadApplications();
    }

    public function updateAgreement(): View
    {
        $view = new View('sa_update_pdf_agreement', static::viewLocation());
        return $view;
    }

    public function show($request)
    {
        $nurseAppService = ioc::resolve('NurseApplicationService');

        $application = ioc::getRepository('NurseApplication')->find(
            $request->getRouteParams()->get('application')
        );

        $view = new View('nurse_application_show', static::viewLocation());
        $view->data['application'] = $nurseAppService->getFullApplication($application);
        $view->data['member'] = $application->getMember();

        if ($application->getApprovedAt()) {
            $view->data['application_status'] = 'approved';
        } else if ($application->getDeclinedAt()) {
            $view->data['application_status'] = 'declined';
        } else if ($application->getSubmittedAt()) {
            $view->data['application_status'] = 'submitted';
        } else if (!$application->getSubmittedAt() && !$application->getDeclinedAt() && !$application->getApprovedAt()) {
            $view->data['application_status'] = 'saved';
        }

        return $view;
    }

    public function applicationView($request)
    {
        $application = ioc::getRepository('NurseApplication')->find(
            $request->getRouteParams()->get('application')
        );

        $view = new View('sa_nurse_application_view', static::viewLocation());
        $view->data['application'] = $application;

        return $view;
    }

    public static function update($data)
    {
        $response = ['success' => false];

        /** @var NurseApplication $application */
        $application = ioc::getRepository('NurseApplication')->find($data['id']);
        doctrineUtils::setEntityData($data['data'], $application);
        app::$entityManager->flush();

        $response = ['success' => true];
        return $response;
    }

    public static function approveNurseDeprecated($data)
    {
        $response = ['success' => false];
        $service = new NurseApplicationService();
        $service->approveNurseDeprecated($data);

        $i9 = self::generatei9($data);
        array_push($data['form']['files'], $i9['i9_file']);

        // $f1099 = self::generate1099($data);
        // array_push($data['form']['files'], $f1099['1099_file']);

        // get application entity and save the new files array with the newly added file (in json array format)
        /** @var NurseApplication $application */
        $application = ioc::getRepository('NurseApplication')->find($data['id']);
        $application->setFiles(json_encode($data['form']['files']));
        // Do a whole lot of determining if i9 file is tagged correctly
        // Create tag if nedcessary, but just make sure it's tagged
        try {
            $member = $application->getMember();
            $nurse = $member->getNurse();
            $i9File = ioc::getRepository('saFile')->findOneBy(['id' => $i9['i9_file']['id']]);
            if ($i9File && !$nurse->getNurseFiles()->contains($i9File)) {
                $nurse->addNurseFile($i9File);
                $i9File->setNurse($nurse);
                if (!$i9File->getTag()) {
                    /** @var NstFileTagRepository $fileTagRepo */
                    $fileTagRepo = ioc::getRepository('NstFileTag');
                    $i9Tag = $fileTagRepo->findOneBy(['name' => 'i-9']);
                    if (!$i9Tag) {
                        $i9Tag = $fileTagRepo->createNewTagByName('i-9', 'i-9', 'Nurse', false);
                    }
                    $i9File->setTag($i9Tag);
                }
            }
        } catch(\Throwable $t) {
            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . DIRECTORY_SEPARATOR . 'willtest.txt',
            'ATTEMPTING TO TAG AND SAVE i9 FILE CORRECTLY: ' . $t->getMessage() . PHP_EOL, FILE_APPEND);
        }
        app::$entityManager->flush();

        static::sendApprovedEmail($data);

        $response['success'] = true;
        return $response;
    }

    public static function approveNurse($data)
    {
        $response = ['success' => false];

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->find($data['application_id']);

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var saMemberUsers $memberUser */
        $memberUser = $member->getUsers()[0];

        $i9Data['form']['nurse']['first_name'] = $member?->getFirstName();
        $i9Data['form']['nurse']['middle_name'] = $member?->getMiddleName();
        $i9Data['form']['nurse']['last_name'] = $member?->getLastName();
        $i9Data['form']['nurse']['street_address'] = $application?->getStreetAddress();
        $i9Data['form']['nurse']['street_address_two'] = $application?->getStreetAddress2();
        $i9Data['form']['nurse']['city'] = $application?->getCity();
        $i9Data['form']['nurse']['state'] = NurseApplicationService::convertToFullStateName($application?->getState());
        $i9Data['form']['nurse']['zip_code'] = $application->getZipCode();
        $i9Data['form']['nurse']['date_of_birth'] = $application->getDOB();
        $i9Data['form']['nurse']['email'] = $memberUser->getUsername();
        $i9Data['form']['nurse']['phone_number'] = $application->getPhoneNumber();

        // if citizen of US value is set to "No" as they don't need special authorization to work in the US
        if ($application->getIsCitizen()) { $i9Data['form']['nurse']['authorized_to_work_in_the_us'] = "No"; }
        else $i9Data['form']['nurse']['authorized_to_work_in_the_us'] = "Yes";

        // decrypt socsec
        $encryptedSSN = $application->getSocSec();
        $cipher = "AES-128-CTR";
        $key = $memberUser->getUserKey();
        $i9Data['form']['nurse']['socialsecurity_number'] = openssl_decrypt($encryptedSSN, $cipher, (string)$key, 0, ord($key));
        // decrypt socsec

        /* Code removed to prevent I9 generation. This was supposed to be W9, but was never completed. Removed by Zac Hiler on 2024-04
	-02 at 11:10AM EST. */

	/*
	$i9 = self::generatei9($i9Data);

        // Do a whole lot of determining if i9 file is tagged correctly
        // Create tag if nedcessary, but just make sure it's tagged
        try {

            $i9File = ioc::getRepository('saFile')->findOneBy(['id' => $i9['i9_file']['id']]);
            if ($i9File) {
                $application2->addApplicationFile($i9File);
                $i9File->setNurseApplicationPartTwo($application2);
                if (!$i9File->getTag()) {
                    /** @var NstFileTagRepository $fileTagRepo 
                    $fileTagRepo = ioc::getRepository('NstFileTag');
                    $i9Tag = $fileTagRepo->findOneBy(['name' => 'i-9']);
                    if (!$i9Tag) {
                        $i9Tag = $fileTagRepo->createNewTagByName('i-9', 'i-9', 'Nurse', false);
                    }
                    $i9File->setTag($i9Tag);
                }
            }
        } catch(\Throwable $t) {
            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . DIRECTORY_SEPARATOR . 'willtest.txt',
            'ATTEMPTING TO TAG AND SAVE i9 FILE CORRECTLY: ' . $t->getMessage() . PHP_EOL, FILE_APPEND);
        }
	*/
        app::$entityManager->flush();

        $service = new NurseApplicationService();
        $service->approveNurse($data);

        app::$entityManager->flush();

        $emailData['email'] = $memberUser->getUsername();
        static::sendApprovedEmail($emailData);

        $response['success'] = true;
        return $response;
    }

    public static function declineNurse($data)
    {
        $response = ['success' => false];

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->find($data['application_id']);

        $application->setDeclinedAt(new DateTime());

        $emailData['declineMessage'] = $data['declineMessage'];
        $emailData['email'] = $application->getMember()->getUsers()[0]->getUsername();
        //static::sendDeclinedEmail($emailData);

        $response['success'] = true;
        return $response;
    }

    public static function generatei9($data)
    {
        $response['success'] = false;
        $location = self::moduleLocation();
        $filePath = $location . '/other/i9-template.pdf';
        $filename = 'i9.pdf';

        $middleInitial = substr($data['form']['nurse']['middle_name'], 0, 1);
        $firstThreeSocial = substr($data['form']['nurse']['socialsecurity_number'], 0, 3);
        $middleTwoSocial = substr($data['form']['nurse']['socialsecurity_number'], 4, 2);
        $lastFourSocial = substr($data['form']['nurse']['socialsecurity_number'], 7, 4);
        if ($data['form']['nurse']['authorized_to_work_in_the_us'] == "Yes") {
            $nonCitizen = "No";
        } else $nonCitizen = "Yes";

        // State abbreviations on pdf are unintuitive, these abbreviations are actually correct
        if ($data['form']['nurse']['state'] == "Alabama") {
            $nurseState = "AK";
        } else if ($data['form']['nurse']['state'] == "Alaska") {
            $nurseState = "AL";
        } else if ($data['form']['nurse']['state'] == "Arkansas") {
            $nurseState = "AZ";
        } else if ($data['form']['nurse']['state'] == "Arizona") {
            $nurseState = "CA";
        } else if ($data['form']['nurse']['state'] == "California") {
            $nurseState = "CO";
        } else if ($data['form']['nurse']['state'] == "Colorado") {
            $nurseState = "CT";
        } else if ($data['form']['nurse']['state'] == "Connecticut") {
            $nurseState = "DE";
        } else if ($data['form']['nurse']['state'] == "Deleware") {
            $nurseState = "GA";
        } else if ($data['form']['nurse']['state'] == "District of Columbia") {
            $nurseState = "FL";
        } else if ($data['form']['nurse']['state'] == "Florida") {
            $nurseState = "GU";
        } else if ($data['form']['nurse']['state'] == "Georgia") {
            $nurseState = "HI";
        } else if ($data['form']['nurse']['state'] == "Guam") {
            $nurseState = "IA";
        } else if ($data['form']['nurse']['state'] == "Hawaii") {
            $nurseState = "ID";
        } else if ($data['form']['nurse']['state'] == "Idaho") {
            $nurseState = "IN";
        } else if ($data['form']['nurse']['state'] == "Illinois") {
            $nurseState = "KS";
        } else if ($data['form']['nurse']['state'] == "Indiana") {
            $nurseState = "KY";
        } else if ($data['form']['nurse']['state'] == "Iowa") {
            $nurseState = "IL";
        } else if ($data['form']['nurse']['state'] == "Kansas") {
            $nurseState = "LA";
        } else if ($data['form']['nurse']['state'] == "Kentucky") {
            $nurseState = "MA";
        } else if ($data['form']['nurse']['state'] == "Louisiana") {
            $nurseState = "MD";
        } else if ($data['form']['nurse']['state'] == "Maine") {
            $nurseState = "MN";
        } else if ($data['form']['nurse']['state'] == "Marshall Islands") {
            $nurseState = "MT";
        } else if ($data['form']['nurse']['state'] == "Maryland") {
            $nurseState = "MI";
        } else if ($data['form']['nurse']['state'] == "Massachusetts") {
            $nurseState = "ME";
        } else if ($data['form']['nurse']['state'] == "Michigan") {
            $nurseState = "MO";
        } else if ($data['form']['nurse']['state'] == "Minnesota") {
            $nurseState = "MP";
        } else if ($data['form']['nurse']['state'] == "Mississippi") {
            $nurseState = "NC";
        } else if ($data['form']['nurse']['state'] == "Missouri") {
            $nurseState = "MS";
        } else if ($data['form']['nurse']['state'] == "Montana") {
            $nurseState = "ND";
        } else if ($data['form']['nurse']['state'] == "Nebraska") {
            $nurseState = "NJ";
        } else if ($data['form']['nurse']['state'] == "Nevada") {
            $nurseState = "OH";
        } else if ($data['form']['nurse']['state'] == "New Hampshire") {
            $nurseState = "NM";
        } else if ($data['form']['nurse']['state'] == "New Jersey") {
            $nurseState = "NV";
        } else if ($data['form']['nurse']['state'] == "New Mexico") {
            $nurseState = "NY";
        } else if ($data['form']['nurse']['state'] == "New York") {
            $nurseState = "OK";
        } else if ($data['form']['nurse']['state'] == "North Carolina") {
            $nurseState = "NE";
        } else if ($data['form']['nurse']['state'] == "North Dakota") {
            $nurseState = "NH";
        } else if ($data['form']['nurse']['state'] == "Ohio") {
            $nurseState = "OR";
        } else if ($data['form']['nurse']['state'] == "Oklahoma") {
            $nurseState = "PA";
        } else if ($data['form']['nurse']['state'] == "Oregon") {
            $nurseState = "PR";
        } else if ($data['form']['nurse']['state'] == "Pennsylvania") {
            $nurseState = "RI";
        } else if ($data['form']['nurse']['state'] == "Rhode Island") {
            $nurseState = "SD";
        } else if ($data['form']['nurse']['state'] == "South Carolina") {
            $nurseState = "TN";
        } else if ($data['form']['nurse']['state'] == "South Dakota") {
            $nurseState = "TX";
        } else if ($data['form']['nurse']['state'] == "Tennessee") {
            $nurseState = "UT";
        } else if ($data['form']['nurse']['state'] == "Texas") {
            $nurseState = "VA";
        } else if ($data['form']['nurse']['state'] == "Utah") {
            $nurseState = "VI";
        } else if ($data['form']['nurse']['state'] == "Vermont") {
            $nurseState = "WI";
        } else if ($data['form']['nurse']['state'] == "Virginia") {
            $nurseState = "WA";
        } else if ($data['form']['nurse']['state'] == "Washington") {
            $nurseState = "WV";
        } else if ($data['form']['nurse']['state'] == "West Virginia") {
            $nurseState = "CAN";
        } else if ($data['form']['nurse']['state'] == "Wisconsin") {
            $nurseState = "WY";
        } else if ($data['form']['nurse']['state'] == "Wyoming") {
            $nurseState = "MEX";
        }

        $fillFields = [
            'topmostSubform[0].Page1[0].Last_Name_Family_Name[0]' => $data['form']['nurse']['last_name'],
            'topmostSubform[0].Page1[0].First_Name_Given_Name[0]' => $data['form']['nurse']['first_name'],
            'topmostSubform[0].Page1[0].Middle_Initial[0]' => $middleInitial,
            'topmostSubform[0].Page1[0].Address_Street_Number_and_Name[0]' => $data['form']['nurse']['street_address'],
            'topmostSubform[0].Page1[0].Apt_Number[0]' => $data['form']['nurse']['street_address_two'],
            'topmostSubform[0].Page1[0].City_or_Town[0]' => $data['form']['nurse']['city'],
            'topmostSubform[0].Page1[0].ZIP_Code[0]' => $data['form']['nurse']['zip_code'],
            'topmostSubform[0].Page1[0].State[0]' => $nurseState,
            'topmostSubform[0].Page1[0].Date_of_Birth_mmddyyyy[0]' => $data['form']['nurse']['date_of_birth'],
            'topmostSubform[0].Page1[0].U\.S\._Social_Security_Number__First_3_Numbers_[0]' => $firstThreeSocial,
            'topmostSubform[0].Page1[0].U\.S\._Social_Security_Number__Next_2_numbers_[0]' => $middleTwoSocial,
            'topmostSubform[0].Page1[0].U\.S\._Social_Security_Number__Last_4_numbers_[0]' => $lastFourSocial,
            'topmostSubform[0].Page1[0].Employees_Email_Address[0]' => $data['form']['nurse']['email'],
            'topmostSubform[0].Page1[0].Employees_Telephone_Number[0]' => $data['form']['nurse']['phone_number'],
            'topmostSubform[0].Page1[0]._1\._A_citizen_of_the_United_States[0]' => $data['form']['nurse']['authorized_to_work_in_the_us'],
            'topmostSubform[0].Page1[0]._2\._A_noncitizen_national_of_the_United_States__See_instructions_[0]' => $nonCitizen,
            'topmostSubform[0].Page1[0].I_did_not_use_a_preparer_or_translator[0]' => "Yes",
        ];

        // Generate pdf in memory (does not save to disk)
        $pdf = new Pdf($filePath);
        $result = $pdf->fillForm($fillFields)->flatten();
        if (!$result) {
            $error = $pdf->getError();
            return $error;
        }

        // generate new saFile with the string data of the PDF
        $saf = new saFile;
        $file = $saf->saveStringToFile(time() . $filename, $pdf->toString());

        $newFile['id'] = $file->getId();
        $newFile['filename'] = $file->getFileName();
        $newFile['route'] = app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $file->getFolder(), 'file' => $file->getFilename()]);
        $newFile['fileTag'] = 'i-9';
        $newFile['tag'] = 'i9';

        $response['i9_file'] = $newFile;
        $response['success'] = true;
        return $response;
    }

    public static function generate1099($data)
    {
        $response['success'] = false;

        /** @var Nurse $nurse */
        $nurse = ioc::getRepository('Nurse')->findOneBy(['id' => $data['id']]);

        // remove any previous 1099
        $pastFileId = ioc::getRepository('saFile')->nurseHasFileWithTagInYear($data['year'], $data['tag_id'], $data['id'])['file_id'];
        while ($pastFileId) {
            /** @var NstFile $file */
            $pastFile = ioc::getRepository('saFile')->findOneBy(['id' => $pastFileId]);
            $nurse->removeNurseFile($pastFile);
            app::$entityManager->remove($pastFile);
            app::$entityManager->flush();

            $pastFileId = ioc::getRepository('saFile')->nurseHasFileWithTagInYear($data['year'], $data['tag_id'], $data['id'])['file_id'];
        }

        $location = self::moduleLocation();
        $filePath = $location . '/other/f1099nec.pdf';
        $filename = $data['file_name'] . '1099.pdf';

        $lastYear = date('y', strtotime('-1 year'));
        $yearLastTwoDigits = $lastYear;

        $payersInfo = "NurseStat LLC\n226 Morris Drive\nHarrodsburg, KY 40330\n859-748-9600";
        $recipientName = $data['name'];
        $streetAddAndAptNum = $data['street_and_apt'];
        $cityStateCountryZip = $data['city_state_country_zip'];
        $socialSecurity = $data['social_security'];
        $payerTin = "27-0728996";
        $compensation = $data['compensation'];

        $fillFields = [
            // Form A for IRS
            //'topmostSubform[0].CopyA[0].PgHeader[0].CalendarYear[0].f1_1[0]' => $yearLastTwoDigits,
            'topmostSubform[0].CopyA[0].PgHeader[0].f1_1[0]' => $yearLastTwoDigits,
            'topmostSubform[0].CopyA[0].LeftColumn[0].f1_2[0]' => $payersInfo,
            'topmostSubform[0].CopyA[0].LeftColumn[0].f1_3[0]' => $payerTin,
            'topmostSubform[0].CopyA[0].LeftColumn[0].f1_4[0]' => $socialSecurity,
            'topmostSubform[0].CopyA[0].LeftColumn[0].f1_5[0]' => $recipientName,
            'topmostSubform[0].CopyA[0].LeftColumn[0].f1_6[0]' => $streetAddAndAptNum,
            'topmostSubform[0].CopyA[0].LeftColumn[0].f1_7[0]' => $cityStateCountryZip,
            'topmostSubform[0].CopyA[0].RightColumn[0].f1_9[0]' => $compensation,
            // Copy 1 for state tax department
            'topmostSubform[0].Copy1[0].Copy1Header[0].CalendarYear[0].f2_1[0]' => $yearLastTwoDigits,
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_2[0]' => $payersInfo,
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_3[0]' => $payerTin,
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_4[0]' => $socialSecurity,
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_5[0]' => $recipientName,
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_6[0]' => $streetAddAndAptNum,
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_7[0]' => $cityStateCountryZip,
            'topmostSubform[0].Copy1[0].RightColumn[0].f2_9[0]' => $compensation,
            // Copy B for Recipient
            'topmostSubform[0].CopyB[0].CopyBHeader[0].CalendarYear[0].f2_1[0]' => $yearLastTwoDigits,
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_2[0]' => $payersInfo,
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_3[0]' => $payerTin,
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_4[0]' => $socialSecurity,
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_5[0]' => $recipientName,
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_6[0]' => $streetAddAndAptNum,
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_7[0]' => $cityStateCountryZip,
            'topmostSubform[0].CopyB[0].RightColumn[0].f2_9[0]' => $compensation,
            // Copy 2 To be filed with recipient's state income tax return, when required.
            'topmostSubform[0].Copy2[0].CopyCHeader[0].CalendarYear[0].f2_1[0]' => $yearLastTwoDigits,
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_2[0]' => $payersInfo,
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_3[0]' => $payerTin,
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_4[0]' => $socialSecurity,
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_5[0]' => $recipientName,
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_6[0]' => $streetAddAndAptNum,
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_7[0]' => $cityStateCountryZip,
            'topmostSubform[0].Copy2[0].RightColumn[0].f2_9[0]' => $compensation,
            // Copy C for Payer
            'topmostSubform[0].CopyC[0].CopyCHeader[0].CalendarYear[0].f2_1[0]' => $yearLastTwoDigits,
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_2[0]' => $payersInfo,
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_3[0]' => $payerTin,
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_4[0]' => $socialSecurity,
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_5[0]' => $recipientName,
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_6[0]' => $streetAddAndAptNum,
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_7[0]' => $cityStateCountryZip,
            'topmostSubform[0].CopyC[0].RightColumn[0].f2_9[0]' => $compensation
        ];

        // Generate pdf in memory (does not save to disk)
        $pdf = new Pdf($filePath);
        $content = $pdf->fillForm($fillFields)->flatten()->toString();
        $error = $pdf->getError();
        if ($error != "") {
            return $error;
        }

        // generate new saFile with the string data of the PDF
        $saf = new saFile;
        /** @var NstFile $file */
        $currentFile = $saf->saveStringToFile($filename, $content);
        app::$entityManager->persist($currentFile);

        /** @var NstFileTag $fileTag */
        $fileTag = ioc::getRepository('NstFileTag')->findOneBy(['id' => $data['tag_id']]);
        $currentFile->setTag($fileTag);

        // add new generated 1099 to nurse
        $nurse->addNurseFile($currentFile);

        // set nurse to file
        $currentFile->setNurse($nurse);

        // persist data
        $application = ioc::getRepository('NurseApplication')->find($data['id']);
        $application->setFiles(json_encode($data['form']['files']));
        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }

    /**
     * @throws ModRequestAuthenticationException
     */
    public static function sendApprovedEmail($data)
    {
        modRequest::request('messages.startEmailBatch');
        modRequest::request('messages.sendEmail', [
            'to' => $data['email'],
            'body' => '
                    <h1 style="text-align: center">NurseStat</h1>
                    
                    <p>Your application has been approved! Please click the link below to download the app.</p> <p>Thank you for completing this portion of the application. Our
                    next steps will be to:</p>

                    <ul class="bulleted">
                        <li>
                            Conduct a facetime interview where we will review the
                            agreements, expectations and answer all your questions
                        </li>
                        <li>
                            Complete a drug screen (10-panel or better)
                        </li>
                        <li>
                            Send us required Documentation
                        </li>
                        <li>
                            Complete I-9 Form
                        </li>
                    </ul>
                    <br>

                    <p>Items we need from you include:</p>

                    <ol class="bulleted">
                        <li>Covid Vaccination Card or exemption form
                            , TB
                            skin test and CPR Card (nurses). Pictures of these can be
                            texted to our office @ 8597489600, or emailed to
                            app@nursestatky.com
                        </li>
                    </ol>

                    <p>For more information on how to complete the drug screen,
                        please contact our office at
                        <a href="tel:+8597489600">859-748-9600</a></p>

                        <p><b><u>Danville</u></b></p>
                        <p>
                            <u>Guardian Support Services</u><br>
                            380 Whirl Away Dr #1<br>
                            Danville, KY 40422<br>
                            859-236-6002
                        </p>
                        <p><b><u>Richmond</u></b></p>
                        <p>
                            <u>Baptist Health Occ</u><br>
                            648 University Shopping Center<br>
                            Richmond, KY 40475<br>
                            859-623-0535
                        </p>
                        <p><b><u>Lexington</u></b></p>
                        <p>
                            <u>Baptist Health Occ</u><br>
                            1051 Newtown Pike #100<br>
                            Lexington, KY 40511<br>
                            859-253-0076<br><br>
                            <u>Baptist Health Urgent Care</u><br>
                            610 Brannon Rd Suite 100<br>
                            Nicholasville, KY 40356<br>
                            859-260-5540
                        </p>
                        <p><b><u>Elizabethtown</u></b></p>
                        <p>
                            <u>WorkWell (SM)</u><br>
                            Occupational Health Service<br>
                            400 Ring Rd #148<br>
                            Elizabethtown, KY 42701<br>
                            270-706-5621
                        </p>
                        <p><b><u>Lebanon</u></b></p>
                        <p>
                            <u>Industrial Choice Healthcare, PLLC</u><br>
                            108 Cemetery Rd<br>
                            Lebanon, KY 40033<br>
                            270-692-2569
                        </p>
                        <p><b><u>Shelbyville</u></b></p>
                        <p>
                            <u>Baptist Health Occ</u><br>
                            101 Stonecrest Rd #1<br>
                            Shelbyville, KY 40065<br>
                            502-633-2233
                        </p>
                        <p><b><u>London</u></b></p>
                        <p>
                            <u>Select Lab</u><br>
                            140 East 5th St<br>
                            London, KY 40741<br>
                            606-864-9731
                        </p>
                        <p><b><u>Louisville</u></b></p>
                        <p>
                            <u>Baptist Health Occ</u><br>
                            11630 Commonwealth Dr #300<br>
                            Louisville, KY 40299<br>
                            502-267-6292
                        </p>
                        <p>
                            <u>Baptist Health Occ</u><br>
                            3303 Fern Valley Rd<br>
                            Louisville, KY 40299<br>
                            502-964-4889
                        </p>
                        <p>
                            <u>Baptist Health Occ</u><br>
                            7092 Distribution Dr<br>
                            Louisville, KY 40258<br>
                            502-935-9970
                        </p>
                        <p><b><u>Hazard</u></b></p>
                        <p>
                            <u>Little Flower Clinic</u><br>
                            279 East Main St<br>
                            Hazard, KY 41701<br>
                            606-487-9505
                        </p>
                        <p>Ask for a <u>Quick Screen 10-Panel</u> for NurseStat.</p>
                        <p>Send us a picture of your receipt (text).</p>

                    <p>Thank you for your interest in NurseStat. We
                        look
                        forward to working with you.</p>

                    <p><a href="https://apps.apple.com/us/app/nursestat/id1601856975">iPhone / iPad</a> or <a href="https://play.google.com/store/apps/details?id=com.nursestatky.nursestat&hl=en_US&gl=US">Android</a></p>',
            'subject' => 'NurseStat - You\'ve been approved!'
        ]);
        modRequest::request('messages.commitEmailBatch');
    }

    /**
     * @throws ModRequestAuthenticationException
     */
    public static function sendDeclinedEmail($data)
    {
        modRequest::request('messages.startEmailBatch');
        modRequest::request('messages.sendEmail', [
            'to' => $data['email'],
            'body' => '<h1 style="text-align: center">NurseStat</h1><p>Your application has been declined.</p>',
            'subject' => 'NurseStat - Your application has been declined'
        ]);
        modRequest::request('messages.commitEmailBatch');
    }

    public function maskPhoneInput($data)
    {
        $length = strlen($data);

        if ($length >= 7) {
            $data = substr_replace($data, '-', 6, 0);
            $data = substr_replace($data, ' ', 3, 0);
            $data = substr_replace($data, ')', 3, 0);
            $data = substr_replace($data, '(', 0, 0);
        } else if ($length >= 4) {
            $data = substr_replace($data, ' ', 3, 0);
            $data = substr_replace($data, ')', 3, 0);
            $data = substr_replace($data, '(', 0, 0);
        } else if ($length >= 1) {
            $data = substr_replace($data, '(', 0, 0);
        }

        return $data;
    }

    public static function loadApplicationData($data)
    {
        $response = ['success' => false];

        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['application_id']]);
        $member = $application->getMember();
        $memberUser = $member->getUsers()[0];
        $application2 = $member->getApplicationPart2();
        $applicationStatus = $application2?->getApplicationStatus();

        $nurseApplicationService = new NurseApplicationService();
        if ($applicationStatus?->getLicenseVerified()) {
            $nurseApplicationService->loadDrugScreenProgress($data);
        }

        if ($applicationStatus?->getDrugScreenAccepted()) {
            $nurseApplicationService->loadBackgroundCheckProgress($data);
        }

        // decrypt soc sec
        $socSec = $application->getSocSec();
        $cipher = "AES-128-CTR";
        $key = $memberUser->getUserKey();
        $decryptedSocSec = openssl_decrypt($socSec, $cipher, $key, 0, ord($key));

        $response['basic_info'] = [

            'first_name' => $member->getFirstName(),
            'middle_name' => $member->getMiddleName(),
            'last_name' => $member->getLastName(),

            'street_address' => $application->getStreetAddress(),
            'street_address_2' => $application->getStreetAddress2(),
            'city' => $application->getCity(),
            'state' => $application->getState(),
            'zipcode' => $application->getZipCode(),

            'dob' => $application->getDOB(),
            'ssn' => $decryptedSocSec,
            'citizen_of_us' => $application->getIsCitizen(),
            'authorized_to_work_in_us' => $application->getIsAllowedToWork(),

            'position' => $application->getPosition(),
            'explanation' => $application->getExplanation(),

            'email' => $memberUser->getUsername(),
            'phone' => $application->getPhoneNumber(),
        ];

        $licensesAndCerts = [];
        foreach ($application->getLicenseAndCertifications() as $certification) {

            if (count($licensesAndCerts) == 0) {
                $licensesAndCerts[] = $certification;
            } else {
                $licensesAndCerts[] = " " . $certification;
            }
        }

        $medicalHistory = [];
        foreach ($application2->getMedicalHistory() as $condition) {

            if (count($medicalHistory) == 0) {
                $medicalHistory[] = $condition;
            } else {
                $medicalHistory[] = " " . $condition;
            }
        }

        $response['application'] = [

            // page 2
            'one_year_ltc_experience' => $application->getOneYearLTCExperience(),
            'one_year_experience_explanation' => $application->getOneYearExplanation(),
            'currently_employed' => $application->getCurrentlyEmployed(),

            'company1' => [

                'name' => $application->getCompany1CompanyName(),
                'supervisor_name' => $application->getCompany1SupervisorName(),
                'address' => $application->getCompany1CompanyAddress(),
                'city' => $application->getCompany1CompanyCity(),
                'state' => $application->getCompany1CompanyState(),
                'zipcode' => $application->getCompany1CompanyZip(),
                'phone' => $application->getCompany1CompanyPhone(),
                'email' => $application->getCompany1CompanyEmail(),
                'job_title' => $application->getCompany1JobTitle(),
                'start_date' => $application->getCompany1StartDate(),
                'end_date' => $application->getCompany1EndDate(),
                'responsibilities' => $application->getCompany1Responsibilites(),
                'reason_for_leaving' => $application->getCompany1ReasonForLeaving(),
                'may_we_contact_employer' => $application->getCompany1MayWeContactEmployer(),
            ],

            'company2' => [

                'name' => $application->getCompany2CompanyName(),
                'supervisor_name' => $application->getCompany2SupervisorName(),
                'address' => $application->getCompany2CompanyAddress(),
                'city' => $application->getCompany2CompanyCity(),
                'state' => $application->getCompany2CompanyState(),
                'zipcode' => $application->getCompany2CompanyZip(),
                'phone' => $application->getCompany2CompanyPhone(),
                'email' => $application->getCompany2CompanyEmail(),
                'job_title' => $application->getCompany2JobTitle(),
                'start_date' => $application->getCompany2StartDate(),
                'end_date' => $application->getCompany2EndDate(),
                'responsibilities' => $application->getCompany2Responsibilites(),
                'reason_for_leaving' => $application->getCompany2ReasonForLeaving(),
                'may_we_contact_employer' => $application->getCompany2MayWeContactEmployer(),
            ],

            'company3' => [

                'name' => $application->getCompany3CompanyName(),
                'supervisor_name' => $application->getCompany3SupervisorName(),
                'address' => $application->getCompany3CompanyAddress(),
                'city' => $application->getCompany3CompanyCity(),
                'state' => $application->getCompany3CompanyState(),
                'zipcode' => $application->getCompany3CompanyZip(),
                'phone' => $application->getCompany3CompanyPhone(),
                'email' => $application->getCompany3CompanyEmail(),
                'job_title' => $application->getCompany3JobTitle(),
                'start_date' => $application->getCompany3StartDate(),
                'end_date' => $application->getCompany3EndDate(),
                'responsibilities' => $application->getCompany3Responsibilites(),
                'reason_for_leaving' => $application->getCompany3ReasonForLeaving(),
                'may_we_contact_employer' => $application->getCompany3MayWeContactEmployer(),
            ],

            // page 3
            'hs_or_ged' => $application->getHSorGED(),

            'college' => [

                'name' => $application->getCollegeName(),
                'city' => $application->getCollegeCity(),
                'state' => $application->getCollegeState(),
                'year_graduated' => $application->getCollegeGraduated(),
                'subjects_major_degree' => $application->getCollegeSubjects(),
            ],

            'ged' => [

                'name' => $application->getGEDName(),
                'city' => $application->getGEDCity(),
                'state' => $application->getGEDState(),
                'year_graduated' => $application->getGEDYearGraduated(),
            ],

            'high_school' => [

                'name' => $application->getHSName(),
                'city' => $application->getHSCity(),
                'state' => $application->getHSState(),
                'year_graduated' => $application->getHSYearGraduated(),
            ],

            'other' => [

                'name' => $application->getOtherEducationName(),
                'city' => $application->getOtherEducationCity(),
                'state' => $application->getOtherEducationState(),
                'year_graduated' => $application->getOtherEducationYearGraduated(),
                'subjects_major_degree' => $application->getOtherEducationSubjects(),
            ],

            // page 4
            'reference1' => [

                'name' => $application->getProfessionalReferenceOneName(),
                'relationship' => $application->getProfessionalReferenceOneRelationship(),
                'company' => $application->getProfessionalReferenceOneCompany(),
                'phone' => $application->getProfessionalReferenceOnePhone(),
            ],

            'reference2' => [

                'name' => $application->getProfessionalReferenceTwoName(),
                'relationship' => $application->getProfessionalReferenceTwoRelationship(),
                'company' => $application->getProfessionalReferenceTwoCompany(),
                'phone' => $application->getProfessionalReferenceTwoPhone(),
            ],

            'reference3' => [

                'name' => $application->getProfessionalReferenceThreeName(),
                'relationship' => $application->getProfessionalReferenceThreeRelationship(),
                'company' => $application->getProfessionalReferenceThreeCompany(),
                'phone' => $application->getProfessionalReferenceThreePhone(),
            ],

            'licenses_and_certifications' => $licensesAndCerts,

            // page 5
            // 'signature' => $application->getAgreementSignature(),

            // page 6
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

            // page 7
            'pay_type' => $application2->getPayType(),
            'account_type' => $application2->getAccountType(),
            'account_number' => $application2->getAccountNumber(),
            'routing_number' => $application2->getRoutingNumber(),
            'bank_name' => $application2->getBankName(),

            'heard_about_us' => $application2->getHeardAboutUs(),
            'heard_about_us_other' => $application2->getHeardAboutUsOther(),
            'referrer' => $application2->getReferrer(),
        ];

        $tags = [];
        foreach ($application2->getApplicationFiles() as $file) {

            $tag = $file->getTag()->getName();
            $tags[] = $tag;

            switch ($tag) {

                case 'Nurse License 1':

                    $response['files']['licenses']['nurse_license_1'] = [
                        'url' => app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id' => $file->getId()]),
                        'id' => $file->getId(),
                        'name' => $file->getFileName() ? $file->getFileName() : '',
                        'fileTag' => $tag,

                        'file' => [

                            'date' => '',
                            'id' => $file->getId(),
                            'name' => $file->getFileName() ? $file->getFileName() : '',
                            'size' => '',
                            'type' => '',
                            'url' => '',
                        ],

                        'state' => $application2->getLicense1State(),
                        'license_number' => $application2->getLicense1Number(),
                        'full_name' => $application2->getLicense1FullName(),
                        'expiration' => $application2->getLicense1Expiration(),
                        'accepted' => $application2->getLicense1Accepted(),
                    ];
                    break;

                case 'Nurse License 2':

                    $response['files']['licenses']['nurse_license_2'] = [
                        'url' => app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id' => $file->getId()]),
                        //'url' => app::get()->getRouter()->generate('files_browser_view_file', ['folder' => 'uploads', 'file' => $file->getFileName()]),
                        'id' => $file->getId(),
                        'name' => $file->getFileName() ? $file->getFileName() : '',
                        'fileTag' => $tag,

                        'file' => [

                            'date' => '',
                            'id' => $file->getId(),
                            'name' => $file->getFileName() ? $file->getFileName() : '',
                            'size' => '',
                            'type' => '',
                            'url' => '',
                        ],

                        'state' => $application2->getLicense2State(),
                        'license_number' => $application2->getLicense2Number(),
                        'full_name' => $application2->getLicense2FullName(),
                        'expiration' => $application2->getLicense2Expiration(),
                        'accepted' => $application2->getLicense2Accepted(),
                    ];

                    break;

                case 'Nurse License 3':

                    $response['files']['licenses']['nurse_license_3'] = [
                        'url' => app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id' => $file->getId()]),
                        //'url' => app::get()->getRouter()->generate('files_browser_view_file', ['folder' => 'uploads', 'file' => $file->getFileName()]),
                        'id' => $file->getId(),
                        'name' => $file->getFileName() ? $file->getFileName() : '',
                        'fileTag' => $tag,

                        'file' => [

                            'date' => '',
                            'id' => $file->getId(),
                            'name' => $file->getFileName() ? $file->getFileName() : '',
                            'size' => '',
                            'type' => '',
                            'url' => '',
                        ],

                        'state' => $application2->getLicense3State(),
                        'license_number' => $application2->getLicense3Number(),
                        'full_name' => $application2->getLicense3FullName(),
                        'expiration' => $application2->getLicense3Expiration(),
                        'accepted' => $application2->getLicense3Accepted(),
                    ];

                    break;

                case 'Driver License':

                    $response['files']['files']['driver_license'] = [
                        'url' => app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id' => $file->getId()]),
                        //'url' => app::get()->getRouter()->generate('files_browser_view_file', ['folder' => 'uploads', 'file' => $file->getFileName()]),
                        'id' => $file->getId(),
                        'name' => $file->getFileName() ? $file->getFileName() : '',
                        'fileTag' => $tag,

                        'file' => [

                            'date' => '',
                            'id' => $file->getId(),
                            'name' => $file->getFileName() ? $file->getFileName() : '',
                            'size' => '',
                            'type' => '',
                            'url' => '',
                        ]
                    ];

                    break;

                case 'Social Security':

                    $response['files']['files']['social_security'] = [
                        'url' => app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id' => $file->getId()]),
                        //'url' => app::get()->getRouter()->generate('files_browser_view_file', ['folder' => 'uploads', 'file' => $file->getFileName()]),
                        'id' => $file->getId(),
                        'name' => $file->getFileName() ? $file->getFileName() : '',
                        'fileTag' => $tag,

                        'file' => [

                            'date' => '',
                            'id' => $file->getId(),
                            'name' => $file->getFileName() ? $file->getFileName() : '',
                            'size' => '',
                            'type' => '',
                            'url' => '',
                        ]
                    ];

                    break;

                case 'TB Skin Test':

                    $response['files']['files']['tb_skin_test'] = [
                        'url' => app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id' => $file->getId()]),
                        //'url' => app::get()->getRouter()->generate('files_browser_view_file', ['folder' => 'uploads', 'file' => $file->getFileName()]),
                        'id' => $file->getId(),
                        'name' => $file->getFileName() ? $file->getFileName() : '',
                        'fileTag' => $tag,

                        'file' => [

                            'date' => '',
                            'id' => $file->getId(),
                            'name' => $file->getFileName() ? $file->getFileName() : '',
                            'size' => '',
                            'type' => '',
                            'url' => '',
                        ]
                    ];

                    break;

                case 'CPR Card':

                    $response['files']['files']['cpr_card'] = [
                        'url' => app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id' => $file->getId()]),
                        //'url' => app::get()->getRouter()->generate('files_browser_view_file', ['folder' => 'uploads', 'file' => $file->getFileName()]),
                        'id' => $file->getId(),
                        'name' => $file->getFileName() ? $file->getFileName() : '',
                        'fileTag' => $tag,

                        'file' => [

                            'date' => '',
                            'id' => $file->getId(),
                            'name' => $file->getFileName() ? $file->getFileName() : '',
                            'size' => '',
                            'type' => '',
                            'url' => '',
                        ]
                    ];

                    break;

                case 'BLS ACL':

                    $response['files']['files']['bls_acl'] = [
                        'url' => app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id' => $file->getId()]),
                        //'url' => app::get()->getRouter()->generate('files_browser_view_file', ['folder' => 'uploads', 'file' => $file->getFileName()]),
                        'id' => $file->getId(),
                        'name' => $file->getFileName() ? $file->getFileName() : '',
                        'fileTag' => $tag,

                        'file' => [

                            'date' => '',
                            'id' => $file->getId(),
                            'name' => $file->getFileName() ? $file->getFileName() : '',
                            'size' => '',
                            'type' => '',
                            'url' => '',
                        ]
                    ];

                    break;

                case 'Covid Vaccine':

                    $response['files']['files']['covid_vaccine'] = [
                        'url' => app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id' => $file->getId()]),
                        //'url' => app::get()->getRouter()->generate('files_browser_view_file', ['folder' => 'uploads', 'file' => $file->getFileName()]),
                        'id' => $file->getId(),
                        'name' => $file->getFileName() ? $file->getFileName() : '',
                        'fileTag' => $tag,

                        'file' => [

                            'date' => '',
                            'id' => $file->getId(),
                            'name' => $file->getFileName() ? $file->getFileName() : '',
                            'size' => '',
                            'type' => '',
                            'url' => ''
                        ]
                    ];

                    break;

                case 'ID Badge':

                    $response['files']['files']['id_badge'] = [
                        'url' => app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id' => $file->getId()]),
                        //'url' => app::get()->getRouter()->generate('files_browser_view_file', ['folder' => 'uploads', 'file' => $file->getFileName()]),
                        'id' => $file->getId(),
                        'name' => $file->getFileName() ? $file->getFileName() : '',
                        'fileTag' => $tag,

                        'file' => [

                            'date' => '',
                            'id' => $file->getId(),
                            'name' => $file->getFileName() ? $file->getFileName() : '',
                            'size' => '',
                            'type' => '',
                            'url' => ''
                        ]
                    ];

                    break;
            }
        }

        $skeleton = [

            'url' => '',
            'id' => '',
            'name' => '',
            'fileTag' => '',

            'file' => [

                'date' => '',
                'id' => '',
                'name' => '',
                'size' => '',
                'type' => '',
                'url' => '',
            ],

            'state' => '',
            'license_number' => '',
            'full_name' => '',
            'expiration' => '',
            'accepted' => '',
        ];

        if (!in_array('Nurse License 1', $tags)) {
            $response['files']['licenses']['nurse_license_1'] = $skeleton;
            $response['files']['licenses']['nurse_license_1']['fileTag'] = 'Nurse License 1';
        }
        if (!in_array('Nurse License 2', $tags)) {
            $response['files']['licenses']['nurse_license_2'] = $skeleton;
            $response['files']['licenses']['nurse_license_2']['fileTag'] = 'Nurse License 2';
        }
        if (!in_array('Nurse License 3', $tags)) {
            $response['files']['licenses']['nurse_license_3'] = $skeleton;
            $response['files']['licenses']['nurse_license_3']['fileTag'] = 'Nurse License 3';
        }
        if (!in_array('Driver License', $tags)) {
            $response['files']['files']['driver_license'] = $skeleton;
            $response['files']['files']['driver_license']['fileTag'] = 'Driver license';
        }
        if (!in_array('Social Security', $tags)) {
            $response['files']['files']['social_security'] = $skeleton;
            $response['files']['files']['social_security']['fileTag'] = 'Social Security';
        }
        if (!in_array('TB Skin Test', $tags)) {
            $response['files']['files']['tb_skin_test'] = $skeleton;
            $response['files']['files']['tb_skin_test']['fileTag'] = 'TB Skin Test';
        }
        if (!in_array('CPR Card', $tags)) {
            $response['files']['files']['cpr_card'] = $skeleton;
            $response['files']['files']['cpr_card']['fileTag'] = 'CPR Card';
        }
        if (!in_array('BLS ACL', $tags)) {
            $response['files']['files']['bls_acl'] = $skeleton;
            $response['files']['files']['bls_acl']['fileTag'] = 'BLS ACL';
        }
        if (!in_array('Covid Vaccine', $tags)) {
            $response['files']['files']['covid_vaccine'] = $skeleton;
            $response['files']['files']['covid_vaccine']['fileTag'] = 'Covid Vaccine';
        }
        if (!in_array('ID Badge', $tags)) {
            $response['files']['files']['id_badge'] = $skeleton;
            $response['files']['files']['id_badge']['fileTag'] = 'ID Badge';
        }

        $response['drug_screen'] = $applicationStatus?->getDrugScreenReport();
        $response['drug_screen']['accepted'] = $applicationStatus?->getDrugScreenAccepted();

        $response['background_check'] = $applicationStatus?->getBackgroundCheckReport();

        /** @var NurseApplicationStatus $applicationStatus */
        $applicationStatus = $application2->getApplicationStatus();

        $applicationSignedTime = $applicationStatus?->getBackgroundCheckSignedTime();
        if ($applicationSignedTime) {
            $response['can_approve'] = true;
        } else {
            $response['can_approve'] = false;
        }

        $response['checkr_domain'] = app::get()->getConfiguration()->get('checkr_base_url')->getValue();

        $response['approved'] = $application?->getApprovedAt();
        $response['declined'] = $application?->getDeclinedAt();

        $response['success'] = true;
        return $response;
    }

    public static function acceptLicense($data)
    {
        $response = ['success' => false];

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['application_id']]);

        /** @var ApplicationPart2 $application2 */
        $application2 = $application->getMember()->getApplicationPart2();

        /** @var ApplicationStatus $applicationStatus */
        $applicationStatus = $application2->getApplicationStatus();

        $smsData['application_id'] = $data['application_id'];
        $smsData['message'] = 'Your license has been verified. You should recieve an invitation to take a drug screen via email.';

        $verifiedStatus = $applicationStatus->getLicenseVerified();

        if ($data['license_number'] == 1) {

            $application2->setLicense1Accepted(true);

            if ($verifiedStatus == false || $verifiedStatus == null) {

                $applicationStatus->setLicenseVerified(true);
                $response['sms_attempt'] = SmsService::sendApplicantSMS($smsData);

                $nurseAppService = new NurseApplicationService();
                $result['start_drug_screen_success'] = $nurseAppService->startDrugScreen($data);
            }

        } else if ($data['license_number'] == 2) {

            $application2->setLicense2Accepted(true);

            if ($verifiedStatus == false || $verifiedStatus == null) {

                $applicationStatus->setLicenseVerified(true);
                $response['sms_attempt'] = SmsService::sendApplicantSMS($smsData);

                $nurseAppService = new NurseApplicationService();
                $result['start_drug_screen_success'] = $nurseAppService->startDrugScreen($data);
            }

        } else if ($data['license_number'] == 3) {

            $application2->setLicense3Accepted(true);

            if ($verifiedStatus == false || $verifiedStatus == null) {

                $applicationStatus->setLicenseVerified(true);
                $response['sms_attempt'] = SmsService::sendApplicantSMS($smsData);

                $nurseAppService = new NurseApplicationService();
                $result['start_drug_screen_success'] = $nurseAppService->startDrugScreen($data);
            }
        }

        if ($data['license_number'] == 1) {
            $application2->setLicense1Expiration($data['license1_expiration']);
        } else if ($data['license_number'] == 2) {
            $application2->setLicense2Expiration($data['license2_expiration']);
        } else if ($data['license_number'] == 3) {
            $application2->setLicense3Expiration($data['license3_expiration']);
        }

        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }

    public static function rejectLicense($data)
    {
        $response = ['success' => false];

        /** @var ApplicationPart1 */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['application_id']]);

        /** @var NstMember */
        $member = $application->getMember();

        /** @var ApplicationPart2 */
        $application2 = $member->getApplicationPart2();

        if ($data['license_number'] == 1) {
            $application2->setLicense1Accepted(false);
        } else if ($data['license_number'] == 2) {
            $application2->setLicense2Accepted(false);
        } else if ($data['license_number'] == 3) {
            $application2->setLicense3Accepted(false);
        }

        if ($data['license_number'] == 1) {

            $nurseLicense1 = ioc::getRepository('NstFile')->findOneBy(['id' => $data['license1_id']]);
            $nurseLicense1->setNurseApplicationPartTwo(null);
            app::$entityManager->remove($nurseLicense1);

        } else if ($data['license_number'] == 2) {

            $nurseLicense2 = ioc::getRepository('NstFile')->findOneBy(['id' => $data['license2_id']]);
            $nurseLicense2->setNurseApplicationPartTwo(null);
            app::$entityManager->remove($nurseLicense2);

        } else if ($data['license_number'] == 3) {

            $nurseLicense3 = ioc::getRepository('NstFile')->findOneBy(['id' => $data['license3_id']]);
            $nurseLicense3->setNurseApplicationPartTwo(null);
            app::$entityManager->remove($nurseLicense3);
        }

        // send applicant sms message rejection
        $response['sms_attempt'] = SmsService::sendApplicantSMS($data);

        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }

    public static function setAgreementPDF($data)
    {
        $response = ['success' => false];

        /** @var NstFileTagRepository $fileTagRepo */
        $fileTagRepo = ioc::getRepository('NstFileTag');

        /** @var NstFileTag $appAgreementFileTag */
        $appAgreementFileTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => 'Application Agreement']);
        if (!$appAgreementFileTag) {

            $appAgreementFileTag = $fileTagRepo->createNewTagByName('Application Agreement', 'Application Agreement', 'Nurse', false);
            app::$entityManager->persist($appAgreementFileTag);
        }

        /** @var NstFileRepository $fileRepository */
        $fileRepository = ioc::getRepository('NstFile');

        $previousAgreementArray = $fileRepository->findFileByFileTag($appAgreementFileTag->getId());
        foreach ($previousAgreementArray as $previousAgreement) {

            $previousAgreement = ioc::getRepository('NstFile')->findOneBy(['id' => $previousAgreement['id']]);
            if ($previousAgreement) {
                app::$entityManager->remove($previousAgreement);
            }
        }

        $newAgreement = ioc::getRepository('NstFile')->findOneBy(['id' => (int) $data['file']['id']]);
        $newAgreement->setTag($appAgreementFileTag);

        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }

    public static function setBackgroundCheckAgreement($data)
    {
        $response = ['success' => false];

        /** @var NstFileTag $appAgreementFileTag */
        $backgroundCheckAgreementFileTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => 'Background Check Agreement']);
        if (!$backgroundCheckAgreementFileTag) {

            /** @var NstFileTagRepository $fileTagRepo */
            $fileTagRepo = ioc::getRepository('NstFileTag');

            $backgroundCheckAgreementFileTag = $fileTagRepo->createNewTagByName('Background Check Agreement', 'Background Check Agreement', 'Nurse', false);
            app::$entityManager->persist($backgroundCheckAgreementFileTag);
        }

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['application_id']]);

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var NstFile $nurseFiles */
        $nurseFiles = $application2->getApplicationFiles();
        foreach ($nurseFiles as $file) {

            if ($file->getTag()->getId() == $backgroundCheckAgreementFileTag->getId()) {
                app::$entityManager->remove($file);
            }
        }

        $newAgreement = ioc::getRepository('NstFile')->findOneBy(['id' => (int) $data['file']['id']]);
        $newAgreement->setTag($backgroundCheckAgreementFileTag);

        $application2->addApplicationFile($newAgreement);
        $newAgreement->setNurseApplicationPartTwo($application2);

        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }

    public static function getAgreementPDF()
    {
        $response = ['success' => false];

        /** @var NstFileTag $appAgreementFileTag */
        $appAgreementFileTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => 'Application Agreement']);

        /** @var NstFileRepository $fileRepository */
        $fileRepository = ioc::getRepository('NstFile');

        $agreementArray = $fileRepository->findFileByFileTag($appAgreementFileTag->getId());
        $agreement = ioc::getRepository('NstFile')->findOneBy(['id' => $agreementArray[0]['id']]);

        if ($agreement) {
            $response['url'] = app::get()->getRouter()->generate('files_browser_view_file', ['folder' => 'uploads', 'file' => $agreement->getFileName()]);
        } else {
            $response['url'] = '';
        }

        $response['success'] = true;
        return $response;
    }

    public static function getBackgroundCheckAgreement($data)
    {
        $response = ['success' => false];

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['application_id']]);

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var NstFileTag $appAgreementFileTag */
        $backgroundAgreementFileTag = ioc::getRepository('NstFileTag')->findOneBy(['name' => 'Background Check Agreement']);

        $applicantFiles = $application2?->getApplicationFiles();
        $agreement = null;
        try {

            foreach ($applicantFiles as $file) {

                if ($file->getTag()->getId() == $backgroundAgreementFileTag->getId()) {
                    $agreement = $file;
                }
            }
        } catch (\Throwable $e) {
            $agreement = null;
        }

        if ($agreement) {
            $response['url'] = app::get()->getRouter()->generate('files_browser_view_file', ['folder' => 'uploads', 'file' => $agreement->getFileName()]);
        } else {
            $response['url'] = '';
        }

        $response['background_check_approved'] = $application2->getApplicationStatus()?->getBackgroundCheckAccepted();

        $response['success'] = true;
        return $response;
    }

    public static function acceptDrugScreen($data)
    {
        $response = ['success' => false];

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['application_id']]);

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var NurseApplicationStatus */
        $applicationStatus = $application2->getApplicationStatus();

        $applicationStatus->setDrugScreenAccepted(true);
        app::$entityManager->flush();

        $nurseApplicationService = new NurseApplicationService();
        $nurseApplicationService->startBackgroundCheck($data);

        $response['success'] = true;
        return $response;
    }

    public static function acceptBackgroundCheck($data)
    {
        $response = ['success' => false];

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['application_id']]);

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var NurseApplicationStatus */
        $applicationStatus = $application2->getApplicationStatus();

        $applicationStatus->setBackgroundCheckAccepted(true);
        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }
}
