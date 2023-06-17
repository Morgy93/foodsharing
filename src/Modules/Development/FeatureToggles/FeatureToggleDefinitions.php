<?php

namespace Foodsharing\Modules\Development\FeatureToggles;

use ReflectionClass;

final class FeatureToggleDefinitions
{
    public const ALWAYS_TRUE_FOR_TESTING_PURPOSES = 'alwaysTrueForTestingPurposes';
    public const ALWAYS_FALSE_FOR_TESTING_PURPOSES = 'alwaysFalseForTestingPurposes';

    /**
     * Returns all feature toggle identifiers.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        $identifierDefinitions = new ReflectionClass(self::class);

        return array_values($identifierDefinitions->getConstants());
    }
}
