<?php

namespace Foodsharing\Modules\Map\DTO;

use Carbon\Carbon;
use Foodsharing\Modules\Core\DBConstants\Map\MapMarkerType;
use OpenApi\Annotations as OA;

class FoodbasketMapMarker extends MapMarker
{
	/**
	 * Date until when the food basket is available.
	 *
	 * @OA\Property(example="2022-11-04T07:32:37+01:00")
	 */
	public ?string $until;

	/**
	 * Picture path for a foodbasket.
	 *
	 * @OA\Property(example="path/to/image")
	 */
	public ?string $picture;

	/**
	 * Creates a marker out of an array representation like the Database select.
	 */
	public static function createFromArray($queryResult, $type = MapMarkerType::FOODBASKET): FoodbasketMapMarker
	{
		$marker = new FoodbasketMapMarker($queryResult, $type);
		$marker->id = $queryResult['id'];
		$marker->description = $queryResult['description'];

		$marker->latitude = $queryResult['lat'];
		$marker->longitude = $queryResult['lon'];

		$marker->type = $type;

		$marker->until = Carbon::parse($queryResult['until'])->toIso8601String();
		$marker->picture = $queryResult['picture'];

		return $marker;
	}
}
