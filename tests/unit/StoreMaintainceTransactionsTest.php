<?php

use Carbon\Carbon;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Store\StoreMaintainceTransactions;

class StoreMaintainceTransactionsTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;
    private readonly StoreMaintainceTransactions $transactions;

    public function _before()
    {
        $this->transactions = $this->tester->get(StoreMaintainceTransactions::class);
    }

    public function testTriggerFetchWarningNotificationWithAllStoreTypes()
    {
        $region = $this->tester->createRegion();

        $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => CooperationStatus::UNCLEAR->value]);
        $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => CooperationStatus::NO_CONTACT->value]);
        $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => CooperationStatus::IN_NEGOTIATION->value]);
        $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => CooperationStatus::COOPERATION_STARTING->value]);
        $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US->value]);
        $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED->value]);
        $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => CooperationStatus::GIVES_TO_OTHER_CHARITY->value]);
        $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => CooperationStatus::PERMANENTLY_CLOSED->value]);

        $statistics = $this->transactions->triggerFetchWarningNotification();
        $this->assertEquals(2, $statistics['count_stores']);
    }

    public function testTriggerFetchWarningNotificationWithFor48HourBeforePickup()
    {
        $region = $this->tester->createRegion();
        $foodsaver1 = $this->tester->createFoodsaver();
        $storeCoordinator = $this->tester->createStoreCoordinator();

        $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => '5']);
        $storeWithRegularPickup = $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => '5']);

        $this->tester->addStoreTeam($storeWithRegularPickup['id'], $foodsaver1['id'], false);
        $this->tester->addStoreTeam($storeWithRegularPickup['id'], $storeCoordinator['id'], true);

        $referenceDate = Carbon::create(2020, 12, 10, 10, 10, 10);
        $dayOfPickup = $referenceDate->clone()->addHour();
        $timeOfPickup = $referenceDate->clone()->format('H:i:s');
        $dayOfWeekToday = intval($dayOfPickup->format('w'));
        $dayOfWeekTomorrow = intval($dayOfPickup->clone()->addHours(24)->format('w'));
        $dayOfWeekDayAfterTomorrow = intval($dayOfPickup->clone()->addHours(48)->addSecond(1)->format('w'));
        $timeOfPickAfterTomorrow = $dayOfPickup->clone()->addHours(48)->addSecond(1)->format('H:i:s');
        $this->tester->addRecurringPickup($storeWithRegularPickup['id'], ['time' => $timeOfPickup, 'dow' => $dayOfWeekToday, 'fetcher' => 1]);
        $this->tester->addRecurringPickup($storeWithRegularPickup['id'], ['time' => $timeOfPickup, 'dow' => $dayOfWeekTomorrow, 'fetcher' => 1]);
        $this->tester->addRecurringPickup($storeWithRegularPickup['id'], ['time' => $timeOfPickAfterTomorrow, 'dow' => $dayOfWeekDayAfterTomorrow, 'fetcher' => 1]);

        $statistics = $this->transactions->triggerFetchWarningNotification();
        $this->assertEquals(2, $statistics['count_stores']);
        $this->assertEquals(1, $statistics['count_stores_with_notifications']);
        $this->assertEquals(1, $statistics['count_unique_foodsavers']);
        $this->assertEquals(2, $statistics['count_total_empty_pickups']);
    }

    public function testTriggerFetchWarningNotificationWithOnlyRegularPickups()
    {
        $region = $this->tester->createRegion();
        $foodsaver1 = $this->tester->createFoodsaver();
        $storeCoordinator = $this->tester->createStoreCoordinator();

        $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => '5']);
        $storeWithRegularPickup = $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => '5']);

        $this->tester->addStoreTeam($storeWithRegularPickup['id'], $foodsaver1['id'], false);
        $this->tester->addStoreTeam($storeWithRegularPickup['id'], $storeCoordinator['id'], true);

        $dayOfPickup = (new Carbon())->addHour(1);
        $timeOfPickup = $dayOfPickup->clone()->format('H:i:s');
        $dayOfWeek = intval($dayOfPickup->format('w'));
        $dayOfWeek2 = intval((new Carbon())->clone()->addDays(3)->format('w'));
        $this->tester->addRecurringPickup($storeWithRegularPickup['id'], ['time' => $timeOfPickup, 'dow' => $dayOfWeek, 'fetcher' => 1]);
        $this->tester->addRecurringPickup($storeWithRegularPickup['id'], ['time' => $timeOfPickup, 'dow' => $dayOfWeek2, 'fetcher' => 1]);

        $statistics = $this->transactions->triggerFetchWarningNotification();
        $this->assertEquals(2, $statistics['count_stores']);
        $this->assertEquals(1, $statistics['count_stores_with_notifications']);
        $this->assertEquals(1, $statistics['count_unique_foodsavers']);
        $this->assertEquals(1, $statistics['count_total_empty_pickups']);

        $this->tester->addCollector($foodsaver1['id'], $storeWithRegularPickup['id'], ['date' => $dayOfPickup]);
        $statistics = $this->transactions->triggerFetchWarningNotification();
        $this->assertEquals(2, $statistics['count_stores']);
        $this->assertEquals(0, $statistics['count_unique_foodsavers']);
        $this->assertEquals(0, $statistics['count_total_empty_pickups']);
        $this->assertEquals(0, $statistics['count_stores_with_notifications']);
    }

    public function testTriggerFetchWarningNotificationWithRegularPickupsWithOverrideZeroSlots()
    {
        $region = $this->tester->createRegion();
        $foodsaver1 = $this->tester->createFoodsaver();
        $storeCoordinator = $this->tester->createStoreCoordinator();
        $storeCoordinator2 = $this->tester->createStoreCoordinator();

        $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => '5']);
        $storeWithRegularPickup = $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => '5']);

        $this->tester->addStoreTeam($storeWithRegularPickup['id'], $foodsaver1['id'], false);
        $this->tester->addStoreTeam($storeWithRegularPickup['id'], $storeCoordinator['id'], true);
        $this->tester->addStoreTeam($storeWithRegularPickup['id'], $storeCoordinator2['id'], true);

        $dayOfPickup = (new Carbon())->addHour(1);
        $timeOfPickup = $dayOfPickup->clone()->format('H:i:s');

        $dayOfWeek = intval($dayOfPickup->format('w'));
        $dayOfWeek2 = intval((new Carbon())->clone()->addDays(3)->format('w'));
        $this->tester->addRecurringPickup($storeWithRegularPickup['id'], ['time' => $timeOfPickup, 'dow' => $dayOfWeek, 'fetcher' => 1]);
        $this->tester->addRecurringPickup($storeWithRegularPickup['id'], ['time' => $timeOfPickup, 'dow' => $dayOfWeek2, 'fetcher' => 1]);

        $statistics = $this->transactions->triggerFetchWarningNotification();
        $this->assertEquals(2, $statistics['count_stores']);
        $this->assertEquals(1, $statistics['count_stores_with_notifications']);
        $this->assertEquals(2, $statistics['count_unique_foodsavers']);
        $this->assertEquals(1, $statistics['count_total_empty_pickups']);

        $this->tester->addPickup($storeWithRegularPickup['id'], ['time' => $dayOfPickup, 'fetchercount' => 0]);

        $statistics = $this->transactions->triggerFetchWarningNotification();
        $this->assertEquals(2, $statistics['count_stores']);
        $this->assertEquals(0, $statistics['count_unique_foodsavers']);
        $this->assertEquals(0, $statistics['count_total_empty_pickups']);
        $this->assertEquals(0, $statistics['count_stores_with_notifications']);
    }

    public function testTriggerFetchWarningNotificationWithMixedPickups()
    {
        $region = $this->tester->createRegion();
        $foodsaver1 = $this->tester->createFoodsaver();
        $storeCoordinator = $this->tester->createStoreCoordinator();

        $storeWithRegularPickup = $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => '5']);
        $this->tester->addStoreTeam($storeWithRegularPickup['id'], $foodsaver1['id'], false);
        $this->tester->addStoreTeam($storeWithRegularPickup['id'], $storeCoordinator['id'], true);

        $dayOfPickup = new Carbon();
        $timeOfPickup = $dayOfPickup->clone()->addHour(1)->format('H:i:s');

        $dayOfWeek = intval($dayOfPickup->format('w'));
        $dayOfWeek2 = intval((new Carbon())->clone()->addDays(3)->format('w'));

        // Regular pickups
        $this->tester->addRecurringPickup($storeWithRegularPickup['id'], ['time' => $timeOfPickup, 'dow' => $dayOfWeek, 'fetcher' => 1]);
        $this->tester->addRecurringPickup($storeWithRegularPickup['id'], ['time' => $timeOfPickup, 'dow' => $dayOfWeek2, 'fetcher' => 1]);

        // Additional pickups
        $additionalPickupDate = $dayOfPickup->addDay();
        $this->tester->addPickup($storeWithRegularPickup['id'], ['time' => $additionalPickupDate, 'fetchercount' => 10]);

        // Test warning
        $statistics = $this->transactions->triggerFetchWarningNotification();
        $this->assertEquals(1, $statistics['count_stores']);
        $this->assertEquals(1, $statistics['count_unique_foodsavers']);
        $this->assertEquals(2, $statistics['count_total_empty_pickups']);
        $this->assertEquals(1, $statistics['count_stores_with_notifications']);

        // Add foodsaver to additional pickup
        $this->tester->addCollector($foodsaver1['id'], $storeWithRegularPickup['id'], ['date' => $additionalPickupDate]);

        // Check reducing warnings
        $statistics = $this->transactions->triggerFetchWarningNotification();
        $this->assertEquals(1, $statistics['count_stores']);
        $this->assertEquals(1, $statistics['count_unique_foodsavers']);
        $this->assertEquals(1, $statistics['count_total_empty_pickups']);
        $this->assertEquals(1, $statistics['count_stores_with_notifications']);
    }

    public function testTriggerFetchWarningNotificationOnLeavedConfirmation()
    {
        $region = $this->tester->createRegion();
        $foodsaver1 = $this->tester->createFoodsaver();
        $storeCoordinator = $this->tester->createStoreCoordinator();

        $storeWithRegularPickup = $this->tester->createStore($region['id'], null, null, ['betrieb_status_id' => '5']);

        $this->tester->addStoreTeam($storeWithRegularPickup['id'], $storeCoordinator['id'], true);

        $dayOfPickup = new Carbon();

        $additionalPickupDate = $dayOfPickup->addDay();
        $this->tester->addPickup($storeWithRegularPickup['id'], ['time' => $additionalPickupDate, 'fetchercount' => 10]);

        $statistics = $this->transactions->triggerFetchWarningNotification();
        $this->assertEquals(1, $statistics['count_stores']);
        $this->assertEquals(1, $statistics['count_unique_foodsavers']);
        $this->assertEquals(1, $statistics['count_total_empty_pickups']);
        $this->assertEquals(1, $statistics['count_stores_with_notifications']);

        $this->tester->addCollector($foodsaver1['id'], $storeWithRegularPickup['id'], ['confirmed' => 0, 'date' => $additionalPickupDate]);

        $statistics = $this->transactions->triggerFetchWarningNotification();
        $this->assertEquals(1, $statistics['count_stores']);
        $this->assertEquals(1, $statistics['count_unique_foodsavers']);
        $this->assertEquals(1, $statistics['count_total_empty_pickups']);
        $this->assertEquals(1, $statistics['count_stores_with_notifications']);
    }
}
