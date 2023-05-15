<?php

// table `fs_betrieb`

namespace Foodsharing\Modules\Core\DBConstants\Store;

/**
 * column `team_status`
 * store team states
 * TINYINT(2)          NOT NULL DEFAULT '1',.
 */
enum TeamSearchStatus: int
{
    case CLOSED = 0;
    case OPEN = 1;
    case OPEN_SEARCHING = 2;

    public static function isValidStatus(int $status): bool
    {
        return TeamSearchStatus::tryFrom($status) != null;
    }
}
