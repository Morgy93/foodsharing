<?php

namespace Foodsharing\Modules\Core\DTO;

/**
 * Describes the geogrpahic coordinates for visualization on maps.
 *
 */
class Address
{
	/**
	 * Street and street number
	 */
	public string $address = '';
	

	/**
	 * zip code.
	 */
	public string $zip = '';

	/**
	 * Name of city
	 */
	public string $city = '';

	/**
	 * Generates from an array like a DB result
	 */
	public static function createFromArray($query_result, $prefix = ''): Address
	{
		$obj = new Address();
		$obj->address = $query_result["{$prefix}anschrift"];
		$obj->zip = $query_result["{$prefix}plz"];
		$obj->city = $query_result["{$prefix}stadt"];

		return $obj;
	}
}