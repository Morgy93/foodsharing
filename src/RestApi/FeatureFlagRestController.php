<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Development\FeatureFlags\DependencyInjection\FeatureFlagChecker;
use Foodsharing\RestApi\Models\FeatureFlag\IsFeatureFlagActiveResponse;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class FeatureFlagRestController extends AbstractFOSRestController
{
    public function __construct(
        private readonly FeatureFlagChecker $featureFlagChecker,
    ) {
    }

    #[Tag("featureflags")]
    #[Get(path: "featureflags/{featureflag}/is-active")]
    #[Parameter(name: "featureflag", description: "Identifier for feature flag", in: "path", required: true)]
    #[\OpenApi\Attributes\Response(response: Response::HTTP_OK, description: "Successfull")]
    public function isFeatureFlagActiveAction(string $featureFlag): JsonResponse
    {
        $isFeatureFlagActive = $this->featureFlagChecker->isFeatureFlagActive($featureFlag);
        return $this->json(IsFeatureFlagActiveResponse::create($featureFlag, $isFeatureFlagActive), Response::HTTP_OK);
    }
}
