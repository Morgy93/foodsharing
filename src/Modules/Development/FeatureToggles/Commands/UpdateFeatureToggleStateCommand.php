<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureToggles\Commands;

use Foodsharing\Modules\Core\Database;

final class UpdateFeatureToggleStateCommand
{
    public function __construct(
        private readonly Database $database,
        private readonly string $siteEnvironment,
    ) {
    }

    public function execute(string $featureToggleIdentifier, bool $newState): void
    {
        $this->database->update(
            'fs_feature_toggles',
            ['is_active' => $newState],
            ['identifier' => $featureToggleIdentifier, 'site_environment' => $this->siteEnvironment],
        );
    }
}
