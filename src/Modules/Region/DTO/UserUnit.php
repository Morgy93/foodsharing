<?php

namespace Foodsharing\Modules\Region\DTO;

class UserUnit
{
	public Unit $region;
	public bool $isResponsible;

	public function __construct()
	{
		$this->region = new Unit();
		$this->isResponsible = false;
	}

	public static function createFromArray($query_result, $prefix = ''): UserUnit
	{
		$obj = new UserUnit();
		$obj->region = Unit::createFromArray($query_result, $prefix);
		$obj->isResponsible = $query_result["{$prefix}isResponsible"];

		return $obj;
	}
}
