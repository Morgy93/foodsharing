<?php

declare(strict_types=1);

namespace Foodsharing\RestApi\Models\FeatureFlag;

use OpenApi\Attributes\Property;
use Symfony\Component\Validator\Constraints\NotBlank;

class IsFeatureFlagActiveResponse
{
    #[Property(description: 'Identifier for requested feature flag')]
    #[NotBlank]
    public readonly string $featureFlag;

    #[Property]
    #[NotBlank]
    public readonly bool $isActive;

    public function __construct(string $featureFlag, bool $isActive)
    {
        $this->featureFlag = $featureFlag;
        $this->isActive = $isActive;
    }
}
