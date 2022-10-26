<?php

namespace Foodsharing\Modules\Map\DTO;

use Carbon\Carbon;
use Foodsharing\Modules\Core\DBConstants\Map\MapMarkerType;
use OpenApi\Annotations as OA;

class FoodbasketMapMarker extends MapMarker
{
	/**
	 * Date until when the food basket is available.
	 */
	#[
		OA\Property(
			example: 'ABC'
		),
	]
	public string $until;

	/**
	 * Picture path for a foodbasket.
	 */
	#[
		OA\Property(
			example: 'ABC'
		),
	]
	public ?string $picture;

	/**
	 * Creates a marker out of an array representation like the Database select.
	 */
	public static function createFromArray($queryResult, $type = MapMarkerType::FOODBASKET): FoodbasketMapMarker
	{
		$marker = new FoodbasketMapMarker($queryResult, $type);
		$marker->id = $queryResult['id'];
		$marker->description = $queryResult['description'];

		$marker->lat = $queryResult['lat'];
		$marker->lon = $queryResult['lon'];

		$marker->type = $type;

		$marker->until = Carbon::parse($queryResult['until'])->toIso8601String();
		$marker->picture = $queryResult['picture'];

		return $marker;
	}
}
