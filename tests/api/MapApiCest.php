<?php

use Foodsharing\Modules\Core\DBConstants\Region\RegionPinStatus;

class MapApiCest
{
    private $region;
    private $communityPin;
    private $user;

    public function _before(ApiTester $I)
    {
        $this->region = $I->createRegion();
        $this->user = $I->createFoodsaver();
        $this->communityPin = $I->createCommunityPin($this->region['id']);
    }

    public function canFetchMarkersWithoutLogin(ApiTester $I)
    {
        $I->sendGet('api/map/markers', ['types' => 'baskets']);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $I->sendGet('api/map/markers', ['types' => 'fairteiler']);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $I->sendGet('api/map/markers', ['types' => 'communities']);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
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
