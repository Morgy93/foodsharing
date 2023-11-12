<?php

namespace Foodsharing\Command;

use Foodsharing\Modules\Development\FeatureToggles\Services\FeatureToggleService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateFeatureTogglesCommand extends Command
{
    protected static $defaultName = 'foodsharing:update:featuretoggles';
    protected static $defaultDescription = 'This command updates the feature toggles to manage them via api.';

    public function __construct(
        private readonly FeatureToggleService $featureToggleService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->featureToggleService->updateFeatureToggles();
        $output->writeln('<info>Updated feature toggle identifiers to manage them via api.</info>');

        return Command::SUCCESS;
    }
}
