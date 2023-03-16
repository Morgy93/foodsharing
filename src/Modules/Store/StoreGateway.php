<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Store;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\Milestone;
use Foodsharing\Modules\Core\DBConstants\Store\TeamSearchStatus;
use Foodsharing\Modules\Core\DBConstants\StoreTeam\MembershipStatus;
use Foodsharing\Modules\Map\DTO\MapMarker;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\DTO\Store;
use Foodsharing\Modules\Store\DTO\StoreTeamMembership;

class StoreGateway extends BaseGateway
{
    private RegionGateway $regionGateway;

    public function __construct(
        Database $db,
        RegionGateway $regionGateway
    ) {
        parent::__construct($db);

        $this->regionGateway = $regionGateway;
    }

    public function addStore(Store $store): int
    {
        return $this->db->insert('fs_betrieb', [
            'name' => $store->name,
            'bezirk_id' => $store->regionId,
            'lat' => $store->location->lat,
            'lon' => $store->location->lon,
            'str' => $store->address->street,
            'plz' => $store->address->zipCode,
            'stadt' => $store->address->city,
            'public_info' => $store->publicInfo,
            'added' => $this->db->date($store->createdAt, false),
            'status_date' => $this->db->date($store->updatedAt, false)
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
					`fs_betrieb`.stadt,
					`fs_betrieb`.lat,
					`fs_betrieb`.lon,
					`fs_betrieb`.str AS anschrift,
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
        if ($verantwortlich = $this->getBiebsForStore($storeId)) {
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

    public function getStore(int $storeId): Store
    {
        $result = $this->db->fetch(
            'SELECT	`id`,
                    `name`,
					`bezirk_id` as regionId,
					`lat`,
					`lon`,
					`str` AS street,
					`plz` AS zipCode,
					`stadt` as city,
					`public_info`,
					`public_time`,
					`betrieb_kategorie_id` as categoryId,
					`kette_id` as chainId,
					`betrieb_status_id` as cooperationStatus,
					`begin` as cooperationStart,
					`besonderheiten` as description,
					`ansprechpartner` as contactName,
					`telefon` as contactPhone,
					`fax` as contactFax,
					`email` as contactEmail,
					`prefetchtime` as calendarInterval,
					`abholmenge` as weight,
					`ueberzeugungsarbeit` as effort,
					`presse` as publicity,
					`sticker`,
					`team_status` as teamStatus,
					`use_region_pickup_rule` as useRegionPickupRule,
					`status_date` as updatedAt,
					`added` as createdAt
			FROM 	`fs_betrieb`

			WHERE 	`id` = :storeId
		', [
            ':storeId' => $storeId,
        ]);

        if ($result) {
            $result['groceries'] = array_column($this->getGroceries($storeId), 'id');
        }

        return Store::createFromArray($result);
    }

    public function updateStoreData(Store $store, bool $groceriesChanged = false)
    {
        $this->db->update('fs_betrieb', [
            'name' => $store->name,
            'bezirk_id' => $store->regionId,

            'lat' => $store->location->lat,
            'lon' => $store->location->lon,
            'str' => $store->address->street,
            'plz' => $store->address->zipCode,
            'stadt' => $store->address->city,

            'public_info' => $store->publicInfo,
            'public_time' => $store->publicTime->value,

            'betrieb_kategorie_id' => $store->categoryId,
            'kette_id' => $store->chainId,
            'betrieb_status_id' => $store->cooperationStatus->value,

            'besonderheiten' => $store->description,

            'ansprechpartner' => $store->contact->name,
            'telefon' => $store->contact->phone,
            'fax' => $store->contact->fax,
            'email' => $store->contact->email,
            'begin' => $store->cooperationStart ? $this->db->date($store->cooperationStart) : null,
            'team_status' => $store->teamStatus->value,

            'prefetchtime' => $store->calendarInterval,
            'use_region_pickup_rule' => $store->options->useRegionPickupRule,
            'abholmenge' => $store->weight,
            'ueberzeugungsarbeit' => $store->effort->value,
            'presse' => $store->publicity,
            'sticker' => $store->showsSticker,
            'status_date' => $this->db->date($store->updatedAt)
        ], [
            'id' => $store->id,
        ]);

        if ($groceriesChanged) {
            $this->setGroceries($store->id, $store->groceries);
        }
    }

    public function getMapsStores(int $regionId): array
    {
        return $this->db->fetchAll('
            SELECT 	b.id,
                    b.betrieb_status_id,
					b.plz,
					b.`lat`,
					b.`lon`,
					b.`stadt`,
					b.kette_id,
					b.betrieb_kategorie_id,
					b.name,
					b.str,
					b.`betrieb_status_id`,
					k.logo

			FROM 	fs_betrieb b
			LEFT JOIN fs_kette k ON b.kette_id = k.id

			WHERE 	b.bezirk_id = :regionId
			  AND	b.betrieb_status_id <> :permanentlyClosed
			  AND	b.`lat` != ""
		', [
            ':regionId' => $regionId,
            ':permanentlyClosed' => CooperationStatus::PERMANENTLY_CLOSED->value,
        ]);
    }

    public function listMyStores(int $fsId): array
    {
        return $this->db->fetchAll('
			SELECT 	b.id,
					b.name,
					b.plz,
					b.stadt,
					b.str

			FROM	fs_betrieb b
					INNER JOIN fs_betrieb_team t
					ON b.id = t.betrieb_id

			WHERE	t.foodsaver_id = :fsId
			AND     t.active = :membershipStatus
		', [
            ':fsId' => $fsId,
            ':membershipStatus' => MembershipStatus::MEMBER
        ]);
    }

    private function getStoreListQuery(): string
    {
        return '
			SELECT 	s.id,
					s.name,
					s.betrieb_status_id,
					s.kette_id,
					s.betrieb_kategorie_id,

					r.name AS region_name,

					s.added,
					s.ansprechpartner,
					s.fax,
					s.telefon,
					s.email,

					s.str AS anschrift,
					s.str,
					s.plz,
					s.stadt,
					CONCAT(s.lat,", ",s.lon) AS geo,
					s.`betrieb_status_id`,

					t.verantwortlich,
					t.active

			FROM 	fs_betrieb s
					INNER JOIN      fs_bezirk r        ON  r.id = s.bezirk_id
					LEFT OUTER JOIN fs_betrieb_team t  ON  t.betrieb_id = s.id
		';
    }

    /**
     * @param ?int $userId if set, include all own stores (from any region) in output
     * @param ?int $addFromRegionId if set, include all stores (own or otherwise) from given region in output
     * @param bool $sortByOwnTeamStatus if true, split the resulting stores into multiple categories (depending on own team status)
     */
    public function getMyStores(?int $userId, ?int $addFromRegionId = null, bool $sortByOwnTeamStatus = true): array
    {
        $query = $this->getStoreListQuery();

        $betriebe = [];

        if (!is_null($userId)) {
            $betriebe = $this->db->fetchAll($query . '
				WHERE    t.foodsaver_id = :userId

				ORDER BY t.verantwortlich DESC, s.name ASC
			', [
                ':userId' => $userId,
            ]);
        }

        if ($sortByOwnTeamStatus) {
            $result = [
                'verantwortlich' => [],
                'team' => [],
                'waitspringer' => [],
                'requested' => [],
                'sonstige' => [],
            ];
        } else {
            $result = [];
        }

        $already_in = [];

        foreach ($betriebe as $b) {
            $already_in[$b['id']] = true;

            if ($sortByOwnTeamStatus) {
                if ($b['verantwortlich'] == 0) {
                    if ($b['active'] == MembershipStatus::APPLIED_FOR_TEAM) {
                        $result['requested'][] = $b;
                    } elseif ($b['active'] == MembershipStatus::MEMBER) {
                        $result['team'][] = $b;
                    } elseif ($b['active'] == MembershipStatus::JUMPER) {
                        $result['waitspringer'][] = $b;
                    }
                } else {
                    $result['verantwortlich'][] = $b;
                }
            } else {
                $result[$b['id']] = $b;
            }
        }

        if ($addFromRegionId !== null) {
            $child_region_ids = $this->regionGateway->listIdsForDescendantsAndSelf($addFromRegionId);
            if (!empty($child_region_ids)) {
                $placeholders = $this->db->generatePlaceholders(count($child_region_ids));
                $betriebe = $this->db->fetchAll(
                    $query . ' WHERE bezirk_id IN(' . $placeholders . ') ORDER BY r.name DESC',
                    $child_region_ids
                );

                foreach ($betriebe as $b) {
                    if (!isset($already_in[$b['id']])) {
                        $already_in[$b['id']] = true;
                        if ($sortByOwnTeamStatus) {
                            $result['sonstige'][] = $b;
                        } else {
                            $result[$b['id']] = $b;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function getMyStore(int $fs_id, int $storeId): array
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
        			b.`use_region_pickup_rule`,
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
            $result['springer'] = $this->getBetriebSpringer($storeId);
            $result['requests'] = $this->getApplications($storeId);
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

    private function setGroceries(int $storeId, array $foodTypeIds): int
    {
        $this->db->delete('fs_betrieb_has_lebensmittel', ['betrieb_id' => $storeId]);

        $newFoodData = array_map(function ($foodId) use ($storeId) {
            return ['betrieb_id' => $storeId, 'lebensmittel_id' => $foodId];
        }, $foodTypeIds);

        return $this->db->insertMultiple('fs_betrieb_has_lebensmittel', $newFoodData);
    }

    private function getApplications(int $storeId): array
    {
        return $this->db->fetchAll('
			SELECT 		fs.`id`,
						fs.photo,
						CONCAT(fs.name," ",fs.nachname) AS name,
						name as vorname,
						fs.sleep_status,
			       		fs.verified

			FROM 		`fs_betrieb_team` t
						INNER JOIN `fs_foodsaver` fs
			            ON fs.id = t.foodsaver_id

			WHERE 		`betrieb_id` = :storeId
			AND 		t.active = :membershipStatus
			AND			fs.deleted_at IS NULL
		', [
            ':storeId' => $storeId,
            ':membershipStatus' => MembershipStatus::APPLIED_FOR_TEAM
        ]);
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

    public function existStoreCategory(int $id): bool
    {
        return $this->db->exists('fs_betrieb_kategorie', ['id' => $id]);
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

    public function existStoreChain(int $id): bool
    {
        return $this->db->exists('fs_kette', ['id' => $id]);
    }

    public function getStoreTeam($storeId): array
    {
        return $this->db->fetchAll('
				SELECT  fs.`id`,
						fs.`verified`,
						fs.`active`,
						fs.`telefon`,
						fs.`handy`,
						fs.photo,
						fs.quiz_rolle,
						fs.rolle,
						CONCAT(fs.name," ",fs.nachname) AS name,
						name as vorname,
						t.`active` AS team_active,
						t.`verantwortlich`,
						t.`stat_last_update`,
						t.`stat_fetchcount`,
						t.`stat_first_fetch`,
						t.`stat_add_date`,
						UNIX_TIMESTAMP(t.`stat_last_fetch`) AS last_fetch,
						UNIX_TIMESTAMP(t.`stat_add_date`) AS add_date,
						fs.sleep_status

				FROM 	`fs_betrieb_team` t
				INNER JOIN `fs_foodsaver` fs
				     	ON fs.id = t.foodsaver_id

				WHERE	`betrieb_id` = :id
				AND 	t.active  = :membershipStatus
				AND		fs.deleted_at IS NULL

				ORDER BY fs.id
		', [
            ':id' => $storeId,
            ':membershipStatus' => MembershipStatus::MEMBER
        ]);
    }

    public function getBetriebSpringer($storeId): array
    {
        return $this->db->fetchAll('
				SELECT  fs.`id`,
						fs.`verified`,
						fs.`active`,
						fs.`telefon`,
						fs.`handy`,
						fs.photo,
						fs.quiz_rolle,
						fs.rolle,
						CONCAT(fs.name," ",fs.nachname) AS name,
						name as vorname,
						t.`active` AS team_active,
						t.`verantwortlich`,
						t.`stat_last_update`,
						t.`stat_fetchcount`,
						t.`stat_first_fetch`,
						t.`stat_add_date`,
						UNIX_TIMESTAMP(t.`stat_last_fetch`) AS last_fetch,
						UNIX_TIMESTAMP(t.`stat_add_date`) AS add_date,
						fs.sleep_status

				FROM 	`fs_betrieb_team` t
						INNER JOIN `fs_foodsaver` fs
				        ON fs.id = t.foodsaver_id

				WHERE 	`betrieb_id` = :id
				AND 	t.active  = :membershipStatus
				AND		fs.deleted_at IS NULL

				ORDER BY fs.id
		', [
            ':id' => $storeId,
            ':membershipStatus' => MembershipStatus::JUMPER
        ]);
    }

    public function getBiebsForStore($storeId)
    {
        return $this->db->fetchAll('
			SELECT 	`foodsaver_id` as id

			FROM fs_betrieb_team

			WHERE `betrieb_id` = :betrieb_id
			AND verantwortlich = 1
			AND `active` = :membershipStatus
        ', [
            ':betrieb_id' => $storeId,
            ':membershipStatus' => MembershipStatus::MEMBER
        ]);
    }

    /**
     * Returns all managers of a store.
     */
    public function getStoreManagers(int $storeId): array
    {
        return $this->db->fetchAllValues('
			SELECT 	t.`foodsaver_id`,
					t.`verantwortlich`

			FROM 	`fs_betrieb_team` t
					INNER JOIN  `fs_foodsaver` fs
					ON fs.id = t.foodsaver_id

			WHERE 	t.`betrieb_id` = :storeId
					AND t.verantwortlich = 1
					AND fs.deleted_at IS NULL
		', [
            ':storeId' => $storeId,
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

    public function getUseRegionPickupRule(int $storeId)
    {
        return $this->db->fetchValueByCriteria('fs_betrieb', 'use_region_pickup_rule', ['id' => $storeId]);
    }

    public function getStoreCountForBieb($fs_id)
    {
        return $this->db->count('fs_betrieb_team', ['foodsaver_id' => $fs_id, 'verantwortlich' => 1]);
    }

    public function getStoreTeamStatus(int $storeId): TeamSearchStatus
    {
        return TeamSearchStatus::tryFrom($this->db->fetchValueByCriteria('fs_betrieb', 'team_status', ['id' => $storeId]));
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
        return $this->db->fetchAllValues('
            SELECT DISTINCT
                    bt.foodsaver_id

            FROM    `fs_bezirk_closure` c
			        INNER JOIN `fs_betrieb` b
                    ON c.bezirk_id = b.bezirk_id
			            INNER JOIN `fs_betrieb_team` bt
                        ON bt.betrieb_id = b.id
			                INNER JOIN `fs_foodsaver` fs
                            ON fs.id = bt.foodsaver_id

			WHERE   c.ancestor_id = :regionId
            AND     bt.verantwortlich = 1
            AND     fs.deleted_at IS NULL
        ', [
            ':regionId' => $regionId
        ]);
    }

    /**
     * Returns a list with all store memberships of the foodsaver.
     *
     * @param int $fsId Foodsharer Id
     * @param CooperationStatus[] $storeCooperationStates All store state should should be contained @see CooperationStatus
     *
     * @return StoreTeamMembership[] Returns a array of memberships
     */
    public function listAllStoreTeamMembershipsForFoodsaver(int $fsId, array $storeCooperationStates)
    {
        if ($fsId == 0) {
            return [];
        }

        $inPlaceHolder = implode(', ', array_fill(0, count($storeCooperationStates), '?'));
        $rows = $this->db->fetchAll('
			SELECT 	b.id as store_id,
					b.name as store_name,
					bt.verantwortlich AS managing,
					bt.active as membership_status
			FROM fs_betrieb_team bt
				INNER JOIN fs_betrieb b
					ON bt.betrieb_id = b.id
			WHERE   bt.`foodsaver_id` = ?
			AND 	b.betrieb_status_id IN (' . $inPlaceHolder . ')
			ORDER BY bt.verantwortlich DESC, membership_status ASC, b.name ASC
		', [
            $fsId,
            array_map(function (CooperationStatus $state) { return $state->value; },
                $storeCooperationStates)
        ]);

        $results = [];
        foreach ($rows as $row) {
            $results[] = StoreTeamMembership::createFromArray($row);
        }

        return $results;
    }

    public function listStoreIds($fsId)
    {
        return $this->db->fetchAllValuesByCriteria('fs_betrieb_team', 'betrieb_id', ['foodsaver_id' => $fsId]);
    }

    public function listStoreIdsWhereResponsible($fsId)
    {
        return $this->db->fetchAllByCriteria('fs_betrieb_team', ['betrieb_id'], ['foodsaver_id' => $fsId, 'verantwortlich' => 1]);
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
            'content' => $content ? strip_tags($content) : '',
            'reason' => $reason ? strip_tags($reason) : ''
        ]);
    }

    public function getAllStores(array $listOfcooperationStatus = []): array
    {
        $where = '';
        $countStates = count($listOfcooperationStatus);
        if ($countStates != 0) {
            $cooperationStatusPlaceHolder = implode(',', array_fill(0, $countStates, '?'));

            $where = 'WHERE b.betrieb_status_id IN (' . $cooperationStatusPlaceHolder . ');';
        }
        $values = array_map(
            function (CooperationStatus $status) {
                return $status->value;
            },
            $listOfcooperationStatus);

        return $this->db->fetchAll(
            'SELECT
                b.`id`, b.`name`
            FROM
                fs_betrieb b ' . $where,
            $values);
    }

    /**
     * Return all stores.
     * Can be restricted to stores the user is a member of.
     *
     * @return array name and id of the stores
     */
    public function getStores(int $fs_id = null, array $cooperationStatus = []): array
    {
        if ($fs_id) {
            return $this->db->fetchAll('SELECT
				b.id,
				b.`name`
			FROM
				fs_betrieb_team t
			JOIN fs_betrieb b ON
				b.id = t.betrieb_id AND b.betrieb_status_id IN (:established, :starting)
			WHERE
				t.foodsaver_id = :fs_id AND t.active = :membership_status
			', [
                'fs_id' => $fs_id,
                'membership_status' => MembershipStatus::MEMBER,
                ':established' => CooperationStatus::COOPERATION_ESTABLISHED->value,
                ':starting' => CooperationStatus::COOPERATION_STARTING->value
            ]);
        } else {
            return $this->getAllStores($cooperationStatus);
        }
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

        /**
         * Returns a count of all stores which belong to the given region and optionally all sub regions.
         */
        public function countStoresInRegion(int $regionId, bool $includeSubregions = false): int
        {
            $regionIds = [$regionId];
            if ($includeSubregions) {
                $regionIds = array_merge($regionIds, $this->regionGateway->listIdsForDescendantsAndSelf($regionId));
            }

            $placeholders = implode(',', array_fill(0, count($regionIds), '?'));
            $storeCount = $this->db->fetchValue('SELECT count(fs_betrieb.id)
						FROM 	fs_betrieb,
								fs_bezirk
						WHERE 	fs_betrieb.bezirk_id = fs_bezirk.id
						AND 	fs_betrieb.bezirk_id IN(' . $placeholders . ')
				', $regionIds);

            return $storeCount;
        }

        /**
         * Returns a list of stores which belong to regions.
         *
         *  @return array<Store>
         */
        public function listStoresInRegion(int $regionId, bool $includeSubregions = false, int $startOffset = 0, int $limit = 9999): array
        {
            $regionIds = [$regionId];
            if ($includeSubregions) {
                $regionIds = array_merge($regionIds, $this->regionGateway->listIdsForDescendantsAndSelf($regionId));
            }

            $placeholders = implode(',', array_fill(0, count($regionIds), '?'));
            $results = $this->db->fetchAll('SELECT
                    fs_betrieb.id,
                    fs_betrieb.name,
                    fs_betrieb.bezirk_id as regionId,
                    fs_betrieb.lat,
                    fs_betrieb.lon,
                    fs_betrieb.str AS street,
                    fs_betrieb.plz AS zipCode,
                    fs_betrieb.stadt as city,
                    fs_betrieb.public_info,
                    fs_betrieb.public_time,
                    fs_betrieb.betrieb_kategorie_id as categoryId,
                    fs_betrieb.kette_id as chainId,
                    fs_betrieb.betrieb_status_id as cooperationStatus,
                    fs_betrieb.begin as cooperationStart,
                    fs_betrieb.besonderheiten as description,
                    fs_betrieb.ansprechpartner as contactName,
                    fs_betrieb.telefon as contactPhone,
                    fs_betrieb.fax as contactFax,
                    fs_betrieb.email as contactEmail,
                    fs_betrieb.prefetchtime as calendarInterval,
                    fs_betrieb.abholmenge as weight,
                    fs_betrieb.ueberzeugungsarbeit as effort,
                    fs_betrieb.presse as publicity,
                    fs_betrieb.sticker,
                    fs_betrieb.team_status as teamStatus,
                    fs_betrieb.use_region_pickup_rule as useRegionPickupRule,
                    fs_betrieb.status_date as updatedAt,
                    fs_betrieb.added as createdAt
				FROM 	fs_betrieb,
						fs_bezirk
				WHERE 	fs_betrieb.bezirk_id = fs_bezirk.id
				AND 	fs_betrieb.bezirk_id IN(' . $placeholders . ')
				LIMIT ' . $limit . '
				OFFSET ' . $startOffset . '
				', $regionIds);

            return array_map(function ($store) {
                return Store::createFromArray($store);
            }, $results);
        }

    public function listStoresWithoutRegion(array $storeIds): array
    {
        return $this->db->fetchAll(
            'SELECT id,name,bezirk_id,str
			FROM fs_betrieb
			WHERE id IN(' . implode(',', $storeIds) . ')
			AND ( bezirk_id = 0 OR bezirk_id IS NULL)'
        );
    }

    public function getStoreLogsByActionType(int $storeId, array $storeActions): array
    {
        $logEntries = $this->db->fetchAll('
			SELECT
				date_activity as performed_at,
				action as action_id,
				fs_id_a as affected_foodsaver_id,
				fs_id_p as performed_foodsaver_id,
				date_reference,
				content,
				reason

			FROM
				fs_store_log

			WHERE
				store_id = :storeId
		', [
            'storeId' => $storeId,
        ]);

        $logEntriesWithRequiredStoreActions = array_filter($logEntries, function ($logEntry) use ($storeActions) {
            return in_array($logEntry['action_id'], $storeActions);
        });

        return $logEntriesWithRequiredStoreActions;
    }

    public function listRegionStoresActivePickupRule(int $regionId): array
    {
        return $this->db->fetchAll(
            'select  id as storeId,
        				   name as storeName
					from fs_betrieb b
					where b.bezirk_id = :regionId
					  and b.use_region_pickup_rule',
            [':regionId' => $regionId]
        );
    }

    /**
     * Provides Stores with position markers.
     *
     * @param array<CooperationStatus> $excludedStoreTypes Excludes stores of this types
     */
    public function getStoreMarkers(array $excludedStoreTypes, array $teamStatus): array
    {
        $query = 'SELECT id, lat, lon FROM fs_betrieb WHERE (lat != "" OR lon != "")';

        if (!empty($excludedStoreTypes)) {
            $query .= ' AND betrieb_status_id NOT IN(' . implode(',', array_fill(0, count($excludedStoreTypes), '?')) . ')';
        }
        if (!empty($teamStatus)) {
            $query .= ' AND team_status IN (' . implode(',', array_fill(0, count($teamStatus), '?')) . ')';
        }
        $teamStatusIds = array_map(function (TeamSearchStatus $item) { return $item->value; }, $teamStatus);
        $excludedStoreTypesIds = array_map(function (CooperationStatus $storeType) { return $storeType->value; }, $excludedStoreTypes);
        $markers = $this->db->fetchAll($query, array_merge($excludedStoreTypesIds, $teamStatusIds));

        return array_map(function ($x) {
            return MapMarker::create($x['id'], floatval($x['lat']), floatval($x['lon']));
        }, $markers);
    }
}
