<?php

namespace Foodsharing\RestApi\Models\User;

use OpenApi\Annotations as OA;

/**
 * Desribes foodsharing user and this information
 *
 * @OA\Schema(required={"lat", "lon"})
 */
class CoordinatesModel
{
	/**
	 *
	 * @OA\Property(type="string", example="Govinda Natur GmbH")
	 */
	public float $lat;

	/**
	 *
	 * @OA\Property(type="string", example="Govinda Natur GmbH")
	 */
	public int $lon;
}