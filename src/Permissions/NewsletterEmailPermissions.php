<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;

class NewsletterEmailPermissions
{
    private Session $session;

    public function __construct(
        Session $session
    ) {
        $this->session = $session;
    }

    public function mayAdministrateNewsletterEmail(): bool
    {
        return $this->session->mayRole(Role::ORGA) || $this->session->isAdminFor(RegionIDs::NEWSLETTER_WORK_GROUP);
    }
}
