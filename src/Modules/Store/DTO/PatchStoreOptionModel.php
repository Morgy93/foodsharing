<?php

namespace Foodsharing\RestApi\Models\Store;

namespace Foodsharing\Modules\Store\DTO;

/**
 * Describes configuration option for managing the store.
 */
class PatchStoreOptionModel
{
    /**
     * Boolean which represents usage of the region for pickup slot allocation rule.
     */
    public ?bool $useRegionPickupRule = null;

    public static function apply(PatchStoreOptionModel &$storeOptionChange, StoreOptionModel &$storeOption): bool
    {
        $patchNeeded = false;
        if (!is_null($storeOptionChange->useRegionPickupRule)) {
            $patchNeeded = true;
            $storeOption->useRegionPickupRule = $storeOptionChange->useRegionPickupRule;
        }

        return $patchNeeded;
    }
}
