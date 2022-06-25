<?php

// table `fs_betrieb_team`

namespace Foodsharing\Modules\Core\DBConstants\StoreTeam;

/**
 * column `active`
 * membership states for foodsavers and foodsharers
 * INT(11)          NOT NULL DEFAULT '0',.
 */
class MembershipStatus
{
	public const APPLIED_FOR_TEAM = 0; // Has pending request for the team
	public const MEMBER = 1; // Is member of the team
	public const JUMPER = 2; // Is waiting to join the team

	public static function toString(int $status)
	{
		switch ($status) {
			case MembershipStatus::APPLIED_FOR_TEAM:
				return 'REQUESTED';
			case MembershipStatus::MEMBER:
				return 'MEMBER';
			case MembershipStatus::JUMPER:
				return 'WAITING';
		}

		return 'UNKNOWN';
	}
}
