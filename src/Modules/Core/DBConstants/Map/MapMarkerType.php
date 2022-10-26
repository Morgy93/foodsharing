<?php

namespace Foodsharing\Modules\Core\DBConstants\Map;

use ReflectionClass;

class MapMarkerType
{
	public const UNDEFINED = 0;
	public const STORE = 1;
	public const COMMUNITY = 2;
	public const FOODBASKET = 3;
	public const FOODSHAREPOINT = 4;

	public static function getType(int $value): string
	{
		$class = new ReflectionClass(__CLASS__);
		$constants = array_flip($class->getConstants());

		return $constants[$value];
	}
}
