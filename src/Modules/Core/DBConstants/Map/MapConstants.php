<?php

namespace Foodsharing\Modules\Core\DBConstants\Map;

class MapConstants
{
	public const CENTER_GERMANY_LAT = 50.89;
	public const CENTER_GERMANY_LON = 10.13;
	public const CENTER_GERMANY = MapConstants::CENTER_GERMANY_LAT . ',' . MapConstants::CENTER_GERMANY_LON;
	public const DEFAULT_SEARCH_DISTANCE = 45;
	public const MIN_SEARCH_DISTANCE = 1;
	public const MAX_SEARCH_DISTANCE = 1000;
	public const ZOOM_COUNTRY = 6;
	public const ZOOM_CITY = 13;
}
