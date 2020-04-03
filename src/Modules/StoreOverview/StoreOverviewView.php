<?php

namespace Foodsharing\Modules\StoreOverview;

use Foodsharing\Modules\Core\View;

class StoreOverviewView extends View
{
	public function registerForm()
	{
		return $this->vueComponent('store-overview', 'StoreOverview');
	}
}
