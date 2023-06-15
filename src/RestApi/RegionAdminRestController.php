<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\RegionAdmin\RegionAdminTransactions;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\RestApi\Models\Region\RegionUpdateModel;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegionAdminRestController extends AbstractFOSRestController
{
    public function __construct(
        private readonly RegionAdminTransactions $regionAdminTransactions,
        private readonly RegionPermissions $regionPermissions,
        private readonly RegionGateway $regionGateway,
        private readonly Session $session,
    ) {
    }

    /**
     * Returns details about a region for the region admin module.
     *
     * @OA\Tag(name="region")
     * @OA\Response(
     * 		response="200",
     * 		description="Success",
     *      @Model(type=RegionDetails::class))
     * )
     * @OA\Response(response="401", description="Not logged in")
     * @OA\Response(response="403", description="Insufficient permissions")
     * @OA\Response(response="404", description="The region does not exist")
     * @Rest\Get("region/{regionId}", requirements={"regionId" = "\d+"})
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
     * @OA\Response(
     *     response="200",
     *     description="Success",
     *     @Model(type=RegionDetails::class))
     * )
     * @OA\Response(response="400", description="Parent region does not exist")
     * @OA\Response(response="401", description="Not logged in")
     * @OA\Response(response="403", description="Insufficient permissions")
     * @Rest\Post("region/{parentId}/children", requirements={"parentId" = "\d+"})
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

    /**
     * Changes properties of a region.
     *
     * @OA\Tag(name="region")
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="400", description="Invalid parameters")
     * @OA\Response(response="401", description="Not logged in")
     * @OA\Response(response="403", description="Insufficient permissions")
     * @OA\Response(response="404", description="Region does not exist")
     * @OA\RequestBody(@Model(type=RegionUpdateModel::class))
     * @ParamConverter("regionModel", class="Foodsharing\RestApi\Models\Region\RegionUpdateModel", converter="fos_rest.request_body")
     * @Rest\Patch("region/{regionId}", requirements={"regionId" = "\d+"})
     */
    public function updateRegionAction(int $regionId, RegionUpdateModel $regionModel, ValidatorInterface $validator): Response
    {
        // check permissions
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException('');
        }

        if (!$this->regionPermissions->mayAdministrateRegions()) {
            throw new AccessDeniedHttpException();
        }
        if (!is_null($regionModel->workingGroupFunction) &&
            !$this->regionPermissions->mayAdministrateWorkgroupFunction($regionModel->workingGroupFunction)) {
            throw new AccessDeniedHttpException();
        }

        $region = $this->regionGateway->getRegion($regionId);
        if (empty($region)) {
            throw new NotFoundHttpException();
        }

        // validate the data
        $errors = $validator->validate($regionModel);
        if ($errors->count() > 0) {
            $firstError = $errors->get(0);
            throw new BadRequestHttpException(json_encode(['field' => $firstError->getPropertyPath(), 'message' => $firstError->getMessage()]));
        }

        // do the update
        $this->regionAdminTransactions->updateRegion($regionId, $regionModel, $region);

        return $this->handleView($this->view([], 200));
    }
}
