<?php

namespace Foodsharing\Modules\Development\FeatureToggles\Querys;

use Foodsharing\Modules\Core\Database;

class IsFeatureToggleInsideDatabaseQuery
{
    public function __construct(
        private readonly Database $database,
    ) {
    }

    public function execute(string $featureToggleIdentifier): bool
    {
        return $this->database->exists("fs_feature_toggles", [
            'identifier' => $featureToggleIdentifier,
        ]);
    }
}
