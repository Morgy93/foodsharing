<?php

namespace Foodsharing\Modules\Map\DTO;

use Carbon\Carbon;
use Foodsharing\Modules\Core\DBConstants\Map\MapMarkerType;
use OpenApi\Annotations as OA;

class FoodbasketMapMarker extends MapMarker
{
	public function __construct(
		/**
		 * Date until when the food basket is available.
		 *
		 * @OA\Property(example="2022-11-04T07:32:37+01:00")
		 */
		public ?string $until = null,

		/**
		 * Picture path for a foodbasket.
		 *
		 * @OA\Property(example="path/to/image")
		 */
		public ?string $picture = null,
	) {
		parent::__construct();
	}

	public static function createFromArray(mixed $value, ?int $type = null): FoodbasketMapMarker
	{
		$marker = new FoodbasketMapMarker();
		$marker->id = $value['id'];
		$marker->description = $value['description'];

		$marker->latitude = $value['lat'];
		$marker->longitude = $value['lon'];

		$marker->setType(MapMarkerType::FOODBASKET);

		$marker->until = Carbon::parse($value['until'])->toIso8601String();
		$marker->picture = $value['picture'];

		return $marker;
	}
}
