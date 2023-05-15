<?php

namespace Foodsharing\Modules\StoreChain;

use Exception;
use Foodsharing\Modules\StoreChain\DTO\StoreChainForUpdate;

class StoreChainTransactions
{
    public function __construct(
        private readonly StoreChainGateway $storeChainGateway,
        private readonly KeyAccountManagerGateway $keyAccountManagerGateway
    ) {
    }

    /**
     * @throws Exception
     */
    public function updateStoreChain(StoreChainForUpdate $storeData, $id, bool $updateKams): void
    {
        $this->storeChainGateway->updateStoreChain($storeData, $id);

        if ($updateKams) {
            $this->keyAccountManagerGateway->updateAllKeyAccountManagers($id, $storeData->kams);
        }
    }

    /**
     * @throws Exception
     */
    public function addStoreChain(StoreChainForUpdate $storeData): int
    {
        $chainId = $this->storeChainGateway->addStoreChain($storeData);

        $this->keyAccountManagerGateway->updateAllKeyAccountManagers($chainId, $storeData->kams);

        return $chainId;
    }
}
