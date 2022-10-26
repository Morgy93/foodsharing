<?php

// table `fs_betrieb`

namespace Foodsharing\Modules\Core\DBConstants\Store;

use ReflectionClass;

/**
 * column `team_status`
 * store team states
 * TINYINT(2)          NOT NULL DEFAULT '1',.
 */
class TeamStatus
{
	public const CLOSED = 0;
	public const OPEN = 1;
	public const OPEN_SEARCHING = 2;

	public static function isValidStatus(int $value): bool
	{
		return in_array($value, range(self::CLOSED, self::OPEN_SEARCHING));
	}

	public static function getConstants()
	{
		$reflectionClass = new ReflectionClass(__CLASS__);

		return $reflectionClass->getConstants();
	}
}
