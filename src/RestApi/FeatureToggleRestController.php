<?php

namespace Foodsharing\RestApi;

use Foodsharing\Modules\Development\FeatureToggles\DependencyInjection\FeatureToggleChecker;
use Foodsharing\Modules\Development\FeatureToggles\FeatureToggleDefinitions;
use Foodsharing\Modules\Development\FeatureToggles\FeatureToggleService;
use Foodsharing\RestApi\Models\FeatureFlag\FeatureToggle;
use Foodsharing\RestApi\Models\FeatureFlag\FeatureTogglesResponse;
use Foodsharing\RestApi\Models\FeatureFlag\IsFeatureToggleActiveResponse;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

final class FeatureToggleRestController extends AbstractFOSRestController
{
    public function __construct(
        private readonly FeatureToggleChecker $featureToggleChecker,
        private readonly FeatureToggleService $featureToggleService,
    )
    {
    }

    /**
     * Returns all feature toggle identifiers with some information.
     */
    #[Tag('featuretoggle')]
    #[Get(path: 'featuretoggle/')]
    #[Response(response: HttpResponse::HTTP_OK, description: 'Successful', content: new Model(type: FeatureTogglesResponse::class))]
    public function getAllFeatureTogglesAction(): JsonResponse
    {
        $featureToggles = [];

        foreach (FeatureToggleDefinitions::all() as $featureToggleIdentifier) {
            $featureToggles[] = FeatureToggle::create(
                $featureToggleIdentifier,
                $this->featureToggleChecker->isFeatureToggleActive($featureToggleIdentifier),
                $this->featureToggleService->getFeatureToggleOrigin($featureToggleIdentifier),
            );
        }

        return $this->json(
            new FeatureTogglesResponse($featureToggles),
            HttpResponse::HTTP_OK
        );
    }

    #[Tag('featuretoggle')]
    #[Get(path: 'featuretoggle/test')]
    #[Response(response: HttpResponse::HTTP_OK, description: 'Successful')]
    public function getTestsAction(): JsonResponse
    {
        return $this->json(
            [],
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
