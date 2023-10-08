<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\Modules\Region\RegionGateway;

final class RegionPermissions
{
    private RegionGateway $regionGateway;
    private Session $session;
    private GroupFunctionGateway $groupFunctionGateway;

    public function __construct(RegionGateway $regionGateway, Session $session, GroupFunctionGateway $groupFunctionGateway)
    {
        $this->regionGateway = $regionGateway;
        $this->session = $session;
        $this->groupFunctionGateway = $groupFunctionGateway;
    }

    public function mayJoinRegion(int $regionId): bool
    {
        $type = $this->regionGateway->getType($regionId);

        return $this->session->mayRole(Role::FOODSAVER) && UnitType::isAccessibleRegion($type);
    }

    public function mayAdministrateRegions(): bool
    {
        return $this->session->mayRole(Role::ORGA);
    }

    public function maySetRegionAdmin(): bool
    {
        return $this->session->mayRole(Role::ORGA);
    }

    public function mayRemoveRegionAdmin(): bool
    {
        return $this->session->mayRole(Role::ORGA);
    }

    public function mayAdministrateWorkgroupFunction(int $wgfunction): bool
    {
        if (WorkgroupFunction::isRestrictedWorkgroupFunction($wgfunction)) {
            return $this->session->mayRole(Role::ORGA) && $this->session->isAdminFor(RegionIDs::CREATING_WORK_GROUPS_WORK_GROUP);
        }

        return true;
    }

    public function mayAccessStatisticCountry(): bool
    {
        // Temporarily disabled because it is too inefficient for Europe and Germany
        /* if ($this->session->mayRole(Role::ORGA)) {
            return true;
        } */

        return false;
    }

    public function mayHandleFoodsaverRegionMenu(int $regionId): bool
    {
        if ($this->session->mayRole(Role::ORGA)) {
            return true;
        }

        return $this->session->isAmbassadorForRegion([$regionId], false, false);
    }

    public function maySetRegionOptionsReportButtons(int $regionId): bool
    {
        if ($this->session->mayRole(Role::ORGA)) {
            return true;
        }

        return $this->session->isAmbassadorForRegion([$regionId], false, false);
    }

    public function maySetRegionOptionsRegionPickupRule(int $regionId): bool
    {
        if ($this->session->mayRole(Role::ORGA)) {
            return true;
        }

        if ($this->groupFunctionGateway->existRegionFunctionGroup($regionId, WorkgroupFunction::STORES_COORDINATION)) {
            if ($this->groupFunctionGateway->isRegionFunctionGroupAdmin($regionId, WorkgroupFunction::STORES_COORDINATION, $this->session->id())) {
                return true;
            }

            return false;
        }

        return $this->session->isAmbassadorForRegion([$regionId], false, false);
    }

    public function maySetRegionPin(int $regionId): bool
    {
        if ($this->session->mayRole(Role::ORGA)) {
            return true;
        }

        if ($this->groupFunctionGateway->existRegionFunctionGroup($regionId, WorkgroupFunction::PR)) {
            if ($this->groupFunctionGateway->isRegionFunctionGroupAdmin($regionId, WorkgroupFunction::PR, $this->session->id())) {
                return true;
            }

            return false;
        }

        return $this->session->isAmbassadorForRegion([$regionId], false, false);
    }

    public function hasConference(int $regionType): bool
    {
        return in_array($regionType, [UnitType::COUNTRY, UnitType::FEDERAL_STATE, UnitType::CITY, UnitType::WORKING_GROUP, UnitType::PART_OF_TOWN, UnitType::DISTRICT, UnitType::REGION, UnitType::BIG_CITY]);
    }

    public function mayDeleteFoodsaverFromRegion(int $regionId): bool
    {
        return $this->mayHandleFoodsaverRegionMenu($regionId);
    }

    public function maySeeRegionMembers(int $regionId): bool
    {
        if ($this->session->mayRole(Role::ORGA)) {
            return true;
        }

        return in_array($regionId, $this->session->listRegionIDs());
    }

    public function mayListFoodSharePointsInRegion(int $regionId)
    {
        if ($this->session->mayRole(Role::ORGA)) {
            return true;
        }

        return in_array($regionId, $this->session->listRegionIDs());
    }

    // excluding working groups!
    public function isAmbassadorForAnyRegion(): bool
    {
        return $this->regionGateway->isAmbassadorForAnyRegion($this->session->id());
    }
}
