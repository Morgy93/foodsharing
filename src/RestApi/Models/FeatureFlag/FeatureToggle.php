<?php

namespace Foodsharing\RestApi\Models\FeatureFlag;

use OpenApi\Attributes\Property;

class FeatureToggle
{
    public readonly string $identifier;
    public readonly bool $isActive;

    #[Property(
        description: 'When origin = database, the featuretoggle can be disabled via weboverview.',
        enum: ['filesystem', 'database'],
    )]
    public readonly string $origin;

    public static function create(string $identifier, bool $isActive, string $origin): self
    {
        $featureToggle = new FeatureToggle();

        $featureToggle->isActive = $isActive;
        $featureToggle->origin = $origin;
        $featureToggle->identifier = $identifier;

        return $featureToggle;
    }
}
