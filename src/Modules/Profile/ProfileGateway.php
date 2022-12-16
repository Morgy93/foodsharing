<?php

namespace Foodsharing\Modules\Profile;

use Foodsharing\Lib\WebSocketConnection;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\BasketRequests\Status as RequestStatus;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Utility\WeightHelper;

final class ProfileGateway extends BaseGateway
{
	private WebSocketConnection $webSocketConnection;
	private $weightHelper;

	public function __construct(Database $db, WebSocketConnection $webSocketConnection, WeightHelper $weightHelper)
	{
		parent::__construct($db);
		$this->webSocketConnection = $webSocketConnection;
		$this->weightHelper = $weightHelper;
	}

	/**
	 * @param int $fsId id of the foodsaver we want the info from
	 * @param int $viewerId id of foodsaver looking for info. Pass -1 to prevent loading profile information of the viewer.
	 * @param bool $mayHandleReports info such as nb. of violations is only retrieved if this is true
	 */
	public function getData(int $fsId, int $viewerId, bool $mayHandleReports): array
	{
		$stm = '
			SELECT 	fs.`id`,
					fs.`bezirk_id`,
					fs.`position`,
					fs.`plz`,
					fs.`stadt`,
					fs.`lat`,
					fs.`lon`,
					fs.`email`,
					fs.`name`,
					fs.`nachname`,
					fs.`anschrift`,
					fs.`telefon`,
					fs.`homepage`,
					fs.`handy`,
					fs.`geschlecht`,
					fs.`geb_datum`,
					fs.`anmeldedatum`,
					fs.`photo`,
					fs.`about_me_intern`,
					fs.`about_me_public`,
					fs.`orgateam`,
					fs.`data`,
					fs.`last_login` as last_activity,
					fs.stat_fetchweight,
					fs.stat_fetchcount,
					fs.stat_ratecount,
					fs.stat_rating,
					fs.stat_postcount,
					fs.stat_buddycount,
					fs.stat_fetchrate,
					fs.stat_bananacount,
					fs.verified,
					fs.anmeldedatum,
					fs.sleep_status,
					fs.sleep_msg,
					fs.sleep_from,
					fs.sleep_until,
					fs.rolle,
					UNIX_TIMESTAMP(fs.sleep_from) AS sleep_from_ts,
					UNIX_TIMESTAMP(fs.sleep_until) AS sleep_until_ts,
					fs.mailbox_id,
					fs.deleted_at

			FROM 	fs_foodsaver fs

			WHERE 	fs.id = :fs_id
			';
		if (($data = $this->db->fetch($stm, [':fs_id' => $fsId])) === []
		) {
			return [];
		}
		$data['online'] = $this->webSocketConnection->isUserOnline($fsId);

		$data['bouched'] = false;
		$data['bananen'] = false;
		if ($viewerId != -1) {
			$stm = 'SELECT 1 FROM `fs_rating` WHERE rater_id = :viewerId AND foodsaver_id = :fs_id';

			try {
				if ($this->db->fetchValue($stm, [':viewerId' => $viewerId, ':fs_id' => $fsId])) {
					$data['bouched'] = true;
				}
			} catch (\Exception $e) {
				// has to be caught until we can check whether a to be fetched value does really exist.
			}
		}
		$this->loadBananas($data, $fsId);

		$data['botschafter'] = false;
		$data['foodsaver'] = false;
		$data['orga'] = false;

		if ($mayHandleReports) {
			$data['violation_count'] = $this->getViolationCount($fsId);
			$data['note_count'] = $this->getNotesCount($fsId);
		}

		$stm = '
			SELECT 	bz.`name`,
					bz.`id`
			FROM 	`fs_bezirk` bz,
					fs_botschafter b
			WHERE 	b.`bezirk_id` = bz.`id`
			AND 	b.foodsaver_id = :fs_id
			AND 	bz.type != 7
		';
		if ($bot = $this->db->fetchAll($stm, [':fs_id' => $fsId])) {
			$data['botschafter'] = $bot;
		}

		$stm = '
			SELECT 	bz.`name`,
					bz.`id`,
			        bz.type
			FROM 	`fs_bezirk` bz,
					fs_foodsaver_has_bezirk b
			WHERE 	b.`bezirk_id` = bz.`id`
			AND 	b.foodsaver_id = :fs_id
			AND		bz.type != :type
		';
		if ($fs = $this->db->fetchAll($stm, [':fs_id' => $fsId, ':type' => UnitType::WORKING_GROUP])) {
			$data['foodsaver'] = $fs;
		}

		// find all working groups in which both the foodsaver and the viewer of the profile are active members
		$stm = '
			SELECT 	bz.name,
					bz.id
			FROM 	fs_bezirk bz
			JOIN    fs_foodsaver_has_bezirk b1
			ON      b1.bezirk_id = bz.id
			LEFT JOIN fs_foodsaver_has_bezirk b2
			ON    	b1.bezirk_id = b2.bezirk_id
			WHERE 	b1.foodsaver_id = :fs_id
			AND     b1.active = 1
			AND 	b2.foodsaver_id = :viewerId
			AND     b2.active = 1
			AND     bz.type = :type
		';
		if ($fs = $this->db->fetchAll($stm, [
			':fs_id' => $fsId,
			':viewerId' => $viewerId,
			':type' => UnitType::WORKING_GROUP
		])) {
			$data['working_groups'] = $fs;
		}

		$stm = '
			SELECT 	bz.`name`,
					bz.`id`
			FROM 	`fs_bezirk` bz,
					fs_botschafter b
			WHERE 	b.`bezirk_id` = bz.`id`
			AND 	b.foodsaver_id = :fs_id
			AND 	bz.type = 7
		';
		if ($orga = $this->db->fetchAll($stm, [':fs_id' => $fsId])) {
			$data['orga'] = $orga;
		}

		$data['pic'] = false;
		if (!empty($data['photo']) && file_exists('images/' . $data['photo'])) {
			$data['pic'] = [
				'original' => 'images/' . $data['photo'],
				'medium' => 'images/130_q_' . $data['photo'],
				'mini' => 'images/50_q_' . $data['photo'],
			];
		}

		$stm = '
			SELECT his.date,
			       his.changer_id,
			       concat(ch.name," " ,ch.nachname) as changer_full_name,
			       his.old_value as old_region,
			       bez.name as  old_region_name
			FROM `fs_foodsaver_change_history` his
				left outer join fs_foodsaver ch on his.changer_id  = ch.id
				left outer join fs_bezirk bez on his.old_value = bez.id
			where
				fs_id = :fs_id and
				object_name = \'bezirk_id\'
			order by date desc
			limit 1';
		if ($home_district_history = $this->db->fetch($stm, [':fs_id' => $fsId])) {
			$data['home_district_history'] = $home_district_history;
		}

		return $data;
	}

	public function isUserVerified(int $userId): bool
	{
		return boolval($this->db->fetchValueByCriteria('fs_foodsaver', 'verified', ['id' => $userId]));
	}

	/**
	 * @param array $data pass by reference with "&" --> otherwise the array will only be changed in scope of the method
	 * @param int $fsId the foodsaver id for which bananas should be loaded
	 */
	private function loadBananas(array &$data, int $fsId): void
	{
		$stm = '
					SELECT 	fs.id,
							fs.name,
							fs.photo,
							r.`msg`,
							r.`time`
					FROM 	`fs_foodsaver` fs,
							 `fs_rating` r
					WHERE 	r.rater_id = fs.id
					AND 	r.foodsaver_id = :fs_id
					ORDER BY time DESC
			';

		$bananaList = $this->db->fetchAll($stm, [':fs_id' => $fsId]);
		foreach ($bananaList as &$banana) {
			$banana['createdAt'] = str_replace(' ', 'T', $banana['time']);
		}

		$data['bananen'] = $bananaList;
		$bananaCountNew = count($bananaList);

		if ($data['stat_bananacount'] != $bananaCountNew) {
			$this->db->update('fs_foodsaver', ['stat_bananacount' => $bananaCountNew], ['id' => $fsId]);
			$data['stat_bananacount'] = $bananaCountNew;
		}

		if (!$data['bananen']) {
			$data['bananen'] = [];
		}
	}

	private function getViolationCount(int $fsId): int
	{
		return (int)$this->db->count('fs_report', ['foodsaver_id' => $fsId]);
	}

	private function getNotesCount(int $fsId): int
	{
		$stm = '
			SELECT
				COUNT(wallpost_id)
			FROM
	           	`fs_usernotes_has_wallpost`
			WHERE
				usernotes_id = :fs_id
		';

		return (int)$this->db->fetchValue($stm, [':fs_id' => $fsId]);
	}

	public function giveBanana(int $fsId, ?int $sessionId, string $message = ''): int
	{
		if ($sessionId === null) {
			throw new \UnexpectedValueException('Must be logged in to give banana.');
		}

		$bananaId = $this->db->insert('fs_rating', [
			'foodsaver_id' => $fsId,
			'rater_id' => $sessionId,
			'msg' => $message,
			'time' => $this->db->now(),
		]);

		return $bananaId;
	}

	/**
	 * Returns whether the user with the raterId has already given a banana with the user with userId.
	 */
	public function hasGivenBanana(?int $raterId, int $userId): bool
	{
		if ($raterId === null) {
			return false;
		}

		return $this->db->exists('fs_rating', ['foodsaver_id' => $userId, 'rater_id' => $raterId]);
	}

	/**
	 * Deletes a banana. Returns whether it existed and was deleted.
	 */
	public function removeBanana(int $userId, int $raterId): bool
	{
		return $this->db->delete('fs_rating', ['foodsaver_id' => $userId, 'rater_id' => $raterId]) > 0;
	}

	/**
	 * Counts how many pickups were done that the foodsaver signed up for 20 hours before pickup time therefore
	 * securing the pickup during a week.
	 *
	 *  int $fsId FoodsaverId
	 *  int $week Number of weeks to be added as interval to current date
	 */
	public function getSecuredPickupsCount(int $fsId, int $week): int
	{
		$stm = 'SELECT
                    COUNT(*) as Anzahl
                FROM
                    (SELECT
                        a.foodsaver_id, a.betrieb_id, a.date
                     FROM
                        `fs_abholer` a
                        left outer join `fs_store_log` b on a.betrieb_id = b.store_id and a.date = b.date_reference + INTERVAL 1 HOUR
                     WHERE a.foodsaver_id = :fs_id
                        AND b.action = :action
                        AND DATE_FORMAT(a.date,\'%Y-%v\') = DATE_FORMAT(CURRENT_DATE() + INTERVAL :week WEEK,\'%Y-%v\')
                        AND TIMESTAMPDIFF(HOUR, b.date_activity, b.date_reference) < 20
                     GROUP BY
                        a.foodsaver_id, a.betrieb_id, a.date
                    ) z';

		$res = $this->db->fetchAll($stm, [
			':fs_id' => $fsId,
			':action' => StoreLogAction::SIGN_UP_SLOT,
			':week' => $week
		]);

		return $res['0']['Anzahl'];
	}

	public function getBasketsShared(int $fsId, int $week): int
	{
		$stm = 'SELECT
       				 COUNT(DISTINCT a.foodsaver_id) as count
				FROM `fs_basket` b
				left outer join fs_basket_anfrage a on b.id = a.basket_id
				WHERE b.foodsaver_id = :fs_id
				  AND DATE_FORMAT(b.`time`,\'%Y-%v\') = DATE_FORMAT(CURRENT_DATE() + INTERVAL :week WEEK,\'%Y-%v\')
				  AND a.status = :basket_status
		';
		$res = $this->db->fetchAll($stm, [
			':fs_id' => $fsId,
			':week' => $week,
			':basket_status' => RequestStatus::DELETED_PICKED_UP
		]);

		return $res['0']['count'];
	}

	public function getBasketsOfferedStat(int $fsId, int $week): array
	{
		$stm = 'SELECT
       				COUNT(*) as count,
       				SUM(weight) as weight
				FROM `fs_basket` a
				WHERE a.foodsaver_id = :fs_id
				  AND DATE_FORMAT(a.`time`,\'%Y-%v\') = DATE_FORMAT(CURRENT_DATE() + INTERVAL :week WEEK,\'%Y-%v\')
		';

		return $this->db->fetchAll($stm, [
			':fs_id' => $fsId,
			':week' => $week
		]);
	}

	public function getResponsibleActiveStoresCount(int $fsId): int
	{
		$stm = '
			SELECT 	COUNT(*) as count
			FROM             fs_betrieb_team st
			LEFT OUTER JOIN  fs_betrieb s  ON  s.id = st.betrieb_id

			WHERE  st.foodsaver_id = :fs_id
			AND    s.betrieb_status_id in (:stat_start, :stat_est)
			AND    st.verantwortlich = 1
		';

		$res = $this->db->fetchAll($stm, [
			':fs_id' => $fsId,
			':stat_start' => CooperationStatus::COOPERATION_STARTING->value,
			':stat_est' => CooperationStatus::COOPERATION_ESTABLISHED->value
		]);

		return $res['0']['count'];
	}

	public function getEventsCreatedCount(int $fsId, int $week): int
	{
		$stm = 'SELECT
					COUNT(*) AS count
				FROM `fs_event` a
				WHERE a.foodsaver_id = :fs_id
				  	AND DATE_FORMAT(a.`start`,\'%Y-%v\') = DATE_FORMAT(CURRENT_DATE() + INTERVAL :week WEEK,\'%Y-%v\')
		';

		$res = $this->db->fetchAll($stm, [
			':fs_id' => $fsId,
			':week' => $week
		]);

		return $res['0']['count'];
	}

	public function getEventsParticipatedCount(int $fsId, int $week): array
	{
		$stm = 'SELECT
					COUNT(*) AS count,
					SUM(TIMESTAMPDIFF(MINUTE,start,end))DIV 60 as duration_hours,
					LPAD(SUM(TIMESTAMPDIFF(MINUTE,start,end))%60,2,0) as duration_minutes
				FROM `fs_foodsaver_has_event` a
					LEFT OUTER JOIN fs_event e on a.event_id = e.id
				WHERE a.foodsaver_id = :fs_id
				  	AND a.status = :part_status
				  	AND DATE_FORMAT(e.start,\'%Y-%v\') = DATE_FORMAT(CURRENT_DATE() + INTERVAL :week WEEK,\'%Y-%v\')
		';

		return $this->db->fetchAll($stm, [
			':fs_id' => $fsId,
			':week' => $week,
			':part_status' => 1
		]);
	}

	public function getPickupsStat(int $fsId, int $week): array
	{
		$stm = 'SELECT 	bez.name AS districtName,
						kat.name AS categorieName,
						CASE b.abholmenge
							WHEN 0 THEN \'' . $this->weightHelper->getFetchWeightName(0) . '\'
							WHEN 1 THEN \'' . $this->weightHelper->getFetchWeightName(1) . '\'
							WHEN 2 THEN \'' . $this->weightHelper->getFetchWeightName(2) . '\'
							WHEN 3 THEN \'' . $this->weightHelper->getFetchWeightName(3) . '\'
							WHEN 4 THEN \'' . $this->weightHelper->getFetchWeightName(4) . '\'
							WHEN 5 THEN \'' . $this->weightHelper->getFetchWeightName(5) . '\'
							WHEN 6 THEN \'' . $this->weightHelper->getFetchWeightName(6) . '\'
							WHEN 7 THEN \'' . $this->weightHelper->getFetchWeightName(7) . '\'
						END AS pickupAmount,
						COUNT(*) AS pickupCount
				FROM `fs_abholer` a
					LEFT OUTER JOIN fs_betrieb b ON a.betrieb_id = b.id
					LEFT OUTER JOIN fs_betrieb_kategorie kat ON b.betrieb_kategorie_id = kat.id
					LEFT OUTER JOIN fs_bezirk bez ON b.bezirk_id = bez.id
				WHERE a.foodsaver_id = :fs_id
				  AND DATE_FORMAT(date,\'%x-%v\') = DATE_FORMAT(CURRENT_DATE() + INTERVAL :week WEEK,\'%x-%v\')
				GROUP BY DATE_FORMAT(date,\'%x-%v\'),
						 bez.name,
						 kat.id,
						 b.abholmenge
				ORDER BY
						a.`date` DESC,
						bez.name ASC,
						kat.id   ASC,
						b.abholmenge ASC
		';

		return $this->db->fetchAll($stm, [
			':fs_id' => $fsId,
			':week' => $week
		]);
	}

	public function getPassHistory(int $fsId): array
	{
		$stm = '
			SELECT
			  pg.foodsaver_id,
			  pg.date,
			  UNIX_TIMESTAMP(pg.date) AS date_ts,
			  pg.bot_id,
			  fs.nachname,
			  fs.name
			FROM
			  fs_pass_gen pg
			LEFT JOIN
			  fs_foodsaver fs
			ON
			  pg.bot_id = fs.id
			WHERE
			  pg.foodsaver_id = :fs_id
			ORDER BY
			  pg.date
			DESC
		';

		return $this->db->fetchAll($stm, [':fs_id' => $fsId]);
	}

	public function getVerifyHistory(int $fsId): array
	{
		$stm = '
			SELECT
			  vh.fs_id,
			  vh.date,
			  UNIX_TIMESTAMP(vh.date) AS date_ts,
			  vh.change_status,
			  vh.bot_id,
			  fs.nachname,
			  fs.name
			FROM
			  fs_verify_history vh
			LEFT JOIN
			  fs_foodsaver fs
			ON
			  vh.bot_id = fs.id
			WHERE
			  vh.fs_id = :fs_id
			ORDER BY
			  vh.date
			DESC
		';
		$ret = $this->db->fetchAll($stm, [':fs_id' => $fsId]);

		return $ret;
	}

	public function listStoresOfFoodsaver(int $fsId): array
	{
		return $this->db->fetchAll('
			SELECT 	s.id,
					s.name,
					st.verantwortlich as isManager,
					st.active,
					s.betrieb_status_id as cooperationStatus

			FROM             fs_betrieb_team st
			LEFT OUTER JOIN  fs_betrieb s  ON  s.id = st.betrieb_id

			WHERE  st.foodsaver_id = :fs_id

			ORDER BY  st.verantwortlich DESC, st.active ASC, s.name ASC
		', [
			':fs_id' => $fsId,
		]);
	}

	public function buddyStatus(int $fsId, int $sessionId): int
	{
		try {
			if (($status = $this->db->fetchValueByCriteria(
				'fs_buddy',
				'confirmed',
				['foodsaver_id' => $sessionId, 'buddy_id' => $fsId]
			)) !== []) {
				return $status;
			}
		} catch (\Exception $e) {
			// has to be caught until we can check whether a to be fetched value does really exist.
		}

		return -1;
	}
}
