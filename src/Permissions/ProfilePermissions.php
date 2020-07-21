<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Region\RegionGateway;

class ProfilePermissions
{
	private $session;
	private $regionGateway;

	public function __construct(Session $session, RegionGateway $regionGateway)
	{
		$this->session = $session;
		$this->regionGateway = $regionGateway;
	}

	public function mayAdministrateUserProfile($userId, $regionId = 0): bool
	{
		if (!$this->session->isAmbassador()) {
			return false;
		}

		if ($regionId !== 0 && $this->session->isAdminFor($regionId)) {
			return true;
		}

		if ($this->session->may('orga')) {
			return true;
		}

		$regionIds = $this->regionGateway->getFsRegionIds($userId);

		return $this->session->isAmbassadorForRegion($regionIds, false, true);
	}

	public function maySeeHistory(int $fsId): bool
	{
		return $this->mayAdministrateUserProfile($fsId);
	}

	public function maySeeEmailAddress(int $fsId): bool
	{
		return $this->session->id() == $fsId || $this->session->isOrgaTeam();
	}

	public function mayDeleteUser($userId): bool
	{
		return $this->session->id() == $userId || $this->session->may('orga');
	}

	public function maySeeBounceWarning(int $userId): bool
	{
		return $this->session->id() == $userId || $this->session->may('orga');
	}
}
