<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\RegionPinStatus;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\TeamSearchStatus;
use Foodsharing\Modules\Map\MapGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class MapRestController extends AbstractFOSRestController
{
    public function __construct(
        private MapGateway $mapGateway,
        private RegionGateway $regionGateway,
        private StoreGateway $storeGateway,
        private Session $session
    ) {
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
            $teamSearchStatus = [];
            $status = $paramFetcher->get('status');
            $userId = null;

            $excludedStoreTypes = array_merge($excludedStoreTypes, [
                CooperationStatus::PERMANENTLY_CLOSED
            ]);

            if (is_array($status) && !empty($status)) {
                foreach ($status as $s) {
                    switch ($s) {
                        case 'needhelpinstant':
                            $teamSearchStatus[] = TeamSearchStatus::OPEN_SEARCHING;
                            break;
                        case 'needhelp':
                            $teamSearchStatus[] = TeamSearchStatus::OPEN;
                            break;
                        case 'nkoorp':
                            $excludedStoreTypes = array_merge($excludedStoreTypes, [
                                CooperationStatus::COOPERATION_STARTING, CooperationStatus::COOPERATION_ESTABLISHED
                            ]);
                            break;
                        case 'mine':
                            $userId = $this->session->id();
                            break;
                    }
                }
            }

            $markers['betriebe'] = $this->storeGateway->getStoreMarkers($excludedStoreTypes, $teamSearchStatus, $userId);
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

    /**
     * Returns the data for the bubble of a basket marker on the map.
     *
     * @OA\Response(response="200", description="Success", @Model(type=BasketBubbleData::class))
     * @OA\Response(response="404", description="The basket does not exist")
     * @OA\Tag(name="map")
     * @Rest\Get("map/baskets/{basketId}")
     * @Rest\QueryParam(name="basketId", requirements="\d+", nullable=true, description="Basket for which to return data")
     */
    public function getBasketBubbleAction(int $basketId): Response
    {
        $basket = $this->mapGateway->getBasketBubbleData($basketId, $this->session->mayRole());
        if (empty($basket)) {
            throw new NotFoundHttpException('basket does not exist');
        }

        return $this->handleView($this->view($basket, 200));
    }
}
