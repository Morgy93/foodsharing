<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\Milestone;
use Foodsharing\Modules\Core\DBConstants\StoreTeam\MembershipStatus;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\DTO\CreateStoreData;
use Foodsharing\Modules\Store\DTO\Store;

class StoreGateway extends BaseGateway
{
	private RegionGateway $regionGateway;

	private const LINKED_TABLES = [
		'r' => ['name' => 'fs_bezirk', 'link' => 'r.id = s.bezirk_id'],
		't' => ['name' => 'fs_betrieb_team', 'link' => 't.betrieb_id = s.id'],
		'c' => ['name' => 'fs_kette', 'link' => 'c.id = s.kette_id'],
	];

	private const DETAILS_COLUMNS = [ // TODO add way to reference other keys easily
		'id' => ['s.id'],
		'name' => ['s.name'],
		'store_status_id' => ['s.betrieb_status_id AS store_status_id'],
		'coords' => ['s.lat', 's.lon'],
		'address' => ['s.str', 's.hsnr', 'CONCAT(s.str, " ",s.hsnr) AS address', 's.stadt AS city', 's.plz AS zip'],
		'contact' => ['s.ansprechpartner', 's.telefon', 's.email', 's.fax'],
		'region_id' => ['s.bezirk_id AS region_id'],
		'region' => ['s.bezirk_id AS region_id', 'r.name AS region_name'],
		'chain_id' => ['s.kette_id AS chain_id'],
		'store_category_id' => ['s.betrieb_kategorie_id AS store_category_id'],
		'chain_logo' => ['c.logo AS chain_logo'],
		'managing' => ['t.verantwortlich AS managing'],
		'membership_status' => ['t.active AS membership_status', 't.verantwortlich AS managing'],
		'added' => ['s.added'],
	];

	private const STATUS_PARAMS = [
		'applicant_user_status' => MembershipStatus::APPLIED_FOR_TEAM,
		'member_user_status' => MembershipStatus::MEMBER,
		'jumper_user_status' => MembershipStatus::JUMPER,
		'starting_store_status' => CooperationStatus::COOPERATION_STARTING,
		'unwilling_store_status' => CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US,
		'established_store_status' => CooperationStatus::COOPERATION_ESTABLISHED,
		'unneeded_store_status' => CooperationStatus::GIVES_TO_OTHER_CHARITY,
		'closed_store_status' => CooperationStatus::PERMANENTLY_CLOSED,
	];

	public function __construct(
		Database $db,
		RegionGateway $regionGateway
	) {
		parent::__construct($db);

		$this->regionGateway = $regionGateway;
	}

	public function getStores(?array $details = [], ?array $options = [])
	{
		$use_grouping = isset($options['foodsaver']) && ($options['group_by_role'] ?? in_array('group_by_role', $options));

		// find the columns to fetch
		$columns = (array)array_map(fn ($key) => self::DETAILS_COLUMNS[$key], array_merge(['id'], $details, $use_grouping ? ['membership_status'] : []));
		$columns = array_unique(array_merge(...$columns));

		$wheres = [];
		$params = [];

		// 'foodsaver' and 'user_involvement' options
		if (isset($options['foodsaver'])) {
			$wheres[] = 't.foodsaver_id = :foodsaver_id';
			$params['foodsaver_id'] = $options['foodsaver'];
			$user_involvement = $options['user_involvement'] ?? 'team';
			$wheres[] = [
				'any' => '', // empty where clasuses get deleted later
				'applicant' => 't.active = :applicant_user_status',
				'jumper' => 't.active = :jumper_user_status',
				'team' => 't.active IN (:member_user_status, :jumper_user_status)',
				'member' => 't.active = :member_user_status',
				'manager' => 't.verantwortlich = 1',
			][$user_involvement];
		}

		// 'region' and 'include_subregions' options
		if (isset($options['region'])) {
			$include_subregions = $options['include_subregions'] ?? in_array('include_subregions', $options);
			$region_ids = $include_subregions ?
				$this->regionGateway->listIdsForDescendantsAndSelf($options['region'], true, false) :
				[$options['region']];
			$wheres[] = 's.bezirk_id IN (' . implode(',', $region_ids) . ')';
		}

		// 'cooperation_status' option
		$cooperation_status = $options['cooperation_status'] ?? 'existing';
		$wheres[] = [
			'any' => '', // empty where clasuses get deleted later
			'existing' => 's.betrieb_status_id <> :closed_store_status',
			'cooperating' => 's.betrieb_status_id IN (:starting_store_status, :established_store_status)',
			'candidates' => 's.betrieb_status_id NOT IN (:closed_store_status, :unwilling_store_status, :unneeded_store_status)'
		][$cooperation_status];

		// 'required' option
		if (isset($options['required'])) {
			$wheres = array_merge($wheres, array_map(fn ($col) => $col . ' IS NOT NULL AND ' . $col . ' != ""', $options['required']));
		}

		// 'order' option
		$order = isset($options['order']) ? 'ORDER BY ' . $options['order'] : '';

		// Find required tables
		$shorthands = array_map(function ($col) {
			preg_match_all('/(\w+)\.\w+/', $col, $matches);

			return $matches[1];
		}, array_merge($columns, $wheres));
		$shorthands = array_diff(array_unique(array_merge(...$shorthands)), ['s']);
		$joins = array_map(
			fn ($shorthand) => 'LEFT JOIN ' . self::LINKED_TABLES[$shorthand]['name'] . ' ' . $shorthand
				. ' ON ' . self::LINKED_TABLES[$shorthand]['link'],
			$shorthands
		);

		// Generate query
		$wheres = array_diff($wheres, ['']);
		$query = 'SELECT ' . implode(', ', $columns) . ' FROM fs_betrieb s ' . implode(' ', $joins) . ' WHERE ' . implode(' AND ', $wheres);
		$query = preg_replace('/(\w+)\.(\w+)/', '$1.`$2`', $query);

		// add required constant parameters:
		foreach (self::STATUS_PARAMS as $param => $value) {
			if (strpos($query, ':' . $param)) {
				$params[$param] = $value;
			}
		}

		$stores = $this->db->fetchAll($query, $params);

		if ($use_grouping) {
			$groups = ['manager' => [], 'member' => [], 'jumper' => [], 'applicant' => []];
			foreach ($stores as $store) {
				$group = $store['managing'] == 1 ? 'manager' : ['applicant', 'member', 'jumper'][$store['membership_status']];
				$groups[$group][] = $store;
			}
			$stores = $groups;
		}
		return $stores;
	}

	public function addStore(CreateStoreData $store): int
	{
		return $this->db->insert('fs_betrieb', [
			'name' => $store->name,
			'bezirk_id' => $store->regionId,
			'lat' => $store->lat,
			'lon' => $store->lon,
			'str' => $store->str,
			'hsnr' => $store->hsnr, // deprecated
			'plz' => $store->zip,
			'stadt' => $store->city,
			'added' => $store->createdAt,
			'status_date' => $store->updatedAt,
		]);
	}

	public function updateStoreData(int $storeId, Store $store): int
	{
		return $this->db->update('fs_betrieb', [
			'name' => $store->name,
			'bezirk_id' => $store->regionId,

			'lat' => $store->lat,
			'lon' => $store->lon,
			'str' => $store->str,
			'hsnr' => $store->hsnr, // deprecated
			'plz' => $store->zip,
			'stadt' => $store->city,

			'public_info' => $store->publicInfo,
			'public_time' => $store->publicTime,

			'betrieb_kategorie_id' => $store->categoryId,
			'kette_id' => $store->chainId,
			'betrieb_status_id' => $store->cooperationStatus,

			'besonderheiten' => $store->description,

			'ansprechpartner' => $store->contactName,
			'telefon' => $store->contactPhone,
			'fax' => $store->contactFax,
			'email' => $store->contactEmail,
			'begin' => $store->cooperationStart,

			'prefetchtime' => $store->calendarInterval,
			'abholmenge' => $store->weight,
			'ueberzeugungsarbeit' => $store->effort,
			'presse' => $store->publicity,
			'sticker' => $store->sticker,

			'status_date' => $store->updatedAt,
		], [
			'id' => $storeId,
		]);
	}

	// TODO rename to addStoreMilestone and clean up data handling
	public function add_betrieb_notiz(array $data): int
	{
		return $this->db->insert('fs_betrieb_notiz', [
			'foodsaver_id' => $data['foodsaver_id'],
			'betrieb_id' => $data['betrieb_id'],
			'milestone' => $data['milestone'],
			'text' => strip_tags($data['text']),
			'zeit' => $data['zeit'],
			'last' => 0, // TODO remove this column entirely
		]);
	}

	public function storeExists(int $storeId): bool
	{
		return $this->db->exists('fs_betrieb', ['id' => $storeId]);
	}

	public function getBetrieb($storeId, bool $includeWallposts = true): array
	{
		$result = $this->db->fetch('
            SELECT  `id`,
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

            FROM    `fs_betrieb`

            WHERE   `fs_betrieb`.`id` = :id', [':id' => $storeId]);

		$result['verantwortlicher'] = '';
		if ($bezirk = $this->regionGateway->getRegionName($result['bezirk_id'])) {
			$result['bezirk'] = $bezirk;
		}
		if ($verantwortlich = $this->getStoreManagers($storeId)) {
			$result['verantwortlicher'] = $verantwortlich;
		}
		if ($kette = $this->getOne_kette($result['kette_id'])) {
			$result['kette'] = $kette;
		}

		if ($includeWallposts) {
			$result['notizen'] = $this->getStorePosts($storeId);
		}

		return $result;
	}

	public function getEditStoreData(int $storeId): array
	{
		$result = $this->db->fetch('
			SELECT	`id`,
					`betrieb_status_id`,
					`bezirk_id`,
					`plz`,
					`stadt`,
					`lat`,
					`lon`,
					`kette_id`,
					`betrieb_kategorie_id`,
					`name`,
					`str`,
					`hsnr`,
					`status_date`,
					`status`,
					`ansprechpartner`,
					`telefon`,
					`fax`,
					`email`,
					`begin`,
					`besonderheiten`,
					`ueberzeugungsarbeit`,
					`presse`,
					`sticker`,
					`abholmenge`,
					`prefetchtime`,
					`public_info`,
					`public_time`

			FROM 	`fs_betrieb`

			WHERE 	`id` = :storeId
		', [
			':storeId' => $storeId,
		]);

		if ($result) {
			$result['lebensmittel'] = array_column($this->getGroceries($storeId), 'id');
		}

		return $result;
	}

	public function getMyStore($fs_id, $storeId): array
	{
		$result = $this->db->fetch('
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

			FROM 	`fs_betrieb` b
        			LEFT JOIN `fs_abholer` a
        			ON a.betrieb_id = b.id
			AND		a.date < CURDATE()

			WHERE 	b.`id` = :storeId

			GROUP BY b.`id`
        ', [
			':storeId' => $storeId
		]);

		if ($result) {
			$result['lebensmittel'] = $this->getGroceries($storeId);
			$result['foodsaver'] = $this->getStoreTeam($storeId);
			$result['springer'] = $this->getStoreTeam($storeId, MembershipStatus::JUMPER);
			$result['requests'] = $this->getStoreTeam($storeId, MembershipStatus::APPLIED_FOR_TEAM);
			$result['verantwortlich'] = false;
			$result['team'] = [];
			$result['jumper'] = false;

			if (!empty($result['springer'])) {
				foreach ($result['springer'] as $v) {
					if ($v['id'] == $fs_id) {
						$result['jumper'] = true;
					}
				}
			}

			if (empty($result['foodsaver'])) {
				$result['foodsaver'] = [];
			} else {
				$result['team'] = [];
				foreach ($result['foodsaver'] as $v) {
					$result['team'][] = [
						'id' => $v['id'],
						'value' => $v['name']
					];
					if ($v['verantwortlich'] == 1) {
						$result['verantwortlicher'] = $v['id'];
						if ($v['id'] == $fs_id) {
							$result['verantwortlich'] = true;
						}
					}
				}
			}
		}

		return $result;
	}

	private function getGroceries(int $storeId): array
	{
		return $this->db->fetchAll('
        	SELECT  l.`id`,
        			l.name

        	FROM 	`fs_betrieb_has_lebensmittel` hl
        			INNER JOIN `fs_lebensmittel` l
        	        ON l.id = hl.lebensmittel_id

        	WHERE 	`betrieb_id` = :storeId
        ', [
			':storeId' => $storeId
		]);
	}

	public function setGroceries(int $storeId, array $foodTypeIds): void
	{
		$this->db->delete('fs_betrieb_has_lebensmittel', ['betrieb_id' => $storeId]);

		$newFoodData = array_map(function ($foodId) use ($storeId) {
			return ['betrieb_id' => $storeId, 'lebensmittel_id' => $foodId];
		}, $foodTypeIds);

		$this->db->insertMultiple('fs_betrieb_has_lebensmittel', $newFoodData);
	}

	public function getStoreName(int $storeId): string
	{
		return $this->db->fetchValueByCriteria('fs_betrieb', 'name', ['id' => $storeId]);
	}

	public function getStoreRegionId(int $storeId): int
	{
		return $this->db->fetchValueByCriteria('fs_betrieb', 'bezirk_id', ['id' => $storeId]);
	}

	public function getStoreCategories(): array
	{
		return $this->db->fetchAll('
			SELECT	`id`,
					`name`
			FROM	`fs_betrieb_kategorie`
			ORDER BY `name`
		');
	}

	public function getBasics_groceries(): array
	{
		return $this->db->fetchAll('
			SELECT 	`id`,
					`name`
			FROM 	`fs_lebensmittel`
			ORDER BY `name`
		');
	}

	public function getBasics_chain(): array
	{
		return $this->db->fetchAll('
			SELECT	`id`,
					`name`
			FROM 	`fs_kette`
			ORDER BY `name`
		');
	}

	public function getStoreTeam($storeId, int $status = MembershipStatus::MEMBER): array
	{
		$userDetails = ' ';
		if ($status !== MembershipStatus::APPLIED_FOR_TEAM) {
			$userDetails = '
				fs.telefon,
				fs.handy,
				fs.quiz_rolle,
				fs.rolle,
				t.active AS team_active,
				t.verantwortlich,
				t.stat_last_update,
				t.stat_fetchcount,
				t.stat_first_fetch,
				t.stat_add_date,
				UNIX_TIMESTAMP(t.stat_last_fetch) AS last_fetch,
				UNIX_TIMESTAMP(t.stat_add_date) AS add_date,';
		}

		return $this->db->fetchAll('SELECT
				' . $userDetails . '
				fs.id,
				fs.photo,
				CONCAT(fs.name," ",fs.nachname) AS name,
				fs.`name` as vorname,
				fs.sleep_status,
				fs.verified,
				fs.active
			FROM fs_betrieb_team t
			INNER JOIN fs_foodsaver fs ON fs.id = t.foodsaver_id
			WHERE betrieb_id = :id AND t.active = :membershipStatus AND fs.deleted_at IS NULL
			ORDER BY fs.id
		', [
			':id' => $storeId,
			':membershipStatus' => $status
		]);
	}

	/**
	 * Returns all managers of a store.
	 */
	public function getStoreManagers(int $storeId): array
	{
		return $this->db->fetchAllValues('SELECT
				t.foodsaver_id,
				t.verantwortlich
			FROM fs_betrieb_team t
			INNER JOIN fs_foodsaver fs ON fs.id = t.foodsaver_id
			WHERE t.betrieb_id = :storeId
				AND t.verantwortlich = 1
				AND fs.deleted_at IS NULL
		', [
			':storeId' => $storeId
		]);
	}

	public function getAllStoreManagers(): array
	{
		$verant = $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`email`

			FROM 	`fs_foodsaver` fs
					INNER JOIN `fs_betrieb_team` bt
			        ON bt.foodsaver_id = fs.id

			WHERE 	bt.verantwortlich = 1
			AND		fs.deleted_at IS NULL
		');

		$result = [];
		foreach ($verant as $v) {
			$result[$v['id']] = $v;
		}

		return $result;
	}

	public function getStoreCountForBieb($fs_id)
	{
		return $this->db->count('fs_betrieb_team', ['foodsaver_id' => $fs_id, 'verantwortlich' => 1]);
	}

	public function getStoreTeamStatus(int $storeId): int
	{
		return $this->db->fetchValueByCriteria('fs_betrieb', 'team_status', ['id' => $storeId]);
	}

	public function getUserTeamStatus(int $userId, int $storeId): int
	{
		$result = $this->db->fetchByCriteria('fs_betrieb_team', [
			'active',
			'verantwortlich'
		], [
			'betrieb_id' => $storeId,
			'foodsaver_id' => $userId
		]);

		if ($result) {
			if ($result['verantwortlich'] && $result['active'] == MembershipStatus::MEMBER) {
				return TeamStatus::Coordinator;
			} else {
				switch ($result['active']) {
					case MembershipStatus::JUMPER:
						return TeamStatus::WaitingList;
					case MembershipStatus::MEMBER:
						return TeamStatus::Member;
					default:
						return TeamStatus::Applied;
				}
			}
		}

		return TeamStatus::NoMember;
	}

	public function getBetriebConversation(int $storeId, bool $springerConversation = false): ?int
	{
		if ($springerConversation) {
			$chatType = 'springer_conversation_id';
		} else {
			$chatType = 'team_conversation_id';
		}

		return $this->db->fetchValueByCriteria('fs_betrieb', $chatType, ['id' => $storeId]);
	}

	// TODO clean up data handling (use a DTO)
	// TODO eventually, switch to wallpost system
	public function addStoreWallpost(array $data): int
	{
		return $this->db->insert('fs_betrieb_notiz', [
			'foodsaver_id' => $data['foodsaver_id'],
			'betrieb_id' => $data['betrieb_id'],
			'milestone' => Milestone::NONE,
			'text' => $data['text'],
			'zeit' => $data['zeit'],
			'last' => 0, // TODO remove this column entirely
		]);
	}

	public function deleteStoreWallpost(int $storeId, int $postId): int
	{
		return $this->db->delete('fs_betrieb_notiz', ['id' => $postId, 'betrieb_id' => $storeId]);
	}

	/**
	 * retrieves all store managers for a given region (by being store manager in a store that is part of that region,
	 * which is semantically not the same we use on platform).
	 */
	public function getStoreManagersOf(int $regionId): array
	{
		return $this->db->fetchAllValues('SELECT DISTINCT
                bt.foodsaver_id
            FROM fs_bezirk_closure c
			INNER JOIN fs_betrieb b ON c.bezirk_id = b.bezirk_id
			INNER JOIN fs_betrieb_team bt ON bt.betrieb_id = b.id
			INNER JOIN fs_foodsaver fs ON fs.id = bt.foodsaver_id
			WHERE c.ancestor_id = :regionId
            	AND bt.verantwortlich = 1
            	AND fs.deleted_at IS NULL
        ', [
			':regionId' => $regionId
		]);
	}

	private function getOne_kette($id): array
	{
		return $this->db->fetch('
			SELECT   `id`,
			         `name`,
			         `logo`

			FROM     `fs_kette`

			WHERE    `id` = :id
        ', [
			':id' => $id
		]);
	}

	/**
	 * Returns the store comment with the specified ID.
	 */
	public function getStoreWallpost(int $storeId, int $postId): array
	{
		return $this->db->fetchByCriteria(
			'fs_betrieb_notiz',
			['id', 'foodsaver_id', 'betrieb_id', 'text', 'zeit'],
			['id' => $postId, 'betrieb_id' => $storeId]
		);
	}

	/**
	 * Returns all comments for a given store.
	 */
	public function getStorePosts(int $storeId, int $offset = 0, int $limit = 50): array
	{
		return $this->db->fetchAll('
			SELECT sn.`id`,
			       sn.`foodsaver_id`,
				   fs.`photo`,
				   CONCAT(fs.`name`," ",fs.`nachname`) AS name,
			       sn.`betrieb_id`,
			       sn.`text`,
			       sn.`milestone`,
			       sn.`zeit`

			FROM `fs_betrieb_notiz` sn
				INNER JOIN fs_foodsaver fs
				ON         fs.id = sn.foodsaver_id

			WHERE  sn.`betrieb_id` = :storeId
			AND    sn.`milestone` = :noMilestone

			ORDER BY sn.`zeit` DESC
			LIMIT :offset, :limit
		', [
			':storeId' => $storeId,
			':noMilestone' => Milestone::NONE,
			':offset' => $offset,
			':limit' => $limit,
		]);
	}

	public function updateStoreRegion(int $storeId, int $regionId): int
	{
		return $this->db->update('fs_betrieb', ['bezirk_id' => $regionId], ['id' => $storeId]);
	}

	public function updateStoreConversation(int $storeId, int $conversationId, bool $isStandby): int
	{
		$fieldToUpdate = $isStandby ? 'springer_conversation_id' : 'team_conversation_id';

		return $this->db->update('fs_betrieb', [$fieldToUpdate => $conversationId], ['id' => $storeId]);
	}

	public function getStoreByConversationId(int $id): ?array
	{
		$store = $this->db->fetch('
			SELECT	id,
					name

			FROM	fs_betrieb

			WHERE	team_conversation_id = :memberId
			OR      springer_conversation_id = :jumperId
		', [
			':memberId' => $id,
			':jumperId' => $id
		]);

		return $store;
	}

	public function addStoreLog(
		int $store_id,
		int $foodsaver_id,
		?int $fs_id_p,
		?\DateTimeInterface $dateReference,
		int $action,
		?string $content = null,
		?string $reason = null
	) {
		return $this->db->insert('fs_store_log', [
			'store_id' => $store_id,
			'action' => $action,
			'fs_id_a' => $foodsaver_id,
			'fs_id_p' => $fs_id_p,
			'date_reference' => $dateReference ? $dateReference->format('Y-m-d H:i:s') : null,
			'content' => strip_tags($content),
			'reason' => strip_tags($reason),
		]);
	}

	public function setStoreTeamStatus(int $storeId, int $teamStatus)
	{
		$this->db->update('fs_betrieb', ['team_status' => $teamStatus], ['id' => $storeId]);
	}

	public function addStoreRequest(int $storeId, int $userId): int
	{
		return $this->db->insertOrUpdate('fs_betrieb_team', [
			'betrieb_id' => $storeId,
			'foodsaver_id' => $userId,
			'verantwortlich' => 0,
			'active' => MembershipStatus::APPLIED_FOR_TEAM,
		]);
	}

	/**
	 * Add store manager to a store and make her responsible for that store.
	 */
	public function addStoreManager(int $storeId, int $userId): int
	{
		return $this->db->insertOrUpdate('fs_betrieb_team', [
			'betrieb_id' => $storeId,
			'foodsaver_id' => $userId,
			'verantwortlich' => 1,
			'active' => MembershipStatus::MEMBER,
		]);
	}

	public function removeStoreManager(int $storeId, int $userId): int
	{
		return $this->db->update('fs_betrieb_team', [
			'verantwortlich' => 0,
		], [
			'betrieb_id' => $storeId,
			'foodsaver_id' => $userId,
		]);
	}

	public function addUserToTeam(int $storeId, int $userId): void
	{
		$this->db->insertOrUpdate('fs_betrieb_team', [
			'betrieb_id' => $storeId,
			'foodsaver_id' => $userId,
			'stat_add_date' => $this->db->now(),
			'active' => MembershipStatus::MEMBER,
		]);
	}

	/**
	 * @param int $newStatus a Core\DBConstants\StoreTeam\MembershipStatus
	 */
	public function setUserMembershipStatus(int $storeId, int $userId, int $newStatus): void
	{
		$this->db->update('fs_betrieb_team', ['active' => $newStatus], [
			'betrieb_id' => $storeId,
			'foodsaver_id' => $userId
		]);
	}

	public function removeUserFromTeam(int $storeId, int $userId): void
	{
		$this->db->delete('fs_betrieb_team', [
			'betrieb_id' => $storeId,
			'foodsaver_id' => $userId
		]);
	}
}
