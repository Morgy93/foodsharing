<?php

namespace Foodsharing\RestApi\Models\Store;

use Foodsharing\Modules\Store\DTO\StoreStatusforMemberDTO;
use Foodsharing\Modules\Store\DTO\PickUpStatus;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Foodsharing\RestApi\Models\Store\MinimalStoreModel;
use Foodsharing\Modules\Core\DBConstants\StoreTeam\MembershipStatus;


class StoreStatusForMemberModel
{
	/**
	 * Identifier information of the store.
	 *
	 * @var MinimalStoreModel
	 * @Model(type=MinimalStoreModel::class)
	 */
	public MinimalStoreModel $store;

	/**
	 * Status of team membership for an store
     *
     * - 'APPLIED_FOR_TEAM' - Request to join the team for foodsaver running.
	 * - 'MEMBER' - Foodsaver which is regular member of the team.
	 * - 'JUMPER' - Foodsaver which is restricted member of the team (Standby/Jumper Member).
	 * - 'MANAGER' - Store responsible foodsaver which manages the store related activities.
     * 
	 * @var String
	 * @OA\Property(type="string",enum={"APPLIED_FOR_TEAM", "MEMBER", "JUMPER", "MANAGER"})
	 * 
	 */
	public String $membershipStatus;

    /**
	 * Indicator about the next open pickup for the store.
	 *
	 * - 'STATUS_GREEN' - Pick up with free slot is in future
	 * - 'STATUS_YELLOW_5_DAYS' - Next pick up with free slots is in 5 days
	 * - 'STATUS_ORANGE_3_DAYS' - Next pick up with free slots is 1-4 days
	 * - 'STATUS_RED_TODAY_TOMORROW' - Next pick up with free slots is today days
	 *  
	 * @var String
	 * @OA\Property(type="string",enum={"STATUS_GREEN", "STATUS_RED_TODAY_TOMORROW", "STATUS_ORANGE_3_DAYS", "STATUS_YELLOW_5_DAYS"})
	 */
	public String $pickup;


	/**
	 * @param StoreStatusforMemberDTO[] $items
	 * @return StoreStatusForMemberModel[]
	 */
	public static function transform(array $items) : array{
		$listOfStoreStatus = array();
		foreach ($items as $item) {
			$store= new StoreStatusForMemberModel();
			$store->store = new MinimalStoreModel();
			$store->store->id = $item->id;
			$store->store->name = $item->name;
			$store->membershipStatus = $item->isManaging? "MANAGER": MembershipStatus::toString($item->membershipStatus);
			$store->pickup = PickUpStatus::toString($item->pickupStatus);
			$listOfStoreStatus[] = $store;
		}
		return $listOfStoreStatus;
	}
}