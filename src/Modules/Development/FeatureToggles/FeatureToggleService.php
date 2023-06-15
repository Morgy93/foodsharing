<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureToggles;

use Flagception\Manager\FeatureManagerInterface;
use Foodsharing\Modules\Development\FeatureToggles\Querys\DependencyInjection\FeatureToggleChecker;
use Foodsharing\Modules\Development\FeatureToggles\Querys\IsFeatureToggleInsideDatabaseQuery;

final class FeatureToggleService implements FeatureToggleChecker
{
    public function __construct(
        private readonly FeatureManagerInterface $manager,
        private readonly IsFeatureToggleInsideDatabaseQuery $isFeatureToggleInsideDatabaseQuery,
    ) {
    }

    public function isFeatureToggleActive(string $identifier): bool
    {
        return $this->manager->isActive($identifier);
    }

    public function isFeatureToggleInsideDatabase(string $identifier): bool
    {
        return $this->isFeatureToggleInsideDatabaseQuery->execute($identifier);
    }
}
