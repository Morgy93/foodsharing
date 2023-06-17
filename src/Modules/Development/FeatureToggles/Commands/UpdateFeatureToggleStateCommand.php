<?php

namespace Foodsharing\Modules\Development\FeatureToggles\Commands;

use Foodsharing\Modules\Core\Database;

class UpdateFeatureToggleStateCommand
{
    public function __construct(
        private readonly Database $database,
    ) {
    }

    public function execute(string $featureToggleIdentifier, bool $newState): void
    {
        $this->database->update(
            'fs_feature_toggles',
            ['isActive' => $newState],
            ['identifier' => $featureToggleIdentifier],
        );
    }
}
