<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\Modules\Region\RegionGateway;

class CommonPermissions
{
    private Session $session;
    private RegionGateway $regionGateway;
    private GroupFunctionGateway $groupFunctionGateway;
    public function __construct(Session $session, RegionGateway $regionGateway, GroupFunctionGateway $groupFunctionGateway)
    {
        $this->session = $session;
        $this->regionGateway = $regionGateway;
        $this->groupFunctionGateway = $groupFunctionGateway;
    }

    public function mayAdministrateRegion(int $userId, ?int $regionId = null): bool
    {
        if ($this->session->mayRole(Role::ORGA)) {
            return true;
        }

        if ($regionId !== null && $this->groupFunctionGateway->existRegionFunctionGroup($regionId, WorkgroupFunction::FSMANAGEMENT)) {
            if ($this->groupFunctionGateway->isRegionFunctionGroupAdmin($regionId, WorkgroupFunction::FSMANAGEMENT, $this->session->id())) {
                return true;
            }

            return false;
        }


        if (!$this->session->isAmbassador()) {
            return false;
        }

        if ($regionId !== null && $this->session->isAdminFor($regionId)) {
            return true;
        }

        $regionIds = $this->regionGateway->getFsRegionIds($userId);

        return $this->session->isAmbassadorForRegion($regionIds, false, true);
    }
}
