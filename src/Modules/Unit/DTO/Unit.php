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
    public int $id = 0;

    /**
     * Label for a unit.
     */
    public string $name = '';

    /**
     * @see UnitType
     */
    public ?int $type = UnitType::UNDEFINED;

    /**
     * Creates a unit out of an array representation like the Database select.
     */
    public static function createFromArray($queryResult, $prefix = ''): Unit
    {
        $unit = new Unit();
        $unit->id = $queryResult["{$prefix}id"];
        $unit->name = $queryResult["{$prefix}name"];
        $unit->type = $queryResult["{$prefix}type"];

        return $unit;
    }
}
