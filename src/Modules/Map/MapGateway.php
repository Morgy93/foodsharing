<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Map\MapMarkerType;
use Foodsharing\Modules\Core\DBConstants\Region\RegionPinStatus;
use Foodsharing\Modules\Map\DTO\MapMarker;
use Foodsharing\RestApi\Models\Map\FilterModel;
use Foodsharing\RestApi\Models\Map\StoreFilterModel;

class MapGateway extends BaseGateway
{
	public function __construct(
		Database $db
	) {
		parent::__construct($db);
	}

	public function getBasketMarkers(): array
	{
		$baskets = $this->db->fetchAllByCriteria('fs_basket', ['id', 'lat', 'lon'], ['status' => 1]);

		return array_map(fn ($row) => MapMarker::createFromArray($row, MapMarkerType::FOODBASKET), $baskets);
	}

	public function getFoodSharePointMarkers(): array
	{
		$foodSharingPoints = $this->db->fetchAllByCriteria('fs_fairteiler', ['id', 'lat', 'lon', 'bezirk_id'], ['status' => 1, 'lat !=' => '']);

		return array_map(fn ($row) => MapMarker::createFromArray($row, MapMarkerType::FOODSHARINGPOINT), $foodSharingPoints);
	}

	public function getCommunityMarkers(): array
	{
		$communities = $this->db->fetchAllByCriteria('fs_region_pin', ['region_id', 'lat', 'lon'], ['lat !=' => '', 'status' => RegionPinStatus::ACTIVE]);

		return array_map(fn ($row) => MapMarker::createFromArray($row, MapMarkerType::COMMUNITY), $communities);
	}

	public function getStoreMarkers(StoreFilterModel $filter): array
	{
		$query = "
				SELECT
					id,
					name,
					lat,
					lon
				FROM
					fs_betrieb
				WHERE
					ST_Distance_Sphere(point(lon, lat), POINT({$filter->lon}, {$filter->lat})) / 1000.0 <= {$filter->distance_in_km}
		";

		if (!empty($filter->cooperationStatus)) {
			$query .= ' AND betrieb_status_id IN(' . implode(',', $filter->cooperationStatus) . ')';
		}
		if (!empty($filter->teamStatus)) {
			$query .= ' AND team_status IN (' . implode(',', $filter->teamStatus) . ')';
		}

		$stores = $this->db->fetchAll($query);

		// return $stores;
		return array_map(fn ($row) => MapMarker::createFromArray($row, MapMarkerType::STORE), $stores);
	}
}
