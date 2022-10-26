<?php

namespace Foodsharing\Modules\Map\DTO;

use Foodsharing\Modules\Core\DBConstants\Map\MapMarkerType;
use OpenApi\Annotations as OA;

class StoreMapMarker extends MapMarker
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
	 * @OA\Property(example=5)
	 */
	public int $cooperationStatus = 0;

	/**
	 * Team status.
	 *
	 * - 0 - CLOSED
	 * - 1 - OPEN
	 * - 2 - OPEN_SEARCHING
	 *
	 * @OA\Property(example=1)
	 */
	public int $teamStatus = 0;

	/**
	 * Creates a marker out of an array representation like the Database select.
	 */
	public static function createFromArray($queryResult, $type = MapMarkerType::STORE): StoreMapMarker
	{
		$marker = new StoreMapMarker();
		$marker->id = $queryResult['id'];
		$marker->name = $queryResult['name'];
		$marker->description = $queryResult['public_info'];

		$marker->latitude = $queryResult['lat'];
		$marker->longitude = $queryResult['lon'];

		$marker->type = $type;

		$marker->cooperationStatus = $queryResult['betrieb_status_id'];
		$marker->teamStatus = $queryResult['team_status'];

		return $marker;
	}
}
