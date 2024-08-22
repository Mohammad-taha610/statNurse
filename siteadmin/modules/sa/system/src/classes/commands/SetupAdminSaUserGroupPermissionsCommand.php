<?php
namespace sa\system\commands;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modRequest;
use sa\system\AssetBuildManager;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use sa\system\saUserGroup;
use sa\system\saUserGroupService;
use sa\system\saUserGroupPermission;

/**
 * Date: 03/29/2023
 *
 * File: SetupAdminSaUserGroupPermissions.php
 */
class SetupAdminSaUserGroupPermissionsCommand extends Command
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('system:usergroup:setup')

            // the short description shown while running "php bin/console list"
            ->setDescription('Generate Admin SA User group.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Generate Admin SA User group and default permissions.')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $app = app::get();
        
        $io = $app->getCliIO();

        /**
         * BEGIN PERMISSIONS GENERATION HERE FIRST
         * ADD NEW PERMISSIONS REQUIRED HERE
         */
        $saUserGroupPermissionRepo = ioc::getRepository('saUserGroupPermission');
        // FOR EACH PERMISSION, SHOULD CHECK IF ALREADY EXISTS BEFORE CREATING IT
        


        // END PERMISSIONS GENERATION

        // BEGIN ADMIN GROUP GENERATION HERE 
        /** @var saUserGroup $saUserGroup */
        $saUserGroup = ioc::get('saUserGroup', ['name' => 'Admin']);
        if (!$saUserGroup) {
            $saUserGroup = ioc::resolve('saUserGroup');
            $saUserGroup->setName('Admin');
            $saUserGroup->setCode('admin');
            $saUserGroup->setDescription('...');
            $saUserGroup->setIsAdmin(true);

            app::$entityManager->persist($saUserGroup);
        }

        $saUserGroupService = new saUserGroupService();
        $saUserGroupService->rebuildAdminPerms();
        
        // END ADMIN GROUP GNERATION
        app::$entityManager->flush();

        $io->success('Completed generating Admin SA User Group and Permissions.');
        return Command::SUCCESS;
    }

}
