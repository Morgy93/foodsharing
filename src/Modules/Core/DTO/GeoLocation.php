<?php

namespace Foodsharing\Modules\Core\DTO;

/**
 * Describes a location by its coordinates, so that a representation on a map is possible.
 */
class GeoLocation
{
    /**
     * Latitude of the location.
     */
    public float $lat = 0;

    /**
     * Longitude of the location.
     */
    public float $lon = 0;

    public static function createFromArray($queryResult)
    {
        $obj = new GeoLocation();
        if (!is_numeric($queryResult['lat']) || !is_numeric($queryResult['lon'])) {
            throw new \InvalidArgumentException('Longitude/Latitude is invalid.');
        }

        $obj->lat = floatval($queryResult['lat']);
        $obj->lon = floatval($queryResult['lon']);

        return $obj;
    }
}
