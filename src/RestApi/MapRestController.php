<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\RegionPinStatus;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\TeamSearchStatus;
use Foodsharing\Modules\FoodSharePoint\FoodSharePointGateway;
use Foodsharing\Modules\Map\MapGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\RestApi\Models\Map\FoodSharePointBubbleData;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class MapRestController extends AbstractFOSRestController
{
    public function __construct(
        private readonly MapGateway $mapGateway,
        private readonly RegionGateway $regionGateway,
        private readonly StoreGateway $storeGateway,
        private readonly FoodSharePointGateway $foodSharePointGateway,
        private readonly Session $session
    ) {
    }

    /**
     * Returns the coordinates of all baskets.
     */
    #[OA\Tag('map')]
    #[Rest\Get(path: 'map/markers')]
    #[Rest\QueryParam(name: 'types')]
    #[Rest\QueryParam(name: 'status')]
    #[OA\Response(response: Response::HTTP_OK, description: 'Successful')]
    #[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Not logged in.')]
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
                CooperationStatus::PERMANENTLY_CLOSED,
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
                                CooperationStatus::COOPERATION_STARTING,
                                CooperationStatus::COOPERATION_ESTABLISHED,
                            ]);
                            break;
                        case 'mine':
                            $userId = $this->session->id();
                            break;
                    }
                }
            }

            $markers['betriebe'] = $this->storeGateway->getStoreMarkers(
                $excludedStoreTypes,
                $teamSearchStatus,
                $userId
            );
        }

        return $this->handleView($this->view($markers, Response::HTTP_OK));
    }

    /**
     * Returns the data for the bubble of a community marker on the map.
     */
    #[OA\Tag('map')]
    #[Rest\Get(path: 'map/regions/{regionId}')]
    #[Rest\QueryParam(name: 'regionId', requirements: '\d+', description: 'Region for which to return the description', nullable: true)]
    #[OA\Response(response: Response::HTTP_OK, description: 'Successful')]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'The region does not exist or does not have a community description.')]
    public function getRegionBubbleAction(int $regionId): Response
    {
        $region = $this->regionGateway->getRegion($regionId);
        $pin = $this->regionGateway->getRegionPin($regionId);
        if (empty($pin) || $pin['status'] != RegionPinStatus::ACTIVE) {
            throw new NotFoundHttpException('region does not exist or its pin is not active');
        }

        return $this->handleView($this->view([
            'name' => $region['name'],
            'description' => $pin['desc'],
        ], Response::HTTP_OK));
    }

    /**
     * Returns the data for a FoodSharePoint.
     */
    #[OA\Tag('map')]
    #[Rest\Get(path: 'map/foodSharePoint/{foodSharePointId}')]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Successful',
        content: new Model(type: FoodSharePointBubbleData::class)
    )]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'The Foodsharepoint does not exist')]
    public function getFoodSharePoint(int $foodSharePointId): Response
    {
        $foodSharePoint = $this->foodSharePointGateway->getFoodSharePoint($foodSharePointId);

        if (count($foodSharePoint) === 0) {
            throw new NotFoundHttpException('The Foodsharepoint does not exist');
        }

        return $this->handleView($this->view(new FoodSharePointBubbleData($foodSharePoint), Response::HTTP_OK));
    }
}
