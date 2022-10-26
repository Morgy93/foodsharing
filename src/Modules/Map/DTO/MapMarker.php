<?php

namespace Foodsharing\Modules\Map\DTO;

use Foodsharing\Modules\Core\DBConstants\Map\MapMarkerType;
use OpenApi\Annotations as OA;

class MapMarker
{
	/**
	 * Identifer of a marker.
	 *
	 * @OA\Property(example=12345)
	 */
	public int $id = 0;

	/**
	 * Label for a marker.
	 *
	 * @OA\Property(example="Betrieb ABC LEFT")
	 */
	public string $name = '';

	/**
	 * Description for a marker.
	 *
	 * @OA\Property(example="Der Betrieb hat häufig ...")
	 */
	public string $description = '';

	/**
	 * Latitude for a marker.
	 *
	 * @OA\Property(example=50.89)
	 */
	public float $latitude = 0.0;

	/**
	 * Longitude for a marker.
	 *
	 * @OA\Property(example=10.13)
	 */
	public float $longitude = 0.0;

	/**
	 * Kind of marker.
	 *
	 * - 0: UNDEFINED
	 * - 1: STORE
	 * - 2: COMMUNITY
	 * - 3: FOODBASKET
	 * - 4: FOODSHARINGPOINT
	 *
	 * @OA\Property(example=1)
	 *
	 * @see MapMarkerType
	 */
	public int $type = MapMarkerType::UNDEFINED;

	/**
	 * Creates a marker out of an array representation like the Database select.
	 */
	public static function createFromArray($queryResult, $type): MapMarker
	{
		$marker = new MapMarker();
		$marker->id = $queryResult['id'];
		$marker->name = $queryResult['name'];
		$marker->description = $queryResult['description'];

		$marker->latitude = $queryResult['lat'];
		$marker->longitude = $queryResult['lon'];

		$marker->type = $type;

		return $marker;
	}
}
