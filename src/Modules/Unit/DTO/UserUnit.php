<?php

namespace Foodsharing\Modules\Unit\DTO;

/**
 * Provides information about the user relation to an unit.
 */
class UserUnit
{
	/**
	 * Identifier object of the unit.
	 */
	public Unit $unit;

	/**
	 * User has a responsiblity for the unit.
	 */
	public bool $isResponsible;

	public function __construct()
	{
		$this->unit = new Unit();
		$this->isResponsible = false;
	}

	/**
	 * Creates a user unit out of an array representation like the database select.
	 */
	public static function createFromArray($queryResult, $prefix = ''): UserUnit
	{
		$unitObj = new UserUnit();
		$unitObj->unit = Unit::createFromArray($queryResult, $prefix);
		$unitObj->isResponsible = $queryResult["{$prefix}isResponsible"];

		return $unitObj;
	}
}
