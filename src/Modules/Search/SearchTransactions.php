<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Buddy\BuddyGateway;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Search\DTO\SearchIndexEntry;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\WorkGroup\WorkGroupGateway;
use Foodsharing\Permissions\SearchPermissions;
use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\Sanitizer;

class SearchTransactions
{
    public function __construct(
        private readonly SearchGateway $searchGateway,
        private readonly FoodsaverGateway $foodsaverGateway,
        private readonly RegionGateway $regionGateway,
        private readonly BuddyGateway $buddyGateway,
        private readonly WorkGroupGateway $workGroupGateway,
        private readonly StoreGateway $storeGateway,
        private readonly Session $session,
        private readonly SearchPermissions $searchPermissions,
        private readonly Sanitizer $sanitizerService,
        private readonly ImageHelper $imageHelper
    ) {
    }

    /**
     * Searches for regions, stores, and foodsavers.
     *
     * @param string $query the search query
     *
     * @return array SearchResult[]
     */
    public function search(string $query): array
    {
        $regionsFilter = null;
        $regionDetails = null;
        if (!$this->searchPermissions->maySearchAllRegions()) {
            $regionsFilter = $this->regionGateway->listIdsForDescendantsAndSelf($this->session->getCurrentRegionId(), true, false);

            $ambassadorRegions = $this->session->getMyAmbassadorRegionIds(false);
            $regionsFilter = array_merge($regionsFilter, $ambassadorRegions);
            $regionDetails = $ambassadorRegions;
        }

        $regions = $this->searchGateway->searchRegions($query);
        $users = $this->searchGateway->searchUserInGroups($query, $regionDetails, $regionsFilter);
        $stores = $this->searchGateway->searchStores($query);
        $foodSharePoints = $this->searchGateway->searchFoodSharePoints($query);
        if ($singleUser = $this->searchUserByID($query)) {
            array_unshift($users, $singleUser);
        }

        if ($this->searchPermissions->maySearchByEmailAddress()) {
            if ($singleUser = $this->searchUserByEmail($query)) {
                array_unshift($users, $singleUser);
            }
        }

        return [
            'regions' => $regions,
            'users' => $users,
            'stores' => $stores,
            'foodSharePoints' => $foodSharePoints
        ];
    }

    private function searchUserByEmail(string $query): array
    {
        if (str_ends_with($query, PLATFORM_MAILBOX_HOST) || !filter_var($query, FILTER_VALIDATE_EMAIL)) {
            return [];
        }

        $user = $this->foodsaverGateway->getUserFromEmail($query);
        if (empty($user)) {
            return [];
        }

        return [
            'id' => $user['id'],
            'name' => $user['name'],
            'teaser' => 'FS-ID: ' . $user['id'] . ' | Mail: ' . $user['email'],
        ];
    }

    private function searchUserByID(string $query): array
    {
        if (!preg_match('/^[0-9]+$/', $query)) {
            return [];
        }
        $userId = intval($query);

        if (!$this->foodsaverGateway->foodsaverExists($userId)) {
            return [];
        }

        return [
            'id' => $userId,
            'name' => $this->foodsaverGateway->getFoodsaverName($userId),
            'teaser' => 'FS-ID: ' . $userId,
        ];
    }

    /**
     * Generates the search index for instant search. Each category (stores, regions, buddies, groups)
     * is mapped to a list of {@link SearchIndexEntry}s.
     */
    public function generateIndex(): array
    {
        $userId = $this->session->id();
        $index = [];

        // load buddies of the user
        if ($buddies = $this->buddyGateway->listBuddies($userId)) {
            $index['myBuddies'] = array_map(function ($b) {
                $img = '/img/avatar-mini.png';
                if (!empty($b['photo'])) {
                    $img = $this->imageHelper->img($b['photo']);
                }

                return SearchIndexEntry::create($b['id'], $b['name'] . ' ' . $b['nachname'], null, $img);
            }, $buddies);
        }

        // load groups in which the user is a member
        if ($groups = $this->workGroupGateway->listMemberGroups($userId)) {
            $index['myGroups'] = array_map(function ($b) {
                $img = '/img/groups.png';
                if (!empty($b['photo'])) {
                    $img = 'images/' . str_replace('photo/', 'photo/thumb_', $b['photo']);
                }

                return SearchIndexEntry::create($b['id'], $b['name'], $this->sanitizerService->tt($b['teaser'], 65), $img);
            }, $groups);
        }

        // load stores in which the user is a member
        if ($betriebe = $this->storeGateway->listMyStores($userId)) {
            $index['myStores'] = array_map(function ($b) {
                return SearchIndexEntry::create($b['id'], $b['name'], $b['str'] . ', ' . $b['plz'] . ' ' . $b['stadt'], null);
            }, $betriebe);
        }

        // load regions in which the user is a member
        $bezirke = $this->regionGateway->listForFoodsaverExceptWorkingGroups($userId);
        $index['myRegions'] = array_map(function ($b) {
            return SearchIndexEntry::create($b['id'], $b['name'], null, null);
        }, $bezirke);

        return $index;
    }

    public function searchForUser(string $query, ?int $regionId): array
    {
        // Search by user ID
        if (preg_match('/^[0-9]+$/', $query) && $this->foodsaverGateway->foodsaverExists((int)$query)) {
            if (is_null($regionId) || $this->regionGateway->hasMember((int)$query, $regionId)) {
                $user = $this->foodsaverGateway->getFoodsaverName((int)$query);

                return [['id' => (int)$query, 'value' => $user . ' (' . (int)$query . ')']];
            } else {
                return [];
            }
        }

        // Find all regions in which the user is allowed to search
        if (!empty($regionId)) {
            $regions = [$regionId];
        } elseif (in_array(RegionIDs::EUROPE_WELCOME_TEAM, $this->session->listRegionIDs(), true) ||
            $this->session->mayRole(Role::ORGA)) {
            $regions = null;
        } else {
            $regions = array_column(array_filter(
                $this->session->getRegions(),
                function ($v) {
                    return in_array($v['type'], UnitType::getSearchableUnitTypes());
                }
            ), 'id');
            $ambassador = $this->session->getMyAmbassadorRegionIds();
            foreach ($ambassador as $region) {
                /* TODO: Refactor listIdsForDescendantsAndSelf to work with multiple regions. I did not do this now as it might impose too big of a risk for the release.
                2020-05-15 NerdyProjects I will care within a few weeks!
                Anyway, the performance of this should be orders of magnitude higher than the previous implementation.
                 */
                $regions = array_merge(
                    $regions,
                    $this->regionGateway->listIdsForDescendantsAndSelf($region)
                );
            }
            $regions = array_unique($regions);
        }

        $results = $this->searchGateway->searchUserInGroups($query, [], $regions);

        return array_map(function ($v) {
            return ['id' => $v->id, 'value' => $v->name . ' (' . $v->id . ')'];
        }, $results);
    }
}
