<?php

namespace Foodsharing\Modules\Development\FeatureToggles\Querys;

use Foodsharing\Modules\Core\Database;
use Symfony\Component\Yaml\Yaml;

class GetFeatureToggleOrigin
{
    private const FEATURE_TOGGLE_CONFIG_FILE_PATH = __DIR__."/../../../../../config/packages/feature_toggles.yaml";
    public function __construct(
        private readonly Database $database,
    ) {
    }

    /**
     * Returns the origin from feature toggle
     * @param string $featureToggleIdentifier
     * @return string origin (possible: filesystem | database | unknown)
     */
    public function execute(string $featureToggleIdentifier): string
    {
        $featureToggleConfigFile = Yaml::parseFile(self::FEATURE_TOGGLE_CONFIG_FILE_PATH);
        $features = $featureToggleConfigFile["flagception"]["features"];
        $hasExistingHardcodedDefault = array_key_exists("default", $features[$featureToggleIdentifier]);

        if ($hasExistingHardcodedDefault) {
            return 'filesystem';
        }

        $isExistingInsideDatabase = $this->database->exists("fs_feature_toggles", [
            'identifier' => $featureToggleIdentifier,
        ]);

        if ($isExistingInsideDatabase) {
            return 'database';
        }

        return 'unknown';
    }
}
