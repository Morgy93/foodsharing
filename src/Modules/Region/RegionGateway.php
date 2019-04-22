<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;

class RegionGateway extends BaseGateway
{
	private $foodsaverGateway;

	public function __construct(
		Database $db,
		FoodsaverGateway $foodsaverGateway
	) {
		parent::__construct($db);
		$this->foodsaverGateway = $foodsaverGateway;
	}

	public function getBezirk($id)
	{
		if ($id == 0) {
			return null;
		}

		return $this->db->fetch('
			SELECT 	`name`,
					`id`,
					`email`,
					`email_name`,
					`has_children`,
					`parent_id`,
					`mailbox_id`
			FROM 	`fs_bezirk`
			WHERE 	`id` = :id',
			[':id' => $id]
		);
	}

	public function getOne_bezirk($id)
	{
		$out = $this->db->fetch('
			SELECT
			`id`,
			`parent_id`,
			`has_children`,
			`name`,
			`email`,
			`email_pass`,
			`email_name`,
			`type`,
			`master`,
			`mailbox_id`

			FROM 		`fs_bezirk`

			WHERE 		`id` = ' . (int)$id);
		$out['botschafter'] = $this->db->fetchAll('
				SELECT 		`fs_foodsaver`.`id`,
							CONCAT(`fs_foodsaver`.`name`," ",`fs_foodsaver`.`nachname`) AS name

				FROM 		`fs_botschafter`,
							`fs_foodsaver`

				WHERE 		`fs_foodsaver`.`id` = `fs_botschafter`.`foodsaver_id`
				AND 		`fs_botschafter`.`bezirk_id` = ' . (int)$id . '
			');

		$out['foodsaver'] = $this->db->fetchAllValues('
				SELECT 		`foodsaver_id`

				FROM 		`fs_botschafter`
				WHERE 		`bezirk_id` = ' . (int)$id . '
			');

		return $out;
	}

	public function getMailBezirk($id)
	{
		return $this->db->fetch('
			SELECT
			`id`,
			`name`,
			`email`,
			`email_name`,
			`email_pass`

			FROM 		`fs_bezirk`

			WHERE 		`id` = ' . (int)$id);
	}

	public function listRegionsIncludingParents($region_id): array
	{
		$stm = 'SELECT DISTINCT ancestor_id FROM `fs_bezirk_closure` WHERE bezirk_id IN (' . implode(',', array_map('intval', $region_id)) . ')';

		return $this->db->fetchAllValues($stm);
	}

	public function getBasics_bezirk()
	{
		return $this->db->fetchAll('
			SELECT 	 	`id`,
						`name`

			FROM 		`fs_bezirk`
			ORDER BY `name`');
	}

	public function listRegionsForFoodsaver($fsId)
	{
		return $this->db->fetchAll('
			SELECT 	b.`id`,
					b.name,
					b.type,
					b.`master`

			FROM 	`fs_foodsaver_has_bezirk` hb,
					`fs_bezirk` b

			WHERE 	hb.bezirk_id = b.id
			AND 	`foodsaver_id` = :id
			AND 	hb.active = 1

			ORDER BY b.name',
			[':id' => $fsId]
		);
	}

	public function getBezirkByParent($parent_id, $include_orga = false)
	{
		$sql = 'AND 		`type` != 7';
		if ($include_orga) {
			$sql = '';
		}

		return $this->db->fetchAll('
			SELECT
				`id`,
				`name`,
				`has_children`,
				`parent_id`,
				`type`,
				`master`
			FROM 		`fs_bezirk`
			WHERE 		`parent_id` = :id
			AND id != 0
			' . $sql . '
			ORDER BY 	`name`',
			[':id' => $parent_id]
		);
	}

	public function listIdsForFoodsaverWithDescendants($fs_id)
	{
		$bezirk_ids = [];
		foreach ($this->listForFoodsaver($fs_id) as $bezirk) {
			$bezirk_ids += $this->listIdsForDescendantsAndSelf($bezirk['id']);
		}

		return $bezirk_ids;
	}

	/**
	 * @param $fsId
	 * @param $regionId
	 *
	 * @return bool true when the given user is active (an accepted member) in the given region
	 */
	public function hasMember($fsId, $regionId)
	{
		return $this->db->exists('fs_foodsaver_has_bezirk', ['bezirk_id' => $regionId, 'foodsaver_id' => $fsId, 'active' => 1]);
	}

	/**
	 * @param $fsId
	 * @param $regionId
	 *
	 * @return bool true when the given user is an admin/ambassador for the given group/region
	 */
	public function isAdmin($fsId, $regionId)
	{
		return $this->db->exists('fs_botschafter', ['bezirk_id' => $regionId, 'foodsaver_id' => $fsId]);
	}

	public function listForFoodsaver($fs_id): array
	{
		$values = $this->db->fetchAll(
			'							
			SELECT 	b.`id`,
					b.name,
					b.type
			
			FROM 	`fs_foodsaver_has_bezirk` hb,
					`fs_bezirk` b
			
			WHERE 	hb.bezirk_id = b.id
			AND 	`foodsaver_id` = :fs_id
			AND 	hb.active = 1
			
			ORDER BY b.name',
			[':fs_id' => $fs_id]
		);

		$output = [];
		foreach ($values as $v) {
			$output[$v['id']] = $v;
		}

		return $output;
	}

	public function getFsRegionIds($foodsaver_id): array
	{
		return $this->db->fetchAllValues('
			SELECT 	`bezirk_id`
			FROM 	`fs_foodsaver_has_bezirk`
			WHERE 	`foodsaver_id` = :fs_id
		', [':fs_id' => $foodsaver_id]);
	}

	public function listIdsForDescendantsAndSelf($bid, $includeSelf = true)
	{
		if ((int)$bid == 0) {
			return [];
		}
		if ($includeSelf) {
			$minDepth = 0;
		} else {
			$minDepth = 1;
		}

		return $this->db->fetchAllValues(
			'SELECT bezirk_id FROM `fs_bezirk_closure` WHERE ancestor_id = :bid AND depth >= :min_depth',
			['bid' => $bid, 'min_depth' => $minDepth]
		);
	}

	public function listForFoodsaverExceptWorkingGroups($fs_id)
	{
		return $this->db->fetchAll('
			SELECT
				b.`id`,
				b.`name`,
				b.`teaser`,
				b.`photo`

			FROM
				fs_bezirk b,
				fs_foodsaver_has_bezirk hb

			WHERE
				hb.bezirk_id = b.id

			AND
				hb.`foodsaver_id` = :fs_id

			AND
				b.`type` != 7

			ORDER BY
				b.`name`
		', ['fs_id' => $fs_id]);
	}

	public function getRegionDetails($id)
	{
		$bezirk = $this->db->fetch('
			SELECT
				`id`,
				`name`,
				`email`,
				`email_name`,
				`type`,
				`stat_fetchweight`,
				`stat_fetchcount`,
				`stat_fscount`,
				`stat_botcount`,
				`stat_postcount`,
				`stat_betriebcount`,
				`stat_korpcount`,
				`moderated`

			FROM 	`fs_bezirk`

			WHERE 	`id` = :id
			LIMIT 1
		', ['id' => $id]);

		$bezirk['foodsaver'] = $this->foodsaverGateway->listActiveByRegion($id);

		$bezirk['sleeper'] = $this->foodsaverGateway->listInactiveByRegion($id);

		$bezirk['fs_count'] = count($bezirk['foodsaver']);

		$bezirk['botschafter'] = $this->foodsaverGateway->listAmbassadorsByRegion($id);

		return $bezirk;
	}

	public function getType($id)
	{
		$bezirkType = $this->db->fetchValue('
			SELECT
				`type`
			FROM 	`fs_bezirk`

			WHERE 	`id` = :id
			LIMIT 1
		', ['id' => $id]);

		return $bezirkType;
	}

	public function listRequests($id)
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`name`,
					fs.`nachname`,
					fs.`photo`,
					fb.application,
					fb.active,
					UNIX_TIMESTAMP(fb.added) AS `time`

			FROM 	`fs_foodsaver_has_bezirk` fb,
					`fs_foodsaver` fs

			WHERE 	fb.foodsaver_id = fs.id
			AND 	fb.bezirk_id = :id
			AND 	fb.active = 0
		', ['id' => $id]);
	}

	public function acceptBezirkRequest($fsid, $bid)
	{
		return $this->db->update(
			'fs_foodsaver_has_bezirk',
					['active' => 1, 'add' => date('Y-m-d H:i:s')],
					['bezirk_id' => $bid, 'foodsaver_id' => $fsid]
		);
	}

	public function linkBezirk($fsid, $bid, $active = 1)
	{
		$this->db->execute('
			REPLACE INTO `fs_foodsaver_has_bezirk`
			(
				`bezirk_id`,
				`foodsaver_id`,
				`added`,
				`active`
			)
			VALUES
			(
				' . (int)$bid . ',
				' . (int)$fsid . ',
				NOW(),
				' . (int)$active . '
			)
		');
	}

	public function update_bezirkNew($id, $data)
	{
		$bezirk_id = (int)$id;
		if (isset($data['botschafter']) && is_array($data['botschafter'])) {
			$this->db->delete('fs_botschafter', ['bezirk_id' => $id]);
			$master = 0;
			if (isset($data['master'])) {
				$master = (int)$data['master'];
			}
			foreach ($data['botschafter'] as $foodsaver_id) {
				$this->db->insert('fs_botschafter', [
					'bezirk_id' => $id,
					'foodsaver_id' => $foodsaver_id
				]);
			}
		}

		$this->db->beginTransaction();

		if ((int)$data['parent_id'] > 0) {
			$this->db->update('fs_bezirk', ['has_children' => 1], ['id' => $data['parent_id']]);
		}

		$has_children = 0;
		if ($this->db->exists('fs_bezirk', ['parent_id' => $id])) {
			$has_children = 1;
		}

		$this->db->update(
			'fs_bezirk',
			[
				'name' => strip_tags($data['name']),
				'email_name' => strip_tags($data['email_name']),
				'parent_id' => $data['parent_id'],
				'type' => $data['type'],
				'master' => $master,
				'has_children' => $has_children,
			],
			['id' => $id]
		);

		$this->db->execute('DELETE a FROM `fs_bezirk_closure` AS a JOIN `fs_bezirk_closure` AS d ON a.bezirk_id = d.bezirk_id LEFT JOIN `fs_bezirk_closure` AS x ON x.ancestor_id = d.ancestor_id AND x.bezirk_id = a.ancestor_id WHERE d.ancestor_id = ' . (int)$bezirk_id . ' AND x.ancestor_id IS NULL');
		$this->db->execute('INSERT INTO `fs_bezirk_closure` (ancestor_id, bezirk_id, depth) SELECT supertree.ancestor_id, subtree.bezirk_id, supertree.depth+subtree.depth+1 FROM `fs_bezirk_closure` AS supertree JOIN `fs_bezirk_closure` AS subtree WHERE subtree.ancestor_id = ' . (int)$bezirk_id . ' AND supertree.bezirk_id = ' . (int)(int)$data['parent_id']);
		$this->db->commit();
	}

	public function denyBezirkRequest($fsid, $bid)
	{
		$this->db->delete('fs_foodsaver_has_bezirk', [
			'bezirk_id' => $bid,
			'foodsaver_id' => $fsid,
		]);
	}

	public function add_bezirk($data)
	{
		$this->db->beginTransaction();

		$id = $this->db->insert('fs_bezirk', [
			'parent_id' => (int)$data['parent_id'],
			'has_children' => (int)$data['has_children'],
			'name' => strip_tags($data['name']),
			'email' => strip_tags($data['email']),
			'email_pass' => strip_tags($data['email_pass']),
			'email_name' => strip_tags($data['email_name'])
		]);

		$this->db->execute('INSERT INTO `fs_bezirk_closure` (ancestor_id, bezirk_id, depth) SELECT t.ancestor_id, ' . $id . ', t.depth+1 FROM `fs_bezirk_closure` AS t WHERE t.bezirk_id = ' . (int)$data['parent_id'] . ' UNION ALL SELECT ' . $id . ', ' . $id . ', 0');
		$this->db->commit();

		if (isset($data['foodsaver']) && is_array($data['foodsaver'])) {
			foreach ($data['foodsaver'] as $foodsaver_id) {
				$this->db->insert('fs_botschafter', [
					'bezirk_id' => (int)$id,
					'foodsaver_id' => (int)$foodsaver_id
				]);
				$this->db->insert('fs_foodsaver_has_bezirk', [
					'bezirk_id' => (int)$id,
					'foodsaver_id' => (int)$foodsaver_id
				]);
			}
		}

		return $id;
	}

	public function getBezirkName($bezirk_id)
	{
		return $this->db->fetchValue('SELECT `name` FROM `fs_bezirk` WHERE `id` = :id', [':id' => $bezirk_id]);
	}

	public function addMember($fsId, $regionId)
	{
		$this->db->insertIgnore('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $fsId,
			'bezirk_id' => $regionId,
			'active' => 1,
			'added' => $this->db->now()
		]);
	}

	public function getMasterId($regionId)
	{
		return $this->db->fetchValueByCriteria('fs_bezirk', 'master', ['id' => $regionId]);
	}

	public function listRegionsForBotschafter($fsId)
	{
		return $this->db->fetchAll(
	'SELECT 	`fs_botschafter`.`bezirk_id`,
					`fs_bezirk`.`has_children`,
					`fs_bezirk`.`parent_id`,
					`fs_bezirk`.name,
					`fs_bezirk`.id,
					`fs_bezirk`.type

			FROM 	`fs_botschafter`,
					`fs_bezirk`

			WHERE 	`fs_bezirk`.`id` = `fs_botschafter`.`bezirk_id`

			AND 	`fs_botschafter`.`foodsaver_id` = :id',
			[':id' => $fsId]
		);
	}

	public function addOrUpdateMember($fsId, $regionId)
	{
		return $this->db->insertOrUpdate('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $fsId,
			'bezirk_id' => $regionId,
			'active' => 1,
			'added' => $this->db->now()
		]);
	}

	public function updateMasterRegions(array $regionIds, int $masterId): void
	{
		$this->db->update('fs_bezirk', ['master' => $masterId], ['id' => $regionIds]);
	}

	public function genderCountRegion($bezirkid)
	{
		return $this->db->fetchAll(
			'select  geschlecht as gender,
						   count(*) as NumberOfGender
					from fs_foodsaver_has_bezirk fb
		 			left outer join fs_foodsaver fs on fb.foodsaver_id=fs.id
					where fb.bezirk_id = :id
					group by geschlecht',
			[':id' => $bezirkid]
		);
	}

	public function genderCountHomeRegion($bezirkid)
	{
		return $this->db->fetchAll(
			'select  geschlecht as gender,
						   count(*) as NumberOfGender
					from fs_foodsaver_has_bezirk fb
		 			left outer join fs_foodsaver fs on fb.foodsaver_id=fs.id
					where fs.bezirk_id = :id
					group by geschlecht',
			[':id' => $bezirkid]
		);
	}

	public function regionPickupsDaily($bezirkid)
	{
		return $this->db->fetchAll(
			'SELECT
						date_format(date,\'%Y-%m-%d\') as time,
						count(distinct date, betrieb_id) as NumberOfAppointments ,
						count(*) as NumberOfSlots,
						count(distinct foodsaver_id) as NumberOfFoodsavers
					from fs_abholer a
					left outer join fs_betrieb b on a.betrieb_id = b.id
						where
								b.bezirk_id = :id
						group by
							DATE_FORMAT(date,GET_FORMAT(DATE,\'INTERNAL\')),
							b.bezirk_id
						ORDER BY date  DESC',
			[':id' => $bezirkid]
		);
	}

	public function regionPickupsWeekly($bezirkid)
	{
		return $this->db->fetchAll(
			'select 
       					date_format(date, \'%Y-%v\') as time,
						count(distinct date,betrieb_id) as NumberOfAppointments ,
						count(*) as NumberOfSlots,
						count(distinct foodsaver_id) as NumberOfFoodsavers
					from fs_abholer a 
					left outer join fs_betrieb b on a.betrieb_id = b.id
					where b.bezirk_id = :id
					group by yearweek(date,1)
					order by date desc',
			[':id' => $bezirkid]
		);
	}

	public function regionPickupsMonthly($bezirkid)
	{
		return $this->db->fetchAll(
			'select 
						date_Format(date,\'%Y-%m\') as time,
						count(distinct date, betrieb_id) as NumberOfAppointments ,
						count(*) as NumberOfSlots,
						count(distinct foodsaver_id) as NumberOfFoodsavers
					from fs_abholer a 
					left outer join fs_betrieb b on a.betrieb_id = b.id
					where b.bezirk_id = :id
					group by date_Format(date,\'%Y-%m\')
					order by date desc',
			[':id' => $bezirkid]
		);
	}
}
