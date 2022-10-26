<?php

namespace Foodsharing\RestApi\Models\Map;

use OpenApi\Annotations as OA;

/**
 * Describes the message options for leaving a pickup slot.
 */
class StoreFilterModel extends FilterModel
{
	/**
	 * Cooperation status.
	 *
	 * @OA\Property(example=1)
	 */
	public array $cooperationStatus = array();

	/**
	 * Team status.
	 *
	 * @OA\Property(example=1)
	 */
	public array $teamStatus = array();

}
