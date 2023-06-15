<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureFlags\DependencyInjection;

interface FeatureToggleChecker
{
    public function isFeatureToggleActive(string $identifier): bool;
}
