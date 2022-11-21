<?php

// table `fs_betrieb`

namespace Foodsharing\Modules\Core\DBConstants\Store;

/**
 * column `ueberzeugungsarbeit`
 * How hard was it to start cooperation with store
 * INT(4).
 */
enum ConvinceStatus: int
{
	case NOT_SET = 0;
	case NO_PROBLEM_AT_ALL = 1;
	case AFTER_SOME_PERSUASION = 2;
	case DIFFICULT_NEGOTIATION = 3;
	case LOOKED_BAD_BUT_WORKED = 4;
}
