<?php

namespace Foodsharing\Modules\Store\DTO;

class StoreStatusforMemberDTO
{
	public int $id;
	public string $name;
	public bool $isManaging;
	public int $membershipStatus;
	public int $pickupStatus;

	public function __construct()
	{
		$this->id = 0;
		$this->name = '';
		$this->isManaging = 0;
		$this->membershipStatus = 0;
		$this->pickupStatus = 0;
	}
}
