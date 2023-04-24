<?php

namespace Foodsharing\Modules\RegionAdmin\DTO;

use Foodsharing\Modules\Map\DTO\MapMarker;

class RegionDetails
{
    public int $id;

    public string $name;

    /**
     * @var MapMarker[]
     */
    public array $storeMarkers;

    public static function create(
        int $id,
        string $name,
        array $storeMarkers
    ) {
        $r = new RegionDetails();
        $r->id = $id;
        $r->name = $name;
        $r->storeMarkers = $storeMarkers;

        return $r;
    }
}
