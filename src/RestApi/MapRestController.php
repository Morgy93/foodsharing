<?php

namespace Foodsharing\RestApi;

use Foodsharing\Modules\Core\DBConstants\Region\RegionPinStatus;
use Foodsharing\Modules\Map\MapGateway;
use Foodsharing\Modules\Region\RegionGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MapRestController extends AbstractFOSRestController
{
    public function __construct(
        private MapGateway $mapGateway,
        private RegionGateway $regionGateway
    ) {
    }

    /**
     * Returns the coordinates of baskets, fairtailer, communities.
     *
     * @OA\Response(response="200", description="Success.")
     * @OA\Response(response="401", description="Not logged in.")
     * @OA\Tag(name="map")
     * @Rest\Get("map/markers")
     * @Rest\QueryParam(name="types")
     * @Rest\QueryParam(name="status")
     */
    public function getMapMarkersAction(ParamFetcher $paramFetcher): Response
    {
        $types = (array)$paramFetcher->get('types');
        $markers = [];
        if (in_array('baskets', $types)) {
            $markers['baskets'] = $this->mapGateway->getBasketMarkers();
        }
        if (in_array('fairteiler', $types)) {
            $markers['fairteiler'] = $this->mapGateway->getFoodSharePointMarkers();
        }
        if (in_array('communities', $types)) {
            $markers['communities'] = $this->mapGateway->getCommunityMarkers();
        }

        return $this->handleView($this->view($markers, 200));
    }

    /**
     * Returns the data for the bubble of a community marker on the map.
     *
     * @OA\Response(response="200", description="Success.")
     * @OA\Response(response="404", description="The region does not exist or does not have a community description.")
     * @OA\Tag(name="map")
     * @Rest\Get("map/regions/{regionId}")
     * @Rest\QueryParam(name="regionId", requirements="\d+", nullable=true, description="Region for which to return the description")
     */
    public function getRegionBubbleAction(int $regionId): Response
    {
        $region = $this->regionGateway->getRegion($regionId);
        $pin = $this->regionGateway->getRegionPin($regionId);
        if (empty($pin) || $pin['status'] != RegionPinStatus::ACTIVE) {
            throw new NotFoundHttpException('region does not exist or its pin is not active');
        }

        return $this->handleView($this->view([
            'name' => $region['name'],
            'description' => $pin['desc']
        ], 200));
    }
}
