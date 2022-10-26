<?php

namespace Foodsharing\Modules\Map\DTO;

class CommunityMapMarker extends MapMarker
{
	/**
	 * Creates a marker out of an array representation like the Database select.
	 */
	public static function createFromArray($queryResult, $type): CommunityMapMarker
	{
		$marker = new CommunityMapMarker();
		$marker->name = $queryResult['name'];
		$marker->lat = $queryResult['lat'];
		$marker->lon = $queryResult['lon'];
		$marker->type = $type;

		$marker->id = $queryResult['region_id'];
		$marker->description = $queryResult['desc'];

		return $marker;
	}
}
