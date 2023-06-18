<?php

namespace Foodsharing\RestApi;

use Foodsharing\Modules\Development\FeatureToggles\DependencyInjection\FeatureToggleChecker;
use Foodsharing\Modules\Development\FeatureToggles\FeatureToggleDefinitions;
use Foodsharing\Modules\Development\FeatureToggles\FeatureToggleService;
use Foodsharing\RestApi\Models\FeatureFlag\FeatureToggle;
use Foodsharing\RestApi\Models\FeatureFlag\FeatureTogglesResponse;
use Foodsharing\RestApi\Models\FeatureFlag\IsFeatureToggleActiveResponse;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Tag;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

final class FeatureToggleRestController extends AbstractFOSRestController
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
    #[Rest\Get(path: 'featuretoggle/')]
    #[Response(response: HttpResponse::HTTP_OK, description: 'Successful', content: new Model(type: FeatureTogglesResponse::class))]
    public function getAllFeatureTogglesAction(): JsonResponse
    {
        $featureToggles = [];

        foreach (FeatureToggleDefinitions::all() as $featureToggleIdentifier) {
            $featureToggles[] = FeatureToggle::create(
                $featureToggleIdentifier,
                $this->featureToggleChecker->isFeatureToggleActive($featureToggleIdentifier),
                $this->featureToggleService->isFeatureToggleToggable($featureToggleIdentifier),
            );
        }

        return $this->json(
            new FeatureTogglesResponse($featureToggles),
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Check if a feature toggle is active or not.
     */
    #[Tag('featuretoggle')]
    #[Rest\Get(path: 'featuretoggle/{featureToggle}')]
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

    #[Tag('featuretoggle')]
    #[Rest\Post(path: 'featuretoggle/{featureToggle}/toggle')]
    #[Parameter(name: 'featureToggle', description: 'Identifier for feature toggle', in: 'path', required: true)]
    #[Response(response: HttpResponse::HTTP_OK, description: 'Successful')]
    #[Response(response: HttpResponse::HTTP_NOT_FOUND, description: 'Feature toggle is not defined')]
    #[Response(response: HttpResponse::HTTP_BAD_REQUEST, description: 'Feature toggle is not toggable')]
    public function toggleFeatureToggleAction(string $featureToggle): JsonResponse
    {
        if (!$this->featureToggleService->isFeatureToggleDefined($featureToggle)) {
            throw $this->createNotFoundException('Feature toggle is not defined');
        }

        if (!$this->featureToggleService->isFeatureToggleToggable($featureToggle)) {
            throw new BadRequestException('Feature toggle is not toggable');
        }

        $currentState = $this->featureToggleChecker->isFeatureToggleActive($featureToggle);

        $this->featureToggleService->updateFeatureToggleState($featureToggle, !$currentState);

        return $this->json(
            null,
            HttpResponse::HTTP_OK
        );
    }

}
