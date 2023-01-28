<?php

namespace Foodsharing\Modules\Region\DTO;

/**
 * Describes a region by the minimal information.
 *
 * This information could be used by transactions or RestAPIs to provide more information about a region then the ID.
 */
class MinimalRegionIdentifier
{
    /**
     * Unique identifier of region.
     */
    public int $id;

    /**
     * Name of the region.
     */
    public ?string $name = null;
}
