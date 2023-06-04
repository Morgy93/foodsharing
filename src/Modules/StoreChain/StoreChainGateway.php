<?php

namespace Foodsharing\Modules\StoreChain;

use Exception;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Pagination;
use Foodsharing\Modules\Foodsaver\DTO\FoodsaverForAvatar;
use Foodsharing\Modules\StoreChain\DTO\StoreChain;
use Foodsharing\Modules\StoreChain\DTO\StoreChainForChainList;

class StoreChainGateway extends BaseGateway
{
    /**
     * @throws Exception
     */
    public function addStoreChain(StoreChain $storeData): int
    {
        $id = $this->db->insert('fs_chain', [
            'name' => $storeData->name,
            'headquarters_zip' => $storeData->headquarters_zip,
            'headquarters_city' => $storeData->headquarters_city,
            'status' => $storeData->status->value,
            'modification_date' => $this->db->now(),
            'allow_press' => $storeData->allow_press,
            'forum_thread' => $storeData->forum_thread,
            'notes' => $storeData->notes,
            'common_store_information' => $storeData->common_store_information,
        ]);
        $this->updateAllKeyAccountManagers($id, $storeData->kams);

        return $id;
    }

    /**
     * @throws Exception
     */
    public function updateStoreChain(StoreChain $storeData, $updateKams)
    {
        $this->db->update(
            'fs_chain',
            [
                'name' => $storeData->name,
                'headquarters_zip' => $storeData->headquarters_zip,
                'headquarters_city' => $storeData->headquarters_city,
                'status' => $storeData->status->value,
                'modification_date' => $this->db->now(),
                'allow_press' => $storeData->allow_press,
                'forum_thread' => $storeData->forum_thread,
                'notes' => $storeData->notes,
                'common_store_information' => $storeData->common_store_information,
            ],
            ['id' => $storeData->id]
        );
        if ($updateKams) {
            $this->updateAllKeyAccountManagers($storeData->id, $storeData->kams);
        }
    }

    /**
     * Delete and insert all key account managers (kams).
     *
     * @param array $kams are account ids for key account managers
     *
     * @throws Exception
     */
    public function updateAllKeyAccountManagers(int $chainId, array $kams)
    {
        //delete previous kams
        $this->db->delete('fs_key_account_manager', ['chain_id' => $chainId]);

        //add new kams
        foreach ($kams as $fs_id) {
            $this->db->insert('fs_key_account_manager', [
                'chain_id' => $chainId,
                'foodsaver_id' => $fs_id,
            ]);
        }
    }

    /**
     * Check is user a key account manager for chain.
     *
     * @throws Exception
     */
    public function isUserKeyAccountManager(int $chainId, int $fs_id): bool
    {
        return $this->db->exists('fs_key_account_manager', ['foodsaver_id' => $fs_id, 'chain_id' => $chainId]);
    }

    /**
     * @return StoreChainForChainList[]
     *
     * @throws Exception
     */
    public function getStoreChains(?int $id = null, Pagination $pagination = new Pagination()): array
    {
        $where = '';
        if (!is_null($id)) {
            $where = 'WHERE c.`id` = :chainId';
        }

        $data = $this->db->fetchAll('SELECT
				c.*,
				COUNT(s.`id`) AS stores
			FROM `fs_chain` c
			LEFT OUTER JOIN `fs_betrieb` s ON
				s.`kette_id` = c.`id`
			' . $where . '
			GROUP BY c.`id`
		' . $pagination->buildSqlLimit(), $pagination->addSqlLimitParameters(!is_null($id) ? ['chainId' => $id] : []));

        $chains = [];
        foreach ($data as $chain) {
            $chain['kams'] = $this->getStoreChainKeyAccountManagers($chain['id']);
            $chains[] = StoreChainForChainList::createFromArray($chain);
        }

        return $chains;
    }

    /**
     * @return FoodsaverForAvatar[]
     */
    public function getStoreChainKeyAccountManagers(int $chainId): array
    {
        $kams = $this->db->fetchAll(
            'SELECT
				k.*, f.name, f.photo
			FROM
				fs_key_account_manager k
			JOIN fs_foodsaver f ON f.id = k.foodsaver_id
			WHERE k.chain_id = :chainId',
            ['chainId' => $chainId]
        );

        return array_map(function ($kam) {
            return FoodsaverForAvatar::createFromArray($kam);
        }, $kams);
    }

    /**
     * @throws Exception
     */
    public function chainExists($chainId): bool
    {
        return $this->db->exists('fs_chain', ['id' => $chainId]);
    }
}
