<?php

namespace Foodsharing\Modules\Map\DTO;

use Foodsharing\Modules\Core\DBConstants\Map\MapMarkerType;

class FoodSharePointMapMarker extends MapMarker
{
	public function __construct()
	{
		parent::__construct();
	}

	public static function createFromArray(mixed $value, ?int $type = null): FoodSharePointMapMarker
	{
		$marker = new FoodSharePointMapMarker();
		$marker->id = $value['id'];
		$marker->name = $value['name'];
		$marker->description = $value['desc'];

		$marker->latitude = $value['lat'];
		$marker->longitude = $value['lon'];

		$marker->setType(MapMarkerType::FOODSHAREPOINT);

		return $marker;
	}
}
