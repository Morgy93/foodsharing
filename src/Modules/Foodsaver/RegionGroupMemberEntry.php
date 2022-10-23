<?php

namespace Foodsharing\Modules\Foodsaver;

class RegionGroupMemberEntry
{
	public int $id;

	public ?string $name;

	public ?string $avatar;

	public int $sleepStatus;

	public int $role;

	public bool $isAdminOrAmbassadorOfRegion;

	public function __construct()
	{
		$this->id = 0;
		$this->name = null;
		$this->avatar = null;
		$this->sleepStatus = 0;
		$this->role = 0;
		$this->isAdminOrAmbassadorOfRegion = false;
	}

	public static function create(
		int $id,
		?string $name,
		?string $avatar,
		int $sleepStatus,
		int $role,
		bool $isAdminOrAmbassadorOfRegion): RegionGroupMemberEntry
	{
		$p = new RegionGroupMemberEntry();
		$p->id = $id;
		$p->name = $name;
		$p->avatar = $avatar;
		$p->sleepStatus = $sleepStatus;
		$p->role = $role;
		$p->isAdminOrAmbassadorOfRegion = $isAdminOrAmbassadorOfRegion;

		return $p;
	}
}
