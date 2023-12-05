<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureToggles\Commands;

use Foodsharing\Modules\Core\Database;

final class DeleteUndefinedFeatureTogglesCommand
{
    public function __construct(
        private readonly Database $database,
        private readonly string $siteEnvironment,
    ) {
    }

    /**
     * Removes database entries for these identifiers.
     *
     * @param string[] $identifiers
     */
    public function execute(array $identifiers): void
    {
        foreach ($identifiers as $identifier) {
            $this->database->delete(
                'fs_feature_toggles',
                [
                    'identifier' => $identifier,
                    'site_environment' => $this->siteEnvironment,
                ],
            );
        }
    }
}
