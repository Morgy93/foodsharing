<?php

// table `fs_betrieb_notiz`

namespace Foodsharing\Modules\Core\DBConstants\WallPost;

/**
 * column `milestone`
 * type of wallpost / store wall entry
 * TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'.
 */
class StoreWallEntryType
{
	public const TEXT_POSTED = 0;
	public const STORE_CREATED = 1;
	public const TEAM_MEMBER_ACCEPTED = 2;
	public const TEAM_STATUS_CHANGED = 3;
	// value 4 is unused and never existed
	public const TEAM_MEMBER_LEFT = 5;
}
