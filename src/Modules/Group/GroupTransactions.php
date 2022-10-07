<?php

namespace Foodsharing\Modules\Group;

use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Unit\UnitGateway;

class GroupTransactions
{
	private GroupGateway $groupGateway;
	private UnitGateway $unitGateway;

	public function __construct(
		GroupGateway $groupGateway,
		UnitGateway $unitGateway
	) {
		$this->groupGateway = $groupGateway;
		$this->unitGateway = $unitGateway;
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
		return $this->unitGateway->listAllDirectReleatedUnitsAndResponsibilitiesOfFoodsaver($fs_id, UnitType::getGroupTypes());
	}
}
