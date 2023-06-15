<?php

namespace Foodsharing\Modules\RegionAdmin\DTO;

use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;

class RegionDetails
{
    public int $id;

    public string $name;

    public ?int $parentId;

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

    public static function create(
        int $id,
        string $name,
        ?int $parentId,
        int $type,
        ?int $workingGroupFunction,
        string $mailbox,
        string $mailboxName
    ): RegionDetails {
        $r = new RegionDetails();
        $r->id = $id;
        $r->name = $name;
        $r->parentId = $parentId;
        $r->type = $type;
        $r->workingGroupFunction = $workingGroupFunction;
        $r->mailbox = $mailbox;
        $r->mailboxName = $mailboxName;

        return $r;
    }
}
