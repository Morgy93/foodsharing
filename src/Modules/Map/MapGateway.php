<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Region\RegionPinStatus;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Map\DTO\MapMarker;

class MapGateway extends BaseGateway
{
	public function __construct(
		Database $db
	) {
		parent::__construct($db);
	}

	public function getStoreLocation(int $storeId): array
	{
		return $this->db->fetchByCriteria('fs_betrieb', ['lat', 'lon'], ['id' => $storeId]);
	}

	public function getFoodsaverLocation(int $foodsaverId): array
	{
		return $this->db->fetchByCriteria('fs_foodsaver', ['lat', 'lon'], ['id' => $foodsaverId]);
	}

	public function getBasketMarkers(): array
	{
		$markers = $this->db->fetchAllByCriteria('fs_basket', ['id', 'lat', 'lon'], [
			'status' => 1
		]);

		return array_map(function ($x) {
			return MapMarker::create($x['id'], $x['lat'], $x['lon']);
		}, $markers);
	}

	public function getFoodSharePointMarkers(): array
	{
		$markers = $this->db->fetchAllByCriteria('fs_fairteiler', ['id', 'lat', 'lon', 'bezirk_id'], [
			'status' => 1,
			'lat !=' => ''
		]);

		return array_map(function ($x) {
			return MapMarker::create($x['id'], $x['lat'], $x['lon'], $x['bezirk_id']);
		}, $markers);
	}

	public function getCommunityMarkers(): array
	{
		$markers = $this->db->fetchAllByCriteria('fs_region_pin', ['region_id', 'lat', 'lon'], [
			'lat !=' => '',
			'status' => RegionPinStatus::ACTIVE
		]);

		return array_map(function ($x) {
			return MapMarker::create($x['region_id'], $x['lat'], $x['lon']);
		}, $markers);
	}

	/**
	 * Provides Stores with position markers.
	 *
	 * @param array<CooperationStatus> $excludedStoreTypes Excludes stores of this types
	 */
	public function getStoreMarkers(array $excludedStoreTypes, array $teamStatus): array
	{
		$query = 'SELECT id, lat, lon FROM fs_betrieb WHERE lat != ""';

		if (!empty($excludedStoreTypes)) {
			$query .= ' AND betrieb_status_id NOT IN(' . implode(',', array_fill(0, count($excludedStoreTypes), '?')) . ')';
		}
		if (!empty($teamStatus)) {
			$query .= ' AND team_status IN (' . implode(',', array_fill(0, count($teamStatus), '?')) . ')';
		}
		$excludedStoreTypesIds = array_map(function (CooperationStatus $storeType) { return $storeType->value; }, $excludedStoreTypes);
		$markers = $this->db->fetchAll($query, array_merge($excludedStoreTypesIds, $teamStatus));

		return array_map(function ($x) {
			return MapMarker::create($x['id'], $x['lat'], $x['lon']);
		}, $markers);
	}
}
