<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Modules\Core\View;

class MapView extends View
{
	public function lMap($center)
	{
		return $this->vueComponent('leaflet-global-map', 'LeafletGlobalMap', [
			'lat' => $center['lat'],
			'lon' => $center['lon'],
		]);
	}

	public function mapControl()
	{
		return $this->vueComponent('map-control', 'MapControl');
	}
}
