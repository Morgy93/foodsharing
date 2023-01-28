<?php

// table `fs_betrieb`

namespace Foodsharing\Modules\Core\DBConstants\Store;

/**
 * column `public_time`
 * typical pickup time range
 * INT(4).
 */
enum PublicTimes: int
{
    case NOT_SET = 0;
    case IN_THE_MORNING = 1;
    case AT_NOON_IN_THE_AFTERNOON = 2;
    case IN_THE_EVENING = 3;
    case AT_NIGHT = 4;
}
