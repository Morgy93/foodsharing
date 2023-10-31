<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Search\DTO\MixedSearchResult;
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
    public function search(string $query): MixedSearchResult
    {
        // TODO: Search by Email for IT-Support Group and ORGA
        // $this->searchPermissions->maySearchByEmailAddress()

        $foodsaverId = $this->session->id();
        $maySearchGlobal = $this->searchPermissions->maySearchGlobal();
        $searchAllWorkingGroups = $this->searchPermissions->maySearchAllWorkingGroups();
        $includeInactiveStores = $this->session->mayRole(Role::STORE_MANAGER);

        $result = new MixedSearchResult();
        $result->regions = $this->searchGateway->searchRegions($query, $foodsaverId);
        $result->workingGroups = $this->searchGateway->searchWorkingGroups($query, $foodsaverId, $searchAllWorkingGroups);
        $result->stores = $this->searchGateway->searchStores($query, $foodsaverId, $includeInactiveStores, $maySearchGlobal);
        $result->foodSharePoints = $this->searchGateway->searchFoodSharePoints($query, $foodsaverId, $maySearchGlobal);
        $result->chats = $this->searchGateway->searchChats($query, $foodsaverId);
        $result->threads = $this->searchGateway->searchThreads($query, $foodsaverId);
        $result->users = $this->searchGateway->searchUsers($query, $foodsaverId, $maySearchGlobal, $this->searchPermissions->maySearchByEmailAddress());

        return $result;
    }
}
