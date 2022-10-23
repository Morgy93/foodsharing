<?php

namespace Foodsharing\RestApi\Models\Store;

use Foodsharing\Modules\Store\StoreTransactions;
use OpenApi\Annotations as OA;

/**
 * Describes the common store metadata.
 */
class CommonStoreMetadataModel
{
	/**
	 * Maximum count of slots per pickup.
	 *
	 * The count of slots are limited by the foodsharing platform.
	 *
	 * @OA\Property(format="int64", example=1)
	 */
	public int $maxCountPickupSlot = StoreTransactions::MAX_SLOTS_PER_PICKUP;
}
