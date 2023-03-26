<?php

namespace Foodsharing\Modules\Core\DTO;

/**
 * Describes a location by its coordinates, so that a representation on a map is possible.
 */
class PatchGeoLocation
{
    /**
     * Latitude of the location.
     */
    public ?float $lat = null;

    /**
     * Longitude of the location.
     */
    public ?float $lon = null;

    public static function apply(PatchGeoLocation &$locationChange, GeoLocation &$location): bool
    {
        $patchNeeded = false;
        if (!empty($locationChange->lat)) {
            $patchNeeded = true;
            $location->lat = $locationChange->lat;
        }

        if (!empty($locationChange->lon)) {
            $patchNeeded = true;
            $location->lon = $locationChange->lon;
        }

        return $patchNeeded;
    }
}
