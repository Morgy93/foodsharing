<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\RegionPinStatus;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\TeamStatus;
use Foodsharing\Modules\Map\DTO\CommunityMapMarker;
use Foodsharing\Modules\Map\DTO\StoreMapMarker;
use Foodsharing\Modules\Map\MapGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\RestApi\Models\Map\FilterModel;
use Foodsharing\RestApi\Models\Map\StoreFilterModel;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class MapRestController extends AbstractFOSRestController
{
	public function __construct(
		private MapGateway $mapGateway,
		private RegionGateway $regionGateway,
		private StoreGateway $storeGateway,
		private StorePermissions $storePermissions,
		private Session $session
	) {
	}

	/**
	 * Returns the coordinates of filteres stores.
	 *
	 * @OA\Response(response="401", description="Not logged in.")
	 * @OA\Tag(name="map")
	 *
	 * @Rest\Get("map/markers/communities")
	 *
	 * @OA\Response(
	 * 		response="200",
	 * 		description="Success returns list of related regions of user",
	 *      @OA\JsonContent(
	 *        type="array",
	 *        @OA\Items(ref=@Model(type=CommunityMapMarker::class))
	 *      )
	 * )
	 * @OA\RequestBody(@Model(type=FilterModel::class))
	 * @ParamConverter("filter", class="Foodsharing\RestApi\Models\Map\filterModel", converter="fos_rest.request_body")
	 */
	public function getCommunityMarkers(FilterModel $filter): Response
	{
		$markers = $this->mapGateway->getCommunityMarkers($filter);

		return $this->handleView($this->view($markers, 200));
	}

	/**
	 * Returns the coordinates of filteres stores.
	 *
	 * @OA\Response(response="401", description="Not logged in.")
	 * @OA\Tag(name="map")
	 *
	 * @Rest\Get("map/markers/stores")
	 *
	 * @OA\Response(
	 * 		response="200",
	 * 		description="Success returns list of related regions of user",
	 *      @OA\JsonContent(
	 *        type="array",
	 *        @OA\Items(ref=@Model(type=StoreMapMarker::class))
	 *      )
	 * )
	 * @OA\RequestBody(@Model(type=StoreFilterModel::class))
	 * @ParamConverter("storeFilter", class="Foodsharing\RestApi\Models\Map\StoreFilterModel", converter="fos_rest.request_body")
	 */
	public function getStoreMarkers(StoreFilterModel $storeFilter): Response
	{
		$markers = $this->mapGateway->getStoreMarkers($storeFilter);

		return $this->handleView($this->view($markers, 200));
	}

	/**
	 * Returns the store filters.
	 *
	 * @OA\Response(response="401", description="Not logged in.")
	 * @OA\Tag(name="map")
	 *
	 * @Rest\Get("map/filters/store")
	 */
	public function getStoreFilters(): Response
	{
		// if (!$this->session->id()) {
		// 	throw new UnauthorizedHttpException('', 'Not logged in.');
		// }

		// if (!$this->session->mayRole(Role::FOODSAVER)) {
		// 	throw new AccessDeniedHttpException('Not allowed.');
		// }

		$cooperationStatus = CooperationStatus::getConstants();
		$teamStatus = TeamStatus::getConstants();

		return $this->handleView($this->view([
			'cooperationStatus' => $cooperationStatus,
			'teamStatus' => $teamStatus
		], 200));
	}

	// /**
	//  * Returns the coordinates of all baskets.
	//  *
	//  * @OA\Response(response="200", description="Success.")
	//  * @OA\Response(response="401", description="Not logged in.")
	//  * @OA\Tag(name="map")
	//  *
	//  * @Rest\Get("map/markers")
	//  * @Rest\QueryParam(name="types")
	//  * @Rest\QueryParam(name="status")
	//  */
	// public function getMapMarkersAction(ParamFetcher $paramFetcher): Response
	// {
	// 	$types = (array)$paramFetcher->get('types');
	// 	$markers = [];
	// 	if (in_array('baskets', $types)) {
	// 		$markers['baskets'] = $this->mapGateway->getBasketMarkers();
	// 	}
	// 	if (in_array('fairteiler', $types)) {
	// 		$markers['fairteiler'] = $this->mapGateway->getFoodSharePointMarkers();
	// 	}
	// 	if (in_array('communities', $types)) {
	// 		$markers['communities'] = $this->mapGateway->getCommunityMarkers();
	// 	}
	// 	if (in_array('betriebe', $types)) {
	// 		if (!$this->session->id()) {
	// 			throw new UnauthorizedHttpException('', 'Not logged in.');
	// 		}

	// 		$excludedStoreTypes = [];
	// 		$teamStatus = [];
	// 		$status = $paramFetcher->get('status');
	// 		if (is_array($status) && !empty($status)) {
	// 			foreach ($status as $s) {
	// 				switch ($s) {
	// 					case 'STANDARD':
	// 						$excludedStoreTypes = array_merge($excludedStoreTypes, [
	// 							CooperationStatus::PERMANENTLY_CLOSED, CooperationStatus::GIVES_TO_OTHER_CHARITY,
	// 							CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US
	// 						]);
	// 						break;
	// 					case 'NEED_HELP_INSTANT':
	// 						$teamStatus[] = TeamStatus::OPEN_SEARCHING;
	// 						$excludedStoreTypes = array_merge($excludedStoreTypes, [
	// 							CooperationStatus::PERMANENTLY_CLOSED, CooperationStatus::GIVES_TO_OTHER_CHARITY,
	// 							CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US
	// 						]);
	// 						break;
	// 					case 'NEED_HELP':
	// 						$teamStatus[] = TeamStatus::OPEN;
	// 						$excludedStoreTypes = array_merge($excludedStoreTypes, [
	// 							CooperationStatus::PERMANENTLY_CLOSED, CooperationStatus::GIVES_TO_OTHER_CHARITY,
	// 							CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US
	// 						]);
	// 						break;
	// 					case 'IN_NEGOTIATION':
	// 						$excludedStoreTypes = array_merge($excludedStoreTypes, [
	// 							CooperationStatus::COOPERATION_STARTING, CooperationStatus::COOPERATION_ESTABLISHED,
	// 							CooperationStatus::PERMANENTLY_CLOSED, CooperationStatus::GIVES_TO_OTHER_CHARITY,
	// 							CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US
	// 						]);
	// 						break;
	// 					case 'ALL_STORES':
	// 						$excludedStoreTypes = array_merge($excludedStoreTypes, [
	// 						]);
	// 						break;
	// 					default:
	// 						$excludedStoreTypes = array_merge($excludedStoreTypes, [
	// 							CooperationStatus::PERMANENTLY_CLOSED, CooperationStatus::GIVES_TO_OTHER_CHARITY,
	// 							CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US
	// 						]);
	// 				}
	// 			}
	// 		}

	// 		$markers['betriebe'] = $this->mapGateway->getStoreMarkers($excludedStoreTypes, $teamStatus);
	// 	}

	// 	return $this->handleView($this->view($markers, 200));
	// }

	/**
	 * Returns the data for the bubble of a region marker on the map.
	 *
	 * @OA\Response(response="200", description="Success.")
	 * @OA\Response(response="404", description="The region does not exist or does not have a description.")
	 * @OA\Tag(name="map")
	 *
	 * @Rest\Get("map/regions/{regionId}")
	 * @Rest\QueryParam(name="regionId", requirements="\d+", nullable=true, description="Region for which to return the description")
	 */
	public function getRegionInformations(int $regionId): Response
	{
		$region = $this->regionGateway->getRegion($regionId);
		$pin = $this->regionGateway->getRegionPin($regionId);
		if (empty($pin) || $pin['status'] != RegionPinStatus::ACTIVE) {
			throw new NotFoundHttpException('region does not exist or the pin is not active');
		}

		return $this->handleView($this->view([
			'name' => $region['name'],
			'description' => $pin['desc']
		], 200));
	}

	// /**
	//  * Returns the informations about a given store.
	//  *
	//  * @OA\Response(response="200", description="Success.")
	//  * @OA\Response(response="404", description="The region does not exist or does not have a community description.")
	//  * @OA\Tag(name="map")
	//  *
	//  * @Rest\Get("map/regions/{regionId}")
	//  * @Rest\QueryParam(name="regionId", requirements="\d+", nullable=true, description="Region for which to return the description")
	//  */
	// public function getStoreInformations(int $storeId): Response
	// {
	// 	if (!$this->session->id()) {
	// 		throw new UnauthorizedHttpException('');
	// 	}

	// 	if (!$this->storePermissions->mayDoPickup($storeId)) {
	// 		throw new AccessDeniedHttpException();
	// 	}

	// 	$region = $this->storeGateway->getBetrieb($storeId);
	// 	$pin = $this->regionGateway->getRegionPin($storeId);
	// 	if (empty($pin) || $pin['status'] != RegionPinStatus::ACTIVE) {
	// 		throw new NotFoundHttpException('region does not exist or its pin is not active');
	// 	}

	// 	return $this->handleView($this->view([
	// 		'name' => $region['name'],
	// 		'description' => $pin['desc']
	// 	], 200));
	// }
}
