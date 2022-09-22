<?php

use Foodsharing\Modules\Core\DBConstants\Region\RegionPinStatus;

class MapApiCest
{
	private $region;
	private $communityPin;

	public function _before(ApiTester $I)
	{
		$this->region = $I->createRegion();
		$this->communityPin = $I->createCommunityPin($this->region['id']);
	}

	public function canFetchRegionBubble(ApiTester $I)
	{
		$I->updateInDatabase('fs_region_pin', ['status' => RegionPinStatus::ACTIVE], ['region_id' => $this->region['id']]);
		$I->sendGet('api/map/regions/' . $this->region['id']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson([
			'description' => $this->communityPin['desc']
		]);
	}

	public function canNotFetchDescriptionOfInvalidRegion(ApiTester $I)
	{
		$I->sendGet('api/map/regions/999999');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
	}

	public function canNotFetchDescriptionOfInactiveMarker(ApiTester $I)
	{
		$I->updateInDatabase('fs_region_pin', ['status' => RegionPinStatus::INACTIVE], ['region_id' => $this->region['id']]);
		$I->sendGet('api/map/regions/' . $this->region['id']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
	}
}
