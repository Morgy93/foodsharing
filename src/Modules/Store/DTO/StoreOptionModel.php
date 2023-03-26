<?php

namespace Foodsharing\RestApi\Models\Store;

namespace Foodsharing\Modules\Store\DTO;

/**
 * Describes configuration option for managing the store.
 *
 * The configuration options are settings for the store which influence the behavior
 * inside of the foodsharing platform.
 */
class StoreOptionModel
{
    /**
     * Boolean which represents usage of the region for pickup slot allocation rule.
     */
    public bool $useRegionPickupRule = false;

    public static function createFromArray($queryResult)
    {
        $obj = new StoreOptionModel();

        $obj->useRegionPickupRule = $queryResult['useRegionPickupRule'];

        return $obj;
    }
}
