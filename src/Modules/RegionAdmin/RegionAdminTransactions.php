<?php

namespace Foodsharing\Modules\RegionAdmin;

use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\Modules\Map\DTO\MapMarker;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\RegionAdmin\DTO\RegionDetails;
use Foodsharing\Modules\Store\StoreGateway;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RegionAdminTransactions
{
    public function __construct(
        private readonly RegionGateway $regionGateway,
        private readonly StoreGateway $storeGateway,
        private readonly GroupFunctionGateway $groupFunctionGateway,
    ) {
    }

    public function getRegionDetails(int $regionId): RegionDetails
    {
        $region = $this->regionGateway->getRegion($regionId);
        $stores = $this->storeGateway->getMapsStores($regionId);
        $stores = array_map(function ($store) {
            return MapMarker::create($store['id'], $store['lat'], $store['lon']);
        }, $stores);

        $workingGroupFunction = $region['type'] === UnitType::WORKING_GROUP
            ? $this->groupFunctionGateway->getRegionGroupFunctionId($region['id'], $region['parent_id'])
            : null;

        return RegionDetails::create(
            $regionId,
            $region['name'],
            $region['parent_id'],
            $region['type'],
            $workingGroupFunction,
            $region['email'],
            $region['email_name'],
            $stores
        );
    }

    /**
     * Adds a new region and returns details about that region.
     */
    public function addRegion(int $parentId): RegionDetails
    {
        $parentRegion = $this->regionGateway->getRegion($parentId);
        if (empty($parentRegion)) {
            throw new BadRequestHttpException('parent region does not exist');
        }

        $regionId = $this->regionGateway->addRegion($parentId, 'Neue Region');
        $this->regionGateway->setRegionHasChildren($parentId, true);

        return $this->getRegionDetails($regionId);
    }
}
