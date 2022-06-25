<?php

namespace Foodsharing\Modules\Store\DTO;

class StoreStatusforMemberDTO
{
	public int $id;
	public string $name;
	public bool $isManaging;
	public string $membershipStatus;
	public string $pickupStatus;

	public function __construct()
	{
		$this->id = 0;
		$this->name = '';
		$this->isManaging = 0;
		$this->membershipStatus = '';
		$this->pickupStatus = '';
	}
}
