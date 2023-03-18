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

    public static function create(string $featureFlag, bool $isActive): self
    {
        $response = new IsFeatureFlagActiveResponse();
        $response->featureFlag = $featureFlag;
        $response->isActive = $isActive;

        return $response;
    }
}
