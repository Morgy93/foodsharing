<?php

declare(strict_types=1);


namespace Foodsharing\RestApi\Models\FeatureFlag;

use Symfony\Component\Validator\Constraints\NotBlank;

class IsFeatureFlagActiveResponse
{
    #[NotBlank]
    public readonly string $featureFlag;

    #[NotBlank]
    public readonly bool $isActive;

    static public function create(string $featureFlag, bool $isActive): self
    {
        $response = new IsFeatureFlagActiveResponse();
        $response->featureFlag = $featureFlag;
        $response->isActive = $isActive;
        return $response;
    }
}
