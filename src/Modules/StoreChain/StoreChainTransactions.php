<?php

namespace Foodsharing\Modules\StoreChain;

use Exception;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\ForumGateway;
use Foodsharing\Modules\StoreChain\DTO\StoreChain;

class StoreChainTransactions
{
    public function __construct(
        private readonly StoreChainGateway $storeChainGateway,
        private readonly FoodsaverGateway $foodsaverGateway,
        private readonly ForumGateway $forumGateway
    ) {
    }

    /**
     * @throws Exception
     */
    public function updateStoreChain(StoreChain $storeData, bool $updateKams): void
    {
        if (!$storeData->id) {
            throw new StoreChainTransactionException(StoreChainTransactionException::INVALID_STORECHAIN_ID);
        }

        $this->throwExceptionIfKeyAccountManagerDoesNotExist($storeData->kams);
        $this->throwExceptionIfForumInvalid($storeData->forumThread);

        $this->storeChainGateway->updateStoreChain($storeData, $updateKams);
    }

    /**
     * @throws Exception
     */
    public function addStoreChain(StoreChain $storeData): int
    {
        $this->throwExceptionIfKeyAccountManagerDoesNotExist($storeData->kams);
        $this->throwExceptionIfForumInvalid($storeData->forumThread);

        return $this->storeChainGateway->addStoreChain($storeData);
    }

    private function throwExceptionIfKeyAccountManagerDoesNotExist($kams)
    {
        $ids = array_map(function ($item) { return $item->id;}, $kams);
        if (!$this->foodsaverGateway->foodsaversExist($ids)) {
            throw new StoreChainTransactionException(StoreChainTransactionException::KEY_ACCOUNT_MANAGER_ID_NOT_EXISTS);
        }
    }

    private function throwExceptionIfForumInvalid(int $threadId)
    {
        $forumResult = $this->forumGateway->getForumsForThread($threadId);
        if (empty($forumResult)) {
            throw new StoreChainTransactionException(StoreChainTransactionException::THREAD_ID_NOT_EXISTS);
        }

        if ($forumResult[0]['forumId'] != RegionIDs::STORE_CHAIN_GROUP) {
            throw new StoreChainTransactionException(StoreChainTransactionException::WRONG_FORUM);
        }
    }
}
