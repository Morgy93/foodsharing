<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Unit\DTO\UserUnit;
use Foodsharing\Modules\Unit\UnitGateway;

class RegionTransactions
{
	public const NEW_FOODSAVER_VERIFIED = 'new_foodsaver_verified';
	public const NEW_FOODSAVER_NEEDS_VERIFICATION = 'new_foodsaver_needs_verification';
	public const NEW_FOODSAVER_NEEDS_INTRODUCTION = 'new_foodsaver_needs_introduction';

	public function __construct(private FoodsaverGateway $foodsaverGateway, private UnitGateway $unitGateway)
	{
	}

	public function getJoinMessage(array $userData): string
	{
		if (!isset($userData['id'])) {
			throw new \InvalidArgumentException('Invalid user data. Id not set.');
		}

		if (isset($userData['verified']) && $userData['verified']) {
			return self::NEW_FOODSAVER_VERIFIED;
		}

		$verifiedBefore = $this->foodsaverGateway->foodsaverWasVerifiedBefore($userData['id']);

		return $verifiedBefore ? self::NEW_FOODSAVER_NEEDS_VERIFICATION : self::NEW_FOODSAVER_NEEDS_INTRODUCTION;
	}

	/**
	 * Returns a list of region which the user is directly related (not the indirect parents).
	 *
	 * @param int $fsId foodsaver identifier of user
	 *
	 * @return UserUnit[] List of regions where the use is part
	 */
	public function getUserRegions(int $fsId): array
	{
		return $this->unitGateway->listAllDirectReleatedUnitsAndResponsibilitiesOfFoodsaver($fsId, UnitType::getRegionTypes());
	}
}
