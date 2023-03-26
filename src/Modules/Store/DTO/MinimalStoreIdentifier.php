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

    public static function createFromArray($queryResult, $prefix = '')
    {
        $obj = new MinimalStoreIdentifier();
        $obj->id = $queryResult["{$prefix}id"];
        $obj->name = $queryResult["{$prefix}name"];

        return $obj;
    }
}
