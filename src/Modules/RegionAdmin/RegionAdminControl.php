<?php

namespace Foodsharing\Modules\RegionAdmin;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Permissions\RegionPermissions;

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
