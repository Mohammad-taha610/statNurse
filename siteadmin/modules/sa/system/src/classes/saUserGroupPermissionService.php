<?php
namespace sa\system;

use \sacore\application\app;
use sacore\application\ioc;
use sacore\utilities\doctrineUtils;
use Throwable;

class saUserGroupPermissionService
{
    public function __construct()
    {
    }

    public function create($data)
    {
        $response = ['success' => false];

        try {
            /** @var saUserGroupPermission $saUserGroupPerm */
            $saUserGroupPerm = ioc::resolve('saUserGroupPermission');

            $saUserGroupPerm->setName($data['name']);
            $saUserGroupPerm->setGrouping($data['grouping']);
            $saUserGroupPerm->setPermissionCode($data['permission_code']);

            app::$entityManager->persist($saUserGroupPerm);
            app::$entityManager->flush($saUserGroupPerm);

            $saUserGroupService = new saUserGroupService();
            $saUserGroupService->rebuildAdminPerms();

            $response['redirect_url'] = app::get()->getRouter()->generate('sa_sausergroup_permissions');
            $response['success'] = true;
        } catch (Throwable $t) {
            $response['message'] = $t->getMessage();
            return $response;
        }

        return $response;
    }

    public function get($data)
    {
        $response = ['success' => false];
        try {
            $id = $data['id'];
            /** @var saUserGroupPermission $saUserGroupPerm */
            $saUserGroupPerm = ioc::get('saUserGroupPermission', ['id' => $id]);

            $response['group_perm'] = doctrineUtils::getEntityArray($saUserGroupPerm);


            $response['success'] = true;
        } catch (Throwable $t) {
            $response['message'] = $t->getMessage();
            return $response;
        }

        return $response;
    }

    public function getGroupings($data)
    {
        $response = ['success' => false];

        $response['groupings'] = array_column(app::getInstance()->getModules(), 'module');

        $response['success'] = true;

        return $response;
    }

    public function save($data)
    {
        $response = ['success' => false];

        try {
            $id = $data['id'];

            /** @var saUserGroupPermission $saUserGroupPerm */
            $saUserGroupPerm = ioc::get('saUserGroupPermission', ['id' => $id]);

            if ($saUserGroupPerm) {
                doctrineUtils::setEntityData($data, $saUserGroupPerm);
                app::$entityManager->flush($saUserGroupPerm);
            } else {
                $response['message'] = "Could not retrieve SA User Group to update";
                return $response;
            }
            $saUserGroupService = new saUserGroupService();
            $saUserGroupService->rebuildAdminPerms();

            $response['redirect_url'] = app::get()->getRouter()->generate('sa_sausergroup_permissions');
            $response['success'] = true;
        } catch (Throwable $t) {
            $response['message'] = $t->getMessage();
            return $response;
        }

        return $response;
    }
}
