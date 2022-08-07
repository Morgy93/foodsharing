<?php

namespace Foodsharing\Modules\Core\DTO;

/**
 * Describes the geogrpahic coordinates for visualization on maps.
 */
class GeoCoordinate
{
	/**
	 * Latitude.
	 */
	public float $lat = 0;

	/**
	 * Longitude.
	 */
	public float $lon = 0;

	/**
	 * Generates from an array like a DB result.
	 */
	public static function createFromArray($query_result, $prefix = ''): GeoCoordinate
	{
		$obj = new GeoCoordinate();
		$obj->lat = $query_result["{$prefix}lat"];
		$obj->lon = $query_result["{$prefix}lon"];

		return $obj;
	}
}
