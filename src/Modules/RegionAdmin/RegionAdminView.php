<?php

namespace Foodsharing\Modules\RegionAdmin;

use Foodsharing\Modules\Core\View;

class RegionAdminView extends View
{
    public function panel()
    {
        $this->pageHelper->addContent($this->vueComponent('region-admin-panel', 'RegionAdminPanel', [
            'regionId' => 0
        ]));
    }
}
