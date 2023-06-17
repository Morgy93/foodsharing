<?php

namespace Foodsharing\Modules\Development\FeatureToggles\Querys;

use Foodsharing\Modules\Core\Database;

final class GetExistingFeatureTogglesFromDatabaseQuery
{
    public function __construct(
        private readonly Database $database,
    ) {
    }

    /**
     * @return string[] identifiers of feature toggles
     */
    public function execute(): array
    {
        return $this->database->fetchAllValues('
            SELECT identifier FROM fs_feature_toggles
        ');
    }
}
