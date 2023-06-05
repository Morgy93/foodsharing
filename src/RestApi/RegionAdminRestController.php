<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\RegionAdmin\DTO\RegionDetails;
use Foodsharing\Modules\RegionAdmin\RegionAdminTransactions;
use Foodsharing\Permissions\RegionPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class RegionAdminRestController extends AbstractFOSRestController
{
    public function __construct(
        private RegionAdminTransactions $regionAdminTransactions,
        private RegionPermissions $regionPermissions,
        private Session $session,
    ) {
    }

    /**
     * Returns details about a region for the region admin module.
     *
     * @OA\Tag(name="region")
     * @Rest\Get("region/{regionId}")
     * @OA\Response(
     * 		response="200",
     * 		description="Success",
     *      @Model(type=RegionDetails::class))
     * )
     * @OA\Response(response="401", description="Not logged in")
     * @OA\Response(response="403", description="Insufficient permissions")
     * @OA\Response(response="404", description="The region does not exist")
     */
    public function getRegionDetailsAction(int $regionId): Response
    {
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException('');
        }

        if (!$this->regionPermissions->mayAdministrateRegions()) {
            throw new AccessDeniedHttpException();
        }

        $region = $this->regionAdminTransactions->getRegionDetails($regionId);
        if (is_null($region)) {
            throw new NotFoundHttpException();
        }

        return $this->handleView($this->view($region, 200));
    }

    /**
     * Creates a new region or working group as a child of another region.
     *
     * @OA\Tag(name="region")
     * @Rest\Post("region/{parentId}/children")
     * @OA\Response(
     *     response="200",
     *     description="Success",
     *     @Model(type=RegionDetails::class))
     * )
     * @OA\Response(response="400", description="Parent region does not exist")
     * @OA\Response(response="401", description="Not logged in")
     * @OA\Response(response="403", description="Insufficient permissions")
     */
    public function addRegionAction(int $parentId): Response
    {
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException('');
        }

        if (!$this->regionPermissions->mayAdministrateRegions()) {
            throw new AccessDeniedHttpException();
        }

        $region = $this->regionAdminTransactions->addRegion($parentId);

        return $this->handleView($this->view($region, 200));
    }
}
