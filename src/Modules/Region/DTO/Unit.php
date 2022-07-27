<?php

namespace Foodsharing\Modules\Region\DTO;

class Unit
{
	public int $id;
	public string $name;
	public ?int $type;

	public function __construct()
	{
		$this->id = 0;
		$this->name = '';
		$this->type = 0;
	}

	public static function createFromArray($query_result, $prefix = ''): Unit
	{
		$obj = new Unit();
		$obj->id = $query_result["{$prefix}id"];
		$obj->name = $query_result["{$prefix}name"];
		$obj->type = $query_result["{$prefix}type"];

		return $obj;
	}
}
