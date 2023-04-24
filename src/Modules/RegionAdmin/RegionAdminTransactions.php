<?php

namespace Foodsharing\Modules\RegionAdmin;

use Foodsharing\Modules\Map\DTO\MapMarker;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\RegionAdmin\DTO\RegionDetails;
use Foodsharing\Modules\Store\StoreGateway;

class RegionAdminTransactions
{
    public function __construct(
        private readonly RegionGateway $regionGateway,
        private readonly StoreGateway $storeGateway,
    ) {
    }

    public function getRegionDetails(int $regionId): RegionDetails
    {
        $region = $this->regionGateway->getRegion($regionId);
        $stores = $this->storeGateway->getMapsStores($regionId);
        $stores = array_map(function ($store) {
            return MapMarker::create($store['id'], $store['lat'], $store['lon']);
        }, $stores);

        return RegionDetails::create($regionId, $region['name'], $stores);
    }
}
