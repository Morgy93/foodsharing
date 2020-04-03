<?php

namespace Foodsharing\Modules\StoreOverview;

use Foodsharing\Modules\Core\View;

class StoreOverviewView extends View
{
	public function StoreOverview()
	{
		return $this->vueComponent('store-overview', 'storeOverview');
	}
}
