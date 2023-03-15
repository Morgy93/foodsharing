<?php

namespace api;

use ApiTester;
use Codeception\Example;
use Codeception\Util\HttpCode as Http;
use Faker;
use Foodsharing\Modules\Core\DBConstants\Store\Milestone;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;

/**
 * Tests for the store api.
 */
class StoreApiCest
{
    private $store;
    private $foodsharer;
    private $user;
    private $unverifiedUser;
    private $teamMember;
    private $manager;
    private $region;
    private $otherRegion;
    private $nextRegion;
    private $faker;

    private const API_STORES = 'api/stores';
    private const API_REGIONS = 'api/region';
    private const EMAIL = 'email';
    private const ID = 'id';

    public function createDefaultNewStoreJson()
    {
        return ['store' => [
            'name' => 'Store Name', 'regionId' => $this->region['id'],
            'location' => ['lat' => 123.01, 'lon' => 4.190000],
            'street' => 'Mühlbachweg 122',
            'zipCode' => '12234',
            'city' => 'Karlsruhe',
            'publicInfo' => 'Wetten des es geht'
        ], 'firstPost' => null
        ];
    }

    public function _before(ApiTester $I)
    {
        $this->region = $I->createRegion();
        $this->nextRegion = $I->createRegion();
        $this->otherRegion = $I->createRegion();
        $I->haveInDatabase('fs_kette', ['id' => 40, 'name' => 'Chain']);
        $I->haveInDatabase('fs_betrieb_kategorie', ['id' => 20, 'name' => 'Category']);
        $this->foodsharer = $I->createFoodsharer(null, ['verified' => 0]);
        $this->user = $I->createFoodsaver(null, ['verified' => 0]);
        $this->unverifiedUser = $I->createFoodsaver(null, ['verified' => 0]);
        $this->teamMember = $I->createFoodsaver();
        $this->manager = $I->createStoreCoordinator(null, ['bezirk_id' => $this->region['id']]);
        $this->teamConversation = $I->createConversation([$this->manager['id'], $this->teamMember['id']]);
        $this->springerConversation = $I->createConversation([$this->manager['id'], $this->teamMember['id']]);
        $this->store = $I->createStore($this->region['id'], $this->teamConversation['id'], $this->springerConversation['id'], ['kette_id' => 40, 'betrieb_kategorie_id' => 20, 'use_region_pickup_rule' => 1]);

        $I->addStoreTeam($this->store[self::ID], $this->teamMember[self::ID], false);

        $I->addRegionMember($this->nextRegion['id'], $this->manager['id']);
        $I->addStoreTeam($this->store[self::ID], $this->manager[self::ID], true);
        $this->faker = Faker\Factory::create('de_DE');
    }

    public function canNotGetAccessToCommonStoreMetadataAsUnknownUser(ApiTester $I)
    {
        $I->sendGET(self::API_STORES . '/meta-data');
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);
    }

    public function getCommonStoreMetadataAsFoodsaver(ApiTester $I)
    {
        $I->login($this->user[self::EMAIL]);
        $I->sendGET(self::API_STORES . '/meta-data');
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['maxCountPickupSlot' => 10,
                                    'storeChains' => null]);

        $groceries = $I->grabDataFromResponseByJsonPath('$.groceries');
        $I->assertNotCount(0, $groceries);
        $categories = $I->grabDataFromResponseByJsonPath('$.categories');
        $I->assertNotCount(0, $categories);
        $status = $I->grabDataFromResponseByJsonPath('$.status');
        $I->assertNotCount(0, $status);
        $weight = $I->grabDataFromResponseByJsonPath('$.weight');
        $I->assertNotCount(0, $weight);
        $convinceStatus = $I->grabDataFromResponseByJsonPath('$.convinceStatus');
        $I->assertNotCount(0, $convinceStatus);
        $publicTimes = $I->grabDataFromResponseByJsonPath('$.publicTimes');
        $I->assertNotCount(0, $publicTimes);
    }

    public function getCommonStoreMetadataAsStoreOwner(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->sendGET(self::API_STORES . '/meta-data');
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['maxCountPickupSlot' => 10]);
        $storeChains = $I->grabDataFromResponseByJsonPath('$.storeChains');
        $I->assertNotCount(0, $storeChains);
    }

    public function canAnonymUserNotAccessToGetListOfStoresInRegion(ApiTester $I)
    {
        $regionRelatedRegion = $I->createRegion();

        $I->sendGET(self::API_REGIONS . '/' . $regionRelatedRegion['id'] . '/stores');
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);
    }

    public function canNotAccessToGetListOfStoresWithInvalidRegion(ApiTester $I)
    {
        $I->sendGET(self::API_REGIONS . '/1234/stores');
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);
    }

    public function foodsharerCanNotAccessToGetListOfStoresInRegion(ApiTester $I)
    {
        $regionRelatedRegion = $I->createRegion();

        $I->login($this->foodsharer[self::EMAIL]);
        $I->sendGET(self::API_REGIONS . '/' . $regionRelatedRegion['id'] . '/stores');
        $I->seeResponseCodeIs(Http::FORBIDDEN);
    }

    public function unverifiedFoodsaverCanAccessToGetListOfStoresInRegion(ApiTester $I)
    {
        $regionRelatedRegion = $I->createRegion();

        $I->login($this->unverifiedUser[self::EMAIL]);
        $I->sendGET(self::API_REGIONS . '/' . $regionRelatedRegion['id'] . '/stores');
        $I->seeResponseCodeIs(Http::OK);
    }

    public function verifiedFoodsaverCanAccessToGetListOfStoresInRegion(ApiTester $I)
    {
        $regionRelatedRegion = $I->createRegion();

        $I->login($this->user[self::EMAIL]);
        $I->sendGET(self::API_REGIONS . '/' . $regionRelatedRegion['id'] . '/stores');
        $I->seeResponseCodeIs(Http::OK);
    }

    public function foodsaverWithRegionRelationCanAccessToGetListOfStoresInRegion(ApiTester $I)
    {
        $regionRelatedRegion = $I->createRegion();
        $I->addRegionMember($regionRelatedRegion['id'], $this->user['id'], true);

        $I->login($this->user[self::EMAIL]);
        $I->sendGET(self::API_REGIONS . '/' . $regionRelatedRegion['id'] . '/stores');
        $I->seeResponseCodeIs(Http::OK);
    }

    public function testContentofGetListOfStoresInRegion(ApiTester $I)
    {
        $regionTop = $I->createRegion(null, ['type' => UnitType::CITY]);
        $I->addRegionMember($regionTop['id'], $this->user['id'], true);

        $regionChild1 = $I->createRegion(null, ['parent_id' => $regionTop['id'], 'type' => UnitType::PART_OF_TOWN]);
        $store1 = $I->createStore($regionChild1['id']);
        $regionChild2 = $I->createRegion(null, ['parent_id' => $regionTop['id'], 'type' => UnitType::PART_OF_TOWN]);
        $store2 = $I->createStore($regionChild2['id']);

        $I->login($this->user[self::EMAIL]);
        $I->sendGET(self::API_REGIONS . '/' . $regionTop['id'] . '/stores');
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();

        $ids = $I->grabDataFromResponseByJsonPath('stores.*.id');
        $I->assertContains($store1[self::ID], $ids);
        $I->assertContains($store2[self::ID], $ids);

        $names = $I->grabDataFromResponseByJsonPath('stores.*.name');
        foreach ($names as $name) {
            $I->assertNull($name);
        }
    }

    public function testContentofGetListOfStoresInRegionExpanded(ApiTester $I)
    {
        $regionTop = $I->createRegion(null, ['type' => UnitType::CITY]);
        $I->addRegionMember($regionTop['id'], $this->user['id'], true);

        $regionChild1 = $I->createRegion(null, ['parent_id' => $regionTop['id'], 'type' => UnitType::PART_OF_TOWN]);
        $store1 = $I->createStore($regionChild1['id']);
        $regionChild2 = $I->createRegion(null, ['parent_id' => $regionTop['id'], 'type' => UnitType::PART_OF_TOWN]);
        $store2 = $I->createStore($regionChild2['id']);

        $I->login($this->user[self::EMAIL]);
        $I->sendGET(self::API_REGIONS . '/' . $regionTop['id'] . '/stores', ['expand' => true]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();

        $ids = $I->grabDataFromResponseByJsonPath('stores.*.id');
        $I->assertContains($store1[self::ID], $ids);
        $I->assertContains($store2[self::ID], $ids);

        $names = $I->grabDataFromResponseByJsonPath('stores.*.name');
        $I->assertContains($store1['name'], $names);
        $I->assertContains($store2['name'], $names);
    }
    
    public function testPaginationOfGetListOfStoresInRegion(ApiTester $I)
  	{
    		$regionTop = $I->createRegion(null, ['type' => UnitType::CITY]);
    		$I->addRegionMember($regionTop['id'], $this->user['id'], true);

    		$regionChild1 = $I->createRegion(null, ['parent_id' => $regionTop['id'], 'type' => UnitType::PART_OF_TOWN]);
    		foreach(range(1,9) as $index) {
    		    $I->createStore($regionChild1['id']);
    		}
    		
    		$regionChild2 = $I->createRegion(null, ['parent_id' => $regionTop['id'], 'type' => UnitType::PART_OF_TOWN]);
    		$store2 = $I->createStore($regionChild2['id']);

    		$I->login($this->user[self::EMAIL]);
    		$I->sendGET(self::API_REGIONS . '/' . $regionTop['id'] . '/stores', ['startOffset' => 5, 'limit' => 5]);
    		$I->seeResponseCodeIs(Http::OK);
    		$I->seeResponseIsJson();

        $ids = $I->grabDataFromResponseByJsonPath('stores.*.id');
    		$I->assertEquals(5, count($ids));
        $total = $I->grabDataFromResponseByJsonPath('total')[0];
    		$I->assertEquals(10, (int)$total);
  	}

    public function canNotGetAccessToCreateStoreAsUnknownUser(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::API_REGIONS . '/' . $this->region['id'] . '/stores', $this->createDefaultNewStoreJson());
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);
    }

    public function canNotGetAccessToCreateStoreAsFoodsharer(ApiTester $I)
    {
        $I->login($this->foodsharer['email']);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::API_REGIONS . '/' . $this->region['id'] . '/stores', $this->createDefaultNewStoreJson());
        $I->seeResponseCodeIs(Http::FORBIDDEN);
    }

    public function canNotGetAccessToCreateStoreAsFoodsaver(ApiTester $I)
    {
        $I->login($this->user['email']);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::API_REGIONS . '/' . $this->region['id'] . '/stores', $this->createDefaultNewStoreJson());
        $I->seeResponseCodeIs(Http::FORBIDDEN);
    }

    public function canNotGetAccessToCreateStoreAsUnverifiedFoodsaver(ApiTester $I)
    {
        $I->login($this->unverifiedUser['email']);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::API_REGIONS . '/' . $this->region['id'] . '/stores', $this->createDefaultNewStoreJson());
        $I->seeResponseCodeIs(Http::FORBIDDEN);
    }

    public function canGetAccessToCreateStoreAsStoreManagerOfRegionWithoutContent(ApiTester $I)
    {
        $I->login($this->manager['email']);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::API_REGIONS . '/' . $this->region['id'] . '/stores', []);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);
    }

    public function createStoreAsStoreManagerOfValidRegionButInvalidContent(ApiTester $I)
    {
        $storeUri = self::API_REGIONS . '/' . $this->region['id'] . '/stores';
        $I->login($this->manager['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');

        // No store data
        $I->sendPOST($storeUri, ['firstPost' => null]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->sendPOST($storeUri, ['store' => null]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        // Empty store name
        $I->sendPOST($storeUri, ['store' => ['name' => ''], 'firstPost' => null]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        // Empty location
        $I->sendPOST($storeUri, [
            'store' => [
                'name' => 'Store Name',
                'location' => null
            ], 'firstPost' => null]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->sendPOST($storeUri, [
            'store' => [
                'name' => 'Store Name',
                'location' => ['lat' => '']
            ], 'firstPost' => null]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);
        $I->sendPOST($storeUri, [
            'store' => [
                'name' => 'Store Name',
                'location' => ['lat' => '123.01', 'lon' => 'sw']
            ], 'firstPost' => null]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);
        $I->sendPOST($storeUri, [
            'store' => [
                'name' => 'Store Name',
                'location' => ['lat' => 123.01, 'lon' => 4.190000]
            ], 'firstPost' => null]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);
        $I->sendPOST($storeUri, [
            'store' => [
                'name' => 'Store Name',
                'location' => ['lat' => 123.01, 'lon' => 4.190000],
                'street' => null,
            ], 'firstPost' => null]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);
        $I->sendPOST($storeUri, [
            'store' => [
                'name' => 'Store Name',
                'location' => ['lat' => 123.01, 'lon' => 4.190000],
                'street' => 'Mühlbachweg 122',
                'zipCode' => null
            ], 'firstPost' => null]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);
        $I->sendPOST($storeUri, [
            'store' => [
                'name' => 'Store Name',
                'location' => ['lat' => 123.01, 'lon' => 4.190000],
                'street' => 'Mühlbachweg 122',
                'zipCode' => '12234',
                'city' => null
            ], 'firstPost' => null]);
        $I->sendPOST($storeUri, [
                'store' => [
                    'name' => 'Store Name',
                    'location' => ['lat' => 123.01, 'lon' => 4.190000],
                    'street' => 'Mühlbachweg 122',
                    'zipCode' => '12234',
                    'city' => 'Karlsruhe',
                    'publicInfo' => null
                ], 'firstPost' => null]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);
    }

    public function createStoreAsStoreManagerWithPublicInfoXssIsBadRequest(ApiTester $I)
    {
        $I->login($this->manager['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $storeInfo = [
            'name' => 'Store Name',
            'location' => ['lat' => 123.01, 'lon' => 4.190000],
            'street' => 'Mühlbachweg 122',
            'zipCode' => '12234',
            'city' => 'Karlsruhe',
            'publicInfo' => 'Wetten <script>alert()</script>des es geht'
        ];
        $I->sendPOST(self::API_REGIONS . '/' . $this->region['id'] . '/stores', [
            'store' => $storeInfo, 'firstPost' => null]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->dontSeeInDatabase('fs_betrieb', [
            'name' => $storeInfo['name'],
            'bezirk_id' => $this->region['id'] + 10,
            'str' => $storeInfo['street'],
            'plz' => $storeInfo['zipCode'],
            'stadt' => $storeInfo['city'],
            'public_info' => $storeInfo['publicInfo']]);
    }

    public function createStoreAsStoreManagerWithNameXssIsBadRequest(ApiTester $I)
    {
        $I->login($this->manager['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $storeInfo = [
            'name' => 'Store Name <script>alert()</script>',
            'location' => ['lat' => 123.01, 'lon' => 4.190000],
            'street' => 'Mühlbachweg 122',
            'zipCode' => '12234',
            'city' => 'Karlsruhe',
            'publicInfo' => 'Wetten des es geht'
        ];
        $I->sendPOST(self::API_REGIONS . '/' . $this->region['id'] . '/stores', [
            'store' => $storeInfo, 'firstPost' => null]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->dontSeeInDatabase('fs_betrieb', [
            'name' => $storeInfo['name'],
            'bezirk_id' => $this->region['id'] + 10,
            'str' => $storeInfo['street'],
            'plz' => $storeInfo['zipCode'],
            'stadt' => $storeInfo['city'],
            'public_info' => $storeInfo['publicInfo']]);
    }

    public function createStoreAsStoreManagerWithStreetXssIsBadRequest(ApiTester $I)
    {
        $I->login($this->manager['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $storeInfo = [
            'name' => 'Store Name <script>alert()</script>',
            'location' => ['lat' => 123.01, 'lon' => 4.190000],
            'street' => 'Mühlbachweg<script>alert()</script> 122',
            'zipCode' => '12234',
            'city' => 'Karlsruhe <script>alert()</script>',
            'publicInfo' => 'Wettendes es geht'
        ];
        $I->sendPOST(self::API_REGIONS . '/' . $this->region['id'] . '/stores', [
            'store' => $storeInfo, 'firstPost' => null]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->dontSeeInDatabase('fs_betrieb', [
            'name' => $storeInfo['name'],
            'bezirk_id' => $this->region['id'] + 10,
            'str' => $storeInfo['street'],
            'plz' => $storeInfo['zipCode'],
            'stadt' => $storeInfo['city'],
            'public_info' => $storeInfo['publicInfo']]);
    }

    public function createStoreAsStoreManagerWithCityXssIsBadRequest(ApiTester $I)
    {
        $I->login($this->manager['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $storeInfo = [
            'name' => 'Store Name <script>alert()</script>',
            'location' => ['lat' => 123.01, 'lon' => 4.190000],
            'street' => 'Mühlbachweg 122',
            'zipCode' => '12234',
            'city' => 'Karlsruhe <script>alert()</script>',
            'publicInfo' => 'Wettendes es geht'
        ];
        $I->sendPOST(self::API_REGIONS . '/' . $this->region['id'] . '/stores', [
            'store' => $storeInfo, 'firstPost' => null]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->dontSeeInDatabase('fs_betrieb', [
            'name' => $storeInfo['name'],
            'bezirk_id' => $this->region['id'] + 10,
            'str' => $storeInfo['street'],
            'plz' => $storeInfo['zipCode'],
            'stadt' => $storeInfo['city'],
            'public_info' => $storeInfo['publicInfo']]);
    }

    public function createStoreAsStoreManagerOfRegionForInvalidRegionIsForbidden(ApiTester $I)
    {
        $I->login($this->manager['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $storeInfo = [
            'name' => 'Store Name',
            'location' => ['lat' => 123.01, 'lon' => 4.190000],
            'street' => 'Mühlbachweg 122',
            'zipCode' => '12234',
            'city' => 'Karlsruhe',
            'publicInfo' => 'Wetten des es geht'
        ];
        $I->sendPOST(self::API_REGIONS . '/' . $this->region['id'] + 10 . '/stores', [
            'store' => $storeInfo, 'firstPost' => null]);
        $I->seeResponseCodeIs(Http::FORBIDDEN);

        $I->dontSeeInDatabase('fs_betrieb', [
            'name' => $storeInfo['name'],
            'bezirk_id' => $this->region['id'] + 10,
            'str' => $storeInfo['street'],
            'plz' => $storeInfo['zipCode'],
            'stadt' => $storeInfo['city'],
            'public_info' => $storeInfo['publicInfo']]);
    }

    public function createStoreAsStoreManagerOfRegionSuccessful(ApiTester $I)
    {
        $I->login($this->manager['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $storeInfo = [
            'name' => 'Store Name',
            'location' => ['lat' => 123.01, 'lon' => 4.190000],
            'street' => 'Mühlbachweg 122',
            'zipCode' => '12234',
            'city' => 'Karlsruhe',
            'publicInfo' => 'Wetten des es geht'
        ];
        $I->sendPOST(self::API_REGIONS . '/' . $this->region['id'] . '/stores', [
            'store' => $storeInfo, 'firstPost' => null]);
        $I->seeResponseCodeIs(Http::CREATED);
        $storeIds = $I->grabDataFromResponseByJsonPath('$.id');
        $I->assertEquals(1, count($storeIds));

        $I->seeInDatabase('fs_betrieb', [
            'id' => $storeIds[0],
            'name' => $storeInfo['name'],
            'bezirk_id' => $this->region['id'],
            'str' => $storeInfo['street'],
            'plz' => $storeInfo['zipCode'],
            'stadt' => $storeInfo['city'],
            'public_info' => $storeInfo['publicInfo']]);

        $I->dontSeeInDatabase('fs_betrieb_notiz', [
            'foodsaver_id' => $this->manager['id'],
            'betrieb_id' => $storeIds[0],
            'milestone' => Milestone::NONE]);
    }

    public function createStoreAsStoreManagerOfRegionSuccessfulWithFirstPost(ApiTester $I)
    {
        $I->login($this->manager['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $storeInfo = [
            'name' => 'Store Name',
            'location' => ['lat' => 123.01, 'lon' => 4.190000],
            'street' => 'Mühlbachweg 122',
            'zipCode' => '12234',
            'city' => 'Karlsruhe',
            'publicInfo' => 'Wetten des es geht'
        ];
        $I->sendPOST(self::API_REGIONS . '/' . $this->region['id'] . '/stores', [
            'store' => $storeInfo, 'firstPost' => 'First post']);
        $I->seeResponseCodeIs(Http::CREATED);
        $storeIds = $I->grabDataFromResponseByJsonPath('$.id');
        $I->assertEquals(1, count($storeIds));

        $I->seeInDatabase('fs_betrieb', [
            'id' => $storeIds[0],
            'name' => $storeInfo['name'],
            'bezirk_id' => $this->region['id'],
            'str' => $storeInfo['street'],
            'plz' => $storeInfo['zipCode'],
            'stadt' => $storeInfo['city'],
            'public_info' => $storeInfo['publicInfo']]);

        $I->seeInDatabase('fs_betrieb_notiz', [
            'foodsaver_id' => $this->manager['id'],
            'betrieb_id' => $storeIds[0],
            'milestone' => Milestone::NONE,
            'text' => 'First post']);
    }

    public function getStore(ApiTester $I)
    {
        $I->login($this->teamMember[self::EMAIL]);
        $I->sendGET(self::API_STORES . '/' . $this->store[self::ID]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['id' => $this->store[self::ID]]);
        $I->seeResponseContainsJson(['phone' => $this->store['telefon']]);
    }

    public function canOnlyAccessStoreAsFoodsaver(ApiTester $I)
    {
        $I->sendGET(self::API_STORES . '/' . $this->store[self::ID]);
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);

        $I->login($this->foodsharer[self::EMAIL]);
        $I->sendGET(self::API_STORES . '/' . $this->store[self::ID]);
        $I->seeResponseCodeIs(Http::FORBIDDEN);
    }

    public function canOnlySeeStoreDetailsAsMember(ApiTester $I)
    {
        $I->login($this->user[self::EMAIL]);
        $I->sendGET(self::API_STORES . '/' . $this->store[self::ID]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseContainsJson(['id' => $this->store[self::ID]]);
        $I->dontSeeResponseContainsJson(['phone' => $this->store['telefon']]);
    }

    public function patchStoreNameAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['name' => 'This is a nice store']);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['store' => ['id' => $this->store[self::ID], 'name' => 'This is a nice store']]);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'name' => 'This is a nice store']);

        $teamConversationName = $I->grabFromDatabase('fs_conversation', 'name', ['id' => $this->teamConversation['id']]);
        $I->assertStringContainsString('This is a nice store', $teamConversationName);
        $sprinterConversationName = $I->grabFromDatabase('fs_conversation', 'name', ['id' => $this->springerConversation['id']]);
        $I->assertStringContainsString('This is a nice store', $sprinterConversationName);
    }

    /**
     * @example {"field": "name", "value": "This is a nice store", "dbField":"name"}
     * @example {"field": "regionId", "value": "{{regionOfMember}}", "dbField":"bezirk_id"}
     * @example {"field": "regionId", "value": "{{regionWithoutMembership}}", "dbField":"bezirk_id"}
     * @example {"field": "publicInfo", "value": "This is a nice store", "dbField":"public_info"}
     * @example {"field": "publicTime", "value": 2, "dbField":"public_time"}
     * @example {"field": "categoryId", "value": 3, "dbField":"betrieb_kategorie_id"}
     * @example {"field": "chainId", "value": 4, "dbField":"kette_id"}
     * @example {"field": "cooperationStatus", "value": 2, "dbField":"betrieb_status_id"}
     * @example {"field": "description", "value": "Invalid", "dbField":"besonderheiten"}
     * @example {"field": "cooperationStart", "value": "2022-01-13", "dbField":"begin"}
     * @example {"field": "calendarInterval", "value": 2, "dbField":"prefetchtime"}
     * @example {"field": "weight", "value": 2, "dbField":"abholmenge"}
     * @example {"field": "effort", "value": 2, "dbField":"ueberzeugungsarbeit"}
     * @example {"field": "teamStatus", "value": 2, "dbField":"team_status"}
     * @example {"field": "location", "value": {"lat": 49.9}, "dbField":"lat"}
     * @example {"field": "location", "value": {"lon": 4.9}, "dbField":"lon"}
     * @example {"field": "address", "value": { "street": "Weberstrasse 123"}, "dbField":"str"}
     * @example {"field": "address", "value": { "city": "Berlin"}, "dbField":"stadt"}
     * @example {"field": "address", "value": { "zipCode": "12345"}, "dbField":"plz"}
     * @example {"field": "contact", "value": {"name": "Invalid"}, "dbField":"ansprechpartner"}
     * @example {"field": "contact", "value": {"phone": "Invalid"}, "dbField":"telefon"}
     * @example {"field": "contact", "value": {"fax": "Invalid"}, "dbField":"fax"}
     * @example {"field": "contact", "value": {"email": "Invalid"}, "dbField":"email"}
     * @example {"field": "showsSticker", "value": true, "dbField":"sticker"}
     * @example {"field": "publicity", "value": true, "dbField":"presse"}
     * @example {"field": "options", "value": { "useRegionPickupRule": true}, "dbField":"use_region_pickup_rule"}
     */
    public function patchStoreAsNormalUserReturnForbidden(ApiTester $I, Example $example)
    {
        $value = $example['value'];
        if (is_string($value)) {
            if ($value == '{{regionOfMember}}') {
                $value = $this->region['id'];
            }
            if ($value == '{{regionWithoutMembership}}') {
                $value = $this->otherRegion['id'];
            }
        }

        $I->login($this->foodsharer[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], [$example['field'] => $value]);
        $I->seeResponseCodeIs(Http::FORBIDDEN);

        $I->login($this->teamMember[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], [$example['field'] => $value]);
        $I->seeResponseCodeIs(Http::FORBIDDEN);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            $example['dbField'] => $this->store[$example['dbField']]]);
    }

    public function patchStoreGroceriesAsUserReturnForbidden(ApiTester $I)
    {
        $I->login($this->user[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['groceries' => [1, 2, 3]]);
        $I->seeResponseCodeIs(Http::FORBIDDEN);

        $I->assertEquals(0, $I->grabNumRecords('fs_betrieb_has_lebensmittel', ['betrieb_id' => $this->store[self::ID]]));
    }

    /**
     * @example {"field": "name", "value": "This is a nice store", "dbField":"name"}
     * @example {"field": "regionId", "value": "{{regionOfMember}}", "dbField":"bezirk_id"}
     * @example {"field": "regionId", "value": "{{regionWithoutMembership}}", "dbField":"bezirk_id"}
     * @example {"field": "location", "value": {"lat": 49.9}, "dbField":"lat"}
     * @example {"field": "location", "value": {"lon": 4.9}, "dbField":"lon"}
     * @example {"field": "address", "value": { "street": "Weberstrasse 123"}, "dbField":"str"}
     * @example {"field": "address", "value": { "city": "Berlin"}, "dbField":"stadt"}
     * @example {"field": "address", "value": { "zipCode": "12345"}, "dbField":"plz"}
     * @example {"field": "publicInfo", "value": "This is a nice store", "dbField":"public_info"}
     * @example {"field": "publicTime", "value": 2, "dbField":"public_time"}
     * @example {"field": "categoryId", "value": 3, "dbField":"betrieb_kategorie_id"}
     * @example {"field": "chainId", "value": 4, "dbField":"kette_id"}
     * @example {"field": "cooperationStatus", "value": 2, "dbField":"betrieb_status_id"}
     * @example {"field": "description", "value": "Invalid", "dbField":"besonderheiten"}
     * @example {"field": "contact", "value": {"name": "Invalid"}, "dbField":"ansprechpartner"}
     * @example {"field": "contact", "value": {"phone": "Invalid"}, "dbField":"telefon"}
     * @example {"field": "contact", "value": {"fax": "Invalid"}, "dbField":"fax"}
     * @example {"field": "contact", "value": {"email": "Invalid"}, "dbField":"email"}
     * @example {"field": "cooperationStart", "value": "2022-01-13", "dbField":"begin"}
     * @example {"field": "calendarInterval", "value": 2, "dbField":"prefetchtime"}
     * @example {"field": "weight", "value": 2, "dbField":"abholmenge"}
     * @example {"field": "effort", "value": 2, "dbField":"ueberzeugungsarbeit"}
     * @example {"field": "teamStatus", "value": 2, "dbField":"team_status"}
     * @example {"field": "showsSticker", "value": true, "dbField":"sticker"}
     * @example {"field": "publicity", "value": true, "dbField":"presse"}
     * @example {"field": "options", "value": { "useRegionPickupRule": true}, "dbField":"use_region_pickup_rule"}
     */
    public function patchStoreAsUnknownUserReturnUnauthorized(ApiTester $I, Example $example)
    {
        $value = $example['value'];
        if (is_string($value)) {
            if ($value == '{{regionOfMember}}') {
                $value = $this->region['id'];
            }
            if ($value == '{{regionWithoutMembership}}') {
                $value = $this->otherRegion['id'];
            }
        }

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], [$example['field'] => $value]);
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            $example['dbField'] => $this->store[$example['dbField']]]);
    }

    public function patchRegionAsStoreManagerOfRegions(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['regionId' => $this->nextRegion['id']]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['store' => ['id' => $this->store[self::ID], 'group' => ['id' => $this->nextRegion['id']]]]);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'bezirk_id' => $this->nextRegion['id']]);
    }

    public function patchStoreZipCodeAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['address' => ['zipCode' => 'A2345']]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['store' => ['id' => $this->store[self::ID], 'address' => ['postalCode' => 'A2345']]]);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'plz' => 'A2345']);
    }

    public function canNotPatchStoreZipCodeWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['address' => ['zipCode' => '123456']]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'plz' => $this->store['plz']]);
    }

    public function patchStoreStreetAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['address' => ['street' => 'Store street 123']]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['store' => ['id' => $this->store[self::ID], 'address' => ['street' => 'Store street 123']]]);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'str' => 'Store Street 123']);
    }

    public function patchStoreGeoLocationLatAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['location' => ['lat' => 49.9]]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['store' => ['id' => $this->store[self::ID], 'lat' => 49.9]]);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'lat' => 49.9]);
    }

    public function canNotPatchStoreGeoLocationLatWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['location' => ['lat' => 'a123']]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'lat' => $this->store['lat']]);
    }

    public function patchStoreGeoLocationLonAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['location' => ['lon' => 49.9]]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['store' => ['id' => $this->store[self::ID], 'lon' => 49.9]]);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'lon' => 49.9]);
    }

    public function canNotPatchStoreGeoLocationLonWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['location' => ['lon' => 'a123']]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'lon' => $this->store['lon']]);
    }

    public function patchStorePublicInformationAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['publicInfo' => 'Test']);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'public_info' => 'Test']);
    }

    public function canNotPatchStorePublicInformationWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['publicInfo' => implode('', array_fill(0, 201, '1'))]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'public_info' => $this->store['public_info']]);
    }

    public function patchStorePublicTimeAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['publicTime' => 2]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'public_time' => 2]);
    }

    public function canNotPatchStorePublicTimeWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['publicTime' => 'A']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['publicTime' => 'hallo']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['publicTime' => 'a2']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['publicTime' => 5]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['publicTime' => implode('', array_fill(0, 201, '1'))]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'public_time' => $this->store['public_time']]);
    }

    public function patchStoreCategoryAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveInDatabase('fs_betrieb_kategorie', ['id' => 2, 'name' => 'Category']);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['categoryId' => 2]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'betrieb_kategorie_id' => 2]);
    }

    public function patchStoreCategoryWithInvalidAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['categoryId' => 200]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'betrieb_kategorie_id' => $this->store['betrieb_kategorie_id']]);
    }

    public function canNotPatchStoreCategoryWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['categoryId' => 'A']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['categoryId' => 'hallo']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['categoryId' => 'a2']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['categoryId' => 5]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['categoryId' => implode('', array_fill(0, 201, '1'))]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'betrieb_kategorie_id' => $this->store['betrieb_kategorie_id']]);
    }

    public function patchStoreChainAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveInDatabase('fs_kette', ['id' => 4, 'name' => 'Chain']);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['chainId' => 4]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'kette_id' => 4]);
    }

    public function patchStoreChainWithInvalidAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['chainId' => 200]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'kette_id' => $this->store['kette_id']]);
    }

    public function canNotPatchStoreChainWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['chainId' => 'A']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['chainId' => 'hallo']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['chainId' => 'a2']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'kette_id' => $this->store['kette_id']]);
    }

    public function patchStoreCooperationStatusAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['cooperationStatus' => 4]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'betrieb_status_id' => 4]);
    }

    public function patchStoreCooperationStatusWithInvalidAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['cooperationStatus' => 200]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'betrieb_status_id' => $this->store['betrieb_status_id']]);
    }

    public function canNotPatchStoreCooperationStatusWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['cooperationStatus' => 'A']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['cooperationStatus' => 'hallo']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['cooperationStatus' => 'a2']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'betrieb_status_id' => $this->store['betrieb_status_id']]);
    }

    public function patchStoreDescriptionAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['description' => 'Store description']);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'besonderheiten' => 'Store description']);
    }

    public function patchStoreContactNameAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['contact' => ['name' => 'Store contactName']]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'ansprechpartner' => 'Store contactName']);
    }

    public function canNotPatchStoreContactNameWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['contact' => ['name' => implode('', array_fill(0, 60 + 1, '1'))]]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'ansprechpartner' => $this->store['ansprechpartner']]);
    }

    public function patchStoreContactPhoneAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['contact' => ['phone' => '+49 123 123456']]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'telefon' => '+49 123 123456']);
    }

    public function canNotPatchStoreContactPhoneWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['contact' => ['phone' => implode('', array_fill(0, 50 + 1, '1'))]]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'telefon' => $this->store['telefon']]);
    }

    public function patchStoreContactFaxAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['contact' => ['fax' => 'Store contactFax']]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'fax' => 'Store contactFax']);
    }

    public function canNotPatchStoreContactFaxWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['contact' => ['fax' => implode('', array_fill(0, 50 + 1, '1'))]]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'fax' => $this->store['fax']]);
    }

    public function patchStoreContactEMailAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['contact' => ['email' => 'Store contactEmail']]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'email' => 'Store contactEmail']);
    }

    public function canNotPatchStoreContactEMailWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['contact' => ['email' => implode('', array_fill(0, 60 + 1, '1'))]]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'email' => $this->store['email']]);
    }

    public function patchStoreCooperationStartAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['cooperationStart' => '2022-04-13']);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'begin' => '2022-04-13']);
    }

    public function canNotPatchStoreCooperationStartWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['cooperationStart' => '']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['cooperationStart' => 'Hallo']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['cooperationStart' => '1-2-2']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['cooperationStart' => 'A1-A23-123']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['cooperationStart' => '12.01.2022']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'begin' => $this->store['begin']]);
    }

    public function patchStoreCalendarIntervalAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['calendarInterval' => 604800]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'prefetchtime' => 604800]);
    }

    public function canNotPatchStoreCalendarIntervalWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['calendarInterval' => 10000000001]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['calendarInterval' => 'a']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['calendarInterval' => '0.1']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'prefetchtime' => $this->store['prefetchtime']]);
    }

    public function patchStoreWeightAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['weight' => 1]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'abholmenge' => 1]);
    }

    public function canNotPatchStoreWeightWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['weight' => 8]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['weight' => 'a']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['weight' => '0.1']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);
        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'abholmenge' => $this->store['abholmenge']]);
    }

    public function patchStoreTeamStatusOk(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['teamStatus' => 2]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['store' => ['id' => $this->store[self::ID], 'teamStatus' => 2]]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['teamStatus' => 1]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['store' => ['id' => $this->store[self::ID], 'teamStatus' => 1]]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['teamStatus' => 0]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['store' => ['id' => $this->store[self::ID], 'teamStatus' => 0]]);
    }

    public function patchStoreEffortAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['effort' => 4]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'ueberzeugungsarbeit' => 4]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['effort' => 0]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'ueberzeugungsarbeit' => 0]);
    }

    public function canNotPatchStoreEffortWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['effort' => 5]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'ueberzeugungsarbeit' => $this->store['ueberzeugungsarbeit']]);
    }

    public function patchStoreOptionUseRegionPickupRuleAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['options' => ['useRegionPickupRule' => true]]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'use_region_pickup_rule' => 1]);
    }

    public function canNotPatchStoreOptionUseRegionPickupRuleWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['options' => ['useRegionPickupRule' => 'A']]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['options' => ['useRegionPickupRule' => 1]]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['options' => ['useRegionPickupRule' => 0]]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'use_region_pickup_rule' => $this->store['use_region_pickup_rule']]);
    }

    public function patchStoreOptionShowsStickerAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['showsSticker' => true]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'sticker' => 1]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['showsSticker' => false]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'sticker' => 0]);
    }

    public function canNotPatchStoreShowsStickerWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['showsSticker' => 'A']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'sticker' => $this->store['sticker']]);
    }

    public function patchStorePublicityAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['publicity' => false]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'presse' => 0]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['publicity' => true]);
        $I->seeResponseCodeIs(Http::OK);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'presse' => 1]);
    }

    public function canNotPatchStorePublicityWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['publicity' => 1]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['publicity' => 'A']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'presse' => $this->store['presse']]);
    }

    public function patchStoreTeamStatusNotFound(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID] + 1, ['teamStatus' => 2]);
        $I->seeResponseCodeIs(Http::NOT_FOUND);
    }

    public function patchStoreTeamStatusInvalid(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['teamStatus' => null]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['teamStatus' => 'a']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['teamStatus' => 3]);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);
    }

    public function patchStoreCityAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['address' => ['city' => 'Store town 123']]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['store' => ['id' => $this->store[self::ID], 'address' => ['city' => 'Store town 123']]]);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'stadt' => 'Store town 123']);
    }

    public function canNotPatchStoreCityWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['address' => 'notAObject']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['address' => ['city' => 123]]); // Wrong type
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'stadt' => $this->store['stadt']]);
    }

    public function canNotPatchStoreCityAsUnknownUser(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['address' => ['city' => 'This is a nice store']]);
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);

        $I->seeInDatabase('fs_betrieb', [
            'id' => $this->store[self::ID],
            'stadt' => $this->store['stadt']]);
    }

    public function patchStoreGroceriesAsStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['groceries' => [1, 2, 3]]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();

        $I->assertEquals(3, $I->grabNumRecords('fs_betrieb_has_lebensmittel', ['betrieb_id' => $this->store[self::ID]]));
        $I->seeInDatabase('fs_betrieb_has_lebensmittel', ['betrieb_id' => $this->store[self::ID], 'lebensmittel_id' => 1]);
        $I->seeInDatabase('fs_betrieb_has_lebensmittel', ['betrieb_id' => $this->store[self::ID], 'lebensmittel_id' => 2]);
        $I->seeInDatabase('fs_betrieb_has_lebensmittel', ['betrieb_id' => $this->store[self::ID], 'lebensmittel_id' => 3]);
    }

    public function canNotPatchStoreGroceriesWithInvalidFormatForStoreManager(ApiTester $I)
    {
        $I->login($this->manager[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['groceries' => 'String is invalid']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['groceries' => '123']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['groceries' => '1, 2, 3']);
        $I->seeResponseCodeIs(Http::BAD_REQUEST);

        $I->assertEquals(0, $I->grabNumRecords('fs_betrieb_has_lebensmittel', ['betrieb_id' => $this->store[self::ID]]));
    }

    public function canNotPatchStoreGroceriesAsUnknownUser(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_STORES . '/' . $this->store[self::ID], ['address' => ['city' => 'This is a nice store']]);
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);

        $I->assertEquals(0, $I->grabNumRecords('fs_betrieb_has_lebensmittel', ['betrieb_id' => $this->store[self::ID]]));
    }

    public function canWriteStoreWallpostAndGetAllPosts(ApiTester $I): void
    {
        $I->login($this->teamMember[self::EMAIL]);
        $newWallPost = $this->faker->realText(200);
        $I->sendPOST(self::API_STORES . '/' . $this->store[self::ID] . '/posts', ['text' => $newWallPost]);

        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeInDatabase('fs_betrieb_notiz', [
            'foodsaver_id' => $this->teamMember[self::ID],
            'betrieb_id' => $this->store[self::ID],
            'text' => $newWallPost,
        ]);

        $I->sendGET(self::API_STORES . '/' . $this->store[self::ID] . '/posts');

        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['text' => $newWallPost]);
    }

    public function noStoreWallIfNotInTeam(ApiTester $I): void
    {
        $I->login($this->user[self::EMAIL]);

        $I->sendGET(self::API_STORES . '/' . $this->store[self::ID] . '/posts');

        $I->seeResponseCodeIs(Http::FORBIDDEN);

        $I->sendPOST(self::API_STORES . '/' . $this->store[self::ID] . '/posts', ['text' => 'Lorem ipsum.']);

        $I->seeResponseCodeIs(Http::FORBIDDEN);
    }

    public function noStoreWallIfNotLoggedIn(ApiTester $I): void
    {
        $I->sendGET(self::API_STORES . '/' . $this->store[self::ID] . '/posts');

        $I->seeResponseCodeIs(Http::UNAUTHORIZED);

        $I->sendPOST(self::API_STORES . '/' . $this->store[self::ID] . '/posts', ['text' => 'Lorem ipsum.']);

        $I->seeResponseCodeIs(Http::UNAUTHORIZED);
    }

    /**
     * All team members can remove their own posts at any time.
     */
    public function canRemoveOwnStorePost(ApiTester $I): void
    {
        $wallPost = [
            'betrieb_id' => $this->store[self::ID],
            'foodsaver_id' => $this->teamMember[self::ID],
            'text' => $this->faker->realText(100),
            'zeit' => $this->faker->dateTimeBetween('-14 days', '-30m')->format('Y-m-d H:i:s'),
            'milestone' => Milestone::NONE,
        ];
        $postId = $I->haveInDatabase('fs_betrieb_notiz', $wallPost);

        $I->login($this->teamMember[self::EMAIL]);

        $I->sendDELETE(self::API_STORES . '/' . $this->store[self::ID] . '/posts/' . $postId);

        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->dontSeeInDatabase('fs_betrieb_notiz', ['id' => $postId]);

        $I->seeInDatabase('fs_store_log', [
            'store_id' => $this->store[self::ID],
            'fs_id_a' => $this->teamMember[self::ID],
            'fs_id_p' => $this->teamMember[self::ID],
            'content' => $wallPost['text'],
            'date_reference' => $wallPost['zeit'],
            'action' => StoreLogAction::DELETED_FROM_WALL,
        ]);
    }

    /**
     * Store managers can remove posts by others if they are older than 1 month.
     */
    public function storeManagerCanRemoveOldStorePost(ApiTester $I): void
    {
        $wallPost = [
            'betrieb_id' => $this->store[self::ID],
            'foodsaver_id' => $this->teamMember[self::ID],
            'text' => $this->faker->realText(100),
            'zeit' => $this->faker->dateTimeBetween('-66 days', '-33 days')->format('Y-m-d H:i:s'),
            'milestone' => Milestone::NONE,
        ];
        $postId = $I->haveInDatabase('fs_betrieb_notiz', $wallPost);

        $I->login($this->manager[self::EMAIL]);

        $I->sendDELETE(self::API_STORES . '/' . $this->store[self::ID] . '/posts/' . $postId);

        $I->seeResponseCodeIs(Http::OK);
        $I->dontSeeInDatabase('fs_betrieb_notiz', ['id' => $postId]);

        $I->seeInDatabase('fs_store_log', [
            'store_id' => $this->store[self::ID],
            'fs_id_a' => $this->manager[self::ID],
            'fs_id_p' => $this->teamMember[self::ID],
            'content' => $wallPost['text'],
            'date_reference' => $wallPost['zeit'],
            'action' => StoreLogAction::DELETED_FROM_WALL,
        ]);
    }

    public function storeManagerCannotRemoveNewStorePost(ApiTester $I): void
    {
        $wallPost = [
            'betrieb_id' => $this->store[self::ID],
            'foodsaver_id' => $this->teamMember[self::ID],
            'text' => $this->faker->realText(100),
            'zeit' => $this->faker->dateTimeBetween('-14 days', '-30m')->format('Y-m-d H:i:s'),
            'milestone' => Milestone::NONE,
        ];
        $postId = $I->haveInDatabase('fs_betrieb_notiz', $wallPost);

        $I->login($this->manager[self::EMAIL]);

        $I->sendDELETE(self::API_STORES . '/' . $this->store[self::ID] . '/posts/' . $postId);

        $I->seeResponseCodeIs(Http::FORBIDDEN);
        $I->seeInDatabase('fs_betrieb_notiz', ['id' => $postId]);
    }
}
