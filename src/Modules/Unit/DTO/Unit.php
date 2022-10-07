<?php

namespace Foodsharing\Modules\Unit\DTO;

use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;

/**
 * A unit represents the group element like a Region (self::CITY, self::REGION, self::DISTRICT) or group.
 */
class Unit
{
	/**
	 * Identifer of the unit.
	 */
	public int $id;

	/**
	 * Label for a unit.
	 */
	public string $name;

	/**
	 * @see UnitType
	 */
	public ?int $type;

	public function __construct()
	{
		$this->id = 0;
		$this->name = '';
		$this->type = UnitType::UNDEFINED;
	}

	/**
	 * Creates a unit out of an array representation like the Database select.
	 */
	public static function createFromArray($query_result, $prefix = ''): Unit
	{
		$obj = new Unit();
		$obj->id = $query_result["{$prefix}id"];
		$obj->name = $query_result["{$prefix}name"];
		$obj->type = $query_result["{$prefix}type"];

		return $obj;
	}
}
