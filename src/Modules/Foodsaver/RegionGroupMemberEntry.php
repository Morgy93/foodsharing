<?php

namespace Foodsharing\Modules\Foodsaver;

use DateTime;

class RegionGroupMemberEntry
{
    public int $id;

    public ?string $name;

    public ?string $avatar;

    public bool $isSleeping;

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
        $this->isSleeping = false;
        $this->role = null;
        $this->lastActivity = null;
        $this->isAdminOrAmbassadorOfRegion = false;
    }

    public static function create(
        int $id,
        ?string $name,
        ?string $avatar,
        bool $isSleeping,
        bool $isAdminOrAmbassadorOfRegion): RegionGroupMemberEntry
    {
        $p = new RegionGroupMemberEntry();
        $p->id = $id;
        $p->name = $name;
        $p->avatar = $avatar;
        $p->isSleeping = $isSleeping;
        $p->isAdminOrAmbassadorOfRegion = $isAdminOrAmbassadorOfRegion;

        return $p;
    }
}
