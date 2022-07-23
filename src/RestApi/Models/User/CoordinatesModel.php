<?php

namespace Foodsharing\RestApi\Models\User;

use OpenApi\Annotations as OA;

/**
 * Describes the geogrpahic coordinates for visualization on maps.
 *
 * @OA\Schema(required={"lat", "lon"})
 */
class CoordinatesModel
{
	/**
	 * Latitude.
	 *
	 * @OA\Property(type="string", example="2.520007")
	 */
	public float $lat;

	/**
	 * Longitude.
	 *
	 *  @OA\Property(type="string", example="13.404954")
	 */
	public int $lon;
}
