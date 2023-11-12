<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureToggles\Querys;

use Foodsharing\Modules\Core\Database;

final class IsFeatureToggleActiveQuery
{
    public function __construct(
        private readonly Database $database,
        private readonly string $siteEnvironment,
    ) {
    }

    public function execute(string $featureToggleIdentifier): bool
    {
        return (bool)$this->database->fetchValue('
           SELECT is_active FROM fs_feature_toggles WHERE identifier = :identifier AND site_environment = :siteEnvironment;
        ', [
            'identifier' => $featureToggleIdentifier,
            'siteEnvironment' => $this->siteEnvironment,
           ],
        );
    }
}
