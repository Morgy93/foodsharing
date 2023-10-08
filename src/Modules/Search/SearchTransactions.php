<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Buddy\BuddyGateway;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
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
     * Searches for regions, stores, foodsavers, food share points and working groups.
     *
     * @param string $query the search query
     *
     * @return array
     */
    public function search(string $query): array
    {
        // TODO: Search by Email for IT-Support Group and ORGA
        // $this->searchPermissions->maySearchByEmailAddress()

        $foodsaverId = $this->session->id();
        $maySearchGlobal = $this->searchPermissions->maySearchGlobal();

        $regions = $this->searchGateway->searchRegions($query, $foodsaverId);
        $this->formatUserList($regions, 'ambassador', ['id', 'name', 'photo']);

        $searchAllWorkingGroups = $this->searchPermissions->maySearchAllWorkingGroups();
        $workingGroups = $this->searchGateway->searchWorkingGroups($query, $foodsaverId, $searchAllWorkingGroups);
        $this->formatUserList($workingGroups, 'admin', ['id', 'name', 'photo']);

        $includeInactiveStores = $this->session->mayRole(Role::STORE_MANAGER);
        $stores = $this->searchGateway->searchStores($query, $foodsaverId, $includeInactiveStores, $maySearchGlobal);

        $foodSharePoints = $this->searchGateway->searchFoodSharePoints($query, $foodsaverId, $maySearchGlobal);

        $chats = $this->searchGateway->searchChats($query, $foodsaverId);
        $this->formatUserList($chats, 'member', ['id', 'name', 'photo']);

        $threads = $this->searchGateway->searchThreads($query, $foodsaverId);

        $users = $this->searchGateway->searchUsers($query, $foodsaverId, $maySearchGlobal);

        return [
            'regions' => $regions,
            'workingGroups' => $workingGroups,
            'stores' => $stores,
            'foodSharePoints' => $foodSharePoints,
            'chats' => $chats,
            'threads' => $threads,
            'users' => $users,
        ];
    }

    private function formatUserList(array &$entries, string $namespace, array $keys)
    {
        foreach ($entries as &$entry) {
            if (empty($entry[$namespace . '_' . $keys[0] . 's'])) {
                $entry[$namespace . 's'] = [];
            } else {
                $entry[$namespace . 's'] = array_map(
                    fn (...$values) => array_combine($keys, $values),
                    ...array_map(fn ($key) => explode(',', $entry[$namespace . '_' . $key . 's']), $keys)
                );
            }
            foreach ($keys as $key) {
                unset($entry[$namespace . '_' . $key . 's']);
            }
        }
    }
}
