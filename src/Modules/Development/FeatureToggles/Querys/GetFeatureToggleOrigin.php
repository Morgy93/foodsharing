<?php

namespace Foodsharing\Modules\Development\FeatureToggles\Querys;

use Exception;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Development\FeatureToggles\Exceptions\FeatureToggleOriginNotFoundException;
use Symfony\Component\Yaml\Yaml;

class GetFeatureToggleOrigin
{
    private const FEATURE_TOGGLE_CONFIG_FILE_PATH = __DIR__ . '/../../../../../config/packages/feature_toggles.yaml';

    public function __construct(
        private readonly Database $database,
    ) {
    }

    /**
     * Returns the origin from feature toggle.
     *
     * @return string origin (possible: filesystem | database)
     * @throws FeatureToggleOriginNotFoundException
     */
    public function execute(string $featureToggleIdentifier): string
    {
        $featureToggleConfigFile = Yaml::parseFile(self::FEATURE_TOGGLE_CONFIG_FILE_PATH);
        $features = $featureToggleConfigFile['flagception']['features'];

        $isFeatureToggleHardcoded = array_key_exists($featureToggleIdentifier, $features);

        if ($isFeatureToggleHardcoded) {
            return 'filesystem';
        }

        $isExistingInsideDatabase = $this->database->exists('fs_feature_toggles', [
            'identifier' => $featureToggleIdentifier,
        ]);

        if ($isExistingInsideDatabase) {
            return 'database';
        }

        throw new FeatureToggleOriginNotFoundException(
            sprintf('Origin from feature toggle (identifier: %s) not found', $featureToggleIdentifier)
        );
    }
}
