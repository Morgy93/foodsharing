<?php

namespace Foodsharing\Modules\StoreChain;

use DateTime;
use Exception;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Foodsaver\DTO\FoodsaverForAvatar;
use Foodsharing\Modules\StoreChain\DTO\StoreChainForChainList;
use Foodsharing\Modules\StoreChain\DTO\StoreChainForUpdate;

class StoreChainGateway extends BaseGateway
{
    /**
     * @throws Exception
     */
    public function addStoreChain(StoreChainForUpdate $storeData): int
    {
        return $this->db->insert('fs_chain', [
            'name' => $storeData->name,
            'headquarters_zip' => $storeData->headquarters_zip,
            'headquarters_city' => $storeData->headquarters_city,
            'status' => $storeData->status,
            'modification_date' => $this->db->now(),
            'allow_press' => $storeData->allow_press,
            'forum_thread' => $storeData->forum_thread,
            'notes' => $storeData->notes,
            'common_store_information' => $storeData->common_store_information,
        ]);
    }

    /**
     * @throws Exception
     */
    public function updateStoreChain(StoreChainForUpdate $storeData, $id)
    {
        $this->db->update(
            'fs_chain',
            [
                'name' => $storeData->name,
                'headquarters_zip' => $storeData->headquarters_zip,
                'headquarters_city' => $storeData->headquarters_city,
                'status' => $storeData->status,
                'modification_date' => $this->db->now(),
                'allow_press' => $storeData->allow_press,
                'forum_thread' => $storeData->forum_thread,
                'notes' => $storeData->notes,
                'common_store_information' => $storeData->common_store_information,
            ],
            ['id' => $id]
        );
    }

    /**
     * @return StoreChainForChainList[]
     *
     * @throws Exception
     */
    public function getStoreChains(?int $id = null): array
    {
        $where = '';
        if (!is_null($id)) {
            $where = 'WHERE c.`id` = ' . $id;
        }
        $data = $this->db->fetchAll('SELECT
				c.*,
				COUNT(s.`id`) AS stores
			FROM `fs_chain` c
			LEFT OUTER JOIN `fs_betrieb` s ON
				s.`kette_id` = c.`id`
			' . $where . '
			GROUP BY c.`id`
		');

        $chains = [];
        foreach ($data as $chain) {
            $formatted = new StoreChainForChainList();
            $formatted->name = $chain['name'];
            $formatted->status = $chain['status'];
            $formatted->allow_press = $chain['allow_press'];
            $formatted->id = $chain['id'];
            $formatted->headquarters_zip = $chain['headquarters_zip'];
            $formatted->headquarters_city = $chain['headquarters_city'];
            $formatted->modification_date = new DateTime($chain['modification_date']);
            $formatted->forum_thread = $chain['forum_thread'];
            $formatted->notes = $chain['notes'];
            $formatted->common_store_information = $chain['common_store_information'];
            $formatted->store_count = $chain['stores'];
            $formatted->kams = [];
            $chains[$chain['id']] = $formatted;
        }

        // Adding Kams:
        if (!is_null($id)) {
            $where = 'WHERE k.chain_id = ' . $id;
        }
        $kams = $this->db->fetchAll('SELECT
				k.*, f.name, f.photo
			FROM
				fs_key_account_manager k
			JOIN fs_foodsaver f ON f.id = k.foodsaver_id
			' . $where
        );

        foreach ($kams as $kam) {
            $formatted = new FoodsaverForAvatar();
            $formatted->id = $kam['foodsaver_id'];
            $formatted->name = $kam['name'];
            $formatted->avatar = $kam['photo'];
            $chains[$kam['chain_id']]->kams[] = $formatted;
        }

        return array_values($chains);
    }

    /**
     * @throws Exception
     */
    public function chainExists($chainId): bool
    {
        return $this->db->exists('fs_chain', ['id' => $chainId]);
    }

    /**
     * @throws Exception
     */
    public function getChainStores($chainId): array
    {
        return $this->db->fetchAllByCriteria('fs_betrieb', ['id', 'name'], ['kette_id' => $chainId]);
    }
}
