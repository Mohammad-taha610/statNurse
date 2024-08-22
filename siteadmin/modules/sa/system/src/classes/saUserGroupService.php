<?php
namespace sa\system;

use \sacore\application\app;
use sacore\application\ioc;
use sacore\utilities\doctrineUtils;

class saUserGroupService
{
    public function __construct()
    {
    }

    public function create($data)
    {
        $response = ['success' => false];

        /** @var saUserGroup $saUserGroup */
        $saUserGroup = ioc::resolve('saUserGroup');

        $saUserGroup->setName($data['name']);
        $saUserGroup->setDescription($data['description']);
        $saUserGroup->setCode($data['code']);
        $saUserGroup->setIsAdmin($data['is_admin']);

        app::$entityManager->persist($saUserGroup);
        app::$entityManager->flush($saUserGroup);

        $response['redirect_url'] = app::get()->getRouter()->generate('sa_sausergroups');
        $response['success'] = true;

        return $response;
    }

    public function get($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        /** @var saUserGroup $saUserGroup */
        $saUserGroup = ioc::get('saUserGroup', ['id' => $id]);

        $response['group'] = doctrineUtils::getEntityArray($saUserGroup);

        // setup permissions array 
        $permissions = [];
        foreach (ioc::getRepository('saUserGroupPermission')->findAll() as $permission) {
            $permissions[$permission->getGrouping()][$permission->getPermissionCode()] = false;
        }

        // sync permissions from saUserGroup.permissions
        // Checking to make sure the permissions still exists so we do not keep a record deleted permissions
        foreach ($response['group']['permissions'] as $moduleKey => $module) {
            foreach ($module as $permKey => $permValue) {
                if (array_key_exists($moduleKey, $permissions) && array_key_exists($permKey, $permissions[$moduleKey])) {
                    $permissions[$moduleKey][$permKey] = $permValue;
                }
            }
        }

        // return the calculated set of permisisons
        $response['group']['permissions'] = $permissions;

        $response['success'] = true;

        return $response;
    }


    public function save($data)
    {
        $response = ['success' => false];
        $id = $data['id'];

        /** @var saUserGroup $saUserGroup */
        $saUserGroup = ioc::get('saUserGroup', ['id' => $id]);

        if ($saUserGroup) {
            doctrineUtils::setEntityData($data, $saUserGroup);

            foreach ($data['permissions'] as $modKey => $module) {
                foreach ($module as $permKey => $perm) {
                    $data['permissions'][$modKey][$permKey] = filter_var($perm, FILTER_VALIDATE_BOOL);
                }
            }
            $saUserGroup->setIsAdmin(filter_var($data['is_admin'], FILTER_VALIDATE_BOOL));
            app::$entityManager->flush($saUserGroup);
        } else {
            $response['message'] = "Could not retrieve SA User Group to update";
            return $response;
        }

        $response['redirect_url'] = app::get()->getRouter()->generate('sa_sausergroups');
        $response['success'] = true;
        return $response;
    }

    public function rebuildAdminPerms() 
    {
        $saUserGroups = ioc::getRepository('saUserGroup')->findBy(['is_admin' => true]);

        $saUserGroupPermissionRepo = ioc::getRepository('saUserGroupPermission');
        $existingPermissions = $saUserGroupPermissionRepo->findAll();
        $calculatedPermissions = [];
        /** @var saUserGroupPermission $permission */
        foreach ($existingPermissions as $permission) {
            $calculatedPermissions[$permission->getGrouping()][$permission->getPermissionCode()] = true;
        }

        foreach ($saUserGroups as $saUserGroup) {
            $saUserGroup->setPermissions($calculatedPermissions);
        }

        app::$entityManager->flush();
    }
}
