<?php

namespace Foodsharing\Command;

use Foodsharing\Modules\Stats\StatsControl;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatsCommand extends Command
{
	protected static $defaultName = 'foodsharing:stats';

	/**
	 * @var StatsControl
	 */
	private $statsControl;

	public function __construct(StatsControl $statsControl)
	{
		$this->statsControl = $statsControl;

		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setDescription('Executes foodsaver, stores and regions statistics tasks.');
		$this->setHelp('This command executes background tasks that need to be run in regular intervals.
		While the exact interval should not matter, it must still be chosen sane. See implementation for details.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->statsControl->foodsaver();
		$this->statsControl->betriebe();
		$this->statsControl->bezirke();

		return 0;
	}
}
