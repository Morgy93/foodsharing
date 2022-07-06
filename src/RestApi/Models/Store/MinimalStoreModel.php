<?php

namespace Foodsharing\RestApi\Models\Store;

use OpenApi\Annotations as OA;

class MinimalStoreModel
{
	/**
	 * The unique identifier of the store.
	 *
	 * @OA\Property(format="int64", example=1)
	 */
	public int $id;

	/**
	 * The name of the store.
	 *
	 * @OA\Property(type="String", example="Govinda Natur GmbH")
	 */
	public String $name;
}
