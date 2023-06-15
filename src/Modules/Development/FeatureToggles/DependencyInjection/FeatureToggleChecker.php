<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureToggles\Querys\DependencyInjection;

interface FeatureToggleChecker
{
    public function isFeatureToggleActive(string $identifier): bool;
}
