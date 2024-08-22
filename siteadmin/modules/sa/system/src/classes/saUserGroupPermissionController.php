<?php
namespace sa\system;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\Request;
use sacore\application\responses\View;
use sacore\application\responses\Redirect;
use sacore\application\ValidateException;
use sacore\application\saController;
use sacore\utilities\notification;

class saUserGroupPermissionController extends saController
{

	public function manageSAUserGroupPermissions()
	{
		$view = new View('table', $this->viewLocation());

		$perPage = 20;
		$currentPage = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$sort = !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : false;
		$sortDir = !empty($_REQUEST['sortDir']) ? $_REQUEST['sortDir'] : false;

		/** @var saUserRepository $repo */
		$repo = app::$entityManager->getRepository(ioc::staticResolve('saUserGroupPermission'));

		$orderBy = null;
		if ($sort) {
			$orderBy = array($sort => $sortDir);
		}

		$data = $repo->search(null, $orderBy, $perPage, (($currentPage - 1) * $perPage));
		$totalRecords = $repo->search(null, null, null, null, true);

		$totalPages = ceil($totalRecords / $perPage);

		$view->data['table'][] = array(
			/* SET THE HEADER OF THE TABLE UP */
			'header' => array(
				array('name' => 'Name', 'class' => '', 'map' => 'name'),
				array('name' => 'Grouping', 'class' => '', 'map' => 'grouping'),
				array('name' => 'Permission Code', 'class' => '', 'map' => 'permission_code'),
			),
			/* SET ACTIONS ON EVERY ROW */
			'actions' => array(
				'edit' => array('name' => 'Edit', 'routeid' => 'sa_sausergroup_permission_edit', 'params' => array('id')),
				'delete' => array('name' => 'Delete', 'routeid' => 'sa_sausergroup_permission_delete', 'params' => array('id')),
			),
			/* SET THE NO DATA MESSAGE */
			'noDataMessage' => 'No SA User Group Permissions Available',
			/* SET THE ACTION FOR THE HEADER CREATE BUTTON */
			'tableCreateRoute' => 'sa_sausergroup_permission_create',
			/* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
			'data' => $data,
			/* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
			'totalRecords' => $totalRecords,
			'totalPages' => $totalPages,
			'currentPage' => $currentPage,
			'perPage' => $perPage,
			'dataRowCallback' => function ($data) {
				if ($data["last_login"])
					$data["last_login"] = $data["last_login"]->format("m/d/Y g:i a");

				return $data;
			}
		);
		return $view;
	}

	public function createSaUserGroupPermission()
	{
		$view = new View('sa_usergroup_permission_create', $this->viewLocation());
		return $view;
	}

	/**
	 * @param Request $request
	 * @return View
	 */
	public function editSaUserGroupPermission(Request $request)
	{
		/** @var saUserGroupRepository $userGroupRepo */
		$userGroupRepo = ioc::getRepository('saUserGroupPermission');
		$view = new View('sa_usergroup_permission_edit', $this->viewLocation());
		$id = $request->getRouteParams()->get('id');

		if (!is_null($id) && $id > 0) {
			$view->data['id'] = $id;
		}

		return $view;
	}

	public function deleteSAUserGroupPermission($request)
	{
		$id = $request->getRouteParams()->get('id');
		$saUserGroupPermission = ioc::staticResolve('saUserGroupPermission');
		$userGroupPermission = app::$entityManager->find($saUserGroupPermission, $id);

		$notify = new notification();

		try {
			/** @var saUser $currentUser */
			$currentUser = modRequest::request('sa.user');
			$currentUserId = $currentUser->getId();

			app::$entityManager->remove($userGroupPermission);
			app::$entityManager->flush();
			$notify->addNotification('success', 'Success', 'User Group Permission deleted successfully.');

			if ($id == $currentUserId) {
				saAuth::getInstance()->logoff();
				return new Redirect(app::get()->getRouter()->generate('sa_login'));
			} else {
				return new Redirect(app::get()->getRouter()->generate('sa_sausergroup_permissions'));
			}
		} catch (ValidateException $e) {
			$notify->addNotification('danger', 'Error', 'An error occured while saving your changes. <br />' . $e->getMessage());
			return new Redirect(app::get()->getRouter()->generate('sa_sausergroup_permissions'));
		}
	}

	public static function create($data)
	{
		$saUserGroupPermissionService = new saUserGroupPermissionService();
		return $saUserGroupPermissionService->create($data);
	}

	public static function get($data)
	{
		$saUserGroupPermissionService = new saUserGroupPermissionService();
		return $saUserGroupPermissionService->get($data);
	}

	public static function save($data)
	{
		$saUserGroupPermissionService = new saUserGroupPermissionService();
		return $saUserGroupPermissionService->save($data);
	}

	public static function getGroupings($data)
	{
		$saUserGroupPermissionService = new saUserGroupPermissionService();
		return $saUserGroupPermissionService->getGroupings($data);
	}
}
