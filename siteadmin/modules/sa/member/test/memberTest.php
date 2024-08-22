<?php
use elink\companies\companiesController;
use elink\companies\ihCompany;
use PHPUnit\Framework\TestCase;
use sacore\application\app;
use sacore\application\ioc;
use sa\member\auth;
use sa\member\saMember;
use sa\member\saMemberGroup;
use sacore\application\modelResult;

class memberTest extends TestCase
{
    protected $backupGlobalsBlacklist = array('_SESSION');

    public function testCanAddState() {


        /** @var saState $state */
        $state = ioc::resolve('saState');
        $state->setAbbreviation('KY');
        $state->setName('Kentucky');
        app::$entityManager->persist($state);
        app::$entityManager->flush();

    }

    public function testCanMemberSignup()
    {
       $_SESSION['unit_testing']['email'] = time().'@email.com';
        $data = array(
            'company'=>'test',
            'first_name'=>'First Name',
            'middle_name'=>'Middle Name',
            'last_name'=>'last Name',
            'is_active'=>'1',
            'is_pending'=>'0',
            'date_created'=>date('Y-m-d g:i:s'),
            'email'=>$_SESSION['unit_testing']['email'],
            'email2'=>$_SESSION['unit_testing']['email'],
            'password'=>'test',
            'password2'=>'test',
            'street_one'=>'test',
            'street_two'=>'Suite 1',
            'city'=>'Lexington',
            'state'=>'KY',
            'postal_code'=>'40501',
            'country'=>'US',
            'phone'=>'6063449541',
            'companyId'=>'1'
        );

        $saMember = \sacore\application\ioc::resolve('saMember');
        $member = $saMember::memberSignUp($data);
        $_SESSION['unit_testing']['member'] = $member;
        $_SESSION['unit_testing']['member_id'] = $member->getId();
    } 

    public function testBasicMemberAuthFunctions()
    {
       $auth = auth::getInstance();
       $success = $auth->logon($_SESSION['unit_testing']['email'], 'test');
       $this->assertTrue($success, 'Member can not login.');
       $user = $auth->getAuthUser();
       $this->assertInstanceOf(  \sacore\application\ioc::staticResolve('saMemberUsers') , $user, 'Can not get user object.');
       $member = $auth->getAuthMember();
       $this->assertInstanceOf( \sacore\application\ioc::staticResolve('saMember') , $member, 'Can not get member object.');
    }

    public function testCanAddGroups()
    {
        $_POST['name'] = 'test group';
        $_POST['description'] = 'test description';

        $group = ioc::resolve('saMemberGroup');
        $group->setName($_POST['name']);
        $group->setDescription($_POST['description']);
        app::$entityManager->persist($group);
        app::$entityManager->flush();

        $_SESSION['unit_testing']['group_id'] = $group->getId();
    } 

    public function testCanAddMemberToGroups()
    {
        /** @var saMember $member */
        $member = app::$entityManager->find( ioc::staticResolve('saMember'), $_SESSION['unit_testing']['member_id']);
        /** @var saMemberGroup $group */
        $group = app::$entityManager->find( ioc::staticResolve('saMemberGroup'), $_SESSION['unit_testing']['group_id']);
        $member->addGroup( $group );

        app::$entityManager->persist($member);
        app::$entityManager->flush();
    }

    public function testCanGetMemberGroups()
    {
        $auth = auth::getInstance();
        /** @var \sa\member\saMember $member */
        $member = $auth->getAuthMember();
        $this->assertInstanceOf(ioc::staticResolve('saMember'), $member, 'Invalid member object returned.');

        $groups = $member->getGroups();

        $this->assertGreaterThanOrEqual( 1, count($groups), 'Failed to get the member groups.');
        $this->assertInstanceOf('sa\member\saMemberGroup', $groups[0], 'Invalid group object returned.');
    }

    // MORE TEST NEED WROTE FOR THIS MEMBER CLASS
}