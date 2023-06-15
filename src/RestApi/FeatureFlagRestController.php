<?php

namespace Foodsharing\RestApi;

use Foodsharing\Modules\Development\FeatureToggles\Querys\DependencyInjection\FeatureToggleChecker;
use Foodsharing\RestApi\Models\FeatureFlag\IsFeatureToggleActiveResponse;
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
        private readonly FeatureToggleChecker $featureToggleChecker,
    ) {
    }

    #[Tag('featuretoggle')]
    #[Get(path: 'featuretoggle/{featureToggle}')]
    #[Parameter(name: 'featureToggle', description: 'Identifier for feature toggle', in: 'path', required: true)]
    #[Response(response: HttpResponse::HTTP_OK, description: 'Successful', content: new Model(type: IsFeatureToggleActiveResponse::class))]
    public function isFeatureToggleActiveAction(string $featureToggle): JsonResponse
    {
        $isFeatureFlagActive = $this->featureToggleChecker->isFeatureToggleActive($featureToggle);
        return $this->json(
            new IsFeatureToggleActiveResponse($featureToggle, $isFeatureFlagActive),
            HttpResponse::HTTP_OK
        );
    }
}
