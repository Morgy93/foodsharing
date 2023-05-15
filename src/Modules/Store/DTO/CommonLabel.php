<?php

namespace Foodsharing\Modules\Store\DTO;

/**
 * Describes a common label like for a item in a select input field.
 */
class CommonLabel
{
    public function __construct(public int $id = 0, public string $name = '')
    {
    }

    public static function createFromArray(array $data): CommonLabel
    {
        $obj = new CommonLabel();
        $obj->id = $data['id'];
        $obj->name = $data['name'];

        return $obj;
    }
}
