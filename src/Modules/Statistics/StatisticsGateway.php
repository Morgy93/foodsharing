<?php

namespace Foodsharing\Modules\Statistics;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\Type;

class StatisticsGateway extends BaseGateway
{
	public function listTotalStat(): array
	{
		$stm = '	
				SELECT
					SUM(`stat_fetchweight`) AS fetchweight,
					SUM(`stat_fetchcount`) AS fetchcount,
					SUM(`stat_korpcount`) AS cooperationscount,
					SUM(`stat_botcount`) AS botcount,
					SUM(`stat_fscount`) AS fscount,
					SUM(`stat_fairteilercount`) AS fairteilercount	
				FROM
					fs_bezirk	
				WHERE
					`id` = :region_id
		';

		return $this->db->fetch($stm, [':region_id' => RegionIDs::EUROPE]);
	}

	public function listStatCities(): array
	{
		$stm = '
			SELECT
				`name`,
				`stat_fetchweight` AS fetchweight,
				`stat_fetchcount` AS fetchcount,
				`type`
			FROM
				fs_bezirk	
			WHERE
				`type` IN(:city, :bigCity)	
			ORDER BY fetchweight DESC
			LIMIT 10
		';

		return $this->db->fetchAll($stm, [':city' => Type::CITY, ':bigCity' => Type::BIG_CITY]);
	}

	public function listStatFoodsaver(): array
	{
		$stm = '
			SELECT
				`id`,
				`name`,
				`nachname`,
				`stat_fetchweight` AS fetchweight,
				`stat_fetchcount` AS fetchcount
			FROM
				fs_foodsaver
			WHERE
				deleted_at IS NULL	
			ORDER BY fetchweight DESC
			LIMIT 10
		';

		return $this->db->fetchAll($stm);
	}

	public function countAllFoodsharers(): int
	{
		return $this->db->count('fs_foodsaver', ['active' => 1, 'deleted_at' => null]);
	}

	public function avgDailyFetchCount(): int
	{
		// get number of all fetches within time range
		$q = '
	    SELECT
	      COUNT(*) as fetchCount
	    FROM
	      fs_abholer
			WHERE
				CAST(`date` as date) > DATE_ADD(CURDATE(), INTERVAL -29 DAY) AND
				CAST(`date` as date) < CURDATE()
	  ';
		$fetchCount = (int)$this->db->fetch($q)['fetchCount'];
		// time range to average over in days
		$diffDays = 28;
		// divide number of fetches by time difference
		return (int)$fetchCount / $diffDays;
	}
}
