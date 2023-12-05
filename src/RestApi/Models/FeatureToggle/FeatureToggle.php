<?php

namespace Foodsharing\RestApi\Models\FeatureToggle;

final class FeatureToggle
{
    public readonly string $identifier;
    public readonly bool $isActive;

    public function __construct(string $identifier, bool $isActive)
    {
        $this->isActive = $isActive;
        $this->identifier = $identifier;
    }
}
