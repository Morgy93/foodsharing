<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;

class RegionTransactions
{
	public const NEW_FOODSAVER_VERIFIED = 'new_foodsaver_verified';
	public const NEW_FOODSAVER_NEEDS_VERIFICATION = 'new_foodsaver_needs_verification';
	public const NEW_FOODSAVER_NEEDS_INTRODUCTION = 'new_foodsaver_needs_introduction';

	private FoodsaverGateway $foodsaverGateway;
	private RegionGateway $regionGateway;

	public function __construct(FoodsaverGateway $foodsaverGateway, RegionGateway $regionGateway)
	{
		$this->foodsaverGateway = $foodsaverGateway;
		$this->regionGateway = $regionGateway;
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

	public function getUserRegions(int $fs_id): array
	{
		return $this->regionGateway->listAllUnitsAndResponsibilitiesOfFoodsaver($fs_id, Type::getRegionTypes());
	}
}
