<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\StoreChain\KeyAccountManagerGateway;

class StoreChainPermissions
{
    public function __construct(
        private readonly Session $session,
        private readonly KeyAccountManagerGateway $gateway
    ) {
    }

    public function mayAdministrateStoreChains(): bool
    {
        return $this->session->mayRole(Role::ORGA) || $this->session->isAdminFor(RegionIDs::STORE_CHAIN_GROUP);
    }

    public function mayAdministrateStoreChain($chainId): bool
    {
        return $this->mayAdministrateStoreChains() || $this->gateway->isUserKeyAccountManager($chainId, $this->session->id());
    }

    public function maySeeChainList(): bool
    {
        return $this->session->mayRole(Role::FOODSAVER);
    }

    public function mayCreateChain(): bool
    {
        return $this->mayAdministrateStoreChains();
    }

    public function mayEditChain($chainId): bool
    {
        return $this->mayAdministrateStoreChain($chainId);
    }

    public function mayEditKams($chainId): bool
    {
        return $this->mayAdministrateStoreChains();
    }

    public function maySeeChainStores($chainId): bool
    {
        return $this->mayAdministrateStoreChain($chainId);
    }
}
