<?php

namespace Foodsharing\Modules\Store\DTO;

class StoreViewDTO
{
	public int $id;
	public string $name;

    public static function createFromArray($query_result, $prefix="") {
        $obj = new StoreViewDTO();
        $obj->id = $query_result[$prefix+"id"];
        $obj->name = $query_result[$prefix+"name"];
        return $obj;
    }
}
