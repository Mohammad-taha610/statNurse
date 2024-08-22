<?php

namespace nst\applications\commands;

use Exception;
use nst\member\Nurse;
use nst\member\NurseApplication;
use sacore\application\app;
use sacore\application\ioc;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EncryptNurseAppSocialSecurityNumberCommand extends Command
{
    protected function configure()
    {
        $this->setName('nurse:encrypt_nurse_app_ss#')
            ->setDescription('Checks if existing nurse applications have their social security number encrypted, if not it will encrypt them')
            ->setHelp('');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $response = ['success' => false];

        // NURSE APPLICATIONS
        $nurseApplications = ioc::getRepository('NurseApplication')->findAll();
        if ($nurseApplications) {
            /** @var NurseApplication $nurseApp */
            foreach ($nurseApplications as $nurseApp) {
                $nurseJson = $nurseApp->getNurse();
                // check if nurse social security number is numbers or encrypted
                // if un-encrypted, encrypt it
                $nurse = json_decode($nurseJson, true);

                // check nurse SS# & encrypt nurse SS# if needed
                // https://www.php.net/openssl_encrypt
                if ($nurse['socialsecurity_number']) {

                    // check if just numbers / dashes
                    $nurseSSN = str_replace('-', '', $nurse['socialsecurity_number']);
                    if (preg_match('/^[0-9]{9}$/', $nurseSSN)) {
                        $cipher = "AES-128-CTR";
                        $key = $nurseApp->getMember()->getUsers()[0]->getUserKey();
                        $nurse['socialsecurity_number'] = openssl_encrypt($nurseSSN, $cipher, $key, 0, ord($key));

                        // save nurse
                        $nurseApp->setNurse( json_encode($nurse) );

                        app::$entityManager->persist($nurseApp);
                        app::$entityManager->flush();
                    }
                }
            }
            $response['success'] = true;
        }

        // NURSES
        $nurses = ioc::getRepository('Nurse')->findAll();
        if ($nurses) {
            /** @var Nurse $nurse */
            foreach ($nurses as $nurse) {
                if (!$nurse->getMember() || $nurse->getMember()->getIsDeleted()) {
                    continue;
                }

                // check nurse SS# & encrypt nurse SS# if needed
                // https://www.php.net/openssl_encrypt
                if ($nurse->getSSN()) {
                    // check if just numbers / dashes
                    $nurseSSN = str_replace('-', '', $nurse->getSSN());
                    if (preg_match('/^[0-9]{9}$/', $nurseSSN)) {
                        $cipher = "AES-128-CTR";
                        $key = $nurse->getMember()->getUsers()[0]->getUserKey();
                        $ss = openssl_encrypt($nurseSSN, $cipher, $key, 0, ord($key));
                        if ($ss) {
                            $nurse->setSSN($ss);
                            app::$entityManager->persist($nurse);
                            app::$entityManager->flush();
                        }
                    }
                }
            }
            $response['success'] = true;
        }

        if ($response['success']) {
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }
}
