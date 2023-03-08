<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureFlags\DependencyInjection;

interface FeatureFlagChecker
{
    public function isFeatureFlagActive(string $identifier, ?int $foodsaverId = null): bool;
}
