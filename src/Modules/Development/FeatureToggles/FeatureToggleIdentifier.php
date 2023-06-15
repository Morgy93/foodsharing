<?php

namespace Foodsharing\Modules\Development\FeatureToggles;

use ReflectionClass;

final class FeatureToggleIdentifier
{
    public const ALWAYS_TRUE_FOR_TESTING_PURPOSES = "alwaysTrueForTestingPurposes";
    public const ALWAYS_FALSE_FOR_TESTING_PURPOSES = "alwaysFalseForTestingPurposes";

    /**
     * Returns all feature toggle identifiers.
     * @return array<String, String>
     */
    public static function all(): array
    {
        $oClass = new ReflectionClass(self::class);
        return $oClass->getConstants();
    }
}
