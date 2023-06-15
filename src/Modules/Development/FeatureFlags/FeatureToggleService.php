<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureFlags;

use Flagception\Manager\FeatureManagerInterface;

final class FeatureToggleService implements DependencyInjection\FeatureToggleChecker
{
    public function __construct(
        private readonly FeatureManagerInterface $manager,
    ) {
    }

    public function isFeatureToggleActive(string $identifier): bool
    {
        return $this->manager->isActive($identifier);
    }
}
