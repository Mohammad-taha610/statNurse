<?php

namespace nst\system\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use sacore\application\app;
use sacore\application\ioc;
use sa\system\saUser;
use sa\system\saUserGroup;
use sa\system\saUserGroupPermission;
use sa\system\saUserGroupPermissionRepository;
use sa\system\saUserGroupService;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateUserGroupsAndPermissionsCommand extends Command
{
    protected function configure() 
    {
        $this->setName('nst:gen_groups_perms')
            ->setDescription('Generates SA User Groups and Permissions and sets them accordingly');
    }

    protected function execute(InputInterface $input, OutputInterface $output) 
    {

        $app = app::get();
        /** @var SymfonyStyle $io */
        $io = $app->getCliIO();

        /** @var saUserGroupPermissionRepository $saUserGroupPermissionRepo */
        $saUserGroupPermissionRepo = ioc::getRepository('saUserGroupPermission');

        /** @var saUserGroupRepository $saUserGroupRepo */
        $saUserGroupRepo = ioc::getRepository('saUserGroup');

        /** @var saUserRepository $saUser */
        $saUserRepo = ioc::getRepository('saUser');
        
        $saUserGroupService = new saUserGroupService();

        /**
         * BEGIN PERMISSIONS GENERATION HERE FIRST
         * ADD NEW PERMISSIONS REQUIRED HERE
         */

        $existingPerms = $saUserGroupPermissionRepo->findAll();

        $permissionData = [
            ['Store Access', 'store', 'store-access'],
            ['Settings Access', 'system', 'system-settings-access'],
            ['Developer Access', 'developer', 'developer-access'],
            ['Create Approved Shifts', 'events', 'events-create-approved-shifts'],
            ['Merge Nurses', 'member', 'member-merge-nurses'],
            ['Create Nurses', 'member', 'member-create-nurses'],
            ['Create Providers', 'member', 'member-create-providers'],
            ['Manage Provider Profile PayRates', 'member', 'member-manage-provider-profile-payrates'],
            ['Manage Provider Profile Files', 'member', 'member-manage-provider-profile-files'],
            ['Manage Reports', 'payroll', 'payroll-manage-reports'],
            ['Can Edit Items', 'payroll', 'payroll-can-edit-items'],
            ['Can Delete Old Shifts', 'events', 'events-can-delete-old-shifts'],
            ['Quickbooks Access', 'quickbooks', 'quickbooks-access'],
            ['Shift Action Log Access', 'events', 'events-shift-action-log-access'],
            ['Can See Bill Rates', 'events', 'events-can-see-bill-rates']
        ];

        foreach ($permissionData as $perm) {
            $io->writeln('Handling permission: ' . $perm[0]);
            
            // FOR EACH PERMISSION, SHOULD CHECK IF ALREADY EXISTS BEFORE CREATING IT
            /** @var saUserGroupPermission $permission */
            $permission = array_pop(array_reverse(array_filter($existingPerms, function($e) use($perm) { return $e->getName() == $perm[0]; })));
            if ($permission) {
                $permission->setGrouping($perm[1]);
                $permission->setPermissionCode($perm[2]);
            } else {
                $permission = new saUserGroupPermission();
                $permission->setName($perm[0]);
                $permission->setGrouping($perm[1]);
                $permission->setPermissionCode($perm[2]);
                app::$entityManager->persist($permission);
            }
        }

        app::$entityManager->flush();
        // END PERMISSIONS GENERATION

        // BEGIN ADMIN GROUP GENERATION HERE
        $groupsToGenerate = [
            ['Admin', '...', 'admin', true],
            ['Manager', '...', 'manager', false],
            ['Scheduler', '...', 'scheduler', false],
            ['Just Testing', '...', 'test group', false]
        ]; 

        $saUserGroups =  $saUserGroupRepo->findBy(['name' => array_map(function($e) { return $e[0]; }, $groupsToGenerate)]);

        foreach ($groupsToGenerate as $groupData) {
            $io->writeln('Handling SA USer Group: ' . $groupData[0]);
            
            /** @var saUserGroup $saUserGroup */
            $saUserGroup = array_pop(array_reverse(array_filter($saUserGroups, function($e) use($groupData) { return $e->getName() == $groupData[0]; })));

            // Always check if the group exists and create it if necessary
            if (!$saUserGroup) {
                $saUserGroup = new saUserGroup();
                app::$entityManager->persist($saUserGroup);
            }
            
            // Also set the known parameters
            $saUserGroup->setName($groupData[0]);
            $saUserGroup->setDescription($groupData[1]);
            $saUserGroup->setCode($groupData[2]);
            $saUserGroup->setIsAdmin($groupData[3]);

            $existingPermissions = $saUserGroupPermissionRepo->findAll();
            $calculatedPermissions = [];

            switch ($groupData[0]) {
                case 'Admin':
                    $saUserGroupService->rebuildAdminPerms();
                    break;
                case 'Manager':
                    $managerRestrictedPermissionCodes = [
                        'store-access', 
                        'system-settings-access', 
                        'developer-access'
                    ];

                    /** @var saUserGroupPermission $permission */
                    foreach ($existingPermissions as $permission) {
                        $calculatedPermissions[$permission->getGrouping()][$permission->getPermissionCode()] = in_array($permission->getPermissionCode(), $managerRestrictedPermissionCodes) ? false : true;
                    }

                    $saUserGroup->setPermissions($calculatedPermissions);
                    break;
                case 'Scheduler':
                    // They don't have any access at the moment for the default permissions
                    $schedulerAccessCodes = [];

                    /** @var saUserGroupPermission $permission */
                    foreach ($existingPermissions as $permission) {
                        $calculatedPermissions[$permission->getGrouping()][$permission->getPermissionCode()] = in_array($permission->getPermissionCode(), $schedulerAccessCodes) ? true : false;
                    }

                    $saUserGroup->setPermissions($calculatedPermissions);
                    break;
                default: 
            }
        }

        app::$entityManager->flush();
        // END ADMIN GROUP GNERATION

        // Set existing non-dev user's groups to Admin.
        $saUsers = $saUserRepo->findBy(['user_type' => [0, 1]]);
        $adminUserGroup = $saUserGroupRepo->findOneBy(['name' => 'Admin']);
        /** @var saUser $saUser */
        foreach ($saUsers as $saUser) {
            $io->writeln('Handling Seting SaUser group to Admin: ' . $saUser?->getFirstName() . ' ' . $saUser?->getLastName());
            $saUser->setSaUserGroup($adminUserGroup);
        }
        app::$entityManager->flush();

        $io->success('Completed generating Admin SA User Group and Permissions.');
        return Command::SUCCESS;
    }
}