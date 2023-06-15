<?php

namespace Foodsharing\RestApi;

use Foodsharing\Modules\Development\FeatureToggles\DependencyInjection\FeatureToggleChecker;
use Foodsharing\Modules\Development\FeatureToggles\FeatureToggleIdentifier;
use Foodsharing\Modules\Development\FeatureToggles\FeatureToggleService;
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
        private readonly FeatureToggleService $featureToggleService,
    ) {
    }

    /**
     * Returns all feature toggle identifiers with some information.
     */
    #[Tag('featuretoggle')]
    #[Get(path: 'featuretoggle/')]
    #[Response(response: HttpResponse::HTTP_OK, description: 'Successful')]
    public function getAllFeatureTogglesAction(): JsonResponse
    {
        $featureToggles = [];

        foreach (FeatureToggleIdentifier::all() as $featureToggleIdentifier) {
            $featureToggles[] = [
                "identifier" => $featureToggleIdentifier,
                "isActive" => $this->featureToggleChecker->isFeatureToggleActive($featureToggleIdentifier),
                "origin" => $this->featureToggleService->getFeatureToggleOrigin($featureToggleIdentifier),
            ];
        }

        return $this->json(
            $featureToggles,
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Check if a feature toggle is active or not.
     */
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
