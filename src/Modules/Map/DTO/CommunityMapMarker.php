<?php

namespace Foodsharing\Modules\Map\DTO;

use Foodsharing\Modules\Core\DBConstants\Map\MapMarkerType;

class CommunityMapMarker extends MapMarker
{
	public function __construct()
	{
		parent::__construct();
	}

	public static function createFromArray(mixed $value, ?int $type = null): CommunityMapMarker
	{
		$marker = new CommunityMapMarker();
		$marker->id = $value['id'];
		$marker->name = $value['name'];
		$marker->description = $value['desc'];

		$marker->latitude = $value['lat'];
		$marker->longitude = $value['lon'];

		$marker->setType(MapMarkerType::COMMUNITY);

		return $marker;
	}
}
