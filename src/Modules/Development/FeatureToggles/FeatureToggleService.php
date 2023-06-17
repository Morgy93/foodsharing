<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureToggles;

use Flagception\Manager\FeatureManagerInterface;
use Foodsharing\Modules\Development\FeatureToggles\DependencyInjection\FeatureToggleChecker;
use Foodsharing\Modules\Development\FeatureToggles\Querys\GetFeatureToggleOrigin;

final class FeatureToggleService implements FeatureToggleChecker
{
    public function __construct(
        private readonly FeatureManagerInterface $manager,
        private readonly GetFeatureToggleOrigin $isFeatureToggleInsideDatabaseQuery,
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
}
