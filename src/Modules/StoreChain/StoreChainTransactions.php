<?php

namespace Foodsharing\Modules\StoreChain;

use Exception;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\StoreChain\DTO\StoreChain;

class StoreChainTransactions
{
    public function __construct(
        private readonly StoreChainGateway $storeChainGateway,
        private readonly FoodsaverGateway $foodsaverGateway
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

        if ($updateKams && !$this->foodsaverGateway->foodsaversExist($storeData->kams)) {
            throw new StoreChainTransactionException(StoreChainTransactionException::KEY_ACCOUNT_MANAGER_ID_NOT_EXISTS);
        }

        $this->storeChainGateway->updateStoreChain($storeData, $updateKams);
    }

    /**
     * @throws Exception
     */
    public function addStoreChain(StoreChain $storeData): int
    {
        if (!$this->foodsaverGateway->foodsaversExist($storeData->kams)) {
            throw new StoreChainTransactionException(StoreChainTransactionException::KEY_ACCOUNT_MANAGER_ID_NOT_EXISTS);
        }

        return $this->storeChainGateway->addStoreChain($storeData);
    }
}
