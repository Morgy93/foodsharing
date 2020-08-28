<?php

namespace Foodsharing\Modules\Event;

class InvitationStatus
{
	public const INVITED = 0; // invited
	public const ACCEPTED = 1; // will join
	public const MAYBE = 2; // might join
	public const WONT_JOIN = 3; // will not join (but has been invited)

	public static function isValidStatus(int $status): bool
	{
		return in_array($status, [
			self::INVITED,
			self::ACCEPTED,
			self::MAYBE,
			self::WONT_JOIN,
		]);
	}
}
