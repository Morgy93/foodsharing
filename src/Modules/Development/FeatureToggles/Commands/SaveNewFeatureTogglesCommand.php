<?php

namespace Foodsharing\Modules\Development\FeatureToggles\Commands;

use Foodsharing\Modules\Core\Database;

final class SaveNewFeatureTogglesCommand
{
    public function __construct(
       private readonly Database $database,
    ) {
    }

    /**
     * Creates new database entries for these identifiers. They are not active after creation.
     *
     * @param string[] $identifiers
     */
    public function execute(array $identifiers): void
    {
        $rows = [];

        foreach ($identifiers as $identifier) {
            $rows[] = [
                'identifier' => $identifier,
                'isActive' => false,
            ];
        }

        $this->database->insertMultiple('fs_feature_toggles', $rows);
    }
}
