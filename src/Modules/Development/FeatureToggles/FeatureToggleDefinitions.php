<?php

namespace Foodsharing\Modules\Development\FeatureToggles;

use ReflectionClass;

/**
 * For each feature toggle, please add a public constant in SCREAMING_SNAKE_CASE with same name as value in lowerCamelCase.
 * After adding or removing some feature toggle definition, please run the command foodsharing:update:featuretoggles.
 * For more description and usage about feature toggles, please visit the devdocs.
 */
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
