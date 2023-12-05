<?php

declare(strict_types=1);

namespace Foodsharing\RestApi\Models\FeatureToggle;

use OpenApi\Attributes\Property;
use Symfony\Component\Validator\Constraints\NotBlank;

class IsFeatureToggleActiveResponse
{
    #[Property(description: 'Identifier for requested feature toggle')]
    #[NotBlank]
    public readonly string $featureToggle;

    #[Property]
    #[NotBlank]
    public readonly bool $isActive;

    public function __construct(string $featureToggle, bool $isActive)
    {
        $this->featureToggle = $featureToggle;
        $this->isActive = $isActive;
    }
}
