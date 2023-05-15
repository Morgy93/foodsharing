<?php

namespace Foodsharing\Modules\StoreChain;

use Exception;
use Foodsharing\Modules\Core\BaseGateway;

class KeyAccountManagerGateway extends BaseGateway
{
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
}
