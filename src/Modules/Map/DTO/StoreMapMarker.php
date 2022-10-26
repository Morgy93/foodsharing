<?php

namespace Foodsharing\Modules\Map\DTO;

use Foodsharing\Modules\Core\DBConstants\Map\MapMarkerType;

class StoreMapMarker extends MapMarker
{
	/**
	 * Creates a marker out of an array representation like the Database select.
	 */
	public static function createFromArray($queryResult, $type = MapMarkerType::STORE): MapMarker
	{
		$marker = new MapMarker($queryResult, $type);
		$marker->id = $queryResult['id'];
		$marker->name = $queryResult['name'];
		$marker->description = $queryResult['public_info'];

		$marker->lat = $queryResult['lat'];
		$marker->lon = $queryResult['lon'];

		$marker->type = $type;

		return $marker;
	}
}
