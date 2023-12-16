<?php

namespace api;

use ApiTester;
use Codeception\Util\HttpCode as Http;
use Faker;

/**
 * Tests for the basket api.
 */
class BasketApiCest
{
    private $user;
    private $userOrga;
    private $faker;

    private const EMAIL = 'email';
    private const API_BASKETS = 'api/baskets';
    private const ID = 'id';
    private const TEST_PICTURE = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAAAAAA6fptVAAAACklEQVR4nGNiAAAABgADNjd8qAAAAABJRU5ErkJggg==';

    public function _before(ApiTester $I)
    {
        $this->user = $I->createFoodsaver();
        $this->userOrga = $I->createOrga();
        $this->faker = Faker\Factory::create('de_DE');
    }

    public function getBasket(ApiTester $I)
    {
        $basket = $I->createFoodbasket($this->user[self::ID]);

        $I->login($this->user[self::EMAIL]);
        $I->sendGET(self::API_BASKETS . '/' . $basket[self::ID]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
    }

    public function getOutdatedBasket(ApiTester $I)
    {
        $basket = $I->createFoodbasket($this->user[self::ID], [
            'time' => $this->faker->dateTime($max = '-2 days'),
            'until' => $this->faker->dateTime($max = '-1 day')
        ]);

        $I->login($this->user[self::EMAIL]);
        $I->sendGET(self::API_BASKETS . '/' . $basket[self::ID]);
        $I->seeResponseCodeIs(Http::NOT_FOUND);
    }

    public function removeExistingBasket(ApiTester $I)
    {
        $basket = $I->createFoodbasket($this->user[self::ID]);

        $I->login($this->user[self::EMAIL]);
        $I->sendDELETE(self::API_BASKETS . '/' . $basket[self::ID]);
        $I->seeResponseCodeIs(Http::OK);
        $I->sendGET(self::API_BASKETS . '/' . $basket[self::ID]);
        $I->seeResponseCodeIs(Http::NOT_FOUND);

        $basket2 = $I->createFoodbasket($this->user[self::ID]);

        $I->login($this->userOrga[self::EMAIL]);
        $I->sendDELETE(self::API_BASKETS . '/' . $basket2[self::ID]);
        $I->seeResponseCodeIs(Http::OK);
        $I->sendGET(self::API_BASKETS . '/' . $basket2[self::ID]);
        $I->seeResponseCodeIs(Http::NOT_FOUND);
    }

    public function removeNonExistingBasket(ApiTester $I)
    {
        $I->login($this->user[self::EMAIL]);
        $I->sendDELETE(self::API_BASKETS . '/999999');
        $I->seeResponseCodeIs(Http::NOT_FOUND);
    }

    public function listMyBaskets(ApiTester $I)
    {
        $I->createFoodbasket($this->user[self::ID]);

        $I->login($this->user[self::EMAIL]);
        $I->sendGET(self::API_BASKETS . '?type=mine');
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
    }

    public function listBasketCoordinates(ApiTester $I)
    {
        $I->createFoodbasket($this->user[self::ID]);

        $I->sendGET(self::API_BASKETS . '?type=coordinates');
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
    }

    public function listNearbyBaskets(ApiTester $I)
    {
        // create a basket owned by userorga close to user's location
        $basket = $I->createFoodbasket($this->userOrga[self::ID], [
            'lat' => $this->user['lat'],
            'lon' => $this->user['lon']
        ]);

        // check that the user can see the nearby basket
        $I->login($this->user[self::EMAIL]);
        $I->sendGET(self::API_BASKETS . '/nearby?distance=30');
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['id' => $basket['id']]);

        // select a location far away from the basket and check that the user cannot see the basket there
        $newLat = $basket['lat'] + 5;
        $newLon = $basket['lon'] + 5;
        $I->sendGET(self::API_BASKETS . "/nearby?lat=$newLat&lon=$newLon&distance=30");
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->cantSeeResponseContainsJson(['id' => $basket['id']]);

        // an invalid distance should not work
        $I->sendGET(self::API_BASKETS . '/nearby?lat=50&lon=9&distance=51');
        $I->seeResponseCodeIs(Http::BAD_REQUEST);
        $I->seeResponseIsJson();
    }

    public function addBasket(ApiTester $I)
    {
        $I->login($this->user[self::EMAIL]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost(self::API_BASKETS, [
            'description' => 'test description',
            'lat' => 0.0,
            'lon' => 0.0,
            'contactTypes' => [1],
            'lifeTimeInDays' => 3,
        ]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
    }

    public function noUnauthorizedActions(ApiTester $I)
    {
        $basket = $I->createFoodbasket($this->user[self::ID]);

        $I->sendGET(self::API_BASKETS . '?type=mine');
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);
        $I->sendGET(self::API_BASKETS . '/' . $basket[self::ID]);
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);
        $I->sendDELETE(self::API_BASKETS . '/' . $basket[self::ID]);
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::API_BASKETS);
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);
    }

    public function editBasket(ApiTester $I)
    {
        $testDescription = 'lorem ipsum';
        $lat = 12.34;
        $lon = 56.78;
        $basket = $I->createFoodbasket($this->user[self::ID]);

        $I->login($this->user[self::EMAIL]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(self::API_BASKETS . '/' . $basket[self::ID], ['description' => '']);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(self::API_BASKETS . '/' . $basket[self::ID], [
            'description' => $testDescription,
            'lat' => $lat,
            'lon' => $lon,
            'contactTypes' => [1],
            'lifeTimeInDays' => 3,
        ]);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->canSeeResponseContainsJson([
            'description' => $testDescription
        ]);
        $I->assertEqualsWithDelta($lat, $I->grabDataFromResponseByJsonPath('basket.lat')[0], 0.1);
        $I->assertEqualsWithDelta($lon, $I->grabDataFromResponseByJsonPath('basket.lon')[0], 0.1);
    }
}
