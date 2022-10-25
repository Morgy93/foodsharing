<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Store\DTO\RegularPickup;

class RegularPickupGateway extends BaseGateway
{
	public function __construct(
		Database $db,
	) {
		parent::__construct($db);
	}

	/**
	 * Return a list for regular pickups for a store.
	 *
	 * @return RegularPickup[] List of found regular pickups for the store
	 */
	public function getRegularPickup(int $storeId): array
	{
		$times = $this->db->fetchAll('
			SELECT `time`, `dow`, `fetcher`
			FROM `fs_abholzeiten`
			WHERE `betrieb_id` = :storeId
			ORDER BY dow, time
		', [':storeId' => $storeId]);

		return array_map(fn ($row) => RegularPickup::createFromArray($row), $times);
	}

	public function insertOrUpdateRegularPickup(int $storeId, RegularPickup $regularPickup): int
	{
		$column_values = [];
		$column_values['time'] = $regularPickup->startTimeOfPickup;
		$column_values['dow'] = $regularPickup->weekday;
		$column_values['fetcher'] = $regularPickup->maxCountOfSlots;
		$column_values['betrieb_id'] = $storeId;

		return $this->db->insertOrUpdate('fs_abholzeiten', $column_values);
	}

	public function deleteAllRegularPickups($storeId)
	{
		return $this->db->delete('fs_abholzeiten', ['betrieb_id' => $storeId]);
	}
}
