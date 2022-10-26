<?php

namespace Foodsharing\RestApi\Models\Map;

use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Describes the message options for leaving a pickup slot.
 */
class StoreFilterModel extends FilterModel
{
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
	 * @Serializer\Type(name="array<int>")
	 */
	public array $cooperationStatus = [];

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
	 * @Serializer\Type(name="array<int>")
	 */
	public array $teamStatus = [];
}
