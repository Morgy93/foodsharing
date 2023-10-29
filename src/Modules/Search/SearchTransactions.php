<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Search\DTO\ChatSearchResult;
use Foodsharing\Modules\Search\DTO\FoodSharePointSearchResult;
use Foodsharing\Modules\Search\DTO\StoreSearchResult;
use Foodsharing\Modules\Search\DTO\ThreadSearchResult;
use Foodsharing\Modules\Search\DTO\UserSearchResult;
use Foodsharing\Permissions\SearchPermissions;

class SearchTransactions
{
    public function __construct(
        private readonly SearchGateway $searchGateway,
        private readonly Session $session,
        private readonly SearchPermissions $searchPermissions
    ) {
    }

    /**
     * Searches for regions, stores, foodsavers, food share points and working groups.
     *
     * @param string $query the search query
     */
    public function search(string $query): array
    {
        // TODO: Search by Email for IT-Support Group and ORGA
        // $this->searchPermissions->maySearchByEmailAddress()

        $foodsaverId = $this->session->id();
        $maySearchGlobal = $this->searchPermissions->maySearchGlobal();
        $searchAllWorkingGroups = $this->searchPermissions->maySearchAllWorkingGroups();
        $includeInactiveStores = $this->session->mayRole(Role::STORE_MANAGER);

        $regions = $this->searchGateway->searchRegions($query, $foodsaverId);
        $workingGroups = $this->searchGateway->searchWorkingGroups($query, $foodsaverId, $searchAllWorkingGroups);
        $stores = $this->searchGateway->searchStores($query, $foodsaverId, $includeInactiveStores, $maySearchGlobal);
        $foodSharePoints = $this->searchGateway->searchFoodSharePoints($query, $foodsaverId, $maySearchGlobal);
        $chats = $this->searchGateway->searchChats($query, $foodsaverId);
        $threads = $this->searchGateway->searchThreads($query, $foodsaverId);
        $users = $this->searchGateway->searchUsers($query, $foodsaverId, $maySearchGlobal, $this->searchPermissions->maySearchByEmailAddress());

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
}
