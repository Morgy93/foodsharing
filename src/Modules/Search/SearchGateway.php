<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;

class SearchGateway extends BaseGateway
{
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    /**
     * Searches the given term in the database of regions.
     */
    public function searchRegions(string $query, int $foodsaverId): array
    {
        list($searchClauses, $parameters) = $this->generateSearchClauses(['region.name', 'region.email'], $query);
        $workingGroupType = UnitType::WORKING_GROUP;
        $rootRegionId = RegionIDs::ROOT;

        return $this->db->fetchAll("SELECT
                region.id,
                region.name,
                region.email,
                parent.id AS parent_id,
                parent.name AS parent_name,
                GROUP_CONCAT(foodsaver.id) AS ambassador_ids,
                GROUP_CONCAT(foodsaver.name) AS ambassador_names,
                GROUP_CONCAT(IFNULL(foodsaver.photo, '')) AS ambassador_photos,
                IF(ISNULL(has_region.foodsaver_id), NULL, 1) as is_member
            FROM fs_bezirk region
            LEFT OUTER JOIN fs_bezirk parent ON parent.id = region.parent_id
            LEFT OUTER JOIN fs_botschafter ambassador ON ambassador.bezirk_id = region.id
            LEFT OUTER JOIN fs_foodsaver foodsaver ON foodsaver.id = ambassador.foodsaver_id
            LEFT OUTER JOIN fs_foodsaver_has_bezirk has_region ON has_region.bezirk_id = region.id AND has_region.foodsaver_id = ?
            WHERE region.type != {$workingGroupType}
            AND region.id != {$rootRegionId}
            AND {$searchClauses}
            GROUP BY region.id
            ORDER BY is_member DESC, name ASC
            LIMIT 30",
            [$foodsaverId, ...$parameters]);
    }

    /**
     * Searches the given term in the database of working groups.
     */
    public function searchWorkingGroups(string $query, int $foodsaverId, bool $searchAllWorkingGroups): array
    {
        list($searchClauses, $parameters) = $this->generateSearchClauses(['region.name', 'region.email', 'parent.name'], $query);
        $membershipCheck = $searchAllWorkingGroups ? '' : 'AND (NOT ISNULL(has_parent_region.foodsaver_id) OR NOT ISNULL(has_region.foodsaver_id))';
        $workingGroupType = UnitType::WORKING_GROUP;

        return $this->db->fetchAll("SELECT
                region.id,
                region.name,
                region.email,
                parent.id AS parent_id,
                parent.name AS parent_name,
                has_region.active as is_member,
                MAX(IF(ambassador.foodsaver_id = ?, 1, 0)) AS is_admin,
                GROUP_CONCAT(foodsaver.id) AS admin_ids,
                GROUP_CONCAT(foodsaver.name) AS admin_names,
                GROUP_CONCAT(IFNULL(foodsaver.photo, '')) AS admin_photos
            FROM fs_bezirk region
            JOIN fs_bezirk parent ON parent.id = region.parent_id
            LEFT OUTER JOIN fs_foodsaver_has_bezirk has_region ON has_region.bezirk_id = region.id AND has_region.foodsaver_id = ?
            LEFT OUTER JOIN fs_foodsaver_has_bezirk has_parent_region ON has_parent_region.bezirk_id = parent.id AND has_parent_region.foodsaver_id = ?
            LEFT OUTER JOIN fs_botschafter ambassador ON ambassador.bezirk_id = region.id
            LEFT OUTER JOIN fs_foodsaver foodsaver ON foodsaver.id = ambassador.foodsaver_id
            WHERE region.type = {$workingGroupType}
            {$membershipCheck}
            AND {$searchClauses}
            GROUP BY region.id
            ORDER BY is_admin DESC, is_member DESC, name ASC
            LIMIT 30",
            [$foodsaverId, $foodsaverId, $foodsaverId, ...$parameters]);
    }

    /**
     * Searches the given term in the database of stores.
     */
    public function searchStores(string $query, int $foodsaverId, bool $includeInactiveStores, bool $searchGlobal): array
    {
        list($searchClauses, $parameters) = $this->generateSearchClauses(
            ['store.name', 'IFNULL(chain.name, "")'],
            $query,
            ['store.str', 'store.plz', 'store.stadt', 'region.name']
        );
        $onlyActiveClause = '';
        if (!$includeInactiveStores) {
            $onlyActiveClause = 'AND 
                (store.betrieb_status_id IN (' . CooperationStatus::COOPERATION_STARTING->value . ',' . CooperationStatus::COOPERATION_ESTABLISHED->value . ')
                    OR store.team_status != 0 OR NOT ISNULL(store_team.active)) AND
                store.betrieb_status_id != ' . CooperationStatus::PERMANENTLY_CLOSED->value;
        }
        $regionRestrictionClause = '';
        if (!$searchGlobal) {
            $regionRestrictionClause = 'AND ? IN (has_region.foodsaver_id, kam.foodsaver_id)';
            $parameters[] = $foodsaverId;
        }

        return $this->db->fetchAll("SELECT
                store.id,
                store.name,
                store.betrieb_status_id AS cooperation_status,
                store.str AS street,
                store.plz AS zip,
                store.stadt AS city,
                region.id AS region_id,
                region.name AS region_name,
                chain.name AS chain_name,
                store_team.active as membership_status,
                store_team.verantwortlich as is_manager
            FROM fs_betrieb AS store
            JOIN fs_bezirk region ON region.id = store.bezirk_id
            JOIN fs_foodsaver_has_bezirk has_region ON has_region.bezirk_id = store.bezirk_id
            LEFT OUTER JOIN fs_key_account_manager AS kam ON kam.chain_id = store.kette_id AND kam.foodsaver_id = ?
            LEFT OUTER JOIN fs_chain AS chain ON chain.id = kam.chain_id
            LEFT OUTER JOIN fs_betrieb_team AS store_team ON store_team.betrieb_id = store.id AND store_team.foodsaver_id = ?
            WHERE {$searchClauses}
            {$onlyActiveClause}
            {$regionRestrictionClause}
            GROUP BY store.id
            ORDER BY is_manager DESC, IF(membership_status = 1, 2, IF(membership_status = 2, 1, 0)) DESC, name ASC
            LIMIT 30",
            [$foodsaverId, $foodsaverId, ...$parameters]);
    }

    /**
     * Searches the given term in the list of food-share-points.
     */
    public function searchFoodSharePoints(string $query, int $foodsaverId, bool $searchGlobal): array
    {
        list($searchClauses, $parameters) = $this->generateSearchClauses(
            ['share_point.name'],
            $query,
            ['share_point.anschrift', 'share_point.plz', 'share_point.ort', 'region.name']
        );
        $regionRestrictionClause = '';
        if (!$searchGlobal) {
            $regionRestrictionClause = 'AND has_region.foodsaver_id = ?';
            $parameters[] = $foodsaverId;
        }

        return $this->db->fetchAll("SELECT
                share_point.id,
                share_point.name,
                share_point.anschrift AS street,
                share_point.plz AS zip,
                share_point.ort AS city,
                region.id AS region_id,
                region.name AS region_name
            FROM fs_fairteiler share_point
            JOIN fs_bezirk region ON region.id = share_point.bezirk_id
            JOIN fs_foodsaver_has_bezirk has_region ON has_region.bezirk_id = region.id
            WHERE share_point.status = 1
            AND {$searchClauses}
            {$regionRestrictionClause}
            ORDER BY name ASC
            LIMIT 30",
            $parameters
        );
    }

    /**
     * Searches the given term in the list of own chats.
     */
    public function searchChats(string $query, int $foodsaverId): array
    {
        list($searchClauses, $parameters) = $this->generateSearchClauses(['GROUP_CONCAT(foodsaver.name)', 'IFNULL(name, "")'], $query);

        return $this->db->fetchAll("SELECT
                conversation.id,
                conversation.name,
                conversation.last,
                conversation.last_foodsaver_id,
                last_author.name as last_foodsaver_name,
                LEFT(conversation.last_message, 120) AS last_message,
                GROUP_CONCAT(foodsaver.id LIMIT 5) AS member_ids,
                GROUP_CONCAT(foodsaver.name LIMIT 5) AS member_names,
                GROUP_CONCAT(foodsaver.photo LIMIT 5) AS member_photos,
                COUNT(*) AS member_count
            FROM fs_foodsaver_has_conversation AS has_conversation
            JOIN fs_conversation AS conversation ON conversation.id = has_conversation.conversation_id
            JOIN fs_foodsaver_has_conversation AS has_member ON has_member.conversation_id = conversation.id
            JOIN fs_foodsaver AS foodsaver ON foodsaver.id = has_member.foodsaver_id
            JOIN fs_foodsaver AS last_author ON last_author.id = conversation.last_foodsaver_id 
            WHERE has_conversation.foodsaver_id = ? -- Only include own chats
            AND has_member.foodsaver_id != has_conversation.foodsaver_id -- Exclude searching for oneself in chat member lists
            GROUP BY conversation.id
            HAVING {$searchClauses}
            ORDER BY last DESC
            LIMIT 30",
            [$foodsaverId, ...$parameters]
        );
    }

    /**
     * Searches the given term in the list of forum threads.
     * Use params $regionId and $subforumId to restrict the search to one forum.
     */
    public function searchThreads(string $query, int $foodsaverId, int $regionId = 0, int $subforumId = 0, $disableRegionCheck = false): array
    {
        list($searchClauses, $parameters) = $this->generateSearchClauses(['thread.name'], $query, ['region.name']);
        $regionRestrictionClause = '';
        if ($regionId > 0) {
            $regionRestrictionClause = 'AND has_thread.bezirk_id = ? AND has_thread.bot_theme = ?';
            array_push($parameters, $regionId, $subforumId);
        }

        $hasRegionJoins = '';
        $hasRegionClause = '';
        if (!$disableRegionCheck) {
            $hasRegionJoins = 'JOIN fs_foodsaver_has_bezirk AS has_region ON has_region.bezirk_id = region.id
                    LEFT OUTER JOIN fs_botschafter AS ambassador ON ambassador.foodsaver_id = has_region.foodsaver_id AND ambassador.bezirk_id = region.id';
            $hasRegionClause = 'AND has_region.foodsaver_id = ?
                                AND(NOT ISNULL(ambassador.foodsaver_id) OR has_thread.bot_theme = 0) -- show Bot forums only to bots';
            array_push($parameters, $foodsaverId);
        }

        return $this->db->fetchAll("SELECT
                thread.id,
                thread.name,
                post.time,
                thread.sticky,
                thread.status AS closed,
                region.id AS region_id,
                region.name AS region_name,
                has_thread.bot_theme AS is_ambassador_forum
            FROM fs_theme AS thread
            JOIN fs_bezirk_has_theme AS has_thread ON has_thread.theme_id = thread.id
            JOIN fs_bezirk AS region ON region.id = has_thread.bezirk_id
            JOIN fs_theme_post AS post ON post.id = thread.last_post_id
            {$hasRegionJoins}
            WHERE thread.active = 1
            AND {$searchClauses}
            {$regionRestrictionClause}
            {$hasRegionClause}
            ORDER BY time DESC
            LIMIT 30",
            [...$parameters]
        );
    }

    /**
     * Searches the given term in the list of users.
     */
    public function searchUsers(string $query, int $foodsaverId, bool $searchGlobal): array
    {
        if ($searchGlobal) {
            return $this->searchUsersGlobal($query);
        }
        list($searchClauses, $parameters) = $this->generateSearchClauses(['foodsaver.name', 'IFNULL(foodsaver.last_name, "")'], $query, ['region.name']);

        return $this->db->fetchAll('SELECT
                foodsaver.id,
                foodsaver.name,
                foodsaver.photo,
                foodsaver.home_region AS region_id,
                region.name AS region_name,
                MAX(foodsaver.last_name) as last_name,
                MAX(foodsaver.mobile) as mobile,
                MAX(foodsaver.buddy) AS buddy
            FROM (
                -- Region / AG members:
                SELECT
                    foodsaver.id,
                    foodsaver.name,
                    foodsaver.photo,
                    foodsaver.bezirk_id AS home_region,
                    IF(MAX(NOT ISNULL(ambassador.foodsaver_id) AND region.type != ' . UnitType::WORKING_GROUP . ') = 1, foodsaver.handy, null) AS mobile,
                    IF(MAX(NOT ISNULL(ambassador.foodsaver_id) AND region.type != ' . UnitType::WORKING_GROUP . ') = 1, foodsaver.nachname, null) AS last_name,
                    0 AS buddy
                FROM fs_foodsaver AS foodsaver
                JOIN fs_foodsaver_has_bezirk has_region ON has_region.foodsaver_id = foodsaver.id
                JOIN fs_bezirk region ON region.id = has_region.bezirk_id
                JOIN fs_foodsaver_has_bezirk have_region ON have_region.bezirk_id = region.id
                LEFT OUTER JOIN fs_botschafter ambassador ON ambassador.bezirk_id = region.id and ambassador.foodsaver_id = have_region.foodsaver_id 
                WHERE have_region.foodsaver_id = ?
                AND region.type IN (' . UnitType::CITY . ',' . UnitType::PART_OF_TOWN . ',' . UnitType::WORKING_GROUP . ')
                GROUP BY foodsaver.id
                UNION ALL
            
                -- Buddies:
                SELECT
                    foodsaver.id,
                    foodsaver.name,
                    foodsaver.photo,
                    foodsaver.bezirk_id AS home_region,
                    NULL, NULL,
                    1
                FROM fs_buddy AS buddy
                JOIN fs_foodsaver AS foodsaver ON foodsaver.id = buddy.foodsaver_id
                WHERE buddy.confirmed = 1
                AND buddy.buddy_id = ?
                UNION ALL
            
                -- By store team:
                SELECT
                    foodsaver.id,
                    foodsaver.name,
                    foodsaver.photo,
                    foodsaver.bezirk_id AS home_region,
                    IF(MAX(IF(my_store_team.active = 1, 1, 0)) = 1, foodsaver.handy, null) AS mobile,
                    IF(MAX(IF(my_store_team.verantwortlich = 1, 1, 0)) = 1, foodsaver.nachname, null) AS last_name,
                    0
                FROM fs_betrieb_team AS my_store_team
                JOIN fs_betrieb_team AS store_team ON store_team.betrieb_id = my_store_team.betrieb_id
                JOIN fs_foodsaver AS foodsaver ON foodsaver.id = store_team.foodsaver_id 
                WHERE my_store_team.foodsaver_id = ?
                AND my_store_team.active != 0
                GROUP BY foodsaver.id
                UNION ALL
            
                -- By Chat membership:
                SELECT
                    foodsaver.id,
                    foodsaver.name,
                    foodsaver.photo,
                    foodsaver.bezirk_id AS home_region,
                    NULL, NULL, 0
                FROM fs_foodsaver_has_conversation AS have_conversation
                JOIN fs_foodsaver_has_conversation AS has_conversation ON have_conversation.conversation_id = has_conversation.conversation_id
                JOIN fs_foodsaver AS foodsaver ON foodsaver.id = has_conversation.foodsaver_id
                WHERE have_conversation.foodsaver_id = ?
                GROUP BY foodsaver.id
                UNION ALL

                -- By Id:
                SELECT
                    foodsaver.id,
                    foodsaver.name,
                    foodsaver.photo,
                    foodsaver.bezirk_id AS home_region,
                    NULL, NULL, 0
                FROM fs_foodsaver AS foodsaver
                WHERE foodsaver.id = ?
            ) foodsaver
            JOIN fs_bezirk AS region ON region.id = foodsaver.home_region
            WHERE (foodsaver.id = ? OR (foodsaver.id != ? AND ' . $searchClauses . '))
            GROUP BY foodsaver.id
            ORDER BY MAX(foodsaver.buddy) DESC, ISNULL(foodsaver.last_name), foodsaver.name, foodsaver.last_name 
            LIMIT 30
            ',
            [$foodsaverId, $foodsaverId, $foodsaverId, $foodsaverId, $parameters[0], $parameters[0], $foodsaverId, ...$parameters]
        );
    }

    public function searchUsersGlobal(string $query, ?int $restrictToRegionId = null): array
    {
        list($searchClauses, $parameters) = $this->generateSearchClauses(['foodsaver.name', 'foodsaver.nachname'], $query, ['region.name']);
        $parameters[] = $parameters[0]; // Param for id search
        $regionRestrictionClause = '';
        $hasRegionJoin = '';
        if ($restrictToRegionId) {
            $hasRegionJoin = 'JOIN fs_foodsaver_has_bezirk has_region ON has_region.foodsaver_id = foodsaver.id';
            $regionRestrictionClause = 'AND has_region.bezirk_id = ?';
            $parameters[] = $restrictToRegionId;
        }

        // TODO Buddys
        return $this->db->fetchAll("SELECT
                foodsaver.id,
                foodsaver.name,
                foodsaver.photo,
                foodsaver.bezirk_id AS region_id,
                home_region.name AS region_name,
                foodsaver.nachname as last_name,
                foodsaver.handy as mobile,
                0 as buddy
            FROM fs_foodsaver as foodsaver
            JOIN fs_bezirk as home_region ON home_region.id = foodsaver.bezirk_id
            {$hasRegionJoin}
            WHERE ({$searchClauses} OR (foodsaver.id = ?))
            {$regionRestrictionClause}
            ORDER BY foodsaver.name, last_name
            LIMIT 30",
            [...$parameters]
        );
    }

    private function generateSearchClauses($searchCriteria, $query, array $detailedSearchCriteria = [])
    {
        $query = preg_replace('/[,;+\.\s]+/', ' ', $query);
        $queryTerms = explode(' ', trim($query));
        if (count($queryTerms) > 1) {
            $searchCriteria = array_merge($searchCriteria, $detailedSearchCriteria);
        }
        $searchCriteria = 'CONCAT(' . implode(',";",', $searchCriteria) . ')';
        $searchClauses = array_map(fn ($term) => $searchCriteria . ' LIKE CONCAT("%", ?, "%")', $queryTerms);

        return [implode(' AND ', $searchClauses), $queryTerms];
    }
}
