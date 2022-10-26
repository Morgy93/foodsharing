<?php

namespace Foodsharing\Modules\Map\DTO;

use Foodsharing\Modules\Core\DBConstants\Map\MapMarkerType;

class MapMarker
{
	/**
	 * Identifer of the MapMarker.
	 */
	public int $id = 0;

	/**
	 * Label for a MapMarker.
	 */
	public string $name = '';

	/**
	 * Location for a MapMarker.
	 */
	public float $lat = 0.0;
	public float $lon = 0.0;

	/**
	 * @see MapMarkerType
	 */
	public ?int $type = MapMarkerType::UNDEFINED;

	/**
	 * Creates a unit out of an array representation like the Database select.
	 */
	public static function createFromArray($queryResult, $type): MapMarker
	{
		$unit = new MapMarker();
		$unit->id = $queryResult['id'] || $queryResult['bezirk_id'];
		$unit->name = $queryResult['name'];
		$unit->lat = $queryResult['lat'];
		$unit->lon = $queryResult['lon'];
		$unit->type = $type;

		return $unit;
	}
}
