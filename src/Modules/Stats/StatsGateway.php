<?php

namespace Foodsharing\Modules\Stats;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;

class StatsGateway extends BaseGateway
{
	private StatsService $statsService;

	public function __construct(Database $db, StatsService $statsService)
	{
		$this->statsService = $statsService;

		parent::__construct($db);
	}

	public function fetchAllStores(): array
	{
		return $this->db->fetchAll('SELECT id, name, added FROM fs_betrieb');
	}

	/**
	 * Update the number of pickups, and the first and last pickup for each user in the store.
	 *
	 * @param int $storeId the store that will be updated
	 *
	 * @throws \Exception
	 */
	public function updateStoreUsersData(int $storeId): void
	{
		$this->db->fetch('UPDATE fs_betrieb_team,
				(SELECT bt.foodsaver_id,
				        IFNULL(innerstat.fetchcount, 0) as fetchcount,
				        IFNULL(innerstat.last_fetch, null) as last_fetch,
				        IFNULL(innerstat.first_fetch, null) as first_fetch
				FROM fs_betrieb_team bt
				LEFT OUTER JOIN (
				    SELECT a.foodsaver_id,
				           COUNT(*) as fetchcount,
				           DATE_FORMAT(max(a.date),"%Y-%m-%d") as last_fetch,
				           DATE_FORMAT(min(a.date),"%Y-%m-%d") as first_fetch
				    FROM fs_abholer a
				    WHERE a.betrieb_id = :storeIdA
				    AND a.date < NOW()
				    AND a.confirmed = 1
				    GROUP BY a.foodsaver_id) innerstat
				ON bt.foodsaver_id = innerstat.foodsaver_id
				WHERE betrieb_id = :storeIdB
			) AS storestats
			SET stat_fetchcount = storestats.fetchcount,
			    stat_first_fetch = storestats.first_fetch,
			    stat_last_fetch = storestats.last_fetch
			WHERE storestats.foodsaver_id = fs_betrieb_team.foodsaver_id
			AND fs_betrieb_team.betrieb_id = :storeIdC', [
			':storeIdA' => $storeId, ':storeIdB' => $storeId, ':storeIdC' => $storeId
		]);
	}

	public function getAllFoodsaverIds()
	{
		return $this->db->fetchAllValuesByCriteria('fs_foodsaver', 'id');
	}

	public function getFoodsaverPickups(): array
	{
		return $this->getUserPropertyCount('fs_abholer', 'WHERE date < NOW() AND confirmed = 1');
	}

	public function getBananaCount(): array
	{
		return $this->getUserPropertyCount('fs_rating');
	}

	public function getBuddyCount(): array
	{
		return $this->getUserPropertyCount('fs_buddy', 'WHERE confirmed = 1');
	}

	public function getForumPostCount(): array
	{
		return $this->getUserPropertyCount('fs_theme_post');
	}

	public function getWallPostCount(): array
	{
		return $this->getUserPropertyCount('fs_wallpost');
	}

	public function getStoreNoteCount(): array
	{
		return $this->getUserPropertyCount('fs_betrieb_notiz', 'WHERE milestone = 0');
	}

	public function getNotFetchedReportCount(): array
	{
		return $this->getUserPropertyCount('fs_report', 'WHERE `reporttype` = 1 AND committed = 1 AND tvalue like \'%Ist gar nicht zum Abholen gekommen%\'');
	}

	/**
	 * Counts the entries per user in a specific table for all users and returns it as a map
	 * from foodsharer-ID to the number.
	 */
	private function getUserPropertyCount(string $table, string $conditions = ''): array
	{
		$data = $this->db->fetch('SELECT f.id, IFNULL(a.count,0) as count
								FROM fs_foodsaver f
								LEFT OUTER JOIN (
									SELECT foodsaver_id as id,
									COUNT(*) as count
									FROM :table ' . $conditions . '
									GROUP BY foodsaver_id
								) a
								ON f.id = a.id', [
			':table' => $table,
			':conditions' => $conditions
		]);

		$mapped = [];
		foreach ($data as $d) {
			$mapped[$d[0]] = $d[1];
		}

		return $mapped;
	}

	public function getTotalKilosFetchedByFoodsaver(int $foodsaverId)
	{
		$result = $this->db->fetch('
			SELECT
			       sum(fw.weight) AS saved
			FROM fs_abholer fa
				left outer join fs_betrieb fb on fa.betrieb_id = fb.id
				left outer join fs_fetchweight fw on fb.abholmenge = fw.id
			WHERE
			      fa.foodsaver_id = :foodsaverId
			  AND fa.date < now()
			  AND fa.confirmed = 1
		', [
			':foodsaverId' => $foodsaverId
		]);

		return empty($result) ? 0 : $result;
	}

	public function updateUserStats(int $userId, float $totalKilos, int $fetchCount, int $postCount,
									int $buddyCount, int $bananaCount, float $fetchRate): void
	{
		$this->db->update('fs_foodsaver', [
			'stat_fetchweight' => $totalKilos,
			'stat_fetchcount' => $fetchCount,
			'stat_postcount' => $postCount,
			'stat_buddycount' => $buddyCount,
			'stat_bananacount' => $bananaCount,
			'stat_fetchrate' => $fetchRate
		], [
			'id' => $userId
		]);
	}
}
