<?php

namespace nst\member\commands;

use nst\member\CheckrPayService;
use sacore\application\app;
use sacore\application\ioc;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCheckrPayIDsCommand extends Command
{

	protected function configure()
	{
		$this->setName('update:checkr_pay_ids')
			->setDescription('Creates Checkr Pay IDs for list of nurses.')
			->setHelp('');
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$response['success'] = false;

		echo "Creating Worker IDs in batches of 50." . PHP_EOL;
		echo "Rerun this command until no other IDs are left to be created." . PHP_EOL;
		
		$checkrPayService = new CheckrPayService;

		$nurses = [
			'1795',
			'1622'
		];

		echo "Total IDs: " . count($nurses) . PHP_EOL;
		$sent = 0;

		foreach ($nurses as $nurse) {

			$nurseObject = ioc::get('Nurse', ['id' => $nurse]);

			if (($nurseObject->getCheckrPayId() == null) && ($sent <= 50)) {
				$newWorkerId = $checkrPayService->createWorker($nurseObject);
				if ($newWorkerId['id']) {
					$nurseObject->setCheckrPayId($newWorkerId['id']);
					app::$entityManager->flush();
					$sent++;
					echo 'Created Worker ID for ' . $nurseObject->getId() . '.' . PHP_EOL;
					echo $sent . PHP_EOL;
				} else {
					echo 'Error creating Worker ID for ' . $nurseObject->getId() . '.' . PHP_EOL;
					print_r($newWorkerId);
				}
			} else {
				echo 'Checkr ID already exists for ' . $nurseObject->getId() . '.' . PHP_EOL;
			}
		}

		if ($response['success']) {
			echo "Sent this round: " . $sent . PHP_EOL;
			return Command::SUCCESS;
		}
		return Command::FAILURE;
	}
}
