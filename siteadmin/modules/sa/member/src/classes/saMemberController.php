<?php
namespace sa\member;

use Doctrine\Common\Util\Debug;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use \eye4tech\worm\db\saEmail;
use \eye4tech\worm\db\saStates;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use \sacore\application\modelResult;
use sacore\application\modRequest;
use sacore\application\Request;
use sacore\application\responses\Json;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use \sacore\application\saController;
use sacore\application\ValidateException;
use sa\system\saState;
use sacore\utilities\doctrineUtils;
use \sacore\utilities\notification;
use sacore\utilities\stringUtils;
use \sacore\utilities\url;
use sacore\application\Event;

class saMemberController extends saController
{
    /** @var saMemberRepository $saMemberRepo */
    protected $saMemberRepo;
    /** @var saMember $saMember */
    protected $saMember;
    /** @var saMemberUsersRepository */
    protected $saMemberUserRepo;

    /**
     * @param static saMember $saMember
     */
    public function __construct($saMember) {
        parent::__construct();

        $this->saMember = $saMember;
        $this->saMemberRepo = ioc::getRepository( $this->saMember );
        $this->saMemberUserRepo = ioc::getRepository('saMemberUsers');
    }

    public static function getAllGroups() {
        $groups = ioc::getRepository('saMemberGroup')->findBy(array(), array('name'=>'asc'));
        return doctrineUtils::getEntityCollectionArray($groups);
    }


    public function manageMembers($request)
    {
        $view = new \sacore\application\responses\View('table', static::viewLocation());

        $auth_sa_user_id = 0;
        if (is_object($auth))
        {
            $user = $auth->getAuthUser();
            $auth_sa_user_id = $user->getId();
        }

        $perPage = 20;
        $fieldsToSearch=array();

        foreach($request->query->all() as $field=>$value) {
            if (strpos($field, 'q_')===0 && !stringUtils::isBlank($value)) {
                $fieldsToSearch[ str_replace('q_', '', $field) ] = $value;
            }
        }

        // Allows for extended member tables to narrow down members
        if(!is_null($request->extra_search_fields) && count($request->extra_search_fields)) {
            foreach($request->extra_search_fields as $field=>$value) {
                if(!array_key_exists($field, $fieldsToSearch)) {
                    $fieldsToSearch[$field] = $value;
                }
            }
        }

        $currentPage = !empty($request->get('page')) ? $request->get('page') : 1;
        $sort = !empty($request->get('sort')) ? $request->get('sort') : false;
        $sortDir = !empty($request->get('sortDir')) ? $request->get('sortDir') : false;
        $orderBy = null;
        if ($sort) {
            $orderBy = array($sort => $sortDir);
        }

        $totalRecords = $this->saMemberRepo->search($fieldsToSearch,null,null,null,true);

        if($sort == 'last_active') {
            $data = $this->saMemberRepo->search($fieldsToSearch);
            usort($data, array($this, 'manageMemberSort' . $sortDir));
            $data = array_slice($data, ($currentPage - 1)*$perPage, $perPage, true);
        } else {
            $data = $this->saMemberRepo->search($fieldsToSearch, $orderBy, $perPage, (($currentPage-1)*$perPage), false, array('last_name'=>'ASC', 'first_name'=>'ASC') );
        }

        $dataArray = array();

        /** @var saMember $m */
        foreach( $data as $m ) {

            $dataSingle = doctrineUtils::getEntityArray($m);

            $users = $m->getUsers();
            $login_dates = array();
            $active_dates = array();
            /** @var saMemberUsers $user */
            foreach($users as $user) {
                $login_dates[] = $user->getLastLogin();
                $active_dates[] = $user->getLastActive();
            }

            $lastLogin = ($login_dates!=[])?max($login_dates):null;
            $lastActive = ($active_dates!=[])?max($active_dates):null;

            if ($dataSingle['date_created'])
                $dataSingle['date_created'] = $dataSingle['date_created']->format('m/d/Y g:i a');

            if ($dataSingle['customer_since_date'])
                $dataSingle['customer_since_date'] = $dataSingle['customer_since_date']->format('m/d/Y g:i a');

            $dataSingle['last_login'] = '';
            if ($lastLogin)
                $dataSingle['last_login'] = $lastLogin->format('m/d/Y g:i a');


            $dataSingle['last_active'] = '';
            if ($lastActive)
                $dataSingle['last_active'] = $lastActive;

            $dataArray[] = $dataSingle;
        }

        $totalPages = ceil($totalRecords / $perPage);

        $member_table = array(
            /* SET THE HEADER OF THE TABLE UP */
            'header'=>array(array('name'=>'Last Name', 'class'=>''), array('name'=>'First Name', 'class'=>''), array('name'=>'Company', 'class'=>''), array('name'=>'Member Since', 'class'=>'hidden-480'), array('searchable'=>false, 'sortable'=>false,  'name'=>'Last Login', 'class'=>'hidden-480'), array('searchable'=>false, 'sortable'=>false,  'name'=>'', 'class'=>'hidden-480')),
            /* SET ACTIONS ON EVERY ROW */
            'actions'=>array('edit'=>array('name'=>'Edit', 'routeid'=>'member_sa_account_edit', 'params'=>array('id')),
                'delete'=>array('name'=>'Delete', 'routeid'=>'member_sa_account_delete', 'params'=>array('id')),
                'user'=>array('name'=>'Login as User', 'routeid'=>'member_sa_account_superuser_login', 'params'=>array('id')),),
            'headerActions'=>array(array('icon' => 'floppy-o', 'name' => 'Export All', 'routeid' => 'member_sa_export')),
            /* SET THE NO DATA MESSAGE */
            'noDataMessage'=>'No Members Available',
            /* SET THE DATA MAP */
            'map'=>array('last_name', 'first_name', 'company', 'date_created', 'last_login', 'last_active'),
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            'data'=>  $dataArray,
            /* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
            'totalRecords'=> $totalRecords,
            'totalPages'=> $totalPages,
            'currentPage'=> $currentPage,
            'perPage'=> $perPage,
            'searchable'=> true,
            'custom_search_fields' => array('phone_number' => 'Phone Number', 'email' => 'Email Address', 'username' => 'Username', 'is_active' => 'Is Active'),
            'dataRowCallback'=> function($data) {
                //$data['date_created'] = $data['date_created']->format('m/d/Y g:i a');

                if ($data['last_active']) {
                    /** @var \DateInterval $diff */
                    $diff = $data['last_active']->diff(new Datetime('now'));
                    $seconds = $this->to_seconds($diff);
                    if ( $seconds <= 60 ) {
                        $data['last_active'] = '<span title="Online '.$this->ago($diff).' ago" class="label label-success arrowed arrowed-right">Online</span> ';
                    }
                    elseif ( $seconds > 60 && $seconds <= 600 ) {
                        $data['last_active'] = '<span title="Online '.$this->ago($diff).' ago" class="label label-info arrowed arrowed-right">Online Recently</span> ';
                    }
                    elseif ( $seconds > 600 && $seconds <= 84000 ) {
                        $data['last_active'] = '<span title="Online '.$this->ago($diff).' ago" class="label arrowed arrowed-right">Online Today</span> ';
                    }
                    else {
                        $data['last_active'] = '';
                    }
                }
                else
                {
                    $data['last_active'] = '';
                }
                return $data;
            }
        );

        if(app::get()->getConfiguration()->get('allow_manual_add_member')->getValue()){
            /* SET THE ACTION FOR THE HEADER CREATE BUTTON */
            $member_table['tableCreateRoute'] = 'member_sa_account_create';
        }

        $view->data['table'][] = $member_table ;

        return $view;
    }

    function pluralize( $count, $text )
    {
        return $count . ( ( $count == 1 ) ? ( " $text" ) : ( " ${text}s" ) );
    }

    function ago( $interval )
    {
        $suffix = ( $interval->invert ? ' ago' : '' );
        if ( $v = $interval->y >= 1 ) return $this->pluralize( $interval->y, 'year' ) . $suffix;
        if ( $v = $interval->m >= 1 ) return $this->pluralize( $interval->m, 'month' ) . $suffix;
        if ( $v = $interval->d >= 1 ) return $this->pluralize( $interval->d, 'day' ) . $suffix;
        if ( $v = $interval->h >= 1 ) return $this->pluralize( $interval->h, 'hour' ) . $suffix;
        if ( $v = $interval->i >= 1 ) return $this->pluralize( $interval->i, 'minute' ) . $suffix;
        return $this->pluralize( $interval->s, 'second' ) . $suffix;
    }

    public function to_seconds($interval)
    {
        return ($interval->y * 365 * 24 * 60 * 60) +
            ($interval->m * 30 * 24 * 60 * 60) +
            ($interval->d * 24 * 60 * 60) +
            ($interval->h * 60 * 60) +
            ($interval->i * 60) +
            $interval->s;
    }

    /**
     * @param Request $request
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     * @throws \sacore\application\Exception
     */
    public function editMember($request)
    {
        $memberId = $request->getRouteParams()->get('id');
        if(is_null($memberId)) $memberId = 0;
        $view = new \sacore\application\responses\View('saProfile', static::viewLocation());
        $view->data['postRoute'] = app::get()->getRouter()->generate('member_sa_account_save', ['id' => $memberId]);
        $view->data['memberId'] = $memberId;

        if ($memberId>0) {
            $member = app::$entityManager->find($this->saMember, $memberId);
            $view->data = array_merge($view->data, doctrineUtils::getEntityArray($member) );
        }else{
            if(!app::get()->getConfiguration()->get('allow_manual_add_member')){
                return new Redirect(app::get()->getConfiguration()->get('member_sa_accounts'));
            }
        }

        if ($request->query->all()) {
            $view->data = array_merge($view->data, $request->query->all());
        }

        if($member) {
            $other = $member->getOther();
        }

        $view->data['other'] = is_array($other) ? $other : array();

        $other_tabs = modRequest::request('sa.member.other_tabs', array( 'tabs'=>array(), 'data'=>$view->data ));
        $view->data['other_tabs'] = $other_tabs['tabs'];

        if ($memberId) {
            $usersData = doctrineUtils::convertEntityToArray($member->getUsers());
            $view->data['table'][] = array(
                /* SET THE HEADER OF THE TABLE UP */
                'title'=>'Usernames/Passwords',
                'tabid'=>'edit-usernames',
                'header'=>array(array('name'=>'First Name'),array('name'=>'Last Name'),array('name'=>'Username'), array('name'=>'Last Login'), array('name'=>'Login Count'), array('name'=>'Is Active', 'type'=>'boolean')),
                'actions'=>array('edit'=>array('name'=>'Edit', 'routeid'=>'member_sa_editusernames', 'params'=>array('member_id', 'id')), 'delete'=>array('name'=>'Delete', 'routeid'=>'member_sa_deleteusernames', 'params'=>array('member_id', 'id')),),
                'tableCreateRoute'=>array('routeId'=>'member_sa_createusers', 'params'=>['member_id' => $memberId]),
                'map'=>array('first_name','last_name',  'username', 'last_login', 'login_count', 'is_active'),
                'data'=> $usersData,
                'dataRowCallback'=> function($data) {
                    $data['last_login'] = !empty($data['last_login']) ? $data['last_login']->format('m/d/Y') : 'N\A';
                    return $data;
                }
            );

            $emailData = doctrineUtils::convertEntityToArray($member->getEmails());
            $view->data['table'][] = array(
                /* SET THE HEADER OF THE TABLE UP */
                'title'=>'Email Addresses',
                'tabid'=>'edit-emailaddresses',
                'header'=>array(array('name'=>'Name'), array('name'=>'Type'), array('name'=>'Primary', 'type'=>'boolean'), array('name'=>'Is Active', 'type'=>'boolean')),
                'actions'=>array('edit'=>array('name'=>'Edit', 'routeid'=>'member_sa_editemail', 'params'=>['member_id', 'id']), 'delete'=>array('name'=>'Delete', 'routeid'=>'member_sa_deleteemail', 'params'=>['member_id', 'id']),),
                'tableCreateRoute'=>array('routeId'=>'member_sa_createemail', 'params'=>['member_id' => $memberId]),
                'map'=>array('0'=>'email', '1'=>'type', '2'=>'is_primary', '3'=>'is_active'),
                'data'=> $emailData
            );

            $addressData = doctrineUtils::convertEntityToArray($member->getAddresses());
            $view->data['table'][] = array(
                /* SET THE HEADER OF THE TABLE UP */
                'title'=>'Addresses',
                'tabid'=>'edit-addresses',
                'header'=>array(array('name'=>'Address'), array('name'=>'Type'), array('name'=>'Is Primary', 'type'=>'boolean'), array('name'=>'Is Active', 'type'=>'boolean')),
                'actions'=>array('edit'=>array('name'=>'Edit', 'routeid'=>'member_sa_editaddress', 'params'=>['member_id', 'id']), 'delete'=>array('name'=>'Delete', 'routeid'=>'member_sa_deleteaddress', 'params'=>['member_id', 'id']),),
                'map'=>array('street_one|city|state|postal_code|country', 'type', 'is_primary', 'is_active'),
                'tableCreateRoute'=>array('routeId'=>'member_sa_createaddress', 'params'=>['member_id' => $memberId]),
                'data'=> $addressData
            );

            $phoneData = $member->getPhones();

            foreach($phoneData as $phone){
                $phone->setPhone(stringUtils::formatPhoneNumber($phone->getPhone()));
            }

            $phoneData = doctrineUtils::convertEntityToArray($phoneData);

            $view->data['table'][] = array(
                /* SET THE HEADER OF THE TABLE UP */
                'title'=>'Phones',
                'tabid'=>'edit-phone',
                'header'=>array(array('name'=>'Phone'), array('name'=>'Type'), array('name'=>'Is Primary', 'type'=>'boolean'), array('name'=>'Is Active', 'type'=>'boolean')),
//                'actions'=>array('edit'=>array('name'=>'Edit', 'routeid'=>'member_sa_editphone', 'params'=>['member_id', 'id']), 'delete'=>array('name'=>'Delete', 'routeid'=>'member_sa_deletephone', 'params'=>['member_id' , 'id']),),
                'actions'=>array('edit'=>array('name'=>'Edit', 'routeid'=>'member_sa_editphone', 'params'=>['id', 'member_id']), 'delete'=>array('name'=>'Delete', 'routeid'=>'member_sa_deletephone', 'params'=>['id', 'member_id']),),
                'map'=>array('phone', 'type', 'is_primary', 'is_active'),
                'tableCreateRoute'=>array('routeId'=>'member_sa_createphone', 'params'=>['member_id' =>$memberId]),
                'data'=> $phoneData
            );


            $groupData = doctrineUtils::convertEntityToArray($member->getGroups());
            $view->data['table'][] = array(
                'title' => 'Groups the member is part of:',
                'tabid' => 'edit-groups',
                'header' => array(array('name' => 'Name')),
                'actions' => array('delete' => array('name' => 'Delete', 'routeid' => 'member_sa_deletememberfromgroup', 'params' => [ 'member_id' => $memberId, 'id']),),
                'map' => array('0' => 'name'),
                'tableCreateRoute' => array('routeId' => 'member_sa_addgrouptomember', 'params' => ['id' =>$memberId]),
                'data' => $groupData
            );


        }

        $metaData = app::$entityManager->getClassMetadata(ioc::staticResolve('saMember'));

        $view->data['dbform'][] = array(
            'title'=>'Miscellaneous',
            'tabid'=>'edit-misc',
            'columns'=> $metaData->fieldMappings,
            'exclude'=> array('id', 'other', 'last_name', 'middle_name', 'first_name', 'is_active', 'last_login', 'date_created', 'company', 'last_name', 'is_pending', 'avatar', 'comment', 'is_deleted')
        );

        $view->addXssSanitationExclude('html');

        return $view;
    }

    public function saUserLoginAsMember($request)
    {
        $notify = new notification();
        $user = null;

        $target_member_id = $request->getRouteParams()->get('id');
        $userid = $request->getRouteParams()->get('userId');

        if ( !$target_member_id || $target_member_id == '0' ) {
            $notify = new notification();

            $notify->addNotification('danger', 'Error', 'Failed to login as this user.');
            return new Redirect(app::get()->getRouter()->get('member_sa_accounts'));
        } else {
            if($userid) {
                /** @var saMemberUsers $user */
                $user = ioc::getRepository('saMemberUsers')->findOneBy(array('id' => $userid));
            } else {
                /** @var saMember $member */
                $member = ioc::getRepository('saMember')->findOneBy(['id' => $target_member_id]);

                if($member) {
                    if($member->getUsers()) {
                        /** @var saMemberUsers $memberUser */
                        foreach($member->getUsers() as $memberUser) {
                            $user = $memberUser;
                            if($memberUser->getIsPrimaryUser()) {
                                break;
                            }
                        }
                    }
                }
            }

            if(!$user) {
                $notify->addNotification('danger', 'Error', 'Failed to login as this user.');
                return new Redirect(app::get()->getRouter()->generate('member_sa_accounts'));
            }

            /** @var auth $auth */
            $auth = ioc::staticGet('auth');
            /** @var auth $auth */
            $auth = $auth::getInstance();

            if($auth->userObjectLogon($user)) {
                return new Redirect(app::get()->getRouter()->generate('dashboard_home'));
            } else {
                $notify->addNotification('danger', 'Error', 'Failed to login as this user.');
                return new Redirect(app::get()->getRouter()->generate('member_sa_accounts'));
            }
        }
    }

    /**@var Request $request
     */
    public function saveMember($request)
    {
        $id = $request->getRouteParams()->get('id');
        $notify = new notification();

        if ($id>0) {
            /** @var saMember $member */
            $member = app::$entityManager->find($this->saMember, $id);
        }
        else {
            /** @var saMember $member */
            $member = ioc::resolve('saMember');
        }

        $request->query->remove('avatar');
        doctrineUtils::setEntityData($request->request->all(), $member, true);

        try {
            app::$entityManager->persist($member);
            app::$entityManager->flush();

            modRequest::request('sa.member.postSave', null, array('member' => $member, 'post' => $request->request->all()));

            $notify->addNotification('success', 'Success', 'Member saved successfully.');

            if ($id) {
                return new Redirect(app::get()->getRouter()->generate( $request->return_route ? $request->return_route : 'member_sa_accounts'));
            } else {
                return new Redirect(app::get()->getRouter()->generate( $request->return_route ? $request->return_route : 'member_sa_account_edit', ['id'=>$member->getId()]));
            }
        }
        catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage() );
            // have to return this due to editMember returning new View obj.
            return $this->editMember($request);
        }
    }

//    public function deleteMember($request, $hard_delete = false)
    public function deleteMember($request)
    {
        $id = $request->getRouteParams()->get('id');
        $returnRoute = $request->return_route ? $request->return_route : 'member_sa_accounts';

        /** @var saMember $member */
        $member = app::$entityManager->find($this->saMember, $id);

        $notify = new notification();

        try {
            //Todo: Not sure how this is supposed to be pased with new requests
//            if($hard_delete)
//                app::$entityManager->remove($member);
//            else {
            $member->setIsDeleted(true);
            $member->setIsActive(false);
            if($member->getUsers()) {
                foreach($member->getUsers() as $user) {
                    $user->setIsActive(false);
                }
            }
//            }
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Member deleted successfully.');
            return new Redirect(app::get()->getRouter()->generate($returnRoute));
        }
        catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while deleting that member. <br />'. $e->getMessage());
            return new Redirect(app::get()->getRouter()->generate($returnRoute));
        }
    }


    // MEMBER USERNAMES
    public function editMemberUsers($request)
    {
        $memberId = $request->getRouteParams()->get('member_id');
        $usernameId = $request->getRouteParams()->get('id');
        if (is_null($usernameId)) {
            $usernameId = 0;
        }
        /** @var saMember $member */
        $member = app::$entityManager->find($this->saMember,$memberId);

        $view = new View('saUsernames', $this->viewLocation(), false);
        $view->data['postRoute'] = app::get()->getRouter()->generate('member_sa_saveusernames', ['member_id' => $memberId, 'id' => $usernameId]);
        $view->data['memberId'] = $memberId;
        $view->data['usernameId'] = $usernameId;
        $view->data['in_groups'] = array();

        if ($usernameId>0) {
            $saMemberUsers = ioc::staticResolve('saMemberUsers');
            $user = app::$entityManager->find($saMemberUsers, $usernameId);

            $mData = doctrineUtils::convertEntityToArray( $user );
            $view->data = array_merge($view->data, $mData);
            $view->data['email'] = '';
            if ($user->getEmail()) {
                $view->data['email'] = $user->getEmail()->getId();;
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

    public function saveMemberUsers($request)
    {
        $memberId = $request->getRouteParams()->get('member_id');
        $usernameId = $request->getRouteParams()->get('id');
        /** @var saMember $member */
        $member = app::$entityManager->find($this->saMember, $memberId);

        /** @var saMemberUsers $user */
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

        $user->setLastName($request->request->get('last_name'));
        $user->setFirstName($request->request->get('first_name'));
        $user->setUsername($request->request->get('username'));
        $user->setIsActive($request->request->get('is_active'));
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

    public function deleteMemberUsers($request)
    {

        $memberId = $request->getRouteParams()->get('member_id');
        $usernameId = $request->getRouteParams()->get('id');
        if(is_null($memberId))$memberId = 0;
        if(is_null($usernameId))$usernameId = 0;
        $saMemberUsers = ioc::staticResolve('saMemberUsers');
        $user = app::$entityManager->find($saMemberUsers, $usernameId);

        $notify = new notification();

        try {
            app::$entityManager->remove($user);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'User deleted successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_sa_account_edit', ['id' =>$memberId]).'#edit-usernames');
        }
        catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage());
            $this->editMemberUsers($request);
        }
    }

    // MEMBER PHONES
    public function editMemberPhone($request)
    {
        $memberId = $request->getRouteParams()->get('member_id');
        $phoneId = $request->getRouteParams()->get('id');
        if (empty($phoneId)) {
            $phoneId = 0;
        }

        $view = new View('saPhone', $this->viewLocation(), false);
        $view->data['postRoute'] = app::get()->getRouter()->generate('member_sa_savephone', ['member_id' => $memberId,'id' => $phoneId] );
        $view->data['memberId'] = $memberId;
        $view->data['phoneId'] = $phoneId;

        if ($phoneId>0) {
            $saMemberUsers = ioc::staticResolve('saMemberPhone');
            $phone = app::$entityManager->find($saMemberUsers, $phoneId);
            $mData = $phone->toArray();
            $view->data = array_merge($view->data, $mData);
        }

        if ($request->request->all()) {
            $view->data = array_merge($view->data, $request->request->all());
        }

        return $view;
    }

    public function saveMemberPhone($request)
    {
        $memberId = $request->getRouteParams()->get('member_id');
        $phoneId = $request->getRouteParams()->get('id');

        if(is_null($memberId))$memberId = 0;
        if(is_null($phoneId))$phoneId = 0;
        /** @var saMember $member */
        $member = app::$entityManager->find($this->saMember, $memberId);

        /** @var saMemberPhone $user */
        if ($phoneId>0) {
            $saMemberPhone = ioc::staticResolve('saMemberPhone');
            $phone = app::$entityManager->find($saMemberPhone, $phoneId);
        }
        else {
            $phone = ioc::resolve('saMemberPhone');
            $member->addPhone($phone);
        }

        $phone->setPhone($request->request->get('phone'));
        $phone->setType($request->request->get('type'));
        $phone->setIsActive($request->request->get('is_active'));
        $phone->setIsPrimary($request->request->get('is_primary'));
        $phone->setMember($member);

        $notify = new notification();

        try {
            app::$entityManager->persist($member);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Phone saved successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_sa_account_edit',['id'=>$memberId]).'#edit-phone');
        }
        catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage());
            $this->editMemberPhone($request);
        }
    }

    public function deleteMemberPhone($request)
    {
        $memberId = $request->getRouteParams()->get('member_id');
        $phoneId = $request->getRouteParams()->get('id');
        $saMemberPhone = ioc::staticResolve('saMemberPhone');
        $phone = app::$entityManager->find($saMemberPhone, $phoneId);

        $notify = new notification();

        try {
            app::$entityManager->remove($phone);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Phone deleted successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_sa_account_edit', ['id' => $memberId]).'#edit-phone');
        }
        catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage());
            $this->editMemberPhone($request);
        }
    }

    // MEMBER EMAILS
    public function editMemberEmail($request)
    {
        $memberId = $request->getRouteParams()->get('member_id');
        $emailId = $request->getRouteParams()->get('id');
        if (empty($emailId)) {
            $emailId = 0;
        }


        $view = new view('saEmail', $this->viewLocation(), false);
        $view->data['postRoute'] = app::get()->getRouter()->generate('member_sa_saveemail', ['member_id' => $memberId, 'id' => $emailId] );
        $view->data['memberId'] = $memberId;
        $view->data['emailId'] = $emailId;

        if ($emailId>0) {
            $saMemberEmail = ioc::staticResolve('saMemberEmail');
            $email = app::$entityManager->find($saMemberEmail, $emailId);
            $mData = $email->toArray();
            $view->data = array_merge($view->data, $mData);
        }

        if ($request->request->all()) {
            $view->data = array_merge($view->data, $request->request->all());
        } else {
            unset($view->data['password']);
        }

        return $view;
    }

    public function saveMemberEmail($request)
    {
        $memberId = $request->getRouteParams()->get('member_id');
        $emailId = $request->getRouteParams()->get('id');
        if(is_null($emailId)) $emailId = 0;
        /** @var saMember $member */
        $member = app::$entityManager->find($this->saMember, $memberId);

        /** @var saMemberEmail $email */
        if ($emailId>0) {
            $saMemberEmail = ioc::staticResolve('saMemberEmail');
            $email = app::$entityManager->find($saMemberEmail, $emailId);
        }
        else {
            $email = ioc::resolve('saMemberEmail');
            $member->addEmail($email);
        }

        $email->setEmail($request->request->get('email'));
        $email->setType($request->request->get('type'));
        $email->setIsActive($request->request->get('is_active'));
        $email->setIsPrimary($request->request->get('is_primary'));
        $email->setMember($member);

        $notify = new notification();

        try {
            app::$entityManager->persist($member);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Email saved successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_sa_account_edit', ['id' => $memberId, 'emailId' => $emailId]).'#edit-emailaddresses');
        }
        catch (ValidateException $e) {
            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage());
            return $this->editMemberEmail($request);
        }

    }

    public function deleteMemberEmail($request)
    {
        $memberId = $request->getRouteParams()->get('member_id');
        $emailId = $request->getRouteParams()->get('id');
        if(is_null($memberId)) $memberId = 0;
        if(is_null($emailId)) $emailId = 0;
        $saMemberEmail = ioc::staticResolve('saMemberEmail');
        $email = app::$entityManager->find($saMemberEmail, $emailId);

        $notify = new notification();

        try {
            app::$entityManager->remove($email);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Email deleted successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_sa_account_edit', ['id' => $memberId]).'#edit-emailaddresses');
        }
        catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage());
            $this->editMemberEmail($request);
        }
    }

    // MEMBER ADDRESSES
    public function editMemberAddress($request)
    {
        $memberId = $request->getRouteParams()->get('member_id');
        $addressId = $request->getRouteParams()->get('id');
        if(is_null($addressId)) $addressId=0;

        $view = new \sacore\application\responses\View( 'saAddress', $this->viewLocation(), 200);
        $view->data['postRoute'] = app::get()->getRouter()->generate('member_sa_saveaddress', ['member_id' => $memberId, 'id' => $addressId]);
        $view->data['memberId'] = $memberId;
        $view->data['addressId'] = $addressId;
        $view->data['countries'] = app::$entityManager->getRepository( ioc::staticResolve('saCountry') )->findAll();
        if ($addressId>0) {
            /** @var saMemberAddress $saMemberAddress */
            $saMemberAddress = ioc::staticResolve('saMemberAddress');
            $address = app::$entityManager->find($saMemberAddress, $addressId);

            $mData = doctrineUtils::getEntityArray($address);

            $view->data = array_merge($view->data, $mData);
            $view->data['country'] = $address->getCountryObject();
            $view->data['state'] = $address->getStateObject();
        }

        /** @var saMemberAddressRepo $addressRepo */
        $addressRepo = ioc::getRepository('saMemberAddress');
        $view->data['otherAddressTypes'] = $addressRepo->getAllDistinctAddressTypes();

        if ($request->request->all()) {
            $view->data = array_merge($view->data, $request->request->all());
        } else {
            unset($view->data['password']);
        }

        return $view;
    }

    public function saveMemberAddress($request)
    {
        $memberId = $request->getRouteParams()->get('member_id');
        $addressId = $request->getRouteParams()->get('id');
        /** @var saMember $member */
        $member = app::$entityManager->find($this->saMember, $memberId);

        /** @var saMemberAddress $address */
        if ($addressId>0) {
            $saMemberAddress = ioc::staticResolve('saMemberAddress');
            $address = app::$entityManager->find($saMemberAddress, $addressId);
        } else {
            $address = ioc::resolve('saMemberAddress');
            $member->addAddress($address);
        }

        $address = doctrineUtils::setEntityData($request->request->all(), $address, true);

        /** @var saState $state */
        $state = null;

        if($request->request->get('state')) {
            $state = app::$entityManager->find( ioc::staticResolve('saState'), $request->request->get('state') );
        }

        if ( $state ) {
            //$_POST['state'] = $state->getName();
            $address->setStateObject($state);
        }

        $country = app::$entityManager->find( ioc::staticResolve('saCountry'), $request->request->get('country') );
        if ( $country ) {
            //$_POST['country'] = $country->getName();
            $address->setCountryObject($country);
        }

        $address->setStreetOne($request->request->get('street_one'));
        $address->setStreetTwo($request->request->get('street_two'));
        $address->setCity($request->request->get('city'));
        $address->setPostalCode($request->request->get('postal_code'));
        //$address->setState($_POST['state']);
        //$address->setCountry($_POST['country']);
        $address->setType($request->request->get('type'));
        $address->setIsActive($request->request->get('is_active'));
        $address->setIsPrimary($request->request->get('is_primary'));
        $address->setMember($member);

        $notify = new notification();

        try {
            app::$entityManager->persist($member);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Address saved successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_sa_account_edit', ['id' => $memberId]).'#edit-addresses');
        }
        catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage());
            return $this->editMemberAddress($request);
        }
    }

    public function deleteMemberAddress($request)
    {
        $memberId = $request->getRouteParams()->get('member_id');
        $addressId = $request->getRouteParams()->get('id');
        if(is_null($memberId)) $memberId = 0;
        if(is_null($addressId)) $addressId = 0;
        $saMemberAddress = ioc::staticResolve('saMemberAddress');
        $address = app::$entityManager->find($saMemberAddress, $addressId);

        $notify = new notification();

        try {
            app::$entityManager->remove($address);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Address deleted successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_sa_account_edit', ['id' =>$memberId]).'#edit-addresses');
        }
        catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage());
            $this->editMemberAddress($request);
        }
    }

    /* ------------------- GROUPS ----------------------- */
    public function manageGroups($request)
    {
        $view = new View('table', $this->viewLocation(), false);
        $perPage = 20;
        $currentPage = !empty($request->get('page')) ? $request->get('page') : 1;
        $sort = !empty($request->get('sort')) ? $request->get('sort') : false;
        $sortDir = !empty($request->get('sortDir')) ? $request->get('sortDir') : false;

        /** @var saMemberGroupRepository $repo */
        $repo = app::$entityManager->getRepository( ioc::staticResolve('saMemberGroup') );

        $orderBy = null;
        if ($sort) {
            $orderBy = array($sort => $sortDir);
        }

        $data = $repo->search( null, $orderBy, $perPage, (($currentPage-1)*$perPage) );
        $totalRecords = $repo->search( null, null, null, null, true );

        /** @var saMemberGroupRepository $groupRepo */
        $groupRepo = ioc::getRepository('saMemberGroup');

        $data = $groupRepo->search(false, $perPage, (($currentPage-1)*$perPage), $sort, $sortDir);

        $totalPages = ceil($totalRecords / $perPage);
        $view->data['table'][] = array(
            /* SET THE HEADER OF THE TABLE UP */
            'header'=>array(
                array('name'=>'Name', 'class'=>'', 'sort'=>'g.name'),
                array('name'=>'Group Id', 'class'=>'', 'sort'=>'g.id'),
                array('name'=>'Is Default', 'class'=>'', 'type'=>'boolean', 'sort'=>'g.is_default')),
            /* SET ACTIONS ON EVERY ROW */
            'actions'=>array('edit'=>array('name'=>'Edit', 'routeid'=>'member_sa_group_edit', 'params'=>array('id')), 'delete'=>array('name'=>'Delete', 'routeid'=>'member_sa_group_delete', 'params'=>array('id')) ),
            /* SET THE NO DATA MESSAGE */
            'noDataMessage'=>'No Groups Available',
            /* SET THE DATA MAP */
            'map'=>array('name', 'id', 'is_default'),
            /* SET THE ACTION FOR THE HEADER CREATE BUTTON */
            'tableCreateRoute'=>'member_sa_group_create',
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            'data'=> $data,
            /* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
            'totalRecords'=> $totalRecords,
            'totalPages'=> $totalPages,
            'currentPage'=> $currentPage,
            'perPage'=> $perPage,
        );

        return $view;
    }

    public function editGroup($request)
    {
        $id = $request->getRouteParams()->get('id');
        if(is_null($id)) $id = 0;

        $view = new view('saGroup', $this->viewLocation(), false);

        if ($id>0) {
            $saMemberGroup = ioc::staticResolve('saMemberGroup');
            $mData = app::$entityManager->find($saMemberGroup, $id)->toArray();
            $view->data = array_merge($view->data, $mData);
        }
        $view->data['groupId'] = $id;

        if ($request->query) {
            $view->data = array_merge($view->data, $request->query->all());
        } else {
            unset($view->data['password']);
        }

        return $view;
    }

    public function saveGroup($request)
    {
        $id = $request->getRouteParams()->get('id');
        /** @var saMemberGroup $group */
        if ($id>0) {
            $saMemberGroup = ioc::staticResolve('saMemberGroup');
            $group = app::$entityManager->find($saMemberGroup, $id);
        }
        else {
            $group = ioc::resolve('saMemberGroup');
        }

        $group->setName($request->request->get('name'));
        $group->setDescription($request->request->get('description'));
        $group->setIsDefault($request->request->get('is_default'));

        // The following lines were removed as they were causing errors as of version 1.0.4.709
        //if($_POST['is_super_user_group'] == "true")
        //	$group->setIsSuperUserGroup(true);
        //else
        //	$group->setIsSuperUserGroup(false);

        $notify = new notification();

        try {
            app::$entityManager->persist($group);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Group saved successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_sa_group'));
        }
        catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage());
            $this->editGroup( $request);
        }
    }

    public function deleteGroup($request)
    {
        $id = $request->getRouteParams()->get('id');
        $saMemberGroup = ioc::staticResolve('saMemberGroup');
        $group = app::$entityManager->find($saMemberGroup, $id);

        $notify = new notification();

        try {
            app::$entityManager->remove($group);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Group deleted successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_sa_group'));
        }
        catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage());
            return new Redirect(app::get()->getRouter()->generate('member_sa_group'));
        }
    }

    public function addMembertoGroup($request)
    {
        $memberId = $request->getRouteParams()->get('id');
        $view = new View('saMembertoGroup', $this->viewLocation(), false);
        $view->data['groups'] = $repo = app::$entityManager->getRepository( ioc::staticResolve('saMemberGroup') )->findAll();
        $view->data['memberId'] = $memberId;
        return $view;
    }

    public function addMembertoGroupSave($request)
    {
        $memberId = $request->getRouteParams()->get('id');
        /** @var saMember $member */
        $member = app::$entityManager->find($this->saMember, $memberId);
        /** @var saMemberGroup $group */
        $group = app::$entityManager->find( ioc::staticResolve('saMemberGroup'),$request->request->get('group_id'));
        $member->addGroup( $group );
        $notify = new notification();

        try {
            app::$entityManager->persist($member);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'The member was added to the group successfully.');
        } catch (ValidateException $e) {
            $notify->addNotification('danger', 'Error', 'An error occurred while trying to add the member to that group. <br />'. $e->getMessage());
        } catch(UniqueConstraintViolationException $e) {
            $notify->addNotification('danger', 'Error', "User has already been assigned to the \"{$group->getName()}\" group.");
        }

        return new Redirect(app::get()->getRouter()->generate('member_sa_account_edit', ['id' => $memberId]).'#edit-groups');
    }

    public function deleteMemberFromGroup($request)
    {
        $memberId = $request->getRouteParams()->get('id');
        $groupId = $request->getRouteParams()->get('groupId');
        if(is_null($memberId)) $memberId = 0;
        if(is_null($groupId)) $groupId = 0;
        /** @var saMember $member */
        $member = app::$entityManager->find($this->saMember, $memberId);
        /** @var saMemberGroup $group */
        $group = app::$entityManager->find( ioc::staticResolve('saMemberGroup'), $groupId);

        $member->removeGroup( $group );
        $notify = new notification();

        try {
            app::$entityManager->persist($member);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'The member was removed from the group successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_sa_account_edit', ['id' => $memberId]).'#edit-groups');
        }
        catch (ValidateException $e) {
            $notify->addNotification('danger', 'Error', 'An error occurred while trying to remove the member from that group. <br />'. $e->getMessage());
            return new Redirect(app::get()->getRouter()->generate('member_sa_account_edit', ['id' => $memberId]).'#edit-groups');
        }
    }

    public function manageUsers($request) {
        $sorting_map = array(
            'username' => 'username',
            'last_login_temp' => 'last_login',
            'company' => 'company',
            'is_active_temp' => 'is_active',
            'access_level' => 'access_level',
            'first_name' => 'first_name',
            'last_name' => 'last_name'
        );

        $view = new \sacore\application\responses\View('table', static::viewLocation());

        $perPage = 20;
        $fieldsToSearch = array();

        foreach($request->query->all() as $field=>$value) {
            if (strpos($field, 'q_')===0 && !stringUtils::isBlank($value)) {
                $fieldsToSearch[ str_replace('q_', '', $field) ] = $value;
            }
        }

        $currentPage = !empty($request->get('page')) ? $request->get('page') : 1;
        $sort = !empty($request->get('sort')) ? $sorting_map[$request->get('sort')] : false;
        $sortDir = !empty($request->get('sortDir')) ? $request->get('sortDir') : false;
        $orderBy = null;

        if ($sort) {
            $orderBy = array($sort => $sortDir);
        }

        $totalRecords = $this->saMemberUserRepo->search($fieldsToSearch, null, null, null, true);

        $data = $this->saMemberUserRepo->search($fieldsToSearch, $orderBy, $perPage, ($currentPage - 1) * $perPage);

        //Create custom variables for table data
        /** @var \sa\member\saMemberUsers $el */
        foreach ($data as $k => $el) {
            if (!empty($request->query->get('q_company')) && strtolower($request->query->get('q_company')) != strtolower($el->getMember()->getCompany())) {
                $totalRecords--;
                unset($data[$k]);
                continue;
            }

            $el->last_login_temp = $el->getLastLogin() ? date("F j, Y H:m:s A", $el->getLastLogin()->getTimestamp()) : "";
            $el->is_active_temp = $el->getIsActive() ? 'Active' : 'Disabled';
            $el->company = $el->getMember()->getCompany();
            $el->member_id = $el->getMember()->getId();
        }

        $totalPages = ceil($totalRecords / $perPage);

        $view->data['table'][] = array(
            /* SET THE HEADER OF THE TABLE UP */
            'header' => array(
                array('name'=>'Email Address', 'class'=>''),
                array('name'=>'Company', 'class'=>''),
                array('name'=>'Status', 'class'=>'', 'searchable' => false),
                array('name'=>'Last Activity', 'class'=>'', 'searchable' => false),
                array('name'=>'First Name', 'class'=>''),
                array('name'=>'Last Name', 'class'=>''),
            ),
            /* SET ACTIONS ON EVERY ROW */
            'actions'=>array(
                'login'=>array('name'=>'Login', 'routeid'=>'member_sa_userlogin','target'=>'blank', 'icon'=>'user', 'params'=>array('member_id', 'id')),
                'edit'=>array('name'=>'Edit', 'routeid'=>'member_sa_editusernames', 'params'=>array('member_id', 'id')),
                'delete'=>array('name'=>'Delete', 'routeid'=>'member_sa_deleteusernames', 'params'=>array('member_id', 'id')),
            ),            /* SET THE NO DATA MESSAGE */
            'noDataMessage' => 'No Users Available',
            /* SET THE DATA MAP */
            'map' => array('username', 'company', 'is_active_temp', 'last_login_temp', 'first_name', 'last_name'),
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            'data' => $data,
            /* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'searchable' => true,
            'custom_search_fields' => array('groups' => 'Groups')
        );

        return $view;
    }

    public function loginAsUser($request) {
        return $this->saUserLoginAsMember($request);
    }

    /**
     * @param \sa\member\saMemberUsers $a
     * @param \sa\member\saMemberUsers $b
     *
     * @return bool
     */
    public function manageMemberSortASC($a, $b) {
        $user_a = ioc::getRepository('saMemberUsers')->findOneBy(array('member' => $a), array('last_login' => 'DESC'));
        $user_b = ioc::getRepository('saMemberUsers')->findOneBy(array('member' => $b), array('last_login' => 'DESC'));

        if ($user_a && $user_b) {
            return ($user_a->getLastLogin() ? $user_a->getLastLogin()->getTimestamp() : 0) > ($user_b->getLastLogin() ? $user_b->getLastLogin()->getTimestamp() : 0);
        }

        return !!$user_a;
    }

    /**
     * @param \sa\member\saMemberUsers $a
     * @param \sa\member\saMemberUsers $b
     *
     * @return bool
     */
    public function manageMemberSortDESC($a, $b) {
        $user_a = ioc::getRepository('saMemberUsers')->findOneBy(array('member' => $a), array('last_login' => 'DESC'));
        $user_b = ioc::getRepository('saMemberUsers')->findOneBy(array('member' => $b), array('last_login' => 'DESC'));
        if ($user_a && $user_b) {
            return ($user_a->getLastLogin() ? $user_a->getLastLogin()->getTimestamp() : 0) < ($user_b->getLastLogin() ? $user_b->getLastLogin()->getTimestamp() : 0);
        }

        return !!$user_b;
    }

    public function uploadAvatar($request) {
        $memberId = $request->getRouteParams()->get('id');
        $member = ioc::getRepository('saMember')->findOneBy(array('id' => $memberId));

        /** @var memberProfileController $memberProfileController */
        $memberProfileController = ioc::resolve('memberProfileController');
        $memberProfileController = new $memberProfileController();

        return $memberProfileController->saveMemberAvatar(ioc::staticGet('saImage'), $member);
    }

    public function getAvatar($memberId) {
        $member = ioc::getRepository('saMember')->findOneBy(array('id' => $memberId));

        /** @var memberProfileController $memberProfileController */
        $memberProfileController = ioc::resolve('memberProfileController');
        $memberProfileController = new $memberProfileController();

        return $memberProfileController->getMemberAvatar(ioc::staticGet('saFile'), $member);
    }

    public function removeAvatar($memberId) {
        /** @var saMember $member */
        $member = ioc::getRepository('saMember')->findOneBy(array('id' => $memberId));

        $member->setAvatar(null);
        app::$entityManager->flush();

        $view = new Json();
        $view->data['success'] = true;

        return $view;
    }
}
