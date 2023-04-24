<?php

namespace Foodsharing\Modules\RegionAdmin\DTO;

use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Map\DTO\MapMarker;

class RegionDetails
{
    public int $id;

    public string $name;

    /**
     * @see UnitType
     */
    public int $type;

    /**
     * @see WorkgroupFunction
     */
    public ?int $workingGroupFunction;

    public string $mailbox;

    public string $mailboxName;

    /**
     * @var MapMarker[]
     */
    public array $storeMarkers;

    public static function create(
        int $id,
        string $name,
        int $type,
        ?int $workingGroupFunction,
        string $mailbox,
        string $mailboxName,
        array $storeMarkers
    ) {
        $r = new RegionDetails();
        $r->id = $id;
        $r->name = $name;
        $r->type = $type;
        $r->workingGroupFunction = $workingGroupFunction;
        $r->storeMarkers = $storeMarkers;

        return $r;
    }
}
