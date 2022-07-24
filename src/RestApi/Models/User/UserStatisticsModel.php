<?php

namespace Foodsharing\RestApi\Models\User;

use OpenApi\Annotations as OA;

/**
 * Describes statistical information about the user.
 *
 * @OA\Schema()
 */
class UserStatisticsModel
{
	/**
	 * saved food in kg.
	 *
	 * @OA\Property(example=420)
	 */
	public float $weight;

	/**
	 * Count of picked up.
	 *
	 * @OA\Property(example=42)
	 */
	public int $count;
}
