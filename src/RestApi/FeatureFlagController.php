<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Development\FeatureFlags\DependencyInjection\FeatureFlagChecker;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use OpenApi\Attributes\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class FeatureFlagController extends AbstractFOSRestController
{
    public function __construct(
        private readonly FeatureFlagChecker $featureFlagChecker,
        private readonly Session $session,
    ) {
    }

    #[Tag("featureflags")]
    #[Get(path: "featureflags/test")]
    public function testAction(Request $request): JsonResponse
    {
        
    }
}
