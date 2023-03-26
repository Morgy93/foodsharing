<?php

namespace Foodsharing\RestApi\Models\Store;

use Foodsharing\Modules\Store\DTO\StoreListInformation;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * Represents a list of store overview information with pagination information.
 */
class StorePaginationResult
{
    /**
     * Total count of stores.
     *
     * @OA\Property(format="int64", example=1)
     */
    public int $total = 0;

    /**
     * @var array<StoreListInformation> Array of store information
     *
     * @OA\Property(
     *        type="array",
     *        @OA\Items(ref=@Model(type=StoreListInformation::class))
     *      )
     */
    public array $stores = [];
}
