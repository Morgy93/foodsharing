<?php

namespace Foodsharing\Modules\Store\DTO;

class MinimalStoreIdentifier
{
	public int $id;
	public string $name;

	public function __construct()
	{
		$this->id = 0;
		$this->name = '';
	}

	public static function createFromArray($query_result, $prefix = '')
	{
		$obj = new MinimalStoreIdentifier();
		$obj->id = $query_result["{$prefix}id"];
		$obj->name = $query_result["{$prefix}name"];

		return $obj;
	}
}
