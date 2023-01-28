<?php

namespace Foodsharing\Modules\Dashboard;

use Foodsharing\Modules\Core\View;

class DashboardView extends View
{
    public function index($params): string
    {
        return $this->vueComponent('dashboard', 'dashboard', $params);
    }
}
