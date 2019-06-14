<?php

namespace Foodsharing\Modules\Maintenance;

use Foodsharing\Lib\Db\Db;

class MaintenanceModel extends Db
{
	public function deleteUnconformedFetchDates()
	{
		return $this->del('DELETE FROM fs_abholer WHERE confirmed = 0 AND `date` < NOW()');
	}

	public function listOldBellIds($days = 7)
	{
		return $this->qCol('
			SELECT id
			FROM `fs_bell`
			WHERE `time` <= NOW( ) - INTERVAL ' . (int)$days . ' DAY
		');
	}

	public function deactivateOldBaskets()
	{
		return $this->update('
			UPDATE fs_basket
			SET `status` = 6 WHERE
			until < NOW()
			AND `status` = 1
		');
	}

	public function setFoodsaverInactive($fsids)
	{
		return $this->update('UPDATE fs_foodsaver SET sleep_status = 2 WHERE id IN(' . implode(',', $fsids) . ')');
	}

	public function getUserBotschafter($fsid)
	{
		return $this->q('
			SELECT 
				fs.id,
				fs.name,
				fs.email
				
			FROM 
				fs_foodsaver_has_bezirk hb,
				fs_botschafter b,
				fs_foodsaver fs
				
			WHERE 
				b.foodsaver_id = fs.id
				
			AND 
				b.bezirk_id = hb.bezirk_id
				
			AND
				hb.foodsaver_id = ' . (int)$fsid . '
		');
	}

	public function listFoodsaverInactiveSince($days)
	{
		return $this->q('
			SELECT 
				`id`,
				`name`,
				`nachname`,
				`email`,
				`geschlecht`

			FROM 
				fs_foodsaver
				
			WHERE 
				sleep_status = 0
			AND
				`last_login` < "' . date('Y-m-d H:i:s', (time() - (84400 * $days))) . '"
		');
	}

	public function getAlertBetriebeAdmins()
	{
		$dow = (int)date('w');

		$dow2 = $dow + 1;
		if ($dow2 == 7) {
			$dow2 = 0;
		}

		$sql = '
			SELECT 
				DISTINCT z.betrieb_id

			FROM 
				fs_abholzeiten z
				
			LEFT JOIN
				fs_betrieb b
				
			ON
				z.betrieb_id = b.id
				
			WHERE
				b.betrieb_status_id IN(3,5)
				
			AND
			(
				(
					z.dow = ' . (int)$dow . '
					AND
					z.time >= "15:00:00"
				)
				OR
				(
					z.dow = ' . (int)$dow2 . '
					AND
					z.time < "15:00:00"
				)
			)
		';

		if ($betriebe = $this->q($sql)) {
			$bids = array();

			foreach ($betriebe as $b) {
				$bids[(int)$b['betrieb_id']] = (int)$b['betrieb_id'];
			}

			$date1 = date('Y-m-d') . ' 15:00:00';
			$date1_end = date('Y-m-d') . ' 23:59:59';

			$date2 = date('Y-m-d', time() + 86400) . ' 00:00:00';
			$date2_end = date('Y-m-d', time() + 86400) . ' 15:00:00';

			$sql2 = '
				SELECT
					DISTINCT b.id
				
				FROM
					fs_betrieb b,
					fs_abholer a
				
				WHERE
					a.betrieb_id = b.id
						
				AND 
					a.confirmed = 1
						
				AND 
					b.id IN(' . implode(',', $bids) . ')
							
				AND 
				(
					(
						a.date >= "' . $date1 . '"
						AND
						a.date <= "' . $date1_end . '"
					)
					OR
					(
						a.date >= "' . $date2 . '"
						AND
						a.date <= "' . $date2_end . '"
					)
				)
			';

			if ($betrieb_has_fetcher = $this->q($sql2)) {
				foreach ($betrieb_has_fetcher as $bb) {
					unset($bids[$bb['id']]);
				}
			}

			if (!empty($bids)) {
				return $this->q('
					SELECT
						fs.id AS fs_id,
						fs.email AS fs_email,
						fs.geschlecht,
						fs.name AS fs_name,
						b.id AS betrieb_id,
						b.name AS betrieb_name
						
					FROM
						fs_betrieb b,
						fs_betrieb_team bt,
						fs_foodsaver fs
						
					WHERE
						b.id = bt.betrieb_id
						
					AND
						bt.foodsaver_id = fs.id
						
					AND
						bt.active = 1
						
					AND
						bt.verantwortlich = 1
					
					AND
						b.id IN(' . implode(',', $bids) . ')');
			}
		}

		return false;
	}

	public function deleteOldIpBlocks()
	{
		return $this->del('DELETE FROM `fs_ipblock` WHERE UNIX_TIMESTAMP(NOW()) > UNIX_TIMESTAMP(start)+duration ');
	}
}
