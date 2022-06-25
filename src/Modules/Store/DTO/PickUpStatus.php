<?php

namespace Foodsharing\Modules\Store\DTO;

class PickUpStatus {
	const STATUS_RED_TODAY_TOMORROW = 3;
	const STATUS_ORANGE_3_DAYS = 2;
	const STATUS_YELLOW_5_DAYS = 1;
	const STATUS_GREEN = 0;

	public static function toString(int $status) {
		switch($status) {
			case PickUpStatus::STATUS_RED_TODAY_TOMORROW: return "RED_TODAY_TOMORROW";
			case PickUpStatus::STATUS_ORANGE_3_DAYS: return "ORANGE_3_DAYS";
			case PickUpStatus::STATUS_YELLOW_5_DAYS: return "YELLOW_5_DAYS";
			case PickUpStatus::STATUS_GREEN: return "GREEN";
		}
		return "UNKNOWN";
	}
}
