<?php

namespace Foodsharing\RestApi\Models\User;

use OpenApi\Annotations as OA;

/**
 * Desribes foodsharing user and this information
 *
 * @OA\Schema(required={"weight", "count"})
 */
class UserStatisticsModel
{
	/**
	 *
	 * @OA\Property(type="string", example="Govinda Natur GmbH")
	 */
	public float $weight;

	/**
	 * ?
	 *
	 * @OA\Property(type="string", example="Govinda Natur GmbH")
	 */
	public int $count;
}