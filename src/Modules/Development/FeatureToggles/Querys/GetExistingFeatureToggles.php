<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureToggles\Querys;

use Foodsharing\Modules\Core\Database;

final class GetExistingFeatureToggles
{
    public function __construct(
        private readonly Database $database,
        private readonly string $siteEnvironment,
    ) {
    }

    /**
     * @return string[] identifiers of feature toggles
     */
    public function execute(): array
    {
        return $this->database->fetchAllValues('
            SELECT identifier FROM fs_feature_toggles WHERE site_environment = :siteEnvironment
        ', [
            'siteEnvironment' => $this->siteEnvironment,
           ],
        );
    }
}
