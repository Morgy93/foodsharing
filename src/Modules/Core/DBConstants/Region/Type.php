<?php

// table fs_bezirk

namespace Foodsharing\Modules\Core\DBConstants\Region;

/**
 * the different regions types. First layer are continents
 * TINYINT(4) | NOT NULL DEFAULT '1'.
 */
class Type
{
	/* fourth layer or lower */
	public const CITY = 1; // default
	/**
	 * fourth layer or lower
	 * political region.
	 */
	public const DISTRICT = 2;
	/**
	 * fourth layer or lower
	 * geographical region.
	 */
	public const REGION = 3;
	/**
	 * third layer
	 * no application (direct member access) possible.
	 */
	public const FEDERAL_STATE = 5;
	/**
	 * second layer
	 * no application (direct member access) possible.
	 */
	public const COUNTRY = 6;
	/* not actually a region and treated differently */
	public const WORKING_GROUP = 7;
	/**
	 * fourth layer or lower
	 * no application (direct member access) possible.
	 */
	public const BIG_CITY = 8;
	/* fifth layer or lower */
	public const PART_OF_TOWN = 9;

	public static function isAccessibleRegion(int $type): bool
	{
		return in_array($type, [self::PART_OF_TOWN, self::CITY, self::REGION, self::DISTRICT]);
	}

	public static function isRegion(int $type): bool
	{
		return $type != self::WORKING_GROUP;
	}

	public static function getRegionTypes(): array
	{
		return [self::PART_OF_TOWN, self::CITY, self::REGION, self::DISTRICT, self::FEDERAL_STATE, self::COUNTRY, self::BIG_CITY];
	}

	public static function getGroupTypes(): array
	{
		return [self::WORKING_GROUP];
	}

	public static function isValid(int $value): bool
	{
		switch ($value) {
			case self::PART_OF_TOWN:
			case self::BIG_CITY:
			case self::WORKING_GROUP:
			case self::COUNTRY:
			case self::FEDERAL_STATE:
				case self::REGION:
					case self::DISTRICT:
							case self::CITY:
								return true;
									default:
									return false;
		}
	}
}
