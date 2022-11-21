<?php

namespace Foodsharing\Command;

use Foodsharing\Modules\Maintenance\MaintenanceControl;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DailyMaintenanceCommand extends Command
{
	protected static $defaultName = 'foodsharing:daily-cronjob';

	/**
	 * @var MaintenanceControl
	 */
	private $maintenanceControl;

	public function __construct(MaintenanceControl $maintenanceControl)
	{
		$this->maintenanceControl = $maintenanceControl;

		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setDescription('Executes daily maintenance tasks.');
		$this->setHelp('This command executes background tasks that need to be run in daily intervals.
		While the exact interval should not matter, it must still be chosen sane. See implementation for details.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->maintenanceControl->daily();

		return 0;
	}
}
