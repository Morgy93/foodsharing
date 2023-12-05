<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureToggles\Querys;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;

final class HasPermissionToManageFeatureTogglesQuery
{
    public function execute(Session $session): bool
    {
        return $session->mayRole(Role::ORGA) || $session->isAdminFor(RegionIDs::IT_AND_SOFTWARE_DEVELOPMENT_GROUP);
    }
}
