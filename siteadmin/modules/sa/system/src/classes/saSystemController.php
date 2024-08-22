<?php
namespace sa\system;

use Captcha\Captcha;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Johnstyle\GoogleAuthenticator\GoogleAuthenticator;
use \sacore\application\app;
use sacore\application\Event;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\IocDuplicateClassException;
use sacore\application\IocException;
use sacore\application\modRequest;
use sacore\application\ModRequestAuthenticationException;
use sacore\application\Request;
use sacore\application\responses\File;
use sacore\application\responses\ISaResponse;
use sacore\application\responses\Redirect;
use sacore\application\responses\ResponseUtils;
use \sacore\application\saController;
use \sacore\application\modelResult;
use sacore\application\ValidateException;
use \sacore\application\responses\View;
use sacore\application\ViewException;
use sacore\utilities\doctrineUtils;
use sacore\application\DateTime;
use \sacore\utilities\url;
use \sacore\utilities\notification;
use sacore\utilities\UserAgent;


class saSystemController extends saController {

	public static function headerWidget($html = '')
	{
		$auth = saAuth::getInstance();
		if (is_object($auth))
		{
			$user = $auth->getAuthUser();

			$view = new View( 'saHeaderWidget', self::viewLocation() );

			$view->data['performance_header_msg'] = 'Running at optimum performance';
			$view->data['performance_status'] = 'check';

			$status = array();

			$app = app::get();

			if ( method_exists($app, 'isRunningPrimaryDBInstance') && !$app->isRunningPrimaryDBInstance()) {
				$view->data['performance_msg'][] = array('type' => 'danger', 'msg' => 'Your site is using the secondary failover database.  Please inspect and fix!');
				$view->data['performance_header_msg'] = '';
				$status[] = 'danger';
			}



			if (!app::get()->getConfiguration()->get('require_ssl')->getValue()) {
				$view->data['performance_msg'][] = array('type' => 'warning', 'msg' => 'Your site does not require an ssl.  This will result in a more insecure site.');
				$view->data['performance_header_msg'] = '';
				$status[] = 'warning';
			}

			if (!app::get()->getConfiguration()->get('site_robot_indexable')->getValue()) {
				$view->data['performance_msg'][] = array('type' => 'danger', 'msg' => 'Your site does not allow robots to index it.  This will destroy your seo.');
				$view->data['performance_header_msg'] = '';
				$status[] = 'danger';
			}

			if (!app::get()->getConfiguration()->get('force_www_redirect')->getValue()) {
				$view->data['performance_msg'][] = array('type' => 'warning', 'msg' => 'Your site does not enforce the use of www. If your site is accessible via www.domain.com and domain.com this could impact your seo.');
				$view->data['performance_header_msg'] = '';
				$status[] = 'warning';
			}
			
			$environment = $app->getEnvironment();

			if (app::getDevMode()) {
				$view->data['performance_msg'][] = array('type' => 'danger', 'msg' => 'Your site is in development mode.  This will significantly impact performance.');
				$view->data['performance_header_msg'] = '';
				$status[] = 'danger';
			}

			if ($environment['errorreporting']) {
				$view->data['performance_msg'][] = array('type' => 'danger', 'msg' => 'Error reporting is turned on.  This will significantly impact system security.');
				$view->data['performance_header_msg'] = '';
				$status[] = 'danger';
			}

			if (static::findInstallScript()) {
				$view->data['performance_msg'][] = array('type' => 'danger', 'msg' => 'The Site Administrator install script has not been removed.  This can possibly lead to data loss. <a href="/siteadmin/remove_script"> Remove Now? </a>' );
				$view->data['performance_header_msg'] = '';
				$status[] = 'danger';
			}

			if (!app::get()->getConfiguration()->get('combine_resources')->getValue() && !app::get()->getConfiguration()->get('http2')->getValue()) {
				$view->data['performance_msg'][] = array('type' => 'warning', 'msg' => 'Your site does not enforce css and js combining or HTTP2 Protocol.  This will significantly impact performance.');
				$view->data['performance_header_msg'] = '';
				$status[] = 'warning';
			}

			if (empty(app::get()->getConfiguration()->get('recaptcha_public')->getValue()) || empty(app::get()->getConfiguration()->get('recaptcha_private')->getValue())) {
				$view->data['performance_msg'][] = array('type' => 'warning', 'msg' => 'Your site does not have the recaptcha keys installed. This will result in a more insecure site.');
				$view->data['performance_header_msg'] = '';
				$status[] = 'warning';
			}


			if (!app::get()->getConfiguration()->get('smtp_host')->getValue() || !app::get()->getConfiguration()->get('smtp_password')->getValue() || !app::get()->getConfiguration()->get('smtp_username')->getValue()) {
				$view->data['performance_msg'][] = array('type' => 'danger', 'msg' => 'SMTP is not configured.  The site will not be able to send emails.');
				$view->data['performance_header_msg'] = '';
				$status[] = 'danger';
			}
			
			if($app->getCacheManager()->isCacheFailSafeMode()) {
                $view->data['performance_msg'][] = array('type' => 'danger', 'msg' => 'Configured Cache system is unavailable. Site performance may suffer due to this issue.');
                $view->data['performance_header_msg'] = '';
                $status[] = 'danger';
			}
			
			// Get alerts from modules
			$modulePerformanceAlerts = modRequest::request('sa.performance_alerts');
			if($modulePerformanceAlerts) {
				foreach($modulePerformanceAlerts as $alert) {
					$view->data['performance_msg'][] = $alert['performance_msg'];
					$view->data['performance_header_msg'] = $alert['performance_header_msg'];
					$status[] = $alert['status'];
				}
			}

			if (in_array('danger', $status)) {
				$view->data['performance_status'] = 'danger';
			}
			else if (in_array('warning', $status)) {
				$view->data['performance_status'] = 'warning';
			}


			$view->data['user'] = $user;
            $html .= $view->getHTML();
		}

        return $html;
	}

	public function showPermissionDenied() {

		$view = new View('permission_denied');
		return $view;

	}



    public function reloadApp()
    {
        Event::fire('app.reload');

        return new Redirect( url::make('sa_dashboard') );
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
	public function images($request)
	{
        $file_path = ResponseUtils::filePath($request->getRouteParams()->get('file'), static::assetLocation('images'));
        if (!$file_path) {
            $response = $this->error404();
        }
        else{
            $response = new File($file_path);
        }
        return $response;
	}






	public function manageSAUsers()
	{
		$view = new View('table', $this->viewLocation());

		$perPage = 20;
		$currentPage = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$sort = !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : false;
		$sortDir = !empty($_REQUEST['sortDir']) ? $_REQUEST['sortDir'] : false;
		//$totalRecords = saUser::findAll(false, false, false, $sort, $sortDir)->length;
		//$data = saUser::findAll(false, $perPage, (($currentPage-1)*$perPage), $sort, $sortDir)->toArray();

		/** @var saUserRepository $repo */
		$repo = app::$entityManager->getRepository( ioc::staticResolve('saUser') );

		$orderBy = null;
		if ($sort) {
			$orderBy = array($sort => $sortDir);
		}

		$data = $repo->search( null, $orderBy, $perPage, (($currentPage-1)*$perPage) );
		$totalRecords = $repo->search( null,null, null, null, true );

		$totalPages = ceil($totalRecords / $perPage);

		$view->data['table'][] = array(
			/* SET THE HEADER OF THE TABLE UP */
			'header'=>array(
				array('name'=>'Last Name', 'class'=>'', 'map'=>'last_name'),
				array('name'=>'First Name', 'class'=>'', 'map'=>'first_name'),
				array('name'=>'Username', 'class'=>'', 'map'=>'username'),
				array('name'=>'Last Logon', 'class'=>'', 'map'=>'last_login'),
				array('name'=>'Is Active', 'class'=>'', 'type'=>'boolean', 'map'=>'is_active'),),
			/* SET ACTIONS ON EVERY ROW */
			'actions'=>array(
				'edit'=>array('name'=>'Edit', 'routeid'=>'sa_sausers_edit', 'params'=>array('id') ),
				'delete'=>array('name'=>'Delete', 'routeid'=>'sa_sausers_delete', 'params'=>array('id')),
			),
			/* SET THE NO DATA MESSAGE */
			'noDataMessage'=>'No SA Users Available',
			/* SET THE ACTION FOR THE HEADER CREATE BUTTON */
			'tableCreateRoute'=>'sa_sausers_create',
			/* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
			'data'=> $data,
			/* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
			'totalRecords'=> $totalRecords,
			'totalPages'=> $totalPages,
			'currentPage'=> $currentPage,
			'perPage'=> $perPage,
			'dataRowCallback'=> function($data) {
				if ($data["last_login"])
					$data["last_login"] = $data["last_login"]->format("m/d/Y g:i a");

				return $data;
			}
		);

		return $view;
	}

    /**
     * @param Request $request
     * @return View
     * @throws Exception
     * @throws ModRequestAuthenticationException
     */
	public function editSAUsers(Request $request) : ISaResponse
	{
        /** @var saUserRepository $userRepo */
        $userRepo = ioc::getRepository('saUser');
        $view = new View('sa_user_edit', $this->viewLocation());
        $id = $request->getRouteParams()->get('id');

        if(!is_null($id) && $id>0) {
            /** @var saUser $user */
            $user = $userRepo->find($id);

            $view->data['id'] = $request->getRouteParams()->get('id');
            $view->data['first_name'] = $user->getFirstName();
            $view->data['last_name'] = $user->getLastName();
            $view->data['is_active'] = $user->getIsActive();
            $view->data['cell_number'] = $user->getCellNumber();
            $view->data['username'] = $user->getUsername();
            $view->data['email'] = $user->getEmail();
            $view->data['user_group'] = $user->getSaUserGroup()?->toArray();

            $permissions = $user->getPermissions();
            $view->data['permissions'] = $permissions;
        } else{
            $view->data['id'] = 0;
        }
		
		$view->data['groups'] = doctrineUtils::getEntityCollectionArray(ioc::getRepository('saUserGroup')->findAll());

        $per_page = 20;
        $history_currentPage = !is_null($request->get('history_page')) ? $request->get('history_page') : 1;
        $history_sort = !is_null($request->get('history_sort')) ? $request->get('history_sort') : false;
        $history_sortDir = !is_null($request->get('history_sortDir')) ? $request->get('history_sortDir') : false;
        $history_totalRecords = 0;
        $history_totalPages = 0;

        $devices_currentPage = !is_null($request->get('devices_page')) ? $request->get('devices_page') : 1;
        $devices_sort = !is_null($request->get('devices_sort')) ? $request->get('devices_sort') : false;
        $devices_sortDir = !is_null($request->get('devices_sortDir')) ? $request->get('devices_sortDir') : false;
        $devices_totalRecords = 0;
        $devices_totalPages = 0;

        /** @var saUser $curUser */
        $curUser = modRequest::request('sa.user');

        $view->data['availablePermissions'] = $userRepo->filterPermissionsFor($curUser);
        $view->data['cur_user_type'] = $curUser->getUserType();

        $view->data['table'][] = array(
            'id' => 'devices',
            'title' => 'Approved Devices',
            'tabid' => 'edit-approved-devices',
            'header' => array(
                array('name' => 'Date', 'map' => 'issue_date'),
                array('name' => 'Description', 'map' => 'description'),
                array('name' => 'Machine Id', 'map' => 'machine_id'),
                array('name' => 'Last Activity', 'map' => 'last_activity_date')
            ),
            'append_links' => '#edit-approved-devices',
            'actions' => array('delete' => array('name' => 'Deactivate', 'routeid' => 'sa_sausers_deactivate_device', 'params' => array($id, 'id'))),
            'totalRecords' => $devices_totalRecords,
            'totalPages' => $devices_totalPages,
            'currentPage' => $devices_currentPage,
            'perPage' => $perPage,
            //'tableCreateRoute' => array('routeId' => 'member_sa_addgrouptomember', 'params' => array($memberId)),
            'data' => doctrineUtils::getEntityCollectionArray($devices_activity),
            'dataRowCallback' => function ($data) {

                if ($data["issue_date"])
                    $data["issue_date"] = $data["issue_date"]->format("m/d/Y g:i a");

                if ($data["last_activity_date"])
                    $data["last_activity_date"] = $data["last_activity_date"]->format("m/d/Y g:i a");

                if (!$data["verified"])
                    $data["last_activity_date"] = '<span class="label label-danger arrowed">Pending Code Verification</span>';

                $agent = UserAgent::parse($data["description"]);
                if ($agent)
                    $data['description'] = $agent->getBrowser() . ' / ' . $agent->getPlatform();

                return $data;
            }
        );

        $view->data['table'][] = array(
            'id' => 'history',
            'title' => 'Login History',
            'tabid' => 'edit-login-history',
            'header' => array(
                array('name' => 'Date', 'map' => 'date'),
                array('name' => 'Was Success', 'map' => 'was_success'),
                array('name' => 'IP Address', 'map' => 'ip_address'),
                array('name' => 'Machine UUID', 'map' => 'machine_uuid'),
                array('name' => 'Browser/OS', 'map' => 'user_agent'),
            ),
            'append_links' => '#edit-login-history',
            //'actions' => array('delete' => array('name' => 'Delete', 'routeid' => 'member_sa_deletememberfromgroup', 'params' => array($memberId, 'id')),),
            'totalRecords' => $history_totalRecords,
            'totalPages' => $history_totalPages,
            'currentPage' => $history_currentPage,
            'perPage' => $perPage,
            //'tableCreateRoute' => array('routeId' => 'member_sa_addgrouptomember', 'params' => array($memberId)),
            'data' => doctrineUtils::getEntityCollectionArray($history_activity),
            'dataRowCallback' => function ($data) {

                if ($data["date"])
                    $data["date"] = $data["date"]->format("m/d/Y g:i a");

                $data["was_success"] = $data["was_success"] ? 'Yes' : 'No';

                $agent = UserAgent::parse($data["user_agent"]);

                if ($agent)
                    $data['user_agent'] = $agent->getBrowser() . ' / ' . $agent->getPlatform();

                return $data;
            }
        );

        // Get Additional tabs from modules for the SaUser Edit Page
        $other_tabs = modRequest::request('sa.user.other_tabs', array('tabs' => array(), 'data' => $view->data));
        $view->data['other_tabs'] = $other_tabs['tabs'];

        $view->addXssSanitationExclude('html');

        return $view;
	}

    /**
     * @param Request $request
     * @return ISaResponse|Redirect|View
     * @throws Exception
     * @throws ModRequestAuthenticationException
     * @throws MappingException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws IocDuplicateClassException
     * @throws IocException
     */
	public function saveSAUsers(Request $request)
	{
		$notify = new notification();
		$id = $request->getRouteParams()->get('id');

		/** @var saUser $user */
        $saUser = ioc::staticResolve('saUser');

        if(!is_null($id) && $id>0) {
            $user = app::$entityManager->find($saUser, $id);
        } else {
            $user = ioc::resolve($saUser);
        }

        $data = $request->request;

        $user = doctrineUtils::setEntityData($request->request->all(), $user, true);
		if($data->get('user_group')) {
			$user->setSaUserGroup(ioc::getRepository('saUserGroup')->find($data->get('user_group')));
		} else {
			$user->setSaUserGroup(null);
		}
		
        $password = $data->get('password');
        $confirm_password = $data->get('confirm_password');

        if (!is_null($password) && $password == $confirm_password) {
            $user->setPassword($password);
        } elseif (!is_null($password)) {
            $notify->addNotification('danger', 'Error', 'The passwords you entered do not match.');
            //Todo: fix this
            return $this->editSAUsers($request);
        }

        try {
            app::$entityManager->persist($user);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'User saved successfully.');

            return new Redirect(app::get()->getRouter()->generate('sa_sausers'));
        } catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occured while saving your changes. <br />' . $e->getMessage());
            return $this->editSAUsers($request);
        }
    }

	public function deactivateSAUserDevice($request) {

	    $user = ioc::getRepository('saUser')->find($request->getRouteParams()->get('userId'));
	    $device = ioc::getRepository('saUserDevice')->find($request->getRouteParams()->get('deviceId'));
		$device->setIsActive(false);

		$notify = new notification();

		try {
			app::$entityManager->flush($device);
			$notify->addNotification('success', 'Success', 'Device deactivate successfully.');

            return new Redirect( app::get()->getRouter()->generate( 'sa_sausers_edit', ["id" => $user->getId()]));
		}
		catch (ValidateException $e) {

			$notify->addNotification('danger', 'Error', 'An error occured while saving your changes. <br />'. $e->getMessage());

            return new Redirect( app::get()->getRouter()->generate( 'sa_sausers_edit', ["id"=> $user->getId()]));
		}
	}

	public function deleteSAUsers($request){

        $id = $request->getRouteParams()->get('id');
		$saUser = ioc::staticResolve('saUser');
		$user = app::$entityManager->find($saUser, $id);

		$notify = new notification();

		try {
            /** @var saUser $currentUser */
            $currentUser = modRequest::request('sa.user');
            $currentUserId = $currentUser->getId();
		    
			app::$entityManager->remove($user);
			app::$entityManager->flush();
			$notify->addNotification('success', 'Success', 'User deleted successfully.');

            if($id == $currentUserId) {
                saAuth::getInstance()->logoff();
                return new Redirect( app::get()->getRouter()->generate( 'sa_login' ) );
            } else {
                return new Redirect( app::get()->getRouter()->generate('sa_sausers') );
            }
        }
		catch (ValidateException $e) {
			$notify->addNotification('danger', 'Error', 'An error occured while saving your changes. <br />'. $e->getMessage());
            return new Redirect( app::get()->getRouter()->generate( 'sa_sausers' ) );
		}
	}



	public function safeMode() {

		$view = new View('safe_mode');
        $view->data['log'] = nl2br(file_get_contents(app::get()->getConfiguration()->get('tempDir')->getValue().'/safe-mode.log'));
		$view->addXssSanitationExclude('log');
        return $view;

	}


	public function safeModeDisable() {

		app::get()->disable_safe_mode();

		return new Redirect( app::get()->getRouter()->generate( 'sa_dashboard' ));

	}

	/*
	 * @return boolean
	 *
	 * Checks the web root of the application (as defined in the config file) to see if the install script
	 * is still there.  Returns a boolean true if the install script is present in the root, and false if not.
	 */
	protected static function findInstallScript() {
		// Checks only in the root directory.  I think that long-term, it would be a good idea to check deeper
		// into the directory structure, but that's going to be more than is really necessary for now.
        $public_directory = app::get()->getConfiguration()->get('public_directory')->getValue();

		return (@filesize($public_directory . "/install.php") > 0) || (@filesize($public_directory . "/sa.install.php") > 0) || (@filesize($public_directory . "/sa.install.phar") > 0);

	}

	/*
	 * Removes the install script from the web root if it is there. Uses notification to craft a message
	 * giving more details.
	 */
	public function deleteInstallScript() {

		$notification = new notification();
        $public_directory = app::get()->getConfiguration()->get('public_directory')->getValue();

		$target = $public_directory . "/install.php";

		// First, double-check whether the file exists or not.
		if (filesize($target) > 0) {
			
			// Next, make sure we're actually able to delete the file
			if( is_writable($target)) {
				
				// Try to delete the file
				if ( unlink($target)) {

					// It worked! Let the user know it's done

					$notification->addNotification("success", "Success", "Install script removed - " . $target . " has been deleted.");
				}
				else {
					// For some reason this failed. I have no idea how this might happen, but better safe than sorry.

					$notification->addNotification("danger", "Failed", "No file removed - Please contact your server admin to resolve this problem.");

					// I'm just gonna default to "Get your server admin to fix this for you".  At this point, the user isn't feasibly going to resolve the problem on their own.  
				}

			}
			else {
				// Let user know that there's a permissions error (probably owned by root, though we won't say that, on the off chance it's a different problem)

				$notification->addNotification("danger", "Failed", "No file removed - It looks like you don't have permission to delete " . $target);

				// I know I could technically try to run chmod() at this point, but I am not going to try to change the file permissions on the fly, because I'll have to fix the fallout if that screws up.
			}

		}
		else {
			// Add message explaining that there was no need to remove anything

			$notification->addNotification("info", "Notice","No file removed - There was nothing there!");
		}
		
		return new Redirect(app::get()->getRouter()->generate('sa_dashboard'));
	}

	public static function getUsersOnlineWidget($data)
	{
		// Online Users Widget
        $data = array();
		$users_online = new View('users_online');
		$users_online->setXSSSanitation(false);
		try{
			$users_count = ioc::getRepository('OnlineUser')->getOnlineUsersCountInTimeframe(new DateTime("-5 minute"), new DateTime("+5 minute"), false);
			$users_online->data['users_count'] = $users_count['total'];

			$users_count = ioc::getRepository('OnlineUser')->getOnlineUsersCountInTimeframe(new DateTime("-1 minute"), new DateTime("+1 minute"), true);
			$users_online->data['active_users_count'] = $users_count['total'];

            $data['resources'] = array();
            $data['html'] = $users_online->getHTML();
            $data['display_name'] = 'Users Online';
            $data['id'] = 'sa.dashboard.users_online';
            $data['default_location'] = 'dashboard-widgets-right-col';
            $data['auto_refresh'] = true;
            $data['auto_refresh_interval'] = 30000;
            $data['display_icon'] = 'fa fa-user orange';
		}

		catch(\Exception $e) {

		    $data['html'] = '';
		}

		return $data;
	}

	public function generateSpriteResources($request)
	{
	    $template = $request->query->get('template');
		$notify = new notification();
        $theme = app::get()->getConfiguration()->get('theme')->getValue();

		if(!empty($_GET['template']) || !empty($template)) {
		    $template = !empty($_GET['template']) ? $_GET['template'] : $template;
		
			try {
				$viewName = 'blank';

                app::get()->setAppTheme($theme);


                $view = new View($template,$viewName,array(app::getAppPath().'/themes/'.$theme.'/views/'));
				$view->setTheme($theme);

				SpriteFactory::buildSpriteJson($view->getHTML());
				SpriteFactory::buildSpriteCSS();

				$notify->addNotification('success','Sprites generated successfully');				    
			} catch( ViewException $e) {
				$notify->addNotification('danger','The template you chose appears to be having problems.',$e->getMessage());
			}

			$redirect = new Redirect(app::get()->getRouter()->generate('sa_system_generate_sprite'));
			return $redirect;
		}

		$view = new View('generate_sprite_css',$this::viewLocation());
		return $view;
	}

}
