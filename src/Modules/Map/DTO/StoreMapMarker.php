<?php

namespace Foodsharing\Modules\Map\DTO;

use Foodsharing\Modules\Core\DBConstants\Map\MapMarkerType;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\TeamStatus;
use OpenApi\Annotations as OA;

class StoreMapMarker extends MapMarker
{
	public function __construct(
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
		 * @OA\Property(example="UNCLEAR")
		 */
		public ?string $cooperationStatus = null,

		/**
		 * Team status.
		 *
		 * - 0 - CLOSED
		 * - 1 - OPEN
		 * - 2 - OPEN_SEARCHING
		 *
		 * @OA\Property(example="CLOSED")
		 */
		public ?string $teamStatus = null,
	) {
		parent::__construct();
	}

	public static function createFromArray(mixed $value, ?int $type = null): StoreMapMarker
	{
		$marker = new StoreMapMarker();
		$marker->id = $value['id'];
		$marker->name = $value['name'];
		$marker->description = $value['description'];

		$marker->latitude = $value['lat'];
		$marker->longitude = $value['lon'];

		$marker->setType(MapMarkerType::STORE);

		$marker->teamStatus = TeamStatus::getStatus($value['teamStatus']);
		$marker->cooperationStatus = CooperationStatus::getStatus($value['cooperationStatus']);

		return $marker;
	}
}
