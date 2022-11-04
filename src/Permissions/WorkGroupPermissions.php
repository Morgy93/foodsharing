<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\ApplyType;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Group\GroupFunctionGateway;

final class WorkGroupPermissions
{
	private Session $session;
	private GroupFunctionGateway $groupFunctionGateway;

	public function __construct(
		Session $session,
		GroupFunctionGateway $groupFunctionGateway
	) {
		$this->session = $session;
		$this->groupFunctionGateway = $groupFunctionGateway;
	}

	public function mayEdit(array $group): bool
	{
		// Global orga team
		if ($this->session->may('orga')) {
			return true;
		}

		$groupFunction = $this->groupFunctionGateway->getRegionGroupFunctionId($group['id'], $group['parent_id']);
		if (!is_null($groupFunction) && WorkgroupFunction::isRestrictedWorkgroupFunction($groupFunction)) {
			return false;
		}

		// Workgroup admins
		$regionId = $group['id'];
		if ($this->session->isAdminFor($regionId)) {
			return true;
		}

		// Ambassadors of _direct parents_ (not all hierarchical parents)
		if (array_key_exists('parent_id', $group)) {
			$parentId = $group['parent_id'];
			if ($this->session->isAdminFor($parentId)) {
				return true;
			}
		}

		return false;
	}

	public function mayAccess(array $group): bool
	{
		// Workgroup members
		$regionId = $group['id'];
		if ($this->session->mayBezirk($regionId)) {
			return true;
		}
		$groupFunction = $this->groupFunctionGateway->getRegionGroupFunctionId($group['id'], $group['parent_id']);
		if (!is_null($groupFunction) && WorkgroupFunction::isRestrictedWorkgroupFunction($groupFunction)) {
			return false;
		}

		// Ambassadors of _direct parents_ (not all hierarchical parents)
		$parentId = $group['parent_id'];
		if ($this->session->isAdminFor($parentId)) {
			return true;
		}

		return false;
	}

	public function mayApply(array $group, array $applications, array $stats): bool
	{
		$regionId = $group['id'];
		if (isset($this->session->getRegions()[$regionId])) {
			return false; // may not apply if already a member
		}
		if (in_array($regionId, $applications)) {
			return false; // may not apply if already applied
		}
		if ($group['apply_type'] == ApplyType::EVERYBODY) {
			return true;
		}
		if ($group['apply_type'] == ApplyType::REQUIRES_PROPERTIES) {
			return $this->fulfillApplicationRequirements($group, $stats);
		}

		return false;
	}

	public function mayJoin(array $group): bool
	{
		$regionId = $group['id'];
		if (isset($this->session->getRegions()[$regionId])) {
			return false; // may not join if already a member
		}

		return $group['apply_type'] == ApplyType::OPEN;
	}

	public function fulfillApplicationRequirements(array $group, array $stats): bool
	{
		if ($group['apply_type'] !== ApplyType::REQUIRES_PROPERTIES) {
			return true;
		}

		return
			$stats['bananacount'] >= $group['banana_count']
			&& $stats['fetchcount'] >= $group['fetch_count']
			&& $stats['weeks'] >= $group['week_num'];
	}
}
