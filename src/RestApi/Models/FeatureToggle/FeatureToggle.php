<?php

namespace Foodsharing\RestApi\Models\FeatureToggle;

use OpenApi\Attributes\Property;

class FeatureToggle
{
    public readonly string $identifier;
    public readonly bool $isActive;

    #[Property(
        description: 'Is feature toggle toggable via api or not.',
    )]
    public readonly bool $isToggable;

    public static function create(string $identifier, bool $isActive, bool $isToggable): self
    {
        $featureToggle = new FeatureToggle();

        $featureToggle->isActive = $isActive;
        $featureToggle->isToggable = $isToggable;
        $featureToggle->identifier = $identifier;

        return $featureToggle;
    }
}
