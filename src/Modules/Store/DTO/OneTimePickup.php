<?php

namespace Foodsharing\Modules\Store\DTO;

use DateTime;
use DateTimeZone;

/**
 * Describes a one time pickup at store.
 */
class OneTimePickup
{
    /**
     * Date and time of pickup.
     */
    public DateTime $date;

    /**
     * Count of slots for pickup.
     */
    public int $slots;

    public static function createFromArray($queryResult)
    {
        $obj = new OneTimePickup();
        $obj->date = DateTime::createFromFormat('Y-m-d H:i:s', $queryResult['time'], new DateTimeZone('Europe/Berlin'));
        $obj->slots = $queryResult['fetchercount'];

        return $obj;
    }
}
