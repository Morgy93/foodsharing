<?php

namespace Foodsharing\RestApi;

use Foodsharing\Modules\Development\FeatureFlags\DependencyInjection\FeatureFlagChecker;
use Foodsharing\RestApi\Models\FeatureFlag\IsFeatureFlagActiveResponse;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

final class FeatureFlagRestController extends AbstractFOSRestController
{
    public function __construct(
        private readonly FeatureFlagChecker $featureFlagChecker,
    ) {
    }

    #[Tag('featureflags')]
    #[Get(path: 'featureflags/{feature flag}')]
    #[Parameter(name: 'feature flag', description: 'Identifier for feature flag', in: 'path', required: true)]
    #[Response(response: HttpResponse::HTTP_OK, description: 'Successful', content: new Model(type: IsFeatureFlagActiveResponse::class))]
    public function isFeatureFlagActiveAction(string $featureFlag): JsonResponse
    {
        $isFeatureFlagActive = $this->featureFlagChecker->isFeatureFlagActive($featureFlag);

        return $this->json(new IsFeatureFlagActiveResponse($featureFlag, $isFeatureFlagActive), HttpResponse::HTTP_OK);
    }
}
