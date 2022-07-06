<?php

namespace Foodsharing\RestApi\Models\Store;

use Foodsharing\Modules\Core\DBConstants\StoreTeam\MembershipStatus;
use Foodsharing\Modules\Store\DTO\PickUpStatus;
use Foodsharing\Modules\Store\DTO\StoreStatusforMemberDTO;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class StoreStatusForMemberModel
{
	/**
	 * Identifier information of the store.
	 *
	 * @Model(type=MinimalStoreModel::class)
	 */
	public MinimalStoreModel $store;

	/**
	 * Status of team membership for an store.
	 *
	 * - 'APPLIED_FOR_TEAM' - Request to join the team for foodsaver running.
	 * - 'MEMBER' - Foodsaver which is regular member of the team.
	 * - 'JUMPER' - Foodsaver which is restricted member of the team (Standby/Jumper Member).
	 * - 'MANAGER' - Store responsible foodsaver which manages the store related activities.
	 *
	 * @OA\Property(type="string",enum={"APPLIED_FOR_TEAM", "MEMBER", "JUMPER", "MANAGER"})
	 */
	public String $membershipStatus;

	/**
	 * Indicator about the next open pickup for the store.
	 *
	 * - 'GREEN' - Pick up with free slot is in future
	 * - 'YELLOW_5_DAYS' - Next pick up with free slots is in 5 days
	 * - 'ORANGE_3_DAYS' - Next pick up with free slots is 1-4 days
	 * - 'RED_TODAY_TOMORROW' - Next pick up with free slots is today days
	 *
	 * @OA\Property(type="string",enum={"GREEN", "RED_TODAY_TOMORROW", "ORANGE_3_DAYS", "YELLOW_5_DAYS"})
	 */
	public String $pickup;

	/**
	 * @param StoreStatusforMemberDTO[] $items
	 *
	 * @return StoreStatusForMemberModel[]
	 */
	public static function transform(array $items): array
	{
		$listOfStoreStatus = [];
		foreach ($items as $item) {
			$model = new StoreStatusForMemberModel();
			$store = new MinimalStoreModel();
			$store->id = $item->store->id;
			$store->name = $item->store->name;
			$model->store = $store;
			$model->membershipStatus = $item->isManaging ? 'MANAGER' : MembershipStatus::toString($item->membershipStatus);
			$model->pickup = PickUpStatus::toString($item->pickupStatus);
			$listOfStoreStatus[] = $model;
		}

		return $listOfStoreStatus;
	}
}
