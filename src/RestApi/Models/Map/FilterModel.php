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
	public function __construct(
		/**
		 * Latitude.
		 *
		 * @OA\Property(example=MapConstants::CENTER_GERMANY_LAT)
		 * @Assert\Type(type="float",
		 *  message="The value {{ value }} is not a valid {{ type }}.",
		 * )]
		 */
		public float $latitude = MapConstants::CENTER_GERMANY_LAT,

		/**
		 * Longitude.
		 *
		 * @OA\Property(example=MapConstants::CENTER_GERMANY_LON)
		 * @Assert\Type(type="float",
		 *  message="The value {{ value }} is not a valid {{ type }}.",
		 * )]
		 */
		public float $longitude = MapConstants::CENTER_GERMANY_LON,

		/**
		 * Search distance in kilometers.
		 *
		 * @OA\Property(example=MapConstants::DEFAULT_SEARCH_DISTANCE)
		 * @Assert\Range(min=MapConstants::MIN_SEARCH_DISTANCE, max=MapConstants::MAX_SEARCH_DISTANCE,
		 *  notInRangeMessage="You must enter a search distance between {{ min }} km and {{ max }} km."
		 * )
		 */
		public int $distance = MapConstants::DEFAULT_SEARCH_DISTANCE,
	) {
	}
}
