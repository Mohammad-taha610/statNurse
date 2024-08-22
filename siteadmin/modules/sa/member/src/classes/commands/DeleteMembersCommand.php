<?php
namespace sa\member\commands;

use sacore\application\app;
use sacore\application\ioc;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use sacore\application\ValidateException;

/**
 * Date: 7/11/2019
 *
 * File: DeleteMembersCommand.php
 */
class DeleteMembersCommand extends Command
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('member:members:delete')

            // the short description shown while running "php bin/console list"
            ->setDescription('Delete Members.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to delete active members based on pre-defined fields.')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $app = app::get();

        $io = $app->getCliIO();

        $io->title('Delete Members');

        $dataForTable = [];
        $fields = ['id', 'first_name', 'last_name', 'company', 'username'];

        $selectField = $io->choice('Select one of the saMember fields to use for searching', $fields);

        $valueForSearch = $io->ask('Please type the value you want to use in your search on ' . $selectField. '');

        $members = app::$entityManager->getRepository(ioc::staticResolve('saMember'))->search(array($selectField => $valueForSearch));
        $members_count = app::$entityManager->getRepository(ioc::staticResolve('saMember'))->search(array($selectField => $valueForSearch),null,null,null,true);

        foreach ($members as $member) {
            $memberUsers = $member->getUsers();
            $usernames = '| ';
            foreach($memberUsers as $memberUser) {
                $usernames .= $memberUser->getUsername() . ' | ';
            }
            $dataForTable[] = 
                [$member->getId(), 
                $member->getFirstName(), 
                $member->getLastName(), 
                $member->getCompany(), 
                $usernames,
                $member->getDateCreated()];
        }

        if($members_count > 0) {

            $io->section($members_count . ' member(s) found.');

            $io->table(['ID', 'First Name', 'Last Name', 'Company', 'Username(s)', 'Date Created'], $dataForTable);

            $io->warning('THIS ACTION CANNOT BE REVERSED!!');

            $delete = $io->confirm('Would you like to delete '. $members_count .' member(s)?', false);

            if($delete) {
                foreach ($members as $member) {
                    try {
                        app::$entityManager->remove($member);
                        app::$entityManager->flush();
                    }
                    catch(ValidateException $e) {
                        $io->error([
                            'Could not delete member ' . $member->getId() . '.',
                            $e->getMessage(),
                        ]);
                    }
                }
                $io->success('Successfully deleted '. $members_count .' member(s).');
            } else {
                $io->success('No member(s) were deleted.');
            }
        } else {
            $io->success('No member(s) were found.');
        }
    }
}
