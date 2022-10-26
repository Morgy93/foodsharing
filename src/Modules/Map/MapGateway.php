<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Map\MapMarkerType;
use Foodsharing\Modules\Core\DBConstants\Region\RegionPinStatus;
use Foodsharing\Modules\Map\DTO\CommunityMapMarker;
use Foodsharing\Modules\Map\DTO\FoodSharePointMapMarker;
use Foodsharing\Modules\Map\DTO\MapMarker;
use Foodsharing\Modules\Map\DTO\StoreMapMarker;
use Foodsharing\RestApi\Models\Map\FilterModel;
use Foodsharing\RestApi\Models\Map\StoreFilterModel;

class MapGateway extends BaseGateway
{
	public function __construct(
		Database $db
	) {
		parent::__construct($db);
	}

	public function getFoodBasketMarkers(FilterModel $filter): array
	{
		$query = "
			SELECT
				id,
				description,
				picture,
				until,
				lat,
				lon
			FROM
				fs_basket
			WHERE
				ST_Distance_Sphere(point(lat, lon), POINT({$filter->latitude}, {$filter->longitude})) / 1000.0 <= {$filter->distanceInKm}
			AND
				status = " . 1
		;
		$baskets = $this->db->fetchAll($query);

		return array_map(fn ($row) => MapMarker::createFromArray($row, MapMarkerType::FOODBASKET), $baskets);
	}

	public function getFoodSharePointMarkers(FilterModel $filter): array
	{
		$query = "
			SELECT
				*
			FROM
				fs_fairteiler
			WHERE
				ST_Distance_Sphere(point(lat, lon), POINT({$filter->latitude}, {$filter->longitude})) / 1000.0 <= {$filter->distanceInKm}
			AND
				status = " . 1
		;
		$foodSharingPoints = $this->db->fetchAll($query);

		return array_map(fn ($row) => FoodSharePointMapMarker::createFromArray($row, MapMarkerType::FOODSHAREPOINT), $foodSharingPoints);
	}

	public function getCommunityMarkers(FilterModel $filter): array
	{
		$query = "
				SELECT
					*,
					(SELECT
						name
					FROM
						fs_bezirk
					WHERE
						id = region_id) as name
				FROM
					fs_region_pin
				WHERE
					ST_Distance_Sphere(point(lat, lon), POINT({$filter->latitude}, {$filter->longitude})) / 1000.0 <= {$filter->distanceInKm}
				AND
					status = " . RegionPinStatus::ACTIVE
		;
		$communities = $this->db->fetchAll($query);

		return array_map(fn ($row) => CommunityMapMarker::createFromArray($row), $communities);
	}

	public function getStoreMarkers(StoreFilterModel $filter): array
	{
		$query = "
				SELECT
					id,
					name,
					public_info as description,
					lat,
					lon,
					betrieb_status_id as cooperationStatus,
					team_status as teamStatus
				FROM
					fs_betrieb
				WHERE
					ST_Distance_Sphere(point(lat, lon), POINT({$filter->latitude}, {$filter->longitude})) / 1000.0 <= {$filter->distanceInKm}
		";

		if (!empty($filter->cooperationStatus)) {
			$query .= ' AND betrieb_status_id IN(' . implode(',', $filter->cooperationStatus) . ')';
		}
		if (!empty($filter->teamStatus)) {
			$query .= ' AND team_status IN (' . implode(',', $filter->teamStatus) . ')';
		}

		$stores = $this->db->fetchAll($query);

		return array_map(fn ($row) => StoreMapMarker::createFromArray($row), $stores);
	}
}
