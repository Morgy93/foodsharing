<?php

namespace Foodsharing\RestApi\Models\Map;

use Foodsharing\Modules\Core\DBConstants\Map\MapConstants;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Describes the message options for leaving a pickup slot.
 */
class FilterModel
{
	/**
	 * Latitude.
	 *
	 * @OA\Property(example=50.89)
	 */
	public float $lat = MapConstants::CENTER_GERMANY_LAT;

	/**
	 * Longitude.
	 *
	 * @OA\Property(example=10.13)
	 */
	public float $lon = MapConstants::CENTER_GERMANY_LON;

	/**
	 * Search distance in kilometers.
	 *
	 * @OA\Property(example=45)
	 * @Assert\Range(min=1, max=150,
	 *  notInRangeMessage="You must enter a search distance between {{ min }} km and {{ max }} km."
	 * )
	 */
	public int $distanceInKm = MapConstants::DEFAULT_SEARCH_DISTANCE;
}
