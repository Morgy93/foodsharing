<?php

namespace Foodsharing\Modules\Store\DTO;

class StoreStatusforMemberDTO
{
	public StoreViewDTO $store;
	public bool $isManaging;
	public int $membershipStatus;
	public int $pickupStatus;

	public function __construct()
	{
		$this->store = new StoreViewDTO();
		$this->isManaging = false;
		$this->membershipStatus = 0;
		$this->pickupStatus = 0;
	}
}
