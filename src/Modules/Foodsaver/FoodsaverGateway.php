<?php

namespace Foodsharing\Modules\Foodsaver;

use Carbon\Carbon;
use Exception;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Region\ForumFollowerGateway;

final class FoodsaverGateway extends BaseGateway
{
	private $forumFollowerGateway;

	public function __construct(
		Database $db,
		ForumFollowerGateway $forumFollowerGateway
	) {
		parent::__construct($db);
		$this->forumFollowerGateway = $forumFollowerGateway;
	}

	public function getFoodsaver($bezirk_id)
	{
		$and = ' AND 		fb.`bezirk_id` = ' . (int)$bezirk_id . '';
		if (is_array($bezirk_id)) {
			if (is_array(end($bezirk_id))) {
				$tmp = $bezirk_id;
				$bezirk_id = array();
				foreach ($tmp as $b) {
					$bezirk_id[$b['id']] = $b['id'];
				}
			}

			$and = ' AND 		fb.`bezirk_id` IN(' . implode(',', $bezirk_id) . ')';
		}

		return $this->db->fetchAll('
			SELECT 		fs.id,
						CONCAT(fs.`name`, " ", fs.`nachname`) AS `name`,
						fs.`name` AS vorname,
						fs.`anschrift`,
						fs.`email`,
						fs.`telefon`,
						fs.`handy`,
						fs.`plz`,
						fs.`geschlecht`

			FROM 		fs_foodsaver_has_bezirk fb,
						`fs_foodsaver` fs

			WHERE 		fb.foodsaver_id = fs.id
			AND			fs.deleted_at IS NULL ' . $and
		);
	}

	public function listFoodsaver(int $regionId, bool $showOnlyInactive = false): array
	{
		$onlyInactiveClause = '';
		if ($showOnlyInactive) {
			$oldestActiveDate = Carbon::now()->subMonths(6)->format('Y-m-d H:i:s');
			$onlyInactiveClause = '
					AND (
							fs.last_login < "' . $oldestActiveDate . '"
							OR
							fs.last_login IS NULL
						)';
		}

		return $this->db->fetchAll('
		    SELECT	fs.id,
					fs.name,
					fs.nachname,
					fs.photo,
					fs.sleep_status,
					CONCAT("#",fs.id) AS href
			 
		    FROM	fs_foodsaver fs
					LEFT JOIN fs_foodsaver_has_bezirk hb
						ON fs.id = hb.foodsaver_id

		    WHERE	fs.deleted_at IS NULL
					AND	hb.bezirk_id = :regionId'
					. $onlyInactiveClause . '
		    
			ORDER BY fs.name ASC
		',
		[':regionId' => $regionId]);
	}

	public function getFoodsaverDetails($fs_id): array
	{
		return $this->db->fetchByCriteria(
			'fs_foodsaver',
			[
				'id',
				'admin',
				'orgateam',
				'bezirk_id',
				'photo',
				'rolle',
				'type',
				'verified',
				'name',
				'nachname',
				'lat',
				'lon',
				'email',
				'token',
				'mailbox_id',
				'option',
				'geschlecht',
				'privacy_policy_accepted_date',
				'privacy_notice_accepted_date'
			],
			['id' => $fsId]
		);
	}

	public function getFoodsaverBasics(int $fsId): array
	{
		if ($fs = $this->db->fetch('
			SELECT 	fs.`name`,
					fs.nachname,
					fs.bezirk_id,
					fs.rolle,
					fs.photo,
					fs.geschlecht,
					fs.stat_fetchweight,
					fs.stat_fetchcount,
					fs.sleep_status,
					fs.id

			FROM 	`fs_foodsaver` fs

			WHERE fs.id = :fsId
		', [':fsId' => $fsId])
		) {
			$fs['bezirk_name'] = '';
			if ($fs['bezirk_id'] > 0) {
				$fs['bezirk_name'] = $this->db->fetchValueByCriteria('fs_bezirk', 'name', ['id' => $fs['bezirk_id']]);
			}

			return $fs;
		}

		return [];
	}

	public function getOne_foodsaver($id)
	{
		$out = $this->db->fetch('
			SELECT
				`id`,
				`bezirk_id`,
				`plz`,
				`stadt`,
				`lat`,
				`lon`,
				`email`,
				`name`,
				`nachname`,
				`anschrift`,
				`telefon`,
				`handy`,
				`geschlecht`,
				`geb_datum`,
				`anmeldedatum`,
				`photo`,
				`about_me_public`,
				`orgateam`,
				`data`,
				`rolle`,
				`position`,
				`homepage`
			FROM 		`fs_foodsaver`
			WHERE 		`id` = :id',
			[':id' => $id]
		);

		$bot = $this->db->fetchAll('
			SELECT `fs_bezirk`.`name`,
				   `fs_bezirk`.`id`
			FROM `fs_bezirk`,
				 fs_botschafter
			WHERE `fs_botschafter`.`bezirk_id` = `fs_bezirk`.`id`
			AND `fs_botschafter`.foodsaver_id = :id',
			[':id' => $id]
		);

		if ($bot) {
			$out['botschafter'] = $bot;
		}

		return $out;
	}

	public function getBotschafter($bezirk_id): array
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`email`,
					fs.`name`,
					fs.`name` AS `vorname`,
					fs.`nachname`,
					fs.`photo`,
					fs.`geschlecht`

			FROM `fs_foodsaver` fs,
			`fs_botschafter`

			WHERE fs.id = `fs_botschafter`.`foodsaver_id`

			AND `fs_botschafter`.`bezirk_id` = :regionId
			AND		fs.deleted_at IS NULL',
			[':regionId' => $bezirk_id]
		);
	}

	public function getBezirkCountForBotschafter($fs_id): int
	{
		return $this->db->count('fs_botschafter', ['foodsaver_id' => $fs_id]);
	}

	public function getAllBotschafter()
	{
		return $this->db->fetchAll('
			SELECT 		fs.`id`,
						fs.`name`,
						fs.`nachname`,
						fs.`geschlecht`,
						fs.`email`

			FROM 		`fs_foodsaver` fs
			WHERE		fs.id
			IN			(SELECT foodsaver_id
						FROM `fs_fs_botschafter` b
						LEFT JOIN `fs_bezirk` bz
						ON b.bezirk_id = bz.id
						WHERE bz.type != 7
						)
			AND		fs.deleted_at IS NULL'
		);
	}

	public function getAllFoodsaver()
	{
		return $this->db->fetchAll('
			SELECT 		fs.id,
						CONCAT(fs.`name`, " ", fs.`nachname`) AS `name`,
						fs.`anschrift`,
						fs.`email`,
						fs.`telefon`,
						fs.`handy`,
						fs.plz

			FROM 		`fs_foodsaver` fs
			WHERE		fs.deleted_at IS NULL AND fs.`active` = 1
		');
	}

	public function getAllFoodsaverNoBotschafter()
	{
		$foodsaver = $this->getAllFoodsaver();
		$out = array();

		$botschafter = $this->getAllBotschafter();
		$bot = array();
		foreach ($botschafter as $b) {
			$bot[$b['id']] = true;
		}

		foreach ($foodsaver as $fs) {
			if (!isset($bot[$fs['id']])) {
				$out[] = $fs;
			}
		}

		return $out;
	}

	public function getOrgateam()
	{
		return $this->db->fetchAll('
			SELECT 		`id`,
						`name`,
						`nachname`,
						`geschlecht`,
						`email`

			FROM 		`fs_foodsaver`

			WHERE 		`orgateam` = 1
		');
	}

	public function getFsMap($bezirk_id)
	{
		return $this->db->fetchAll(
			'SELECT `id`,`lat`,`lon`,CONCAT(`name`," ",`nachname`)
			AS `name`,`plz`,`stadt`,`anschrift`,`photo`
			FROM `fs_foodsaver`
			WHERE `active` = 1
			AND `bezirk_id` = :regionId
			AND `lat` != "" ',
			[':regionId' => $bezirk_id]
		);
	}

	public function xhrGetTagFsAll($bezirk_ids): array
	{
		return $this->db->fetchAll('
			SELECT	DISTINCT fs.`id`,
					CONCAT(fs.`name`," ",fs.`nachname`," (",fs.`id`,")") AS value

			FROM 	fs_foodsaver fs,
					fs_foodsaver_has_bezirk hb
			WHERE 	hb.foodsaver_id = fs.id
			AND 	hb.bezirk_id IN(' . implode(',', $bezirk_ids) . ')
			AND		fs.deleted_at IS NULL
		');
	}

	public function xhrGetFoodsaver($data): array
	{
		if (isset($data['bid'])) {
			throw new Exception('filterung by bezirkIds is not supported anymore');
		}

		$term = $data['term'];
		$term = trim($term);
		$term = preg_replace('/[^a-zA-ZäöüÖÜß]/', '', $term);
		$term = $term . '%';

		if (strlen($term) > 2) {
			$out = $this->db->fetchAll('
				SELECT		`id`,
							CONCAT_WS(" ", `name`, `nachname`, CONCAT("(", `id`, ")")) AS value
				FROM 		fs_foodsaver
				WHERE 		((`name` LIKE :term
				OR 			`nachname` LIKE :term2))
				AND			deleted_at IS NULL
			', [':term' => $term, ':term2' => $term]);

			return $out;
		}

		return array();
	}

	public function getEmailAddress(int $fsId): string
	{
		return $this->db->fetchValueByCriteria('fs_foodsaver',
			'email',
			['id' => $fsId]
		);
	}

	public function getEmailAdressen($region_ids)
	{
		$placeholders = $this->db->generatePlaceholders(count($region_ids));

		return $this->db->fetchAll('
				SELECT 	`id`,
						`name`,
						`nachname`,
						`email`,
						`geschlecht`

				FROM 	`fs_foodsaver`

				WHERE 	`bezirk_id` IN(' . $placeholders . ')
				AND		deleted_at IS NULL',
				$region_ids
			);
	}

	public function getAllEmailFoodsaver($newsletter = false, $only_foodsaver = true)
	{
		if ($only_foodsaver) {
			$min_rolle = Role::FOODSAVER;
		} else {
			$min_rolle = Role::FOODSHARER;
		}
		$where = "WHERE rolle >= $min_rolle";
		if ($newsletter !== false) {
			$where = "WHERE newsletter = 1 AND rolle >= $min_rolle";
		}

		return $this->db->fetchAll('
				SELECT 	`id`,`email`
				FROM `fs_foodsaver`
				' . $where . ' AND active = 1
				AND	deleted_at IS NULL
		');
	}

	public function getEmailBotFromBezirkList($bezirklist)
	{
		$list = array();
		foreach ($bezirklist as $i => $b) {
			if ($b > 0) {
				$list[$b] = $b;
			}
		}
		ksort($list);

		$query = array();
		foreach ($list as $b) {
			$query[] = (int)$b;
		}

		$foodsaver = $this->db->fetchAll('
			SELECT 			fs.`id`,
							fs.`name`,
							fs.`nachname`,
							fs.`geschlecht`,
							fs.`email`

			FROM 	`fs_foodsaver` fs,
					`fs_botschafter` b

			WHERE 	b.foodsaver_id = fs.id
			AND		b.`bezirk_id`  IN(' . implode(',', $query) . ')
			AND		fs.deleted_at IS NULL;
		');

		$out = array();
		foreach ($foodsaver as $fs) {
			$out[$fs['id']] = $fs;
		}

		return $out;
	}

	public function getEmailFoodSaverFromBezirkList($bezirklist)
	{
		$list = array();
		foreach ($bezirklist as $i => $b) {
			if ($b > 0) {
				$list[$b] = $b;
			}
		}
		ksort($list);

		$query = array();
		foreach ($list as $b) {
			$query[] = (int)$b;
		}

		$foodsaver = $this->db->fetchAll('
			SELECT 			fs.`id`,
							fs.`name`,
							fs.`nachname`,
							fs.`geschlecht`,
							fs.`email`

			FROM 	`fs_foodsaver` fs,
					`fs_foodsaver_has_bezirk` b

			WHERE 	b.foodsaver_id = fs.id
			AND		b.`bezirk_id` IN(' . implode(',', $query) . ')
			AND		fs.deleted_at IS NULL;
		');

		$out = array();
		foreach ($foodsaver as $fs) {
			$out[$fs['id']] = $fs;
		}

		return $out;
	}

	public function updateGroupMembers(int $bezirk, array $foodsaver_ids, bool $leave_admins)
	{
		$rows_ins = 0;
		if ($leave_admins) {
			$admins = $this->db->fetchAllValues('SELECT foodsaver_id FROM `fs_botschafter` b WHERE b.bezirk_id = ' . $bezirk);
			if ($admins) {
				$foodsaver_ids = array_merge($foodsaver_ids, $admins);
			}
		}
		$ids = implode(',', array_map('intval', $foodsaver_ids));
		$this->forumFollowerGateway->deleteForumSubscriptions($bezirk, $foodsaver_ids, false);
		if ($ids) {
			$rows_del = $this->db->execute('DELETE FROM `fs_foodsaver_has_bezirk` WHERE bezirk_id = ' . $bezirk . ' AND foodsaver_id NOT IN (' . $ids . ')')->rowCount();
			$insert_strings = array_map(function ($id) use ($bezirk) {
				return '(' . $id . ',' . $bezirk . ',1,NOW())';
			}, $foodsaver_ids);
			$insert_values = implode(',', $insert_strings);
			$rows_ins = $this->db->execute('INSERT IGNORE INTO `fs_foodsaver_has_bezirk` (foodsaver_id, bezirk_id, active, added) VALUES ' . $insert_values)->rowCount();
		} else {
			$rows_del = $this->db->delete('fs_foodsaver_has_bezirk', ['bezirk_id' => $bezirk]);
		}

		return array($rows_ins, $rows_del);
	}

	public function listFoodsaverByRegion(int $regionId)
	{
		$res = $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`photo`,
					fs.`name`,
					fs.sleep_status

			FROM 	`fs_foodsaver` fs,
					`fs_foodsaver_has_bezirk` c

			WHERE 	c.`foodsaver_id` = fs.id
			AND     fs.deleted_at IS NULL
			AND 	c.bezirk_id = :id
			AND 	c.active = 1
			ORDER BY fs.`name`
		', ['id' => $regionId]);

		return array_map(function ($fs) {
			if ($fs['photo']) {
				$image = '/images/50_q_' . $fs['photo'];
			} else {
				$image = '/img/50_q_avatar.png';
			}

			return [
				'user' => [
					'id' => $fs['id'],
					'name' => $fs['name'],
					'sleep_status' => $fs['sleep_status']
				],
				'size' => 50,
				'imageUrl' => $image
			];
		}, $res);
	}

	public function listActiveWithFullNameByRegion($id)
	{
		return $this->db->fetchAll('

			SELECT 	fs.id,
					CONCAT(fs.`name`, " ", fs.`nachname`) AS `name`,
					fs.`name` AS vorname,
					fs.`anschrift`,
					fs.`email`,
					fs.`telefon`,
					fs.`handy`,
					fs.`plz`,
					fs.`geschlecht`

			FROM 	fs_foodsaver_has_bezirk fb,
					`fs_foodsaver` fs

			WHERE 	fb.foodsaver_id = fs.id
			AND 	fb.bezirk_id = :id
			AND 	fb.`active` = 1
			AND		fs.deleted_at IS NULL
		', ['id' => $id]);
	}

	public function listAmbassadorsByRegion($id)
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`photo`,
					fs.`name`,
					fs.`nachname`,
					fs.sleep_status

			FROM 	`fs_foodsaver` fs,
					`fs_botschafter` c

			WHERE 	c.`foodsaver_id` = fs.id
			AND     fs.deleted_at IS NULL
			AND 	c.bezirk_id = :id
		', ['id' => $id]);
	}

	/* retrieves the list of all bots for given bezirk or sub bezirk */
	public function getBotIds($bezirk, $include_bezirk_bot = true, $include_group_bot = false)
	{
		$where_type = '';
		if (!$include_bezirk_bot) {
			$where_type = 'bz.type = 7';
		} elseif (!$include_group_bot) {
			$where_type = 'bz.type <> 7';
		}

		return $this->db->fetchAllValues('SELECT DISTINCT bot.foodsaver_id FROM `fs_bezirk_closure` c
			LEFT JOIN `fs_bezirk` bz ON bz.id = c.bezirk_id
			INNER JOIN `fs_botschafter` bot ON bot.bezirk_id = c.bezirk_id
			INNER JOIN `fs_foodsaver` fs ON fs.id = bot.foodsaver_id
			WHERE c.ancestor_id = ' . (int)$bezirk . ' AND fs.deleted_at IS NULL AND ' . $where_type);
	}

	public function del_foodsaver(int $id)
	{
		$this->db->update('fs_foodsaver', ['password' => null, 'deleted_at' => $this->db->now()], ['id' => $id]);

		$this->db->execute('
			INSERT INTO fs_foodsaver_archive
			(
				SELECT * FROM fs_foodsaver WHERE id = ' . $id . '
			)
		');

		$this->db->delete('fs_apitoken', ['foodsaver_id' => $id]);
		$this->db->delete('fs_application_has_wallpost', ['application_id' => $id]);
		$this->db->delete('fs_basket_anfrage', ['foodsaver_id' => $id]);
		$this->db->delete('fs_botschafter', ['foodsaver_id' => $id]);
		$this->db->execute('
            DELETE FROM fs_buddy
            WHERE foodsaver_id = ' . (int)$id . ' OR buddy_id = ' . $id . '
        ');
		$this->db->delete('fs_email_status', ['foodsaver_id' => $id]);
		$this->db->delete('fs_fairteiler_follower', ['foodsaver_id' => $id]);
		$this->db->delete('fs_foodsaver_has_bell', ['foodsaver_id' => $id]);
		$this->db->delete('fs_foodsaver_has_bezirk', ['foodsaver_id' => $id]);
		$this->db->delete('fs_foodsaver_has_contact', ['foodsaver_id' => $id]);
		$this->db->delete('fs_foodsaver_has_event', ['foodsaver_id' => $id]);
		$this->db->delete('fs_foodsaver_has_wallpost', ['foodsaver_id' => $id]);
		$this->db->delete('fs_mailbox_member', ['foodsaver_id' => $id]);
		$this->db->delete('fs_mailchange', ['foodsaver_id' => $id]);
		$this->db->execute('
            DELETE FROM fs_pass_gen
            WHERE foodsaver_id = ' . (int)$id . ' OR bot_id = ' . $id . '
        ');
		$this->db->delete('fs_pass_request', ['foodsaver_id' => $id]);
		$this->db->delete('fs_quiz_session', ['foodsaver_id' => $id]);
		$this->db->delete('fs_theme_follower', ['foodsaver_id' => $id]);

		// remove bananas given to or by this user
		$this->db->delete('fs_rating', ['foodsaver_id' => $id]);
		$this->db->delete('fs_rating', ['rater_id' => $id]);

		$this->db->update('fs_foodsaver',
			[
				'verified' => 0,
				'rolle' => 0,
				'plz' => null,
				'stadt' => null,
				'lat' => null,
				'lon' => null,
				'photo' => null,
				'email' => null,
				'password' => null,
				'name' => null,
				'nachname' => null,
				'anschrift' => null,
				'telefon' => null,
				'handy' => null,
				'geb_datum' => null,
				'deleted_at' => $this->db->now()
			],
			['id' => $id]);
	}

	public function getFsAutocomplete($bezirk_id)
	{
		$and = 'AND fb.`bezirk_id` = ' . (int)$bezirk_id . '';
		if (is_array($bezirk_id)) {
			if (is_array(end($bezirk_id))) {
				$tmp = $bezirk_id;
				$bezirk_id = array();
				foreach ($tmp as $b) {
					$bezirk_id[$b['id']] = $b['id'];
				}
			}

			$and = 'AND fb.`bezirk_id` IN(' . implode(',', $bezirk_id) . ')';
		}

		return $this->db->fetchAll('
			SELECT DISTINCT
						fs.id,
						CONCAT(fs.`name`, " ", fs.`nachname`, " (",fs.`id`,")") AS value

			FROM 		fs_foodsaver_has_bezirk fb,
						`fs_foodsaver` fs

			WHERE 		fb.foodsaver_id = fs.id
			AND			fs.deleted_at IS NULL ' . $and
		);
	}

	public function updateProfile($fs_id, $data)
	{
		$fields = [
			'bezirk_id',
			'plz',
			'lat',
			'lon',
			'stadt',
			'anschrift',
			'telefon',
			'handy',
			'geb_datum',
			'about_me_public',
			'homepage',
			'position'
		];

		$fieldsToStripTags = [
			'plz',
			'lat',
			'lon',
			'stadt',
			'anschrift',
			'telefon',
			'handy',
			'about_me_public',
			'homepage',
			'position'
		];

		$clean_data = [];
		foreach ($fields as $field) {
			if (!array_key_exists($field, $data)) {
				continue;
			}
			$clean_data[$field] = in_array($field, $fieldsToStripTags, true) ? strip_tags($data[$field]) : $data[$field];
		}

		$this->db->update(
			'fs_foodsaver',
			$clean_data,
			['id' => $fs_id]
		);

		return true;
	}

	public function updatePhoto($fs_id, $photo)
	{
		$this->db->update(
			'fs_foodsaver',
			['photo' => strip_tags($photo)],
			['id' => $fs_id]
	);
	}

	public function getPhoto($fs_id)
	{
		return $this->db->fetchValueByCriteria('fs_foodsaver', 'photo', ['id' => $fs_id]);
	}

	public function emailExists($email)
	{
		return $this->db->exists('fs_foodsaver', ['email' => $email]);
	}

	/**
	 * set option is an key value store each var is avalable in the user session.
	 *
	 * @param string $key
	 * @param $val
	 */
	public function setOption($fs_id, $key, $val)
	{
		$options = array();
		if ($opt = $this->db->fetchValueByCriteria('fs_foodsaver', 'option', ['id' => $fs_id])) {
			$options = unserialize($opt);
		}

		$options[$key] = $val;

		return $this->db->update('fs_foodsaver', ['option' => serialize($options)], ['id' => $fs_id]);
	}

	public function deleteFromRegion(int $bezirk_id, int $foodsaver_id): void
	{
		$this->db->delete('fs_botschafter', ['bezirk_id' => $bezirk_id, 'foodsaver_id' => $foodsaver_id]);
		$this->db->delete('fs_foodsaver_has_bezirk', ['bezirk_id' => $bezirk_id, 'foodsaver_id' => $foodsaver_id]);

		$this->forumFollowerGateway->deleteForumSubscription($bezirk_id, $foodsaver_id);

		$mainRegion_id = $this->db->fetchValueByCriteria('fs_foodsaver', 'bezirk_id', ['id' => $foodsaver_id]);
		if ($mainRegion_id === $bezirk_id) {
			$this->db->update('fs_foodsaver', ['bezirk_id' => 0], ['id' => $foodsaver_id]);
		}
	}

	public function setQuizRole(int $fsId, int $quizRole): int
	{
		return $this->db->update(
			'fs_foodsaver',
			['quiz_rolle' => $quizRole],
			['id' => $fsId]
		);
	}

	public function riseRole(int $fsId, int $newRoleId): void
	{
		$this->db->update(
			'fs_foodsaver',
			['rolle' => $newRoleId],
			[
				'id' => $fsId,
				'rolle <' => $newRoleId
			]
		);
	}

	public function loadFoodsaver(int $foodsaverId): array
	{
		return $this->db->fetch('
			SELECT
				id,
				name,
				nachname,
				photo,
				rolle,
				geschlecht,
				last_login
			FROM
				fs_foodsaver
			WHERE
				id = :fsId
            AND
                deleted_at IS NULL
		', [':fsId' => $foodsaverId]);
	}

	public function getFoodsaverAddress(int $foodsaverId): array
	{
		return $this->db->fetchByCriteria(
			'fs_foodsaver',
			[
				'plz',
				'stadt',
				'lat',
				'lon',
				'anschrift',
			],
			['id' => $foodsaverId]
		);
	}

	public function getSubscriptions(int $fsId): array
	{
		return $this->db->fetchByCriteria(
			'fs_foodsaver',
			[
				'infomail_message',
				'newsletter'
			],
			['id' => $fsId]
		);
	}

	/**
	 * Returns the first name of the foodsaver.
	 */
	public function getFoodsaverName($foodsaverId): string
	{
		return $this->db->fetchValueByCriteria('fs_foodsaver', 'name', ['id' => $foodsaverId]);
	}
}
