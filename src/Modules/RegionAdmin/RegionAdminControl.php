<?php

namespace Foodsharing\Modules\RegionAdmin;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\Utility\IdentificationHelper;

class RegionAdminControl extends Control
{
    private RegionPermissions $regionPermissions;

    public function __construct(
        RegionAdminView $view,
        RegionPermissions $regionPermissions
    ) {
        $this->view = $view;
        $this->regionPermissions = $regionPermissions;

        parent::__construct();

        if (!$this->regionPermissions->mayAdministrateRegions()) {
            $this->routeHelper->goAndExit('/');
        }
    }

    public function index()
    {
        $this->view->panel();
    }
}
