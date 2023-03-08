<?php
declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureFlags;

use Flagception\Manager\FeatureManagerInterface;
class FeatureFlagService implements DependencyInjection\FeatureFlagChecker
{
    public function __construct(
        private readonly FeatureManagerInterface $manager,
        private readonly FeatureFlagContextGenerator $contextGenerator,
    ) {
    }

    public function isFeatureFlagActive(string $identifier, ?int $foodsaverId = null): bool
    {
        if (is_null($foodsaverId)) {
            return $this->manager->isActive($identifier);
        }

        return $this->manager->isActive($identifier, $this->contextGenerator->generate($foodsaverId));
    }
}
