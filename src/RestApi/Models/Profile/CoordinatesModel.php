<?php

namespace Foodsharing\RestApi\Models\Profile;

use OpenApi\Annotations as OA;

/**
 * Describes the geogrpahic coordinates for visualization on maps.
 *
 * @OA\Schema()
 */
class CoordinatesModel
{
	/**
	 * Latitude.
	 *
	 * @OA\Property(example="2.520007")
	 */
	public float $lat;

	/**
	 * Longitude.
	 *
	 * @OA\Property(example="13.404954")
	 */
	public int $lon;
}
