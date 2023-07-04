<?php

namespace Foodsharing\Modules\Development\FeatureToggles\Querys;

use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DatabaseNoValueFoundException;
use Foodsharing\Modules\Development\FeatureToggles\Exceptions\FeatureToggleOriginNotFoundException;
use Symfony\Component\Yaml\Yaml;

final class GetFeatureToggleStateQuery
{
    public function __construct(
        private readonly Database $database,
    ) {
    }

    public function execute(string $featureToggleIdentifier): bool
    {
        try {
            $state = $this->database->fetchValue('SELECT is_active FROM fs_feature_toggles WHERE identifier = :featureToggleIdentifier', [
                'featureToggleIdentifier' => $featureToggleIdentifier,
            ]);
        } catch (DatabaseNoValueFoundException) {
            return false;
        }

        return (bool)$state;
    }
}
