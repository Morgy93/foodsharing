<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureToggles\DependencyInjection;

interface FeatureToggleChecker
{
    public function isFeatureToggleActive(string $identifier): bool;
}
