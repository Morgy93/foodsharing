<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Group\GroupFunctionGateway;

class SearchPermissions
{
    private Session $session;
    private GroupFunctionGateway $groupFunctionGateway;
    private RegionPermissions $regionPermissions;

    public function __construct(
        Session $session,
        GroupFunctionGateway $groupFunctionGateway,
        RegionPermissions $regionPermissions
    ) {
        $this->session = $session;
        $this->groupFunctionGateway = $groupFunctionGateway;
        $this->regionPermissions = $regionPermissions;
    }

    public function maySearchInRegion(int $regionId): bool
    {
        if ($this->session->mayRole(Role::ORGA)) {
            return true;
        }

        return $this->session->mayBezirk($regionId);
    }

    public function maySearchByEmailAddress(): bool
    {
        return $this->session->mayRole(Role::ORGA) || $this->session->isAdminFor(RegionIDs::IT_SUPPORT_GROUP);
    }

    public function maySearchAllWorkingGroups(): bool
    {
        $privilegedFunctionWorkgroups = [WorkgroupFunction::REPORT, WorkgroupFunction::ARBITRATION, WorkgroupFunction::PR];

        return $this->session->mayRole(Role::ORGA) ||
            $this->regionPermissions->isAmbassadorOfAtLeastOneRegion() ||
            $this->groupFunctionGateway->isAdminForSpecialWorkingGroup($privilegedFunctionWorkgroups, $this->session->id());
    }

    public function maySearchGlobal(): bool
    {
        return $this->session->mayRole(Role::ORGA);
    }
}
