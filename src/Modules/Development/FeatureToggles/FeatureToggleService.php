<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureToggles;

use Flagception\Manager\FeatureManagerInterface;
use Foodsharing\Modules\Development\FeatureToggles\Commands\DeleteUndefinedFeatureTogglesCommand;
use Foodsharing\Modules\Development\FeatureToggles\Commands\SaveNewFeatureTogglesCommand;
use Foodsharing\Modules\Development\FeatureToggles\Commands\UpdateFeatureToggleStateCommand;
use Foodsharing\Modules\Development\FeatureToggles\DependencyInjection\FeatureToggleChecker;
use Foodsharing\Modules\Development\FeatureToggles\Querys\GetExistingFeatureTogglesFromDatabaseQuery;
use Foodsharing\Modules\Development\FeatureToggles\Querys\GetExistingHardcodedFeatureTogglesQuery;
use Foodsharing\Modules\Development\FeatureToggles\Querys\GetFeatureToggleOriginQuery;

final class FeatureToggleService implements FeatureToggleChecker
{
    public function __construct(
        private readonly FeatureManagerInterface $manager,
        private readonly GetFeatureToggleOriginQuery $isFeatureToggleInsideDatabaseQuery,
        private readonly GetExistingFeatureTogglesFromDatabaseQuery $existingFeatureTogglesFromDatabaseQuery,
        private readonly GetExistingHardcodedFeatureTogglesQuery $existingHardcodedFeatureTogglesQuery,
        private readonly SaveNewFeatureTogglesCommand $saveNewFeatureTogglesCommand,
        private readonly DeleteUndefinedFeatureTogglesCommand $deleteUndefinedFeatureTogglesCommand,
        private readonly UpdateFeatureToggleStateCommand $updateFeatureToggleStateCommand,
    ) {
    }

    public function isFeatureToggleActive(string $identifier): bool
    {
        return $this->manager->isActive($identifier);
    }

    public function getFeatureToggleOrigin(string $identifier): string
    {
        return $this->isFeatureToggleInsideDatabaseQuery->execute($identifier);
    }

    public function updateFeatureToggles(): void
    {
        $featureToggles = FeatureToggleDefinitions::all();
        $hardcodedFeatureToggles = $this->existingHardcodedFeatureTogglesQuery->execute();
        $alreadySavedFeatureToggles = $this->existingFeatureTogglesFromDatabaseQuery->execute();

        $newFeatureToggles = array_diff($featureToggles, $alreadySavedFeatureToggles, $hardcodedFeatureToggles);
        $notDefinedFeatureToggles = array_diff($alreadySavedFeatureToggles, $featureToggles);

        $this->saveNewFeatureTogglesCommand->execute($newFeatureToggles);
        $this->deleteUndefinedFeatureTogglesCommand->execute($notDefinedFeatureToggles);
    }

    public function updateFeatureToggleState(string $identifier, bool $newState): void
    {
        $this->updateFeatureToggleStateCommand->execute($identifier, $newState);
    }
}
