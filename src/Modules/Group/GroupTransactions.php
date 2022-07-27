<?php

namespace Foodsharing\Modules\Group;

use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Region\RegionGateway;

class GroupTransactions
{
	private GroupGateway $groupGateway;
	private RegionGateway $regionGateway;

	public function __construct(
		GroupGateway $groupGateway,
		RegionGateway $regionGateway
	) {
		$this->groupGateway = $groupGateway;
		$this->regionGateway = $regionGateway;
	}

	/**
	 * Returns whether the group still contains any sub-regions, stores, or foodsharepoints.
	 */
	public function hasSubElements(int $groupId): bool
	{
		$hasRegions = $this->groupGateway->hasSubregions($groupId);
		if ($hasRegions) {
			return true;
		}

		$hasFSPs = $this->groupGateway->hasFoodSharePoints($groupId);
		if ($hasFSPs) {
			return true;
		}

		return $this->groupGateway->hasStores($groupId);
	}

	public function getUserGroups(int $fs_id): array
	{
		return $this->regionGateway->listAllUnitsAndResponsibilitiesOfFoodsaver($fs_id, Type::getGroupTypes());
	}
}
