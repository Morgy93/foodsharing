<?php

namespace Foodsharing\RestApi;

use Codeception\Util\HttpCode;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Map\MapConstants;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\TeamStatus;
use Foodsharing\Modules\Map\DTO\CommunityMapMarker;
use Foodsharing\Modules\Map\DTO\FoodbasketMapMarker;
use Foodsharing\Modules\Map\DTO\FoodSharePointMapMarker;
use Foodsharing\Modules\Map\DTO\StoreMapMarker;
use Foodsharing\Modules\Map\MapGateway;
// use Foodsharing\Modules\Region\RegionGateway;
// use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\RestApi\Constants\HttpExceptionResponse;
use Foodsharing\RestApi\Models\Map\FilterModel;
use Foodsharing\RestApi\Models\Map\StoreFilterModel;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MapRestController extends AbstractFOSRestController
{
	public function __construct(
		private MapGateway $mapGateway,
		// private RegionGateway $regionGateway,
		// private StoreGateway $storeGateway,
		private Session $session
	) {
	}

	/**
	 * Returns a list with coordinates of foodbaskets, limited by distance of coordinates.
	 *
	 * @OA\Response(
	 * 		response=HttpCode::OK,
	 * 		description=HttpExceptionResponse::SUCCESS,
	 *      @OA\JsonContent(
	 *        type="array",
	 *        @OA\Items(ref=@Model(type=FoodbasketMapMarker::class))
	 *      )
	 * )
	 * @Rest\QueryParam(
	 *  name="d",
	 *  default=MapConstants::DEFAULT_SEARCH_DISTANCE,
	 *  description="Defines the search distance in kilometers.",
	 * 	requirements="\d+",
	 *  nullable=true
	 * )
	 * @Rest\QueryParam(
	 *  name="cp",
	 *  default=MapConstants::CENTER_GERMANY,
	 *  description="Defines the search center point with `[latitude, longitude]`.",
	 * 	requirements="[+-]?((\d+\.?\d*)|(\.\d+)),[+-]?((\d+\.?\d*)|(\.\d+))",
	 *  nullable=true
	 * )
	 * @OA\Tag(name="map")
	 * @Rest\Get("marker/foodbaskets")
	 */
	public function getFoodBasketMarkers(
		ValidatorInterface $validator,
		ParamFetcher $paramFetcher,
	): Response {
		// QueryParams
		[$latitude, $longitude] = explode(',', $paramFetcher->get('cp') ?? MapConstants::CENTER_GERMANY);
		$distance = $paramFetcher->get('d') ?? MapConstants::DEFAULT_SEARCH_DISTANCE;

		// Filtering
		$filter = new FilterModel((float)$latitude, (float)$longitude, $distance);
		$errors = $validator->validate($filter);
		$this->throwBadRequestExceptionOnError($errors);

		// Get and send markers
		$markers = $this->mapGateway->getFoodBasketMarkers($filter);

		return $this->handleView($this->view($markers, HttpCode::OK));
	}

	/**
	 * Returns a list with coordinates of foodsharing points, limited by distance of coordinates.
	 *
	 * @OA\Response(
	 * 		response=HttpCode::OK,
	 * 		description=HttpExceptionResponse::SUCCESS,
	 *      @OA\JsonContent(
	 *        type="array",
	 *        @OA\Items(ref=@Model(type=FoodSharePointMapMarker::class))
	 *      )
	 * )
	 * @Rest\QueryParam(
	 *  name="d",
	 *  default=MapConstants::DEFAULT_SEARCH_DISTANCE,
	 *  description="Defines the search distance in kilometers.",
	 * 	requirements="\d+",
	 *  nullable=true
	 * )
	 * @Rest\QueryParam(
	 *  name="cp",
	 *  default=MapConstants::CENTER_GERMANY,
	 *  description="Defines the search center point with `[latitude, longitude]`.",
	 * 	requirements="[+-]?((\d+\.?\d*)|(\.\d+)),[+-]?((\d+\.?\d*)|(\.\d+))",
	 *  nullable=true
	 * )
	 * @OA\Tag(name="map")
	 * @Rest\Get("marker/foodsharepoints")
	 */
	public function getFoodSharePointMarkers(
		ValidatorInterface $validator,
		ParamFetcher $paramFetcher,
	): Response {
		// QueryParams
		[$latitude, $longitude] = explode(',', $paramFetcher->get('cp') ?? MapConstants::CENTER_GERMANY);
		$distance = $paramFetcher->get('d') ?? MapConstants::DEFAULT_SEARCH_DISTANCE;

		// Filtering
		$filter = new FilterModel((float)$latitude, (float)$longitude, $distance);
		$errors = $validator->validate($filter);
		$this->throwBadRequestExceptionOnError($errors);

		// Get and send markers
		$markers = $this->mapGateway->getFoodSharePointMarkers($filter);

		return $this->handleView($this->view($markers, HttpCode::OK));
	}

	/**
	 * Returns a list with coordinates of communities, limited by distance of coordinates.
	 *
	 * @OA\Response(
	 * 		response=HttpCode::OK,
	 * 		description=HttpExceptionResponse::SUCCESS,
	 *      @OA\JsonContent(
	 *        type="array",
	 *        @OA\Items(ref=@Model(type=CommunityMapMarker::class))
	 *      )
	 * )
	 * @Rest\QueryParam(
	 *  name="d",
	 *  default=MapConstants::DEFAULT_SEARCH_DISTANCE,
	 *  description="Defines the search distance in kilometers.",
	 * 	requirements="\d+",
	 *  nullable=true
	 * )
	 * @Rest\QueryParam(
	 *  name="cp",
	 *  default=MapConstants::CENTER_GERMANY,
	 *  description="Defines the search center point with `[latitude, longitude]`.",
	 * 	requirements="[+-]?((\d+\.?\d*)|(\.\d+)),[+-]?((\d+\.?\d*)|(\.\d+))",
	 *  nullable=true
	 * )
	 * @OA\Tag(name="map")
	 * @Rest\Get("marker/communities")
	 */
	public function getCommunityMarkers(
		ValidatorInterface $validator,
		ParamFetcher $paramFetcher,
	): Response {
		// QueryParams
		[$latitude, $longitude] = explode(',', $paramFetcher->get('cp') ?? MapConstants::CENTER_GERMANY);
		$distance = $paramFetcher->get('d') ?? MapConstants::DEFAULT_SEARCH_DISTANCE;

		// Filtering
		$filter = new FilterModel((float)$latitude, (float)$longitude, $distance);
		$errors = $validator->validate($filter);
		$this->throwBadRequestExceptionOnError($errors);

		// Get and send markers
		$markers = $this->mapGateway->getCommunityMarkers($filter);

		return $this->handleView($this->view($markers, HttpCode::OK));
	}

	/**
	 * Returns the coordinates of stores or filtered stores based, limited by distance of coordinates.
	 *
	 * @OA\Response(
	 * 		response=HttpCode::OK,
	 * 		description=HttpExceptionResponse::SUCCESS,
	 *      @OA\JsonContent(
	 *        type="array",
	 *        @OA\Items(ref=@Model(type=StoreMapMarker::class))
	 *      )
	 * )
	 * @OA\Response(response=HttpCode::UNAUTHORIZED, description=HttpExceptionResponse::NOT_LOGGED_IN)
	 * @OA\Response(response=HttpCode::FORBIDDEN, description=HttpExceptionResponse::ONLY_FOR_FOODSAVER)
	 * @Rest\QueryParam(
	 *  name="d",
	 *  default=MapConstants::DEFAULT_SEARCH_DISTANCE,
	 *  description="Defines the search distance in kilometers.",
	 * 	requirements="\d+",
	 *  nullable=true
	 * )
	 * @Rest\QueryParam(
	 *  name="cp",
	 *  default=MapConstants::CENTER_GERMANY,
	 *  description="Defines the search center point with `[latitude, longitude]`.",
	 * 	requirements="[+-]?((\d+\.?\d*)|(\.\d+)),[+-]?((\d+\.?\d*)|(\.\d+))",
	 *  nullable=true
	 * )
	 * @Rest\QueryParam(
	 *  name="teamStatus",
	 * 	default="[]",
	 *  description="An array with status numbers. See response schema."
	 * )
	 * @Rest\QueryParam(
	 *  name="cooperationStatus",
	 * 	default="[]",
	 *  description="An array with status numbers. See response schema."
	 * )
	 * @OA\Tag(name="map")
	 * @Rest\Get("marker/stores")
	 */
	public function getStoreMarkers(
		ValidatorInterface $validator,
		ParamFetcher $paramFetcher,
	): Response {
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('', HttpExceptionResponse::NOT_LOGGED_IN);
		}

		if (!$this->session->mayRole(Role::FOODSAVER)) {
			throw new AccessDeniedHttpException(HttpExceptionResponse::ONLY_FOR_FOODSAVER);
		}

		// QueryParams
		[$latitude, $longitude] = explode(',', $paramFetcher->get('cp'));
		$distance = $paramFetcher->get('d') ?? MapConstants::DEFAULT_SEARCH_DISTANCE;
		$teamStatus = json_decode($paramFetcher->get('teamStatus')) ?? [];
		$cooperationStatus = json_decode($paramFetcher->get('cooperationStatus')) ?? [];

		// Filtering
		$filter = new StoreFilterModel((float)$latitude, (float)$longitude, $distance, $teamStatus, $cooperationStatus);
		$errors = $validator->validate($filter);
		$this->throwBadRequestExceptionOnError($errors);

		// Get and send markers
		$markers = $this->mapGateway->getStoreMarkers($filter);

		return $this->handleView($this->view($markers, HttpCode::OK));
	}

	/**
	 * Returns map filters.
	 *
	 * @OA\Response(
	 * 		response=HttpCode::OK,
	 * 		description=HttpExceptionResponse::SUCCESS
	 * )
	 * @OA\Tag(name="map")
	 * @Rest\Get("map/filters")
	 */
	public function getFilters(): Response
	{
		$filters = [];
		if ($this->session->mayRole(Role::FOODSAVER)) {
			array_push($filters, [
				'cooperationStatus' => CooperationStatus::getConstants(),
				'teamStatus' => TeamStatus::getConstants()
			]);
		}

		return $this->handleView($this->view($filters, HttpCode::OK));
	}

	/**
	 * Check if a Constraint violation is found and if it exist it throws an BadRequestExeption.
	 *
	 * @param ConstraintViolationListInterface $errors Validation result
	 *
	 * @throws BadRequestHttpException if violation is detected
	 */
	private function throwBadRequestExceptionOnError(ConstraintViolationListInterface $errors): void
	{
		if ($errors->count() > 0) {
			$firstError = $errors->get(0);
			throw new BadRequestHttpException("{$firstError->getPropertyPath()}: {$firstError->getMessage()}");
		}
	}
}
