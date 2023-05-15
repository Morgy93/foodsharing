<?php

namespace Foodsharing\Modules\Store\DTO;

use DateTime;
use DateTimeZone;

/**
 * Describes the registration to a pickup by a foodsaver.
 */
class PickupSignUp
{
    /**
     * Date and time of pickup.
     */
    public DateTime $date;

    /**
     * Identifer of foodsaver which confirmed this pickup.
     */
    public int $foodsaverId;

    /**
     * State of the pickup sign up.
     *
     * A foodsaver can confirm the pickup, if he leaves the pickup then state exists but is false.
     */
    public bool $isConfirmed;

    public static function createFromArray($query_result)
    {
        $obj = new PickupSignUp();
        $obj->date = DateTime::createFromFormat('Y-m-d H:i:s', $query_result['date'], new DateTimeZone('Europe/Berlin'));
        $obj->foodsaverId = $query_result['foodsaver_id'];
        $obj->isConfirmed = boolval($query_result['confirmed']);

        return $obj;
    }
}
