<?php
namespace sa\system;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\Request;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\saController;
use sacore\application\ValidateException;
use sacore\utilities\notification;

class saUserGroupController extends saController
{
    public function manageSAUserGroups()
    {
        $view = new View('table', $this->viewLocation());

        $perPage = 20;
        $currentPage = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $sort = !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : false;
        $sortDir = !empty($_REQUEST['sortDir']) ? $_REQUEST['sortDir'] : false;

        /** @var saUserRepository $repo */
        $repo = app::$entityManager->getRepository(ioc::staticResolve('saUserGroup'));

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
                array('name' => 'Description', 'class' => '', 'map' => 'description'),
                array('name' => 'Code', 'class' => '', 'map' => 'code'),
            ),
            /* SET ACTIONS ON EVERY ROW */
            'actions' => array(
                'edit' => array('name' => 'Edit', 'routeid' => 'sa_sausergroup_edit', 'params' => array('id')),
                'delete' => array('name' => 'Delete', 'routeid' => 'sa_sausergroup_delete', 'params' => array('id')),
            ),
            /* SET THE NO DATA MESSAGE */
            'noDataMessage' => 'No SA User Groups Available',
            /* SET THE ACTION FOR THE HEADER CREATE BUTTON */
            'tableCreateRoute' => 'sa_sausergroup_create',
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

    public function createSaUserGroup()
    {
        $view = new View('sa_usergroup_create', $this->viewLocation());
        return $view;
    }

    /**
     * @param Request $request
     * @return View
     */
    public function editSaUserGroup(Request $request)
    {
        /** @var saUserGroupRepository $userGroupRepo */
        $userGroupRepo = ioc::getRepository('saUserGroup');
        $view = new View('sa_usergroup_edit', $this->viewLocation());
        $id = $request->getRouteParams()->get('id');

        if (!is_null($id) && $id > 0) {
            $view->data['id'] = $id;
        }

        return $view;
    }

    public function deleteSAUserGroup($request)
    {

        $id = $request->getRouteParams()->get('id');
        $saUserGroup = ioc::staticResolve('saUserGroup');
        $userGroup = app::$entityManager->find($saUserGroup, $id);

        $notify = new notification();

        try {
            /** @var saUser $currentUser */
            $currentUser = modRequest::request('sa.user');
            $currentUserId = $currentUser->getId();

            app::$entityManager->remove($userGroup);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'User Group deleted successfully.');

            if ($id == $currentUserId) {
                saAuth::getInstance()->logoff();
                return new Redirect(app::get()->getRouter()->generate('sa_login'));
            } else {
                return new Redirect(app::get()->getRouter()->generate('sa_sausergroups'));
            }
        } catch (\Throwable $e) {
            $notify->addNotification('danger', 'Error', 'An error occured while saving your changes. <br />' . "Failed to delete group");
            return new Redirect(app::get()->getRouter()->generate('sa_sausergroups'));
        }
    }

    public static function create($data)
    {
        $saUserGroupService = new saUserGroupService();
        return $saUserGroupService->create($data);
    }

    public static function get($data)
    {
        $saUserGroupService = new saUserGroupService();
        return $saUserGroupService->get($data);
    }

    public static function save($data)
    {
        $saUserGroupService = new saUserGroupService();
        return $saUserGroupService->save($data);
    }
}
