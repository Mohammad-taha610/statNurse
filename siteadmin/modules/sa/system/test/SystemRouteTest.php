<?php


namespace sa\system\Test;

use PHPUnit\Framework\Constraint\SameSize;
use PHPUnit\Framework\TestCase;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\Request;
use sacore\application\responses\File;
use sacore\application\responses\Json;
use sacore\application\responses\Raw;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sa\system\saAssetManagerController;
use sa\system\saAuth;
use sa\system\SaAuthController;
use sa\system\saClusterController;
use sa\system\saClusterNode;
use sa\system\saDefaultDataController;
use sa\system\SaLogViewerController;
use sa\system\saSettingsController;
use sa\system\saSystemController;
use sa\system\saUser;
use sa\system\saUserDevice;
use sa\system\SiteBlockController;
use sa\system\systemConfig;
use sa\system\systemController;
use sa\Test\RouteTest;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

//Test the routes for Menus, note, the ordering of functions does matter
class SystemRouteTest extends RouteTest
{
    const NUMBER_OF_ROUTES = 60;

    public function testRouteInit()
    {
        $rCollection = new RouteCollection();
        systemConfig::getRouteCollection($rCollection, 'system');
        $this->assertEquals(self::NUMBER_OF_ROUTES, $rCollection->count());
    }

    public function testRouteAPIUserLoginExists(){
        $this->singleRouteDefinition('api_sa_user_login');
    }

    //Class doesn't exist, just gonna let it fail
    public function testRouteAPIUserLoginFunctionality(){
        $controller = new SaUserApiController();
    }

    public function testRouteSiteBlockExists(){
        $this->singleRouteDefinition('site_block');
    }

    public function testRouteSiteBlockFunctionality(){
        $controller = new SiteBlockController();
        $request = new Request();
        $view = $controller->site_blocked($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testRouteSiteBlockLoginExists(){
        $this->singleRouteDefinitionPost('site_block_login');
    }

    public function testRouteSiteBlockLoginSuccessfulFunctionality(){
        $request = new Request([],['password'=>'elink', 'return_uri' => "/"]);
        $controller = new SiteBlockController();
        $redirect = $controller->site_blocked_login($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
    }

    public function testRouteSiteBlockLoginFailureFunctionality(){
        $request = new Request([],['password'=>'bleh', 'return_uri' => "/"]);
        $controller = new SiteBlockController();
        $redirect = $controller->site_blocked_login($request);
        $this->assertInstanceOf(View::class, $redirect);
    }

    //Not convinced this route needs to exist based on the name, but I could be wrong so its here
    public function testRouteTestingUnitTestingRouteExists(){
        $this->singleRouteDefinition('testing_unit_testing_route');
    }

    public function testRouteTestingUnitTestingRouteFunctionality(){
        //Todo: See if this needs a test, aka is real
        $this->fail();
    }

    public function testRouteSAPingExists(){
        $this->singleRouteDefinitionPost('sa_ping');
    }

    //Most likely a very bad test case, not entirely sure how Ping works or its purpose
    public function testRouteSAPingFunctionality(){
        $requestInfo = ['client_timezone' => 'America/New_York', 'first_ping'=>'false',
            'mouse_activity'=>'true', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36',
            'url'=>'/siteadmin/menus'];
        $request = new Request([], $requestInfo);
        $controller = new systemController();
        $view = $controller->ping($request);
        $this->assertInstanceOf(Json::class, $view);
        $this->fail("It will succeed but considering it does a 500 error on the live page I don't trust it.");
    }

    public function testRouteRobotsTextExists(){
        $this->singleRouteDefinition('robots_txt');
    }

    public function testRouteRobotsTextFunctionality(){
        $controller = new systemController();
        $view = $controller->robots();
        $this->assertInstanceOf(Raw::class, $view);
    }

    public function testRouteSitemapXMLExists(){
        $this->singleRouteDefinition('sitemap_xml');
    }

    public function testRouteSitemapXMLFunctionality(){
        $this->fail("This doesn't return anything, so, not sure how to properly test it");
    }

    public function testRouteSitemapJSONExists(){
        $this->singleRouteDefinition('sitemap_json');
    }

    public function testRouteSitemapJSONFunctionality(){
        $controller = new systemController();
        $json = $controller->sitemapJSON();
        $this->assertInstanceOf(Json::class, $json);
    }

    public function testRouteSitemapHTMLExists(){
        $this->singleRouteDefinition('sitemap_html');
    }

    public function testRouteSitemapHTMLFunctionality(){
        $controller = new systemController();
        $view = $controller->sitemapHTML();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSALoginExists(){
        $this->singleRouteDefinition('sa_login');
    }

    public function testSALoginFunctionality(){
        $request = new Request([], [], static::AUTH_USER_LOGIN_ASSOCIATIVE);
        $controller = new SaAuthController();
        $view = $controller->login($request);
        $this->assertInstanceOf(View::class, $view);

    }

    //Todo: Create testing function for after 3 times logging in

    public function testSASuccessfulLogoffExists(){
        $this->singleRouteDefinition('sa_logoff');
    }

    public function testSALogoffFunctionality(){
        $authentication = new saAuth();
        $authentication->login(...static::AUTH_USER_LOGIN_INDEXED);
        $controller = new SaAuthController();
        $redirect = $controller->logoff();
        $this->assertInstanceOf(Redirect::class, $redirect);
        $this->assertNull(saAuth::getAuthUser());
    }

    public function testSALoginAttemptExists(){
        $this->singleRouteDefinitionPost('sa_loginattempt');
    }

    public function testSALoginAttemptSuccessfulFunctionality(){
        $request = new Request([],static::AUTH_USER_LOGIN_ASSOCIATIVE,[],[],[],['REMOTE_ADDR'=>'127.0.0.1']);
        $controller = new SaAuthController();
        $redirect = $controller->attemptLogin($request);
        $this->assertInstanceOf(Redirect::class, $redirect);

        $user = saAuth::getAuthUser();
        $this->assertInstanceOf(saUser::class, $user);
    }

    public function testSALoginAttemptFailureFunctionality(){
        $request = new Request([],['username'=>'bleh','password'=>''],[],[],[],['REMOTE_ADDR'=>'127.0.0.1']);
        $controller = new SaAuthController();
        $view = $controller->attemptLogin($request);
        $this->assertInstanceOf(View::class, $view);
        $this->assertNull(saAuth::getAuthUser());
    }

    public function testSAImportLocationDataExists(){
        $this->singleRouteDefinition('sa_import_location_data');
    }

    //This will obviously error, function does not exist but I will leave the test here in case it is supposed to
    public function testSAImportLocationDataFunctionality(){
        $controller = new SaAuthController();
        $controller->importLocationData();
        //Honestly, I have no idea why the above line doesn't throw an error, I really feel like it should
        $this->assertTrue(method_exists($controller, 'importLocationData'));
    }

    public function testSAPermissionDeniedExists(){
        $this->singleRouteDefinition('sa_permission_denied');
    }

    public function testSAPermisionDeniedFunctionality(){
        $controller = new SaAuthController();
        $controller->showPermissionDenied();
        $this->assertTrue(method_exists($controller, 'showPermissionDenied'));
    }

    public function testSAHumanVerifyExists(){
        $this->singleRouteDefinition('sa_humanverify');
    }

    public function testSAHumanVerifyFunctionality(){
        $request = new Request([],[],[],[],[],['REMOTE_ADDR'=>'127.0.0.1']);
        $controller = new SaAuthController();
        $view = $controller->humanVerify($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSAHumanVerifyPostExists(){
        $this->singleRouteDefinitionPost('sa_humanverifypost');
    }

    //Todo: Bad Test, not sure how to send a valid captcha
    public function testSAHumanVerifyPostSuccessFunctionality(){
        $request = new Request([],['g-recaptcha-response'=> true],[],[],[],['REMOTE_ADDR'=>'127.0.0.1']);
        $controller = new SaAuthController();
        $redirect = $controller->humanVerifyAttempt($request);
        $this->fail("Not sure how to generate a valid captcha input, will fail until someone figures that out");
//        $this->assertInstanceOf(Redirect::class, $redirect);
    }

    public function testSAHumanVerifyPostFailFunctionality(){
        $request = new Request([],['g-recaptcha-response'=> false],[],[],[],['REMOTE_ADDR'=>'127.0.0.1']);
        $controller = new SaAuthController();
        $redirect = $controller->humanVerifyAttempt($request);
        $this->assertInstanceOf(View::class, $redirect);
    }

    public function testSAMachineVerifyExists(){
        $this->singleRouteDefinition('sa_machineverify');
    }

    public function testSAMachineVerifyFunctionality(){
        $this->loginUser();
        $controller = new SaAuthController();
        $view = $controller->machineVerify();
        $this->assertInstanceOf(View::class, $view);
        $this->logoffUser();
    }

    public function testSALocationBlockedExists(){
        $this->singleRouteDefinition('sa_location_blocked');
    }

    public function testSALocationBlockedFunctionality(){
        $controller = new SaAuthController();
        $view = $controller->loginLocationRestricted();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSiteTwoFactorExists(){
        $this->singleRouteDefinition('sa_two_factor_verify');
    }

    public function testSiteTwoFactorFunctionality(){
        $this->loginUser();
        $controller = new SaAuthController();
        $view = $controller->twoFactorVerify();
        $this->assertInstanceOf(View::class, $view);
        $this->logoffUser();
    }

    public function testSystemImagesExists(){
        $this->singleRouteDefinition('system_images');
    }

    public function testSystemImagesFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['file' => 'tree_one_way.gif']));
        $controller = new saSystemController();
        $response = $controller->images($request);
        $this->assertInstanceOf(File::class, $response);
    }

    public function testSystemJSExists(){
        $this->singleRouteDefinition('system_js');
    }

    public function testSystemJSFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['file' => 'settings.js']));
        $controller = new saSystemController();
        $response = $controller->js($request);
        $this->assertInstanceOf(File::class, $response);
    }

    public function testSystemCSSExists(){
        $this->singleRouteDefinition('system_css');
    }

    public function testSystemCSSFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['file' => 'settings.css']));
        $controller = new saSystemController();
        $response = $controller->css($request);
        $this->assertInstanceOf(File::class, $response);
    }

    public function testThemeResourcesExists(){
        $this->singleRouteDefinition('theme_resources');
    }

    public function testThemeResourcesFunctionality(){
        $request = new Request();
        $url = "/themes/roodandriddle/assets/images/mstile-144x144.png";
        $request->setRequestUri($url);
        $controller = new systemController();
        $file = $controller->themeResource($request);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testThemeResourcesBuildExists(){
        $this->singleRouteDefinition('theme_resources_build');
    }

    //I didn't see /build/themes anywhere but I will put this here anyway
    public function testThemeResourcesBuildFunctionality(){
        $request = new Request();
        $url = "/build/themes/roodandriddle/assets/images/mstile-144x144.png";
        $request->setRequestUri($url);
        $controller = new systemController();
        $file = $controller->themeResource($request);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testComponentResourcesExists(){
        $this->singleRouteDefinition('component_resources');
    }

    //No components in the project, gonna auto fail as there isn't anything to test with
    public function testComponentResourcesFunctionality(){
        $this->fail("No components in the current project");
    }

    public function testComponentResourcesBuildExists(){
        $this->singleRouteDefinition('component_resources_build');
    }

    public function testComponentResourcesBuildFunctionality(){
        $this->fail("No components in the current project");
    }

    public function testSystemResourcesExists(){
        $this->singleRouteDefinition('system_resources');
    }

    public function testSystemResourcesFunctionality(){
        $this->fail("Pretty sure this isn't used in the project anywhere");
    }

    public function testSystemResourcesBuildExists(){
        $this->singleRouteDefinition('system_resources_build');
    }

    public function testSystemResourcesBuildFunctionality(){
        $this->fail("Pretty sure this isn't used in the project anywhere");
    }

    public function testVendorResourcesExists(){
        $this->singleRouteDefinition('vendor_resources');
    }

    public function testVendorResourceFunctionality(){
        $url = "/vendor/blueimp/jquery-file-upload/css/jquery.fileupload.css";
        $request = new Request();
        $request->setRequestUri($url);
        $controller = new systemController();
        $file = $controller->vendorResource($request);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testVendorResourceBuildExists(){
        $this->singleRouteDefinition('vendor_resources_build');
    }

    public function testVendorResourceBuildFunctionality(){
        $url = "/vendor/blueimp/jquery-file-upload/css/jquery.fileupload.css";
        $request = new Request();
        $request->setRequestUri($url);
        $controller = new systemController();
        $file = $controller->vendorResource($request);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testSASettingsExists(){
        $this->singleRouteDefinition('sa_settings');
    }

    public function testSASettingsFunctionality(){
        $controller = new saSettingsController();
        $view = $controller->viewSettings();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSASettingsPostExists(){
        $this->singleRouteDefinitionPost('sa_settings_post');
    }

    //Todo: More thorough check can be done
    public function testSASettingsPostFunctionality(){
        $request = new Request([],['system'=>['require_ssl' => 0]]);
        $controller = new saSettingsController();
        $redirect = $controller->saveSettings($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
    }

    public function testSASettingsModalExists(){
        $this->singleRouteDefinition('sa_settings_modal');
    }

    public function testSASettingsModalFunctionality(){
        $controller = new saSettingsController();
        $view = $controller->viewSettings();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSASettingsModalPostExists(){
        $this->singleRouteDefinitionPost('sa_settings_modal_post');
    }

    //Todo: More thorough check can be done
    public function testSASettingsModalPostFunctionality(){
        $request = new Request([],['system'=>['require_ssl' => 0]]);
        $controller = new saSettingsController();
        $redirect = $controller->saveSettings($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
    }

    public function testSAUsersExists(){
        $this->singleRouteDefinition('sa_sausers');
    }

    public function testSAUsersFunctionality(){
        $controller = new saSystemController();
        $view = $controller->manageSAUsers();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testCreateUserExists(){
        $this->singleRouteDefinition('sa_sausers_create');
    }

    public function testCreateUserFunctionality(){
        $this->loginUser();
        $request = new Request();
        $controller = new saSystemController();
        $view = $controller->createSAUsers($request);
        $this->assertInstanceOf(View::class, $view);
        $this->logoffUser();
    }

    public function testInsertUserExists(){
        $this->singleRouteDefinition('sa_sausers_insert');
    }

    public function testInsertUserFunctionality(){
        //Setup
        $this->loginUser();
        $requestInfo = ['first_name' => 'firstInsertTest', 'last_name'=> 'lastInsertTest',
            'username' => 'insertTest','password'=>'test','confirm_password'=>'test',
            'is_active'=> 1, 'user_type' => saUser::TYPE_STANDARD, 'cell_number' => '555-555-5555',
            'allowed_login_locations' => 'N;'];
        $request = new Request([], $requestInfo);
        $controller = new saSystemController();

        //Test
        $redirect = $controller->insertSAUsers($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $userRepo = ioc::getRepository('saUser');
        $entity = $userRepo->search(['first_name' => 'firstInsertTest'])[0];
        $this->assertInstanceOf(saUser::class, $entity);

        //Cleanup
        $this->logoffUser();

        //Going to save this user to be used on all the other tests, if that becomes a problem uncomment this
//        app::$entityManager->remove($entity);
//        app::$entityManager->flush();
    }

    public function testEditUserExists(){
        $this->singleRouteDefinition('sa_sausers_edit');
    }

    public function testEditUserFunctionality(){
        $this->loginUser();
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id'=>2]));
        $controller = new saSystemController();
        $view = $controller->editSAUsers($request);
        $this->assertInstanceOf(View::class, $view);
        $this->logoffUser();
    }

    public function testSaveUserExists(){
        $this->singleRouteDefinition('sa_sausers_save');
    }

    public function testSaveUserFunctionality(){
        $requestInfo = ['first_name' => 'firstTestTheSecond', 'last_name'=> 'lastTest',
            'username' => 'testSaveTest','password'=>'test','confirm_password'=>'test',
            'is_active'=> 1, 'user_type' => saUser::TYPE_STANDARD, 'cell_number' => '555-555-5555',
            'allowed_login_locations' => 'N;'];
        $request = new Request([], $requestInfo);
        //Id will be 2 as there have only been 2 sausers created and we are looking for the second one
        $request->setRouteParams(new ParameterBag(['id'=>2]));
        $controller = new saSystemController();

        $redirect = $controller->saveSAUsers($request);
        $this->assertInstanceOf(Redirect::class, $redirect);

        $userRepo = ioc::getRepository('saUser');
        $entity = $userRepo->search(['first_name' => 'firstTestTheSecond'])[0];
        $this->assertInstanceOf(saUser::class, $entity);
    }

    public function testDeleteUserExists(){
        $this->singleRouteDefinition('sa_sausers_save');
    }

    public function testDeleteUserFunctionality(){
        //Setup
        $this->loginUser();
        $request = new Request();
        //Id will be 2 as there have only been 2 sausers created and we are looking for the second one
        $request->setRouteParams(new ParameterBag(['id'=>2]));
        $controller = new saSystemController();

        //Test
        $redirect = $controller->deleteSAUsers($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $userRepo = ioc::getRepository('saUser');
        $entity = $userRepo->search(['first_name' => 'firstTestTheSecond']);
        $this->assertEmpty($entity);

        //Cleanup
        $this->logoffUser();
    }

    public function testSAUsersDeactivateDeviceExists(){
        $this->singleRouteDefinition('sa_sausers_deactivate_device');
    }

    public function testSAUsersDeactiveDeviceFunctionality(){
        //Setup
        /**
         * @var saUserDevice $userDevice
         */
        $userDevice = ioc::resolve('saUserDevice');
        $userRepo = ioc::getRepository('saUser');
        $user = $userRepo->find(1);
        $userDevice->setUser($user);
        $userDevice->setIsActive(true);
        $userDevice->setVerified(true);
        app::$entityManager->persist($userDevice);
        app::$entityManager->flush();

        $request = new Request();
        $request->setRouteParams(new ParameterBag(['userId' => 1,'deviceId' => 1]));
        $controller = new saSystemController();

        //Test
        $redirect = $controller->deactivateSAUserDevice($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $userDevice = ioc::getRepository('saUserDevice')->find(1);
        $this->assertFalse($userDevice->getIsActive());

        //Cleanup
        app::$entityManager->remove($userDevice);
        app::$entityManager->flush();
    }

    public function testSADefaultDataExists(){
        $this->singleRouteDefinition('sa_default_data');
    }

    public function testSADefaultDataFunctionality(){
        $controller = new saDefaultDataController();
        $view = $controller->defaultDataIndex();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSADefaultDataImportExists(){
        $this->singleRouteDefinition('sa_default_data_import');
    }

    public function testSADefaultDataImportFunctionality(){
        $request = new Request([],['module' => ['system']]);
        $controller = new saDefaultDataController();
        $view = $controller->defaultDataImport($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSADeleteInstallScriptExists(){
        $this->singleRouteDefinition('sa_delete_install_script');
    }

    public function testSADeleteInstallScriptFunctionality(){
        //Create file for testing
        $public_directory = app::get()->getConfiguration()->get('public_directory')->getValue();
        $target = $public_directory . "/install.php";
        file_put_contents($target, "TestSomething");

        $controller = new saSystemController();
        $redirect = '';
        try {
            $redirect = $controller->deleteInstallScript();
        }catch (\Error $e){
            $this->fail($e->getMessage());
        }
        $this->assertInstanceOf(Redirect::class, $redirect);

    }

    public function testSystemSafeModeExists(){
        $this->singleRouteDefinition('system_safemode');
    }

    public function testSystemSafeModeFunctionality(){
        file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue().'/safe-mode.log', '');

        $controller = new saSystemController();
        $view = $controller->safeMode();
        $this->assertInstanceOf(View::class, $view);

        unlink(app::get()->getConfiguration()->get('tempDir')->getValue().'/safe-mode.log');
    }

    public function testSystemSafeModeDisableExists(){
        $this->singleRouteDefinition('system_safemode_disable');
    }

    public function testSystemSafeModeDisableFunctionality(){
        $controller = new saSystemController();
        app::get()->enable_safe_mode("");
        $redirect = $controller->safeModeDisable();
        $this->assertInstanceOf(Redirect::class, $redirect);

        $this->assertFalse(app::get()->getConfiguration()->get('safe_mode')->getValue());
        app::get()->disable_safe_mode();
    }

    public function testSASystemGenerateSpriteExists(){
        $this->singleRouteDefinition('sa_system_generate_sprite');
    }

    public function testSASystemGenerateSpriteFunctionality(){
        $request = new Request([],['template'=>'master']);
        $controller = new saSystemController();
        $view = $controller->generateSpriteResources($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSAFlushBuildExists(){
        $this->singleRouteDefinition('sa_flush_build');
    }

    public function testSAFlushBuildFunctionality(){
        $controller = new saAssetManagerController();
        $redirect = $controller->flush();
        $this->assertInstanceOf(Redirect::class, $redirect);
    }

    public function testSAFlushCacheExists(){
        $this->singleRouteDefinition('sa_flush_cache');
    }

    public function testSAFlushCacheFunctionality(){
        $controller = new saAssetManagerController();
        $controller->flushCache();
        $this->assertTrue(method_exists($controller, 'flushCache'));
    }

    public function testSABuildAssetCacheCombineExists(){
        $this->singleRouteDefinition('sa_build_asset_cache_combine');
    }

    //Todo: Bad Test, not entirely sure the purpose of the function so not entirely sure what the correct outcome is
    public function testSABuildAssetCacheCombineFunctionality(){
        $controller = new saAssetManagerController();
        $view = $controller->build();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSABuildAssetCacheCombineNowExists(){
        $this->singleRouteDefinition('sa_build_asset_cache_combine_now');
    }

    //Todo: Bad Test, not entirely sure the purpose of the function so not entirely sure what the correct outcome is
    public function testSABuildAssetCacheCombineNowFunctionality(){
        $controller = new saAssetManagerController();
        $view = $controller->buildNow();
    }

    public function testSAAssetBuildLogExists(){
        $this->singleRouteDefinition('sa_asset_build_log');
    }

    public function testSAAssetBuildLogFunctionality(){
        $controller = new saAssetManagerController();
        $view = $controller->buildLog();
        $this->assertInstanceOf(Json::class, $view);
    }

    //Weird name, maybe be moved into an actual test method later?
    public function testTestGeoCodingIPExists(){
        $this->singleRouteDefinition('test_geo_coding_ip');
    }

    public function testTestGeoCodingIPFunctionality(){
        $controller = new systemController();
        $controller->repairOnlineUsersGeo();
        //The table is left with a bunch of non null empty columns(''), not sure if that is intended but I'll mark it
//        $users = ioc::getRepository('OnlineUser')->findBy(array('ip_country'=>''));
        $users = ioc::getRepository('OnlineUser')->findBy(array('ip_country'=>null));
        $this->assertCount(0, $users);
    }

    public function testSASystemClusterExists(){
        $this->singleRouteDefinition('sa_system_cluster');
    }

    public function testSASystemClusterFunctionality(){
        $controller = new saClusterController();
        $view = $controller->index();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSASystemClusterNodeDeleteExists(){
        $this->singleRouteDefinition('sa_system_cluster_node_delete');
    }

    public function testSASystemClusterNodeDeleteFunctionality(){
        //Setup
        /**
         * @var saClusterNode $clusterNode
         */
        $clusterNode = ioc::resolve('saClusterNode');
        $clusterNode->setName("test");
        app::$entityManager->persist($clusterNode);
        app::$entityManager->flush();

        $nodes = ioc::getRepository('saClusterNode')->findBy(array('name'=>'test'));
        $this->assertCount(1, $nodes);

        //Test
        $controller = new saClusterController();
        $redirect = $controller->delete($clusterNode);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $nodes = ioc::getRepository('saClusterNode')->findBy(array('name'=>'test'));
        $this->assertCount(0, $nodes);
    }

    public function testSASystemClusterNodeAddExists(){
        $this->singleRouteDefinition('sa_system_cluster_node_add');
    }

    public function testSASystemClusterNodeAddFunctionality(){
        $controller =  new saClusterController();
        $view = $controller->showAdd();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSASystemClusterNodeEditExists(){
        $this->singleRouteDefinition('sa_system_cluster_node_edit');
    }

    public function testSASystemClusterNodeEditFunctionality(){
        //Setup
        /**
         * @var saClusterNode $clusterNode
         */
        $clusterNode = ioc::resolve('saClusterNode');
        $clusterNode->setName("test");
        app::$entityManager->persist($clusterNode);
        app::$entityManager->flush();

        //Test
        $controller =  new saClusterController();
        $view = $controller->showEdit();
        $this->assertInstanceOf(View::class, $view);

        //Cleanup
        app::$entityManager->remove($clusterNode);
        app::$entityManager->flush();
    }

    public function testSASystemClusterNodeAddSaveExists(){
        $this->singleRouteDefinition('sa_system_cluster_node_add_save');
    }

    //Ignore until API module is added
    public function testSASystemClusterNodeAddSaveFunctionality(){
//        $request = new Request([],[]);
//        $controller = new saClusterController();
//        $controller->saveAdd();
        $this->fail("Until API Module is added in this will fail");
    }

    public function testSASystemClusterNodeEditSaveExists(){
        $this->singleRouteDefinition('sa_system_cluster_node_edit_save');
    }

    public function testSASystemClusterNodeEditSaveFunctionality(){
        $this->fail("Until API Module is added in this will fail");
    }

    public function testSystemLogExists(){
        $this->singleRouteDefinition('sa_system_log');
    }

    public function testSystemLogFunctionality(){
        $log = new SaLogViewerController();
        $view = $log->index();
        $this->assertInstanceOf(View::class, $view);
    }



    /** HELPER FUNCTIONS */
    protected function moduleName()
    {
        return "system";
    }

    protected function configClass()
    {
        return systemConfig::class;
    }
}

