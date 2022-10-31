<?php

namespace Foodsharing\api;

use Carbon\Carbon;
use Codeception\Util\HttpCode;

class PickupApiCest
{
	private $user;
	private $store;
	private $store2;
	private $store3;
	private $region;
	private $waiter;

	public function _before(\ApiTester $I)
	{
		$this->user = $I->createFoodsaver();
		$this->storeCoordinator = $I->createStoreCoordinator();
		$this->region = $I->createRegion();
		$this->store = $I->createStore($this->region['id']);
		$I->addStoreTeam($this->store['id'], $this->user['id']);
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);
		$this->waiter = $I->createFoodsaver();
		$I->addStoreTeam($this->store['id'], $this->waiter['id'], false, true);
		$this->store2 = $I->createStore($this->region['id'], null, null, ['use_region_pickup_rule' => 1]);
		$I->addStoreTeam($this->store2['id'], $this->user['id']);
		$this->store3 = $I->createStore($this->region['id'], null, null, ['use_region_pickup_rule' => 1]);
		$I->addStoreTeam($this->store3['id'], $this->user['id']);
		$this->store4 = $I->createStore($this->region['id'], null, null, ['use_region_pickup_rule' => 1]);
		$I->addStoreTeam($this->store4['id'], $this->user['id']);
	}

	public function acceptsDifferentIsoFormats(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$id = $this->user['id'];
		$pickupBaseDate = Carbon::now()->add('2 days');
		$pickupBaseDate->hours(13)->minutes(45)->seconds(0);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s') . '+0000/' . $id);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$pickupBaseDate->minutes(50);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->copy()->setTimezone('+01:00')->format('Y-m-d\TH:i:s') . '.000+01:00/' . $id);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$pickupBaseDate->minutes(55);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->copy()->setTimezone('-01:00')->format('Y-m-d\TH:i:s') . '-01:00/' . $id);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$pickupBaseDate->minutes(35);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s') . 'Z/' . $id);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function signupAsWaiterDoesNotWork(\ApiTester $I)
	{
		$I->login($this->waiter['email']);
		$pickupBaseDate = Carbon::now()->add('2 days');
		$pickupBaseDate->hours(14)->minutes(50)->seconds(0);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->toIso8601String() . '/' . $this->waiter['id']);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
	}

	public function signupReturnsPickupConfirmationState(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$pickupBaseDate = Carbon::now()->add('2 days');
		$pickupBaseDate->hours(14)->minutes(45)->seconds(0);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->toIso8601String() . '/' . $this->user['id']);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->canSeeResponseContainsJson([
			'isConfirmed' => false
		]);
	}

	public function signupAsCoordinarIsPreconfirmed(\ApiTester $I)
	{
		$pickupBaseDate = Carbon::now()->add('2 days');
		$pickupBaseDate->hours(16)->minutes(45)->seconds(0);
		$coordinator = $I->createStoreCoordinator();
		$I->addStoreTeam($this->store['id'], $coordinator['id'], true, false, true);
		$I->login($coordinator['email']);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->toIso8601String() . '/' . $coordinator['id']);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->canSeeResponseContainsJson([
			'isConfirmed' => true
		]);
	}

	public function AsWaiterICannotSeePickups(\ApiTester $I)
	{
		$pickupBaseDate = Carbon::now()->add('2 days');
		$pickupBaseDate->hours(16)->minutes(55)->seconds(0);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->login($this->waiter['email']);
		$I->sendGET('api/stores/' . $this->store['id'] . '/pickups');
		$I->seeResponseCodeIs(HttpCode::FORBIDDEN);
	}

	public function testSinglePickupInListExistsAndIsValid(\ApiTester $I)
	{
		$pickupBaseDate = Carbon::now()->add('2 days');
		$pickupBaseDate->hours(16)->minutes(55)->seconds(0);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->login($this->user['email']);
		$I->sendGET('api/stores/' . $this->store['id'] . '/pickups');
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->canSeeResponseContainsJson([
			'pickups' => [[
			'date' => $pickupBaseDate->toIso8601String(),
			'totalSlots' => 2,
			'occupiedSlots' => [],
			'isAvailable' => true
			]]
		]);
	}

	public function testSinglePickupHistoryInListExistsAndIsValid(\ApiTester $I)
	{
		$refDate = Carbon::now()->subYears(3)->subHours(8);
		$I->haveInDatabase('fs_abholer', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $this->storeCoordinator['id'],
			'date' => $refDate
		]);

		$e = $I->grabFromDatabase('fs_abholer', 'date', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $this->storeCoordinator['id']
		]);

		$startDate = $refDate->copy()->subYears(2);
		$endDate = $refDate->copy()->addYears(2);

		$I->login($this->storeCoordinator['email']);
		$I->sendGET('api/stores/' . $this->store['id'] . '/history/' . $startDate->toIso8601String() . '/' . $endDate->toIso8601String());
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->canSeeResponseContainsJson([
			'pickups' => [
				[
					'occupiedSlots' => [
						[
							'profile' => [
								'id' => $this->storeCoordinator['id']
							],
							'date' => $refDate->toIso8601String(),
							'date_ts' => $refDate->timestamp,
							'confirmed' => 0
						]
					]
			]]
		]);
	}

	public function cannotSignOutOfPastPickup(\ApiTester $I)
	{
		$pickupBaseDate = Carbon::now()->sub('2 days');
		$pickupBaseDate->hours(14)->minutes(45)->seconds(0);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->addPicker($this->store['id'], $this->user['id'], ['date' => $pickupBaseDate]);

		$I->login($this->user['email']);
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->sendDELETE('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->toIso8601String() . '/' . $this->user['id']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
	}

	public function canSignOutOfPickupWithMessage(\ApiTester $I)
	{
		$pickupBaseDate = Carbon::now()->add('2 days');
		$pickupBaseDate->hours(14)->minutes(45)->seconds(0);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->addPicker($this->store['id'], $this->user['id'], ['date' => $pickupBaseDate]);

		$I->login($this->storeCoordinator['email']);
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->sendDELETE('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->toIso8601String() . '/' . $this->user['id'], ['sendKickMessage' => true, 'message' => 'Hallo']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
	}

	public function checkDistrictRules(\ApiTester $I)
	{
		/*
			  Create PickupRule

			7 days, max pickup 3 overall, max 2 per day, rule ignored 48 hours before pickup
		*/
		$I->createDistrictPickupRule((int)$this->region['id'], '7', '3', '2', '48');
		$I->login($this->user['email']);

		// Test for maximum 3 pickups in 7 days over multiple stores.
		$pickupBaseDate = Carbon::now()->add('3 days');
		$pickupBaseDate->hours(10)->minutes(00)->seconds(0);
		$I->addPickup($this->store2['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->addPicker($this->store2['id'], $this->user['id'], ['date' => $pickupBaseDate]);

		$pickupBaseDate = Carbon::now()->add('4 days');
		$pickupBaseDate->hours(11)->minutes(00)->seconds(0);
		$I->addPickup($this->store3['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->addPicker($this->store3['id'], $this->user['id'], ['date' => $pickupBaseDate]);

		$pickupBaseDate = Carbon::now()->add('5 days');
		$pickupBaseDate->hours(11)->minutes(00)->seconds(0);
		$I->addPickup($this->store4['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);

		// This signup is ok because it is the third one
		$I->sendGET('api/stores/' . $this->store4['id'] . '/pickupRuleCheck/' . $pickupBaseDate->toIso8601String() . '/' . $this->user['id']);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->canSeeResponseContainsJson([
			'result' => true
		]);

		// this signup breaks the rule as it is the fourth one
		$I->addPicker($this->store4['id'], $this->user['id'], ['date' => $pickupBaseDate]);

		$pickupBaseDate = Carbon::now()->add('6 days');
		$pickupBaseDate->hours(11)->minutes(00)->seconds(0);
		$I->addPickup($this->store4['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendGET('api/stores/' . $this->store4['id'] . '/pickupRuleCheck/' . $pickupBaseDate->toIso8601String() . '/' . $this->user['id']);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->canSeeResponseContainsJson([
			'result' => false
		]);

		// Test for third signups on the same day over multiple stores
		$pickupBaseDate = Carbon::now()->add('20 days');
		$pickupBaseDate->hours(10)->minutes(00)->seconds(0);
		$I->addPickup($this->store2['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->addPicker($this->store2['id'], $this->user['id'], ['date' => $pickupBaseDate]);

		$pickupBaseDate = Carbon::now()->add('20 days');
		$pickupBaseDate->hours(11)->minutes(00)->seconds(0);
		$I->addPickup($this->store3['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->addPicker($this->store3['id'], $this->user['id'], ['date' => $pickupBaseDate]);

		$pickupBaseDate = Carbon::now()->add('20 days');
		$pickupBaseDate->hours(12)->minutes(00)->seconds(0);
		$I->addPickup($this->store4['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendGET('api/stores/' . $this->store4['id'] . '/pickupRuleCheck/' . $pickupBaseDate->toIso8601String() . '/' . $this->user['id']);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->canSeeResponseContainsJson([
			'result' => false
		]);

		// test for ignoring of the rule if signup date is closer then ignorerulehours
		$pickupBaseDate = Carbon::now()->addDay();
		$pickupBaseDate->hours(10)->minutes(00)->seconds(0);
		$I->addPickup($this->store2['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->addPicker($this->store2['id'], $this->user['id'], ['date' => $pickupBaseDate]);

		$pickupBaseDate = Carbon::now()->addDay();
		$pickupBaseDate->hours(11)->minutes(00)->seconds(0);
		$I->addPickup($this->store4['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->addPicker($this->store4['id'], $this->user['id'], ['date' => $pickupBaseDate]);

		$pickupBaseDate = Carbon::now()->addDay();
		$pickupBaseDate->hours(12)->minutes(00)->seconds(0);
		$I->addPickup($this->store4['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendGET('api/stores/' . $this->store4['id'] . '/pickupRuleCheck/' . $pickupBaseDate->toIso8601String() . '/' . $this->user['id']);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->canSeeResponseContainsJson([
			'result' => true
		]);
	}
}
