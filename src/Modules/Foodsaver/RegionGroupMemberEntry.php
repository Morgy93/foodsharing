<?php

namespace Foodsharing\Modules\Foodsaver;

use DateTime;

class RegionGroupMemberEntry
{
    public int $id;

    public ?string $name;

    public ?string $avatar;

    public int $sleepStatus;

    public ?int $role = null;

    public ?DateTime $lastActivity;

    public bool $isAdminOrAmbassadorOfRegion;

    public ?bool $isVerified = null;

    public ?bool $isHomeRegion = null;

    public function __construct()
    {
        $this->id = 0;
        $this->name = null;
        $this->avatar = null;
        $this->sleepStatus = 0;
        $this->role = null;
        $this->lastActivity = null;
        $this->isAdminOrAmbassadorOfRegion = false;
    }

    public static function create(
        int $id,
        ?string $name,
        ?string $avatar,
        int $sleepStatus,
        bool $isAdminOrAmbassadorOfRegion): RegionGroupMemberEntry
    {
        $p = new RegionGroupMemberEntry();
        $p->id = $id;
        $p->name = $name;
        $p->avatar = $avatar;
        $p->sleepStatus = $sleepStatus;
        $p->isAdminOrAmbassadorOfRegion = $isAdminOrAmbassadorOfRegion;

        return $p;
    }
}
