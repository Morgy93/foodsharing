<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;

class SearchPermissions
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function maySearchAllRegions(): bool
    {
        return $this->session->mayRole(Role::ORGA);
    }

    public function maySearchInRegion(int $regionId): bool
    {
        if ($this->session->mayRole(Role::ORGA)) {
            return true;
        }

        return in_array($regionId, $this->session->listRegionIDs());
    }

    public function maySearchByEmailAddress(): bool
    {
        return $this->session->mayRole(Role::ORGA) || $this->session->isAdminFor(RegionIDs::IT_SUPPORT_GROUP);
    }
}
