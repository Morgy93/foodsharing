<?php

namespace Foodsharing\RestApi\Models\Map;

use Foodsharing\Modules\Core\DBConstants\Map\MapConstants;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Describes the message options for leaving a pickup slot.
 */
class StoreFilterModel
{
	public function __construct(
		/**
		 * Latitude.
		 *
		 * @OA\Property(example=50.89)
		 * @Assert\Type(type="float",
		 *  message="The value {{ value }} is not a valid {{ type }}.",
		 * )]
		 */
		public $latitude = MapConstants::CENTER_GERMANY_LAT,

		/**
		 * Longitude.
		 *
		 * @OA\Property(example=10.13)
		 * @Assert\Type(type="float",
		 *  message="The value {{ value }} is not a valid {{ type }}.",
		 * )]
		 */
		public $longitude = MapConstants::CENTER_GERMANY_LON,

		/**
		 * Search distance in kilometers.
		 *
		 * @OA\Property(example=45)
		 * @Assert\Range(min=1, max=150,
		 *  notInRangeMessage="You must enter a search distance between {{ min }} km and {{ max }} km."
		 * )
		 */
		public $distanceInKm = MapConstants::DEFAULT_SEARCH_DISTANCE,

		/**
		 * Cooperation status.
		 *
		 * - 0 - UNCLEAR
		 * - 1 - NO_CONTACT
		 * - 2 - IN_NEGOTIATION
		 * - 3 - COOPERATION_STARTING
		 * - 4 - DOES_NOT_WANT_TO_WORK_WITH_US
		 * - 5 - COOPERATION_ESTABLISHED
		 * - 6 - GIVES_TO_OTHER_CHARITY
		 * - 7 - PERMANENTLY_CLOSED
		 * - 8 - INACTIVE
		 *
		 * @OA\Property(example="[1,3,2]")
		 *
		 * @Assert\Type (type="array")
		 */
		public array $cooperationStatus = [],

		/**
		 * Team status.
		 *
		 * - 0 - CLOSED
		 * - 1 - OPEN
		 * - 2 - OPEN_SEARCHING
		 *
		 * @OA\Property(example="[1,3,4]")
		 *
		 * @Assert\Type (type="array")
		 */
		public array $teamStatus = [],
	) {
	}
}
