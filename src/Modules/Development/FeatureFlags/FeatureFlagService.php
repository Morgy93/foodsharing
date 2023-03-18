<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureFlags;

use Flagception\Manager\FeatureManagerInterface;

class FeatureFlagService implements DependencyInjection\FeatureFlagChecker
{
    public function __construct(
        private readonly FeatureManagerInterface $manager,
    ) {
    }

    public function isFeatureFlagActive(string $identifier): bool
    {
        return $this->manager->isActive($identifier);
    }
}
