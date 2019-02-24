<?php

namespace Foodsharing\Modules\Store;

use Carbon\CarbonInterval;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\BellUpdaterInterface;
use Foodsharing\Modules\Bell\BellUpdateTrigger;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Region\RegionGateway;

class StoreGateway extends BaseGateway implements BellUpdaterInterface
{
	private $regionGateway;
	private $bellGateway;

	public function __construct(
		Database $db,
		RegionGateway $regionGateway,
		BellGateway $bellGateway,
		BellUpdateTrigger $bellUpdateTrigger
	) {
		parent::__construct($db);

		$this->regionGateway = $regionGateway;
		$this->bellGateway = $bellGateway;

		$bellUpdateTrigger->subscribe($this);
	}

	public function getBetrieb($id): array
	{
		$out = $this->db->fetch('
		SELECT		`id`,
					plz,
					`fs_betrieb`.bezirk_id,
					`fs_betrieb`.kette_id,
					`fs_betrieb`.betrieb_kategorie_id,
					`fs_betrieb`.name,
					`fs_betrieb`.str,
					`fs_betrieb`.hsnr,
					`fs_betrieb`.stadt,
					`fs_betrieb`.lat,
					`fs_betrieb`.lon,
					CONCAT(`fs_betrieb`.str, " ",`fs_betrieb`.hsnr) AS anschrift,
					`fs_betrieb`.`betrieb_status_id`,
					`fs_betrieb`.status_date,
					`fs_betrieb`.ansprechpartner,
					`fs_betrieb`.telefon,
					`fs_betrieb`.email,
					`fs_betrieb`.fax,
					`fs_betrieb`.team_status,
					`kette_id`

		FROM 		`fs_betrieb`

		WHERE 		`fs_betrieb`.`id` = :id',
		[':id' => $id]);

		$out['verantwortlicher'] = '';
		if ($bezirk = $this->regionGateway->getBezirkName($out['bezirk_id'])) {
			$out['bezirk'] = $bezirk;
		}
		if ($verantwortlich = $this->getBiebsForStore($id)) {
			$out['verantwortlicher'] = $verantwortlich;
		}
		if ($kette = $this->getOne_kette($out['kette_id'])) {
			$out['kette'] = $kette;
		}

		$out['notizen'] = $this->getBetriebNotiz($id);

		return $out;
	}

	public function getMapsBetriebe($bezirk_id): array
	{
		return $this->db->fetchAll('
			SELECT 	fs_betrieb.id,
					`fs_betrieb`.betrieb_status_id,
					fs_betrieb.plz,
					`lat`,
					`lon`,
					`stadt`,
					fs_betrieb.kette_id,
					fs_betrieb.betrieb_kategorie_id,
					fs_betrieb.name,
					CONCAT(fs_betrieb.str," ",fs_betrieb.hsnr) AS anschrift,
					fs_betrieb.str,
					fs_betrieb.hsnr,
					fs_betrieb.`betrieb_status_id`

			FROM 	fs_betrieb

			WHERE 	fs_betrieb.bezirk_id = :bezirk_id

			AND `lat` != ""',
			[':bezirk_id' => $bezirk_id]
		);
	}

	public function getMyBetriebe($fs_id, $bezirk_id, $options = array()): array
	{
		$betriebe = $this->db->fetchAll('
			SELECT 	fs_betrieb.id,
						`fs_betrieb`.betrieb_status_id,
						fs_betrieb.plz,
						fs_betrieb.kette_id,

						fs_betrieb.ansprechpartner,
						fs_betrieb.fax,
						fs_betrieb.telefon,
						fs_betrieb.email,

						fs_betrieb.betrieb_kategorie_id,
						fs_betrieb.name,
						CONCAT(fs_betrieb.str," ",fs_betrieb.hsnr) AS anschrift,
						fs_betrieb.str,
						fs_betrieb.hsnr,
						fs_betrieb.`betrieb_status_id`,
						fs_betrieb_team.verantwortlich,
						fs_betrieb_team.active

				FROM 	fs_betrieb,
						fs_betrieb_team

				WHERE 	fs_betrieb.id = fs_betrieb_team.betrieb_id

				AND 	fs_betrieb_team.foodsaver_id = :fs_id

				ORDER BY fs_betrieb_team.verantwortlich DESC, fs_betrieb.name ASC
		', [':fs_id' => $fs_id]);

		$out = array();
		$out['verantwortlich'] = array();
		$out['team'] = array();
		$out['waitspringer'] = array();
		$out['anfrage'] = array();

		$already_in = array();

		if (is_array($betriebe)) {
			foreach ($betriebe as $b) {
				$already_in[$b['id']] = true;
				if ($b['verantwortlich'] == 0) {
					if ($b['active'] == 0) {
						$out['anfrage'][] = $b;
					} elseif ($b['active'] == 1) {
						$out['team'][] = $b;
					} elseif ($b['active'] == 2) {
						$out['waitspringer'][] = $b;
					}
				} else {
					$out['verantwortlich'][] = $b;
				}
			}
		}
		unset($betriebe);

		if (!isset($options['sonstige'])) {
			$options['sonstige'] = true;
		}

		if ($options['sonstige']) {
			$child_region_ids = $this->regionGateway->listIdsForDescendantsAndSelf($bezirk_id);
			$placeholders = $this->db->generatePlaceholders(count($child_region_ids));

			$out['sonstige'] = array();
			$betriebe = $this->db->fetchAll(
		'SELECT 		b.id,
						b.betrieb_status_id,
						b.plz,
						b.kette_id,

						b.ansprechpartner,
						b.fax,
						b.telefon,
						b.email,

						b.betrieb_kategorie_id,
						b.name,
						CONCAT(b.str," ",b.hsnr) AS anschrift,
						b.str,
						b.hsnr,
						b.`betrieb_status_id`,
						bz.name AS bezirk_name

				FROM 	fs_betrieb b,
						fs_bezirk bz

				WHERE 	b.bezirk_id = bz.id
				AND 	bezirk_id IN(' . $placeholders . ')
				ORDER BY bz.name DESC',
			$child_region_ids);

			foreach ($betriebe as $b) {
				if (!isset($already_in[$b['id']])) {
					$out['sonstige'][] = $b;
				}
			}
		}

		return $out;
	}

	public function getMyBetrieb($fs_id, $id): array
	{
		$out = $this->db->fetch('
			SELECT
			b.`id`,
			b.`betrieb_status_id`,
			b.`bezirk_id`,
			b.`plz`,
			b.`stadt`,
			b.`lat`,
			b.`lon`,
			b.`kette_id`,
			b.`betrieb_kategorie_id`,
			b.`name`,
			b.`str`,
			b.`hsnr`,
			b.`status_date`,
			b.`status`,
			b.`ansprechpartner`,
			b.`telefon`,
			b.`fax`,
			b.`email`,
			b.`begin`,
			b.`besonderheiten`,
			b.`public_info`,
			b.`public_time`,
			b.`ueberzeugungsarbeit`,
			b.`presse`,
			b.`sticker`,
			b.`abholmenge`,
			b.`team_status`,
			b.`prefetchtime`,
			b.`team_conversation_id`,
			b.`springer_conversation_id`,
			count(DISTINCT(a.date)) AS pickup_count

			FROM 		`fs_betrieb` b
			LEFT JOIN   `fs_abholer` a
			ON a.betrieb_id = b.id

			WHERE 		b.`id` = :id
			GROUP BY b.`id`',
			[':id' => $id]
		);
		if (!$out) {
			return $out;
		}

		$out['lebensmittel'] = $this->db->fetchAll('
				SELECT 		l.`id`,
							l.name
				FROM 		`fs_betrieb_has_lebensmittel` hl,
							`fs_lebensmittel` l
				WHERE 		l.id = hl.lebensmittel_id
				AND 		`betrieb_id` = :id
		', [':id' => $id]);

		$out['foodsaver'] = $this->getBetriebTeam($id);

		$out['springer'] = $this->getBetriebSpringer($id);

		$out['requests'] = $this->db->fetchAll('
				SELECT 		fs.`id`,
							fs.photo,
							CONCAT(fs.name," ",fs.nachname) AS name,
							name as vorname,
							fs.sleep_status

				FROM 		`fs_betrieb_team` t,
							`fs_foodsaver` fs

				WHERE 		fs.id = t.foodsaver_id
				AND 		`betrieb_id` = :id
				AND 		t.active = 0
				AND			fs.deleted_at IS NULL
		', [':id' => $id]);

		$out['verantwortlich'] = false;
		$foodsaver = array();
		$out['team_js'] = array();
		$out['team'] = array();
		$out['jumper'] = false;

		if (!empty($out['springer'])) {
			foreach ($out['springer'] as $v) {
				if ($v['id'] == $fs_id) {
					$out['jumper'] = true;
				}
			}
		}

		if (!empty($out['foodsaver'])) {
			$out['team'] = array();
			foreach ($out['foodsaver'] as $v) {
				$out['team_js'][] = $v['id'];
				$foodsaver[$v['id']] = $v['name'];
				$out['team'][] = array('id' => $v['id'], 'value' => $v['name']);
				if ($v['verantwortlich'] == 1) {
					$out['verantwortlicher'] = $v['id'];
					if ($v['id'] == $fs_id) {
						$out['verantwortlich'] = true;
					}
				}
			}
		} else {
			$out['foodsaver'] = array();
		}
		$out['team_js'] = implode(',', $out['team_js']);

		$out['abholer'] = false;
		if ($abholer = $this->db->fetchAll('SELECT `betrieb_id`,`dow` FROM `fs_abholzeiten` WHERE `betrieb_id` = :id', [':id' => $id])) {
			$out['abholer'] = array();
			foreach ($abholer as $a) {
				if (!isset($out['abholer'][$a['dow']])) {
					$out['abholer'][$a['dow']] = array();
				}
			}
		}

		return $out;
	}

	public function getBetriebTeam($id): array
	{
		return $this->db->fetchAll('
				SELECT 		fs.`id`,
							fs.`verified`,
							fs.`active`,
							fs.`telefon`,
							fs.`handy`,
							fs.photo,
							fs.quiz_rolle,
							fs.rolle,
							CONCAT(fs.name," ",fs.nachname) AS name,
							name as vorname,
							t.`verantwortlich`,
							t.`stat_last_update`,
							t.`stat_fetchcount`,
							t.`stat_first_fetch`,
							t.`stat_add_date`,
							UNIX_TIMESTAMP(t.`stat_last_fetch`) AS last_fetch,
							UNIX_TIMESTAMP(t.`stat_add_date`) AS add_date,
							fs.sleep_status


				FROM 		`fs_betrieb_team` t,
							`fs_foodsaver` fs

				WHERE 		fs.id = t.foodsaver_id
				AND 		`betrieb_id` = :id
				AND 		t.active  = 1
				AND			fs.deleted_at IS NULL
				ORDER BY 	t.`stat_fetchcount` DESC
		', [':id' => $id]);
	}

	public function getBetriebSpringer($id): array
	{
		return $this->db->fetchAll('
				SELECT 		fs.`id`,
							fs.`active`,
							fs.`telefon`,
							fs.`handy`,
							fs.photo,
							fs.rolle,
							CONCAT(fs.name," ",fs.nachname) AS name,
							name as vorname,
							t.`verantwortlich`,
							t.`stat_last_update`,
							t.`stat_fetchcount`,
							t.`stat_first_fetch`,
							UNIX_TIMESTAMP(t.`stat_add_date`) AS add_date,
							fs.sleep_status

				FROM 		`fs_betrieb_team` t,
							`fs_foodsaver` fs

				WHERE 		fs.id = t.foodsaver_id
				AND 		`betrieb_id` = :id
				AND 		t.active  = 2
				AND			fs.deleted_at IS NULL
		', [':id' => $id]);
	}

	public function getBiebsForStore($betrieb_id)
	{
		return $this->db->fetchAll(
			'
			SELECT 	`foodsaver_id` as id
			FROM fs_betrieb_team
			WHERE `betrieb_id` = :betrieb_id
			AND verantwortlich = 1
			AND `active` = 1',
			[':betrieb_id' => $betrieb_id]);
	}

	public function getAllStoreManagers(): array
	{
		$verant = $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`email`

			FROM 	`fs_foodsaver` fs,
					`fs_betrieb_team` bt

			WHERE 	bt.foodsaver_id = fs.id

			AND 	bt.verantwortlich = 1
			AND		fs.deleted_at IS NULL
		');

		$out = array();
		foreach ($verant as $v) {
			$out[$v['id']] = $v;
		}

		return $out;
	}

	public function getStoreCountForBieb($fs_id)
	{
		return $this->db->count('fs_betrieb_team', ['foodsaver_id' => $fs_id, 'verantwortlich' => 1]);
	}

	public function getEmailBiepBez($region_ids): array
	{
		// TODO can probably be removed
		$placeholders = $this->db->generatePlaceholders(count($region_ids));

		$verant = $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`email`

			FROM 	`fs_foodsaver` fs,
					`fs_betrieb_team` bt,
					`fs_foodsaver_has_bezirk` b

			WHERE 	bt.foodsaver_id = fs.id
			AND 	bt.foodsaver_id = b.foodsaver_id
			AND 	bt.verantwortlich = 1
			AND		b.`bezirk_id` IN(' . $placeholders . ')
			AND		fs.deleted_at IS NULL
		', $region_ids);

		$out = array();
		foreach ($verant as $v) {
			$out[$v['id']] = $v;
		}

		return $out;
	}

	public function isResponsible($fs_id, $betrieb_id)
	{
		return $this->db->exists('fs_betrieb_team', [
			'betrieb_id' => $betrieb_id,
			'foodsaver_id' => $fs_id,
			'verantwortlich' => 1,
			'active' => 1
		]);
	}

	public function userAppliedForStore($fsId, $storeId)
	{
		return $this->db->exists('fs_betrieb_team',
			[
				'betrieb_id' => $storeId,
				'foodsaver_id' => $fsId,
				'verantwortlich' => 0,
				'active' => 0
			]);
	}

	public function addFetcher($fsid, $bid, $date, $confirmed = 0): int
	{
		$queryResult = $this->db->insertIgnore('fs_abholer', [
			'foodsaver_id' => $fsid,
			'betrieb_id' => $bid,
			'date' => $this->dateTimeToPickupDate($date),
			'confirmed' => $confirmed
		]);

		return $queryResult;
	}

	/**
	 * @param bool $markNotificationAsUnread:
	 * if an older notification exists, that has already been marked as read,
	 * it can be marked as unread again while updating it
	 */
	public function updateBellNotificationForBiebs(int $storeId, bool $markNotificationAsUnread = false): void
	{
		$storeName = $this->db->fetchValueByCriteria('fs_betrieb', 'name', ['id' => $storeId]);
		$messageIdentifier = 'store-fetch-unconfirmed-' . $storeId;
		$messageCount = $this->getUnconfirmedFetchesCount($storeId);
		$messageVars = ['betrieb' => $storeName, 'count' => $messageCount];
		$messageTimestamp = $this->getNextUnconfirmedFetchTime($storeId);
		$messageExpiration = $messageTimestamp;

		$oldBellExists = $this->bellGateway->bellWithIdentifierExists($messageIdentifier);

		if ($messageCount === 0 && $oldBellExists) {
			$this->bellGateway->delBellsByIdentifier($messageIdentifier);
		} elseif ($messageCount > 0 && $oldBellExists) {
			$oldBellId = $this->bellGateway->getOneByIdentifier($messageIdentifier);
			$data = [
				'vars' => $messageVars,
				'time' => $messageTimestamp,
				'expiration' => $messageExpiration
			];
			$this->bellGateway->updateBell($oldBellId, $data, $markNotificationAsUnread);
		} elseif ($messageCount > 0 && !$oldBellExists) {
			$this->bellGateway->addBell(
				$this->getResponsibleFoodsavers($storeId),
				'betrieb_fetch_title',
				'betrieb_fetch',
				'img img-store brown',
				['href' => '/?page=fsbetrieb&id=' . $storeId],
				$messageVars,
				$messageIdentifier,
				0,
				$messageExpiration,
				$messageTimestamp
			);
		}
	}

	/**
	 * @deprecated
	 *
	 * This function does not match to our database structure. Column 'dow' does not exist
	 * in 'fs_abholer', but it exists in in 'fs_abholen'. Its only usage in StoreUserControl may need to be
	 * replaced with addFetcher, otherwise it may lead to exceptions.
	 */
	public function addAbholer($betrieb_id, $foodsaver_id, $dow): int
	{
		return $this->db->insert('fs_abholer', [
			'betrieb_id' => $betrieb_id,
			'foodsaver_id' => $foodsaver_id,
			'dow' => $dow
		]);
	}

	public function clearAbholer($betrieb_id): int
	{
		$result = $this->db->delete('fs_abholer', ['betrieb_id' => $betrieb_id]);
		$this->updateBellNotificationForBiebs($betrieb_id);

		return $result;
	}

	public function confirmFetcher($fsid, $bid, $date): int
	{
		$result = $this->db->update(
		'fs_abholer',
			['confirmed' => 1],
			['foodsaver_id' => $fsid, 'betrieb_id' => $bid, 'date' => $date]
		);

		$this->updateBellNotificationForBiebs($bid);

		return $result;
	}

	public function listFetcher($bid, $dates): array
	{
		if (!empty($dates)) {
			$placeholders = $this->db->generatePlaceholders(count($dates));

			$res = $this->db->fetchAll('
				SELECT 	fs.id,
						fs.name,
						fs.photo,
						a.date,
						a.confirmed
	
				FROM 	`fs_abholer` a,
						`fs_foodsaver` fs
	
				WHERE 	a.foodsaver_id = fs.id
				AND 	a.betrieb_id = ?
				AND  	a.date IN(' . $placeholders . ')
				AND		fs.deleted_at IS NULL',
				array_merge([$bid], $dates)
			);

			return $res;
		}

		return [];
	}

	public function getAbholzeiten($betrieb_id)
	{
		if ($res = $this->db->fetchAll('SELECT `time`,`dow`,`fetcher` FROM `fs_abholzeiten` WHERE `betrieb_id` = :id', [':id' => $betrieb_id])) {
			$out = array();
			foreach ($res as $r) {
				$out[$r['dow'] . '-' . $r['time']] = array(
					'dow' => $r['dow'],
					'time' => $r['time'],
					'fetcher' => $r['fetcher']
				);
			}

			ksort($out);

			return $out;
		}

		return false;
	}

	public function getBetriebConversation($bid, $springerConversation = false)
	{
		if ($springerConversation) {
			$ccol = 'springer_conversation_id';
		} else {
			$ccol = 'team_conversation_id';
		}

		return $this->db->fetchValue('SELECT ' . $ccol . ' FROM `fs_betrieb` WHERE `id` = :id', [':id' => $bid]);
	}

	public function changeBetriebStatus($fs_id, $bid, $status): int
	{
		$last = $this->db->fetch('SELECT id, milestone FROM `fs_betrieb_notiz` WHERE `betrieb_id` = :id ORDER BY id DESC LIMIT 1', [':id' => $bid]);

		if ($last['milestone'] == 3) {
			$this->db->delete('fs_betrieb_notiz', ['id' => $last['id']]);
		}

		$this->add_betrieb_notiz(array(
			'foodsaver_id' => $fs_id,
			'betrieb_id' => $bid,
			'text' => 'status_msg_' . (int)$status,
			'zeit' => date('Y-m-d H:i:s'),
			'milestone' => 3
		));

		return $this->db->update(
			'fs_betrieb',
			['betrieb_status_id' => $status],
			['id' => $bid]
		);
	}

	public function add_betrieb_notiz($data): int
	{
		$last = 0;
		if (isset($data['last']) && $data['last'] == 1) {
			$this->db->update(
				'fs_betrieb_notiz',
				['last' => 0],
				['betrieb_id' => $data['betrieb_id'], 'last' => 1]
			);
			$last = 1;
		}

		return $this->db->insert('fs_betrieb_notiz', [
			'foodsaver_id' => $data['foodsaver_id'],
			'betrieb_id' => $data['betrieb_id'],
			'milestone' => $data['milestone'],
			'text' => strip_tags($data['text']),
			'zeit' => $data['zeit'],
			'last' => $last
		]);
	}

	public function deleteBPost($id): int
	{
		return $this->db->delete('fs_betrieb_notiz', ['id' => $id]);
	}

	public function getTeamleader($betrieb_id): array
	{
		return $this->db->fetch(
		'SELECT 	fs.`id`,CONCAT(fs.name," ",nachname) AS name  
				FROM fs_betrieb_team t, fs_foodsaver fs
				WHERE t.foodsaver_id = fs.id
				AND `betrieb_id` = :id
				AND t.verantwortlich = 1
				AND fs.`active` = 1
				AND	fs.deleted_at IS NULL',
			[':id' => $betrieb_id]);
	}

	public function isInTeam($fs_id, $bid): bool
	{
		return $this->db->exists('fs_betrieb_team',
			[
				'foodsaver_id' => $fs_id,
				'betrieb_id' => $bid,
				'active >=' => 1
			]);
	}

	/* retrieves all biebs that are biebs for a given bezirk (by being bieb in a Betrieb that is part of that bezirk, which is semantically not the same we use on platform) */
	public function getBiebIds($bezirk): array
	{
		return $this->db->fetchAllValues('SELECT DISTINCT bt.foodsaver_id FROM `fs_bezirk_closure` c
			INNER JOIN `fs_betrieb` b ON c.bezirk_id = b.bezirk_id
			INNER JOIN `fs_betrieb_team` bt ON bt.betrieb_id = b.id
			INNER JOIN `fs_foodsaver` fs ON fs.id = bt.foodsaver_id
			WHERE c.ancestor_id = :id AND bt.verantwortlich = 1 AND fs.deleted_at IS NULL',
			[':id' => $bezirk]);
	}

	public function listStoresForFoodsaver($fsId)
	{
		return $this->db->fetchAll('
			SELECT 	b.`id`,
					b.name

			FROM 	`fs_betrieb_team` bt,
					`fs_betrieb` b

			WHERE 	bt.betrieb_id = b.id
			AND 	bt.`foodsaver_id` = :id
			AND 	bt.active = 1
			ORDER BY b.name',
			[':id' => $fsId]
		);
	}

	public function listStoreIdsForBieb($fsId)
	{
		return $this->db->fetchAllByCriteria('fs_betrieb_team', ['betrieb_id'], ['foodsaver_id' => $fsId, 'verantwortlich' => 1]);
	}

	public function getPickupSignupsForDate(int $storeId, \DateTime $date)
	{
		return $this->db->fetchAllByCriteria(
			'fs_abholer',
			['foodsaver_id'],
			['date' => $this->dateTimeToPickupDate($date)]
		);
	}

	public function getRegularPickupSlots(int $storeId)
	{
		return $this->db->fetchAllByCriteria(
			'fs_abholzeiten',
			['time', 'dow', 'fetcher'],
			['betrieb_id' => $storeId]);
	}

	public function getSinglePickupSlots(int $storeId, \DateTime $date)
	{
		$result = $this->db->fetchAllByCriteria(
			'fs_fetchdate',
			['time', 'fetchercount'],
			[
				'betrieb_id' => $storeId,
				'time' => $this->dateTimeToPickupDate($date)
			]
		);

		return array_map(function ($e) {
			return [
				'date' => $e['time'],
				'fetcher' => $e['fetchercount']
			];
		}, $result);
	}

	public function getFutureRegularPickupInterval(int $storeId): CarbonInterval
	{
		$result = $this->db->fetchValueByCriteria('fs_betrieb', 'prefetchtime', ['id' => $storeId]);

		return CarbonInterval::seconds($result);
	}

	private function dateTimeToPickupDate(\DateTime $date)
	{
		return $date->format('Y-m-d H:i:s');
	}

	private function getNextUnconfirmedFetchTime(int $storeId): \DateTime
	{
		$date = $this->db->fetchValue(
			'SELECT MIN(`date`) 
					   FROM `fs_abholer`
					   WHERE `betrieb_id` = :storeId AND `confirmed` = 0 AND `date` > NOW()',
			[':storeId' => $storeId]
		);

		return new \DateTime($date);
	}

	private function getUnconfirmedFetchesCount(int $storeId)
	{
		return $this->db->fetchValue(
			'SELECT COUNT(`betrieb_id`)
            FROM `fs_abholer`                                                   
            WHERE `betrieb_id` = :storeId AND `confirmed` = 0 AND `date` > NOW()',
			[':storeId' => $storeId]
		);
	}

	/*
	 * Private methods
	 */

	private function getOne_kette($id): array
	{
		return $this->db->fetch('
			SELECT
			`id`,
			`name`,
			`logo`

			FROM 		`fs_kette`

			WHERE 		`id` = :id',
			[':id' => $id]);
	}

	private function getBetriebNotiz($id): array
	{
		return $this->db->fetchAll('
			SELECT
			`id`,
			`foodsaver_id`,
			`betrieb_id`,
			`text`,
			`zeit`,
			UNIX_TIMESTAMP(`zeit`) AS zeit_ts

			FROM 		`fs_betrieb_notiz`

			WHERE `betrieb_id` = :id',
		[':id' => $id]);
	}

	/**
	 * @return int[]
	 */
	private function getResponsibleFoodsavers(int $storeId): array
	{
		return $this->db->fetchAllValuesByCriteria(
			'fs_betrieb_team',
			'foodsaver_id',
			[
				'betrieb_id' => $storeId,
				'verantwortlich' => 1
			]
		);
	}

	public function updateExpiredBells(): void
	{
		$expiredBells = $this->bellGateway->getExpiredByIdentifier('store-fetch-unconfirmed-%');

		foreach ($expiredBells as $bell) {
			$storeId = substr($bell['identifier'], strlen('store-fetch-unconfirmed-'));
			$storeName = $this->db->fetchValueByCriteria('fs_betrieb', 'name', ['id' => $storeId]);
			$newMessageCount = $this->getUnconfirmedFetchesCount($storeId);

			$newMessageData = [
				'vars' => ['betrieb' => $storeName, 'count' => $newMessageCount],
				'time' => $this->getNextUnconfirmedFetchTime($storeId),
				'expiration' => $this->getNextUnconfirmedFetchTime($storeId)
			];

			$this->bellGateway->updateBell($bell['id'], $newMessageData, false, false);
		}
	}

	public function getStoreNameByConversationId(int $id): ?string
	{
		$store = $this->db->fetch('SELECT name FROM fs_betrieb WHERE team_conversation_id = ? OR springer_conversation_id = ?', [$id, $id]);
		if ($store) {
			return $store['name'];
		} else {
			return null;
		}
	}
}
