<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\RegionPinStatus;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\TeamStatus;
use Foodsharing\Modules\Map\MapGateway;
use Foodsharing\Modules\Region\RegionGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class MapRestController extends AbstractFOSRestController
{
    private MapGateway $mapGateway;
    private RegionGateway $regionGateway;
    private Session $session;

    public function __construct(
        MapGateway $mapGateway,
        RegionGateway $regionGateway,
        Session $session
    ) {
        $this->mapGateway = $mapGateway;
        $this->regionGateway = $regionGateway;
        $this->session = $session;
    }

    /**
     * Returns the coordinates of all baskets.
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
        if (in_array('betriebe', $types)) {
            if (!$this->session->id()) {
                throw new UnauthorizedHttpException('', 'Not logged in.');
            }

            $excludedStoreTypes = [];
            $teamStatus = [];
            $status = $paramFetcher->get('status');
            if (is_array($status) && !empty($status)) {
                foreach ($status as $s) {
                    switch ($s) {
                        case 'needhelpinstant':
                            $teamStatus[] = TeamStatus::OPEN_SEARCHING;
                            break;
                        case 'needhelp':
                            $teamStatus[] = TeamStatus::OPEN;
                            break;
                        case 'nkoorp':
                            $excludedStoreTypes = array_merge($excludedStoreTypes, [
                                CooperationStatus::COOPERATION_STARTING, CooperationStatus::COOPERATION_ESTABLISHED
                            ]);
                            break;
                    }
                }
            }

            $markers['betriebe'] = $this->mapGateway->getStoreMarkers($excludedStoreTypes, $teamStatus);
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
