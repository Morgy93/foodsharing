<?php

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Faker\Factory;
use Faker\Generator;
use Foodsharing\Modules\Core\DBConstants\Bell\BellType;
use Foodsharing\Modules\Core\DBConstants\Store\ConvinceStatus;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\PublicTimes;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Store\PickupGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\StoreTransactionException;
use Foodsharing\Modules\Store\StoreTransactions;

class StoreTransactionsTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;
    protected StoreGateway $store;
    private StoreTransactions $transactions;
    private PickupGateway $gateway;
    private Generator $faker;

    private $region_id;
    private $foodsaver;

    public function _before()
    {
        $this->store = $this->tester->get(StoreGateway::class);
        $this->transactions = $this->tester->get(StoreTransactions::class);
        $this->gateway = $this->tester->get(PickupGateway::class);
        $this->store = $this->tester->get(StoreGateway::class);
        $this->faker = $this->faker = Factory::create('de_DE');
        $this->foodsaver = $this->tester->createFoodsaver();
        $this->region_id = $this->tester->createRegion()['id'];
    }

    public function testDefaultCommonStoreMetaData()
    {
        $foods = [$this->tester->addStoreFoodType(),
            $this->tester->addStoreFoodType()];
        usort($foods, function ($a, $b) { return strcmp($a['name'], $b['name']); });
        $chains = [$this->tester->addStoreChain(), $this->tester->addStoreChain()];
        usort($chains, function ($a, $b) { return strcmp($a['name'], $b['name']); });

        $this->tester->createStoreCategories();

        $common = $this->transactions->getCommonStoreMetadata();

        // Check cooperation status
        $this->assertEquals(CooperationStatus::NO_CONTACT->value, $common->status[0]->id);
        $this->assertEquals(CooperationStatus::IN_NEGOTIATION->value, $common->status[1]->id);
        $this->assertEquals(CooperationStatus::COOPERATION_STARTING->value, $common->status[2]->id);
        $this->assertEquals(CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US->value, $common->status[3]->id);
        $this->assertEquals(CooperationStatus::COOPERATION_ESTABLISHED->value, $common->status[4]->id);
        $this->assertEquals(CooperationStatus::GIVES_TO_OTHER_CHARITY->value, $common->status[5]->id);
        $this->assertEquals(CooperationStatus::PERMANENTLY_CLOSED->value, $common->status[6]->id);

        // Check food types
        foreach ($foods as $key => $food) {
            $this->assertEquals($food['id'], $common->groceries[$key]->id);
            $this->assertEquals($food['name'], $common->groceries[$key]->name);
        }

        // Check store chains
        $this->assertNull($common->storeChains);

        // Check categories
        $this->assertEquals($this->tester->grabNumRecords('fs_betrieb_kategorie'), count($common->categories));
        foreach ($common->categories as $category) {
            $this->tester->seeInDatabase('fs_betrieb_kategorie', ['id' => $category->id, 'name' => $category->name]);
        }

        // Check weight
        $this->assertEquals(0, $common->weight[0]->id);
        $this->assertEquals('Keine Angabe', $common->weight[0]->name);
        $this->assertEquals(1, $common->weight[1]->id);
        $this->assertEquals('1-3 kg', $common->weight[1]->name);
        $this->assertEquals(2, $common->weight[2]->id);
        $this->assertEquals('3-5 kg', $common->weight[2]->name);
        $this->assertEquals(3, $common->weight[3]->id);
        $this->assertEquals('5-10 kg', $common->weight[3]->name);
        $this->assertEquals(4, $common->weight[4]->id);
        $this->assertEquals('10-20 kg', $common->weight[4]->name);
        $this->assertEquals(5, $common->weight[5]->id);
        $this->assertEquals('20-30 kg', $common->weight[5]->name);
        $this->assertEquals(6, $common->weight[6]->id);
        $this->assertEquals('40-50 kg', $common->weight[6]->name);
        $this->assertEquals(7, $common->weight[7]->id);
        $this->assertEquals('mehr als 50 kg', $common->weight[7]->name);

        // Check possible pickup time range
        $this->assertEquals(PublicTimes::IN_THE_MORNING->value, $common->status[0]->id);
        $this->assertEquals(PublicTimes::AT_NOON_IN_THE_AFTERNOON->value, $common->status[1]->id);
        $this->assertEquals(PublicTimes::IN_THE_EVENING->value, $common->status[2]->id);
        $this->assertEquals(PublicTimes::AT_NIGHT->value, $common->status[3]->id);

        // Check convince status
        $this->assertEquals(ConvinceStatus::NO_PROBLEM_AT_ALL->value, $common->status[0]->id);
        $this->assertEquals(ConvinceStatus::AFTER_SOME_PERSUASION->value, $common->status[1]->id);
        $this->assertEquals(ConvinceStatus::DIFFICULT_NEGOTIATION->value, $common->status[2]->id);
        $this->assertEquals(ConvinceStatus::LOOKED_BAD_BUT_WORKED->value, $common->status[3]->id);
    }

    public function testAllCommonStoreMetaDataWithLoadOfStoreChains()
    {
        $foods = [$this->tester->addStoreFoodType(),
            $this->tester->addStoreFoodType()];
        usort($foods, function ($a, $b) { return strcmp($a['name'], $b['name']); });
        $chains = [$this->tester->addStoreChain(), $this->tester->addStoreChain()];
        usort($chains, function ($a, $b) { return strcmp($a['name'], $b['name']); });

        $this->tester->createStoreCategories();

        $common = $this->transactions->getCommonStoreMetadata(false);

        // Check cooperation status
        $this->assertEquals(CooperationStatus::NO_CONTACT->value, $common->status[0]->id);
        $this->assertEquals(CooperationStatus::IN_NEGOTIATION->value, $common->status[1]->id);
        $this->assertEquals(CooperationStatus::COOPERATION_STARTING->value, $common->status[2]->id);
        $this->assertEquals(CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US->value, $common->status[3]->id);
        $this->assertEquals(CooperationStatus::COOPERATION_ESTABLISHED->value, $common->status[4]->id);
        $this->assertEquals(CooperationStatus::GIVES_TO_OTHER_CHARITY->value, $common->status[5]->id);
        $this->assertEquals(CooperationStatus::PERMANENTLY_CLOSED->value, $common->status[6]->id);

        // Check food types
        foreach ($foods as $key => $food) {
            $this->assertEquals($food['id'], $common->groceries[$key]->id);
            $this->assertEquals($food['name'], $common->groceries[$key]->name);
        }

        // Check store chains
        foreach ($chains as $key => $chain) {
            $this->assertEquals($chain['id'], $common->storeChains[$key]->id);
            $this->assertEquals($chain['name'], $common->storeChains[$key]->name);
        }

        // Check categories
        $this->assertEquals($this->tester->grabNumRecords('fs_betrieb_kategorie'), count($common->categories));
        foreach ($common->categories as $category) {
            $this->tester->seeInDatabase('fs_betrieb_kategorie', ['id' => $category->id, 'name' => $category->name]);
        }

        // Check weight
        $this->assertEquals(0, $common->weight[0]->id);
        $this->assertEquals('Keine Angabe', $common->weight[0]->name);
        $this->assertEquals(1, $common->weight[1]->id);
        $this->assertEquals('1-3 kg', $common->weight[1]->name);
        $this->assertEquals(2, $common->weight[2]->id);
        $this->assertEquals('3-5 kg', $common->weight[2]->name);
        $this->assertEquals(3, $common->weight[3]->id);
        $this->assertEquals('5-10 kg', $common->weight[3]->name);
        $this->assertEquals(4, $common->weight[4]->id);
        $this->assertEquals('10-20 kg', $common->weight[4]->name);
        $this->assertEquals(5, $common->weight[5]->id);
        $this->assertEquals('20-30 kg', $common->weight[5]->name);
        $this->assertEquals(6, $common->weight[6]->id);
        $this->assertEquals('40-50 kg', $common->weight[6]->name);
        $this->assertEquals(7, $common->weight[7]->id);
        $this->assertEquals('mehr als 50 kg', $common->weight[7]->name);
    }

    public function testPickupSlotAvailableRegular()
    {
        $store = $this->tester->createStore($this->region_id);
        $foodsaver2 = $this->tester->createFoodsaver();
        $date = Carbon::now()->add('2 days')->hours(16)->minutes(30)->seconds(0)->microseconds(0);
        $dow = $date->weekday();
        $fetcher = 2;

        $this->tester->addRecurringPickup($store['id'], ['time' => '16:30:00', 'dow' => $dow, 'fetcher' => $fetcher]);

        $this->assertEquals($fetcher, $this->transactions->totalSlotsIfPickupSlotAvailable($store['id'], $date, $this->foodsaver['id']));

        $this->tester->addCollector($this->foodsaver['id'], $store['id'], ['date' => $date]);

        $this->assertEquals(0, $this->transactions->totalSlotsIfPickupSlotAvailable($store['id'], $date, $this->foodsaver['id']));
        $this->assertEquals($fetcher, $this->transactions->totalSlotsIfPickupSlotAvailable($store['id'], $date, $foodsaver2['id']));

        $this->tester->addCollector($foodsaver2['id'], $store['id'], ['date' => $date]);
        $this->assertEquals(0, $this->transactions->totalSlotsIfPickupSlotAvailable($store['id'], $date));
    }

    public function testPickupSlotAvailableMixed()
    {
        $store = $this->tester->createStore($this->region_id);
        $date = Carbon::now()->add('3 days')->hours(16)->minutes(40)->seconds(0)->microseconds(0);
        $dow = $date->format('w');

        $fetcher = 2;
        $this->tester->addRecurringPickup($store['id'], ['time' => '16:40:00', 'dow' => $dow, 'fetcher' => $fetcher]);

        $fetchercount = 1;
        $this->tester->addPickup($store['id'], ['time' => $date, 'fetchercount' => $fetchercount]);
        $this->assertEquals($fetchercount, $this->transactions->totalSlotsIfPickupSlotAvailable($store['id'], $date));

        $this->tester->addCollector($this->foodsaver['id'], $store['id'], ['date' => $date]);

        $this->assertEquals(0, $this->transactions->totalSlotsIfPickupSlotAvailable($store['id'], $date, $this->foodsaver['id']));
        $this->assertEquals(0, $this->transactions->totalSlotsIfPickupSlotAvailable($store['id'], $date));
    }

    public function testSinglePickupTimeProperlyTakenIntoAccount()
    {
        $store = $this->tester->createStore($this->region_id);
        $user = $this->tester->createFoodsaver();

        $date = Carbon::instance($this->faker->dateTimeInInterval('+2 days', '+10 days'));
        $this->tester->addPickup($store['id'], ['time' => $date, 'fetchercount' => 1]);

        $date2 = $date->copy()->addHours(1);
        $this->tester->addPickup($store['id'], ['time' => $date2, 'fetchercount' => 1]);

        $this->assertFalse($this->transactions->joinPickup($store['id'], $date, $this->foodsaver['id'], $this->foodsaver['id']));
        $this->expectException(StoreTransactionException::class);

        $this->transactions->joinPickup($store['id'], $date, $user['id'], $user['id']);
    }

    public function testPickupSlotNotAvailableEmpty()
    {
        $store = $this->tester->createStore($this->region_id);
        $date = Carbon::now()->add('1 day')->microseconds(0);

        $this->assertEquals(0, $this->transactions->totalSlotsIfPickupSlotAvailable($store['id'], $date));
    }

    public function testUserCanOnlySignupOncePerSlot()
    {
        $store = $this->tester->createStore($this->region_id);
        $date = Carbon::now()->add('4 days')->hours(16)->minutes(20)->seconds(0)->microseconds(0);
        $dow = $date->format('w');
        $fetcher = 2;

        $this->tester->addRecurringPickup($store['id'], ['time' => '16:20:00', 'dow' => $dow, 'fetcher' => $fetcher]);

        $this->assertFalse($this->transactions->joinPickup($store['id'], $date, $this->foodsaver['id'], $this->foodsaver['id']));
        $this->expectException(StoreTransactionException::class);

        $this->transactions->joinPickup($store['id'], $date, $this->foodsaver['id'], $this->foodsaver['id']);
    }

    public function testUserCanOnlySignupForHimSelfOncePerSlot()
    {
        $store = $this->tester->createStore($this->region_id);
        $date = Carbon::now()->add('4 days')->hours(16)->minutes(20)->seconds(0)->microseconds(0);
        $dow = $date->format('w');
        $fetcher = 2;

        $this->tester->addRecurringPickup($store['id'], ['time' => '16:20:00', 'dow' => $dow, 'fetcher' => $fetcher]);
        $this->transactions->joinPickup($store['id'], $date, $this->foodsaver['id'], $this->foodsaver['id']);

        $this->expectException(StoreTransactionException::class);
        $this->transactions->joinPickup($store['id'], $date, $this->foodsaver['id'], $this->foodsaver['id']);
    }

    public function testComfirmForStoreCoordinatorOfThisStoreOnSignupSelfSuccessful()
    {
        $coordinator = $this->tester->createStoreCoordinator();
        $store = $this->tester->createStore($this->region_id);
        $this->tester->addStoreTeam($store['id'], $coordinator['id'], true);
        $date = Carbon::now()->add('4 days')->hours(16)->minutes(20)->seconds(0)->microseconds(0);
        $dow = $date->format('w');
        $fetcher = 2;

        $this->tester->addRecurringPickup($store['id'], ['time' => '16:20:00', 'dow' => $dow, 'fetcher' => $fetcher]);

        $this->assertTrue($this->transactions->joinPickup($store['id'], $date, $coordinator['id'], $coordinator['id']));
    }

    public function testNoComfirmForStoreTeamMemberOnSignup()
    {
        $store = $this->tester->createStore($this->region_id);
        $date = Carbon::now()->add('4 days')->hours(16)->minutes(20)->seconds(0)->microseconds(0);
        $dow = $date->format('w');
        $fetcher = 2;

        $this->tester->addRecurringPickup($store['id'], ['time' => '16:20:00', 'dow' => $dow, 'fetcher' => $fetcher]);

        $this->assertFalse($this->transactions->joinPickup($store['id'], $date, $this->foodsaver['id'], $this->foodsaver['id']));
    }

    public function testComfirmForStoreCoordinatorOfOtherStoreOnSignupSelfUnsuccessful()
    {
        $coordinator1 = $this->tester->createStoreCoordinator();
        $coordinator2 = $this->tester->createStoreCoordinator();
        $store1 = $this->tester->createStore($this->region_id);
        $store2 = $this->tester->createStore($this->region_id);
        $this->tester->addStoreTeam($store1['id'], $coordinator1['id'], true);
        $this->tester->addStoreTeam($store2['id'], $coordinator2['id'], true);

        $date = Carbon::now()->add('4 days')->hours(16)->minutes(20)->seconds(0)->microseconds(0);
        $dow = $date->format('w');
        $fetcher = 2;

        $this->tester->addRecurringPickup($store1['id'], ['time' => '16:20:00', 'dow' => $dow, 'fetcher' => $fetcher]);
        $this->assertFalse($this->transactions->joinPickup($store1['id'], $date, $coordinator2['id'], $coordinator2['id']));
    }

    public function testUserCanOnlySignupForFuturePickups()
    {
        $store = $this->tester->createStore($this->region_id);
        $pickup = new Carbon('1 hour ago');
        $fetchercount = 1;

        $this->tester->addPickup($store['id'], ['time' => $pickup, 'fetchercount' => $fetchercount]);

        $this->expectException(StoreTransactionException::class);

        $this->transactions->joinPickup($store['id'], $pickup, $this->foodsaver['id'], $this->foodsaver['id']);
    }

    public function testUserCanOnlySignupForNotTooMuchInTheFuturePickups()
    {
        $interval = CarbonInterval::weeks(3);
        $store = $this->tester->createStore($this->region_id, null, null, ['prefetchtime' => $interval->totalSeconds - 360]);

        /* that pickup is now at least some minutes too much in the future to sign up */
        $pickup = Carbon::tomorrow()->add($interval)->microseconds(0);

        /* use recurring pickup here because signing up for single pickups should work indefinitely */
        $fetcher = 1;
        $this->tester->addRecurringPickup($store['id'], [
            'time' => $pickup->toTimeString(),
            'dow' => $pickup->weekday(),
            'fetcher' => $fetcher,
        ]);

        $this->expectException(StoreTransactionException::class);

        $this->transactions->joinPickup($store['id'], $pickup, $this->foodsaver['id'], $this->foodsaver['id']);

        $this->assertFalse($this->transactions->joinPickup($store['id'], $pickup->sub('1 week'), $this->foodsaver['id'], $this->foodsaver['id']));
    }

    public function testUserCanSignupForManualFarInTheFuturePickups()
    {
        $interval = CarbonInterval::weeks(3);
        $store = $this->tester->createStore($this->region_id, null, null, ['prefetchtime' => $interval->totalSeconds - 360]);

        /* that pickup is now at least some minutes too much in the future to sign up */
        $pickup = Carbon::now()->add($interval)->microseconds(0);

        /* use single pickup, which should work indefinitely */
        $this->tester->addPickup($store['id'], ['time' => $pickup, 'fetchercount' => 1]);

        $this->assertFalse($this->transactions->joinPickup($store['id'], $pickup, $this->foodsaver['id'], $this->foodsaver['id']));
    }

    public function testUpdateExpiredBellsUpdatesBellCountIfStillUnconfirmedFetchesAreInTheFuture()
    {
        $store = $this->tester->createStore($this->region_id);
        $foodsaver = $this->tester->createFoodsaver();

        $this->tester->addPickup($store['id'], ['time' => '2150-01-01 00:00:00', 'fetchercount' => 1]);
        $this->tester->addPickup($store['id'], ['time' => '2150-01-02 00:00:00', 'fetchercount' => 1]);

        $this->assertFalse($this->transactions->joinPickup($store['id'], new Carbon('2150-01-01 00:00:00'), $foodsaver['id'], $foodsaver['id']));
        $this->assertFalse($this->transactions->joinPickup($store['id'], new Carbon('2150-01-02 00:00:00'), $foodsaver['id'], $foodsaver['id']));

        // As we can't change the NOW() time in the database for the test, we have to move one fetch date to the past:
        $this->tester->updateInDatabase(
            'fs_abholer',
            ['date' => '1970-01-01 00:00:00'],
            ['date' => '2150-01-01 00:00:00']
        );

        /* Now, we have two unconfirmed fetch dates in the database: One that is in the future (2150-01-02) and one
         * that is in the past (1970-01-01).
         */

        $this->tester->updateInDatabase(
            'fs_bell',
            ['expiration' => '1970-01-01 00:00:00'],
            ['identifier' => BellType::createIdentifier(BellType::STORE_UNCONFIRMED_PICKUP, $store['id'])]
        );
        // expire bell notification
        $this->gateway->updateExpiredBells();

        // The bell should have a count of 1 now - vars are serialized, that's why it looks so strange
        $this->tester->seeInDatabase('fs_bell', ['vars like' => '%"count";i:1;%']);
    }

    /**
     * If there are muliple fetches to confirm for one BIEB, only one store bell should be generated. It should
     * have the date of the soonest fetch as its date, and it should contain the number of only the unconfirmed fetch
     * dates that are in the future.
     */
    public function testStoreBellsAreGeneratedCorrectly()
    {
        $this->tester->clearTable('fs_abholer');

        $user = $this->tester->createFoodsaver();
        $store = $this->tester->createStore(0);

        $pastDate = Carbon::instance($this->faker->dateTimeBetween($max = 'now'));
        $soonDate = Carbon::instance($this->faker->dateTimeBetween('+1 days', '+2 days'));
        $futureDate = Carbon::instance($this->faker->dateTimeBetween('+7 days', '+14 days'));

        $this->tester->addPickup($store['id'], ['time' => $soonDate, 'fetchercount' => 2]);
        $this->tester->addPickup($store['id'], ['time' => $futureDate, 'fetchercount' => 2]);

        $this->gateway->addFetcher($user['id'], $store['id'], $pastDate);
        $this->transactions->joinPickup($store['id'], $soonDate, $user['id'], $user['id']);
        $this->transactions->joinPickup($store['id'], $futureDate, $user['id'], $user['id']);

        $this->tester->seeNumRecords(3, 'fs_abholer');

        $this->tester->seeNumRecords(1, 'fs_bell', ['identifier' => BellType::createIdentifier(BellType::STORE_UNCONFIRMED_PICKUP, $store['id'])]);

        $bellVars = $this->tester->grabFromDatabase('fs_bell', 'vars', ['identifier' => BellType::createIdentifier(BellType::STORE_UNCONFIRMED_PICKUP, $store['id'])]);
        $vars = unserialize($bellVars);
        $this->assertEquals(2, $vars['count']);

        $bellDate = $this->tester->grabFromDatabase('fs_bell', 'time', ['identifier' => BellType::createIdentifier(BellType::STORE_UNCONFIRMED_PICKUP, $store['id'])]);
        $this->assertEquals($soonDate->format('Y-m-d H:i:s'), $bellDate);
    }

    public function testNextAvailablePickupTime()
    {
        $date = Carbon::now()->add('2 days')->hours(16)->minutes(30)->seconds(0)->microseconds(0);
        $maxDate = $date->add('1 day');
        $dow = $date->weekday();

        // stores should result is a non-null date if there are free slots available
        $store = $this->tester->createStore($this->region_id, null, null, ['betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED->value]);
        $this->tester->addRecurringPickup($store['id'], ['time' => '16:30:00', 'dow' => $dow, 'fetcher' => 1]);
        $this->assertEquals($this->transactions->getNextAvailablePickupTime($store['id'], $maxDate), $date);

        $this->tester->addCollector($this->foodsaver['id'], $store['id'], ['date' => $date]);
        $this->assertNull($this->transactions->getNextAvailablePickupTime($store['id'], $maxDate));
    }

    public function testAvailablePickupStatus()
    {
        $date = Carbon::now()->add('2 days')->hours(16)->minutes(30)->seconds(0)->microseconds(0);
        $dow = $date->weekday();

        // stores should have status != 0 if free slots are available
        $store = $this->tester->createStore($this->region_id, null, null, ['betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED->value]);
        $this->tester->addRecurringPickup($store['id'], ['time' => '16:30:00', 'dow' => $dow, 'fetcher' => 1]);
        $this->assertEquals($this->transactions->getAvailablePickupStatus($store['id']), 2);

        $this->tester->addCollector($this->foodsaver['id'], $store['id'], ['date' => $date]);
        $this->assertEquals($this->transactions->getAvailablePickupStatus($store['id']), 0);
    }

    public function testListStoresOfRegionWithoutExpendRegion()
    {
        $regionRelatedRegion = $this->tester->createRegion();
        $this->tester->createStore($regionRelatedRegion['id']);
        $this->tester->createStore($regionRelatedRegion['id']);

        $regionTop = $this->tester->createRegion(null, ['type' => UnitType::CITY]);
        $regionChild1 = $this->tester->createRegion(null, ['parent_id' => $regionTop['id'], 'type' => UnitType::PART_OF_TOWN]);
        $store1 = $this->tester->createStore($regionChild1['id']);
        $regionChild2 = $this->tester->createRegion(null, ['parent_id' => $regionTop['id'], 'type' => UnitType::PART_OF_TOWN]);
        $store2 = $this->tester->createStore($regionChild2['id']);

        $listOfStores = $this->transactions->listOverviewInformationsOfStoresInRegion($regionTop['id'], false);
        $this->assertIsArray($listOfStores);
        $this->assertEquals(2, count($listOfStores));
        $this->assertContainsOnlyInstancesOf('Foodsharing\Modules\Store\DTO\StoreListInformation', $listOfStores);
        $storeIds = array_map(function ($store) { return $store->id; }, $listOfStores);
        $this->assertContainsEquals($store1['id'], $storeIds);
        $this->assertContainsEquals($store2['id'], $storeIds);

        foreach ($listOfStores as $store) {
            $this->assertNull($store->name);
            $this->assertNull($store->region);
        }
    }

    public function testListStoresOfRegionWithExpandRegion()
    {
        $regionRelatedRegion = $this->tester->createRegion();
        $this->tester->createStore($regionRelatedRegion['id']);
        $this->tester->createStore($regionRelatedRegion['id']);

        $regionTop = $this->tester->createRegion(null, ['type' => UnitType::CITY]);
        $regionChild1 = $this->tester->createRegion(null, ['parent_id' => $regionTop['id'], 'type' => UnitType::PART_OF_TOWN]);
        $store1 = $this->tester->createStore($regionChild1['id']);
        $regionChild2 = $this->tester->createRegion(null, ['parent_id' => $regionTop['id'], 'type' => UnitType::PART_OF_TOWN]);
        $store2 = $this->tester->createStore($regionChild2['id']);

        $listOfStores = $this->transactions->listOverviewInformationsOfStoresInRegion($regionTop['id'], true);
        $this->assertIsArray($listOfStores);
        $this->assertEquals(2, count($listOfStores));
        $this->assertContainsOnlyInstancesOf('Foodsharing\Modules\Store\DTO\StoreListInformation', $listOfStores);
        $storeIds = array_map(function ($store) { return $store->id; }, $listOfStores);
        $this->assertContainsEquals($store1['id'], $storeIds);
        $this->assertContainsEquals($store2['id'], $storeIds);

        $storeNames = array_map(function ($store) { return $store->region->name; }, $listOfStores);
        foreach ($listOfStores as $store) {
            $this->assertNotNull($store->region->name);
        }
        $this->assertContainsEquals($regionChild1['name'], $storeNames);
        $this->assertContainsEquals($regionChild2['name'], $storeNames);
    }

    public function testListAllStoreStatusForFoodsaver()
    {
        // Create store coordinator
        $date = Carbon::now()->add('2 days')->hours(16)->minutes(30)->seconds(0)->microseconds(0);
        $dow = $date->weekday();

        $store_coord = $this->tester->createStore($this->region_id, null, null, ['betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED->value]);
        $this->tester->addStoreTeam($store_coord['id'], $this->foodsaver['id'], true);
        $this->tester->addRecurringPickup($store_coord['id'], ['time' => '16:30:00', 'dow' => $dow, 'fetcher' => 1]);
        $regularSlots = $this->gateway->getRegularPickups($store_coord['id']);

        // Create store membership
        $store_member = $this->tester->createStore($this->region_id, null, null, ['betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED->value]);
        $this->tester->addStoreTeam($store_member['id'], $this->foodsaver['id'], false);

        $result = $this->transactions->listAllStoreStatusForFoodsaver($this->foodsaver['id']);
        $this->assertEquals(count($result), 2);

        $this->assertEquals($result[0]->store->id, $store_coord['id']);
        $this->assertTrue($result[0]->isManaging);
        $this->assertEquals($result[0]->membershipStatus, 1);
        $this->assertEquals($result[0]->pickupStatus, 2);

        $this->assertEquals($result[1]->store->id, $store_member['id']);
        $this->assertFalse($result[1]->isManaging);
        $this->assertEquals($result[1]->membershipStatus, 1);
        $this->assertEquals($result[1]->pickupStatus, 0);
    }
}
