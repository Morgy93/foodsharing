<?php

namespace Foodsharing\Modules\StoreChain;

use Exception;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Foodsaver\DTO\FoodsaverForAvatar;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\ForumGateway;
use Foodsharing\Modules\StoreChain\DTO\PatchStoreChain;
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
    public function addStoreChain(StoreChain $storeData): int
    {
        $this->throwExceptionIfKeyAccountManagerDoesNotExist($storeData->kams);
        $this->throwExceptionIfForumInvalid($storeData->forumThread);

        return $this->storeChainGateway->addStoreChain($storeData);
    }

    public function updateStoreChain(int $chainId, PatchStoreChain $storeModel, bool $updateKams): bool
    {
        if (!$chainId) {
            throw new StoreChainTransactionException(StoreChainTransactionException::INVALID_STORECHAIN_ID);
        }

        $changed = false;
        $params = $this->storeChainGateway->getStoreChains($chainId)[0]->chain;
        $params->id = $chainId;
        if (!empty($storeModel->name)) {
            $params->name = $storeModel->name;
            if (empty(trim(strip_tags($params->name)))) {
                throw new StoreChainTransactionException(StoreChainTransactionException::EMPTY_NAME);
            }
            $changed = true;
        }

        if (!empty($storeModel->status)) {
            $status = StoreChainStatus::tryFrom($storeModel->status);
            if (!$status instanceof StoreChainStatus) {
                throw new StoreChainTransactionException(StoreChainTransactionException::INVALID_STATUS);
            }
            $params->status = $status;
            $changed = true;
        }
        if (!empty($storeModel->headquartersZip)) {
            $params->headquartersZip = $storeModel->headquartersZip;
            $changed = true;
        }
        if (!empty($storeModel->headquartersCity)) {
            $params->headquartersCity = $storeModel->headquartersCity;
            $changed = true;
        }
        if (!empty($storeModel->allowPress)) {
            $params->allowPress = $storeModel->allowPress;
            $changed = true;
        }
        if (!empty($storeModel->forumThread)) {
            $params->forumThread = $storeModel->forumThread;
            $changed = true;
        }
        if (!empty($storeModel->notes)) {
            $params->notes = $storeModel->notes;
            $changed = true;
        }
        if (!empty($storeModel->commonStoreInformation)) {
            $params->commonStoreInformation = $storeModel->commonStoreInformation;
            $changed = true;
        }
        if (!empty($storeModel->kams)) {
            $params->kams = array_map(function ($kam) {
                $obj = new FoodsaverForAvatar();
                $obj->id = $kam;

                return $obj;
            }, $storeModel->kams);
            $changed = true;
        }

        if ($changed) {
            $this->throwExceptionIfKeyAccountManagerDoesNotExist($params->kams);
            $this->throwExceptionIfForumInvalid($params->forumThread);

            $this->storeChainGateway->updateStoreChain($params, $updateKams);

            return true;
        } else {
            return false;
        }
    }

    private function throwExceptionIfKeyAccountManagerDoesNotExist($kams)
    {
        $ids = array_map(function ($item) { return $item->id; }, $kams);
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
