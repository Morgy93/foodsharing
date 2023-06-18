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

    public function __construct(string $identifier, bool $isActive, bool $isToggable)
    {
        $this->isActive = $isActive;
        $this->isToggable = $isToggable;
        $this->identifier = $identifier;
    }
}
