<?php

namespace Foodsharing\Modules\Development\FeatureToggles\Querys;

use Symfony\Component\Yaml\Yaml;

final class GetExistingHardcodedFeatureTogglesQuery
{
    private const FEATURE_TOGGLE_CONFIG_FILE_PATH = __DIR__ . '/../../../../../config/packages/feature_toggles.yaml';

    /**
     * @return string[] identifiers of hardcoded feature toggles
     */
    public function execute(): array
    {
        $featureToggleConfigFile = Yaml::parseFile(self::FEATURE_TOGGLE_CONFIG_FILE_PATH);
        return array_keys($featureToggleConfigFile['flagception']['features']);
    }
}
