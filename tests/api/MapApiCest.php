<?php

use Foodsharing\Modules\Core\DBConstants\Region\RegionPinStatus;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\TeamSearchStatus;

class MapApiCest
{
    private $region;
    private $communityPin;
    private $storeCooperation;
    private $storeNoCooperation;
    private $storeTeamNeedsHelp;
    private $storeTeamNeedsHelp2;
    private $storeTeamSearchs;
    private $user;

    public function _before(ApiTester $I)
    {
        $this->region = $I->createRegion();
        $this->user = $I->createFoodsaver();
        $this->communityPin = $I->createCommunityPin($this->region['id']);
        $this->storeCooperation = $I->createStore($this->region['id'], null, null, ['lat' => 49.1, 'lon' => 5.2, 'team_status' => TeamSearchStatus::OPEN_SEARCHING->value, 'betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED->value]);
        $this->storeNoCooperation = $I->createStore($this->region['id'], null, null, ['lat' => 49.1, 'lon' => 5.2, 'team_status' => TeamSearchStatus::CLOSED->value, 'betrieb_status_id' => CooperationStatus::GIVES_TO_OTHER_CHARITY->value]);
        $this->storeTeamNeedsHelp = $I->createStore($this->region['id'], null, null, ['lat' => 49.1, 'lon' => 5.2, 'team_status' => TeamSearchStatus::OPEN_SEARCHING->value, 'betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED->value]);
        $this->storeTeamNeedsHelp2 = $I->createStore($this->region['id'], null, null, ['lat' => 49.1, 'lon' => 5.2, 'team_status' => TeamSearchStatus::OPEN->value, 'betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED->value]);
        $this->storeTeamSearchs = $I->createStore($this->region['id'], null, null, ['lat' => 49.1, 'lon' => 5.2, 'team_status' => TeamSearchStatus::OPEN->value, 'betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED->value]);
        $this->storeTeamSearchs = $I->createStore($this->region['id'], null, null, ['lat' => 49.1, 'lon' => 5.2, 'team_status' => TeamSearchStatus::CLOSED->value, 'betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED->value]);
        $this->storeTeamSearchs = $I->createStore($this->region['id'], null, null, ['lat' => null, 'lon' => null, 'team_status' => TeamSearchStatus::OPEN->value, 'betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED->value]);
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

    public function canNotFetchStoreMarkersWithoutLogin(ApiTester $I)
    {
        $I->sendGet('api/map/markers', ['types' => 'betriebe']);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
    }

    public function canFetchStoreMarkersNoSettings(ApiTester $I)
    {
        $I->login($this->user['email']);
        $I->sendGet('api/map/markers', ['types' => 'betriebe']);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $stores = $I->grabDataFromResponseByJsonPath('$.betriebe');
        $I->assertCount(1, $stores);
        $I->assertCount(6, $stores[0]);
    }

    public function canFetchStoreMarkersSearchingForMembers(ApiTester $I)
    {
        $I->login($this->user['email']);
        $I->sendGet('api/map/markers', ['types' => 'betriebe', 'status' => ['needhelpinstant']]);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $stores = $I->grabDataFromResponseByJsonPath('$.betriebe');
        $I->assertCount(1, $stores);
        $I->assertCount(2, $stores[0]);
    }

    public function canFetchStoreMarkersOpenForMembers(ApiTester $I)
    {
        $I->login($this->user['email']);
        $I->sendGet('api/map/markers', ['types' => 'betriebe', 'status' => ['needhelp']]);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $stores = $I->grabDataFromResponseByJsonPath('$.betriebe');
        $I->assertCount(1, $stores);
        $I->assertCount(2, $stores[0]);
    }

    public function canFetchStoreMarkersShowNoCooperation(ApiTester $I)
    {
        $I->login($this->user['email']);
        $I->sendGet('api/map/markers', ['types' => 'betriebe', 'status' => ['nkoorp']]);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $stores = $I->grabDataFromResponseByJsonPath('$.betriebe');
        $I->assertCount(1, $stores);
        $I->assertCount(1, $stores[0]);
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
