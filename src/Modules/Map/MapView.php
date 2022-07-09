<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Modules\Core\View;

class MapView extends View
{
	public function Index()
	{
		return $this->vueComponent('MapGlobal', 'MapGlobal');
	}
}
