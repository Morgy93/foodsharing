<?php

namespace Foodsharing\Modules\Map\DTO;

use Foodsharing\Modules\Core\DBConstants\Map\MapMarkerType;

class StoreMapMarker extends MapMarker
{
	/**
	 * Creates a marker out of an array representation like the Database select.
	 */
	public static function createFromArray($queryResult, $type = MapMarkerType::STORE): StoreMapMarker
	{
		$marker = new StoreMapMarker();
		$marker->name = $queryResult['name'];
		$marker->lat = $queryResult['lat'];
		$marker->lon = $queryResult['lon'];
		$marker->type = $type;

		$marker->description = $queryResult['public_info'];
		$marker->id = $queryResult['id'];

		return $marker;
	}
}
