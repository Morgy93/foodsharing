<?php

namespace Foodsharing\api;

class PickupRegularManagementApiCest
{
	private $user;
	private $store;
	private $region;

	public function _before(\ApiTester $I)
	{
		$this->user1 = $I->createFoodsaver();
		$this->user2 = $I->createFoodsaver();
		$this->userNoMember = $I->createFoodsaver();
		$this->coordinator = $I->createStoreCoordinator();
		$this->region = $I->createRegion();
		$this->store = $I->createStore($this->region['id']);
		$I->addStoreTeam($this->store['id'], $this->coordinator['id'], true);
		$I->addStoreTeam($this->store['id'], $this->user1['id'], false);
		$I->addStoreTeam($this->store['id'], $this->user2['id'], false);
	}

	public function listRegularPickupsAccessDeniedForNonStoreMember(\ApiTester $I)
	{
		$date = '2018-07-18';
		$time = '16:40:00';
		$datetime = $date . ' ' . $time;
		$dow = 3; /* above date is a wednesday */
		$fetcher = 2;

		$I->addRecurringPickup($this->store['id'],
			['time' => $time, 'dow' => $dow, 'fetcher' => $fetcher]
		);

		$I->addRecurringPickup($this->store['id'],
			['time' => $time, 'dow' => 4, 'fetcher' => $fetcher]
		);
		$I->login($this->userNoMember['email']);
		$I->sendGET('/api/stores/' . $this->store['id'] . '/regularPickup');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
	}

	public function listRegularPickupsAccessByStoreMemberReturnRegularPickups(\ApiTester $I)
	{
		$date = '2018-07-18';
		$time = '16:40:00';
		$datetime = $date . ' ' . $time;
		$dow = 3; /* above date is a wednesday */
		$fetcher = 2;

		$I->addRecurringPickup($this->store['id'],
			['time' => $time, 'dow' => $dow, 'fetcher' => $fetcher]
		);

		$I->addRecurringPickup($this->store['id'],
			['time' => $time, 'dow' => 4, 'fetcher' => $fetcher]
		);
		$I->login($this->user1['email']);
		$I->sendGET('/api/stores/' . $this->store['id'] . '/regularPickup');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson([
			['startTimeOfPickup' => $time, 'weekday' => 3, 'maxCountOfSlots' => $fetcher],
			['startTimeOfPickup' => $time, 'weekday' => 4, 'maxCountOfSlots' => $fetcher]
		]);
	}

	public function listRegularPickups(\ApiTester $I)
	{
		$date = '2018-07-18';
		$time = '16:40:00';
		$datetime = $date . ' ' . $time;
		$dow = 3; /* above date is a wednesday */
		$fetcher = 2;

		$I->addRecurringPickup($this->store['id'],
			['time' => $time, 'dow' => $dow, 'fetcher' => $fetcher]
		);

		$I->addRecurringPickup($this->store['id'],
			['time' => $time, 'dow' => 4, 'fetcher' => $fetcher]
		);
		$I->login($this->coordinator['email']);
		$I->sendGET('/api/stores/' . $this->store['id'] . '/regularPickup');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson([
			['startTimeOfPickup' => $time, 'weekday' => 3, 'maxCountOfSlots' => $fetcher],
			['startTimeOfPickup' => $time, 'weekday' => 4, 'maxCountOfSlots' => $fetcher]
		]);
	}

	public function putRegularPickupsAccessDeniedForNonStoreMember(\ApiTester $I)
	{
		$date = '2018-07-18';
		$time = '16:40:00';
		$datetime = $date . ' ' . $time;
		$dow = 3; /* above date is a wednesday */
		$fetcher = 2;

		$I->login($this->userNoMember['email']);
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->sendPUT('/api/stores/' . $this->store['id'] . '/regularPickup', [
			['startTimeOfPickup' => $time, 'weekday' => 3, 'maxCountOfSlots' => $fetcher],
			['startTimeOfPickup' => $time, 'weekday' => 4, 'maxCountOfSlots' => $fetcher]
		]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
	}

	public function putRegularPickupsAccessDeniedForStoreMember(\ApiTester $I)
	{
		$date = '2018-07-18';
		$time = '16:40:00';
		$datetime = $date . ' ' . $time;
		$dow = 3; /* above date is a wednesday */
		$fetcher = 2;

		$I->login($this->userNoMember['email']);
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->sendPUT('/api/stores/' . $this->store['id'] . '/regularPickup', [
			['startTimeOfPickup' => $time, 'weekday' => 3, 'maxCountOfSlots' => $fetcher],
			['startTimeOfPickup' => $time, 'weekday' => 4, 'maxCountOfSlots' => $fetcher]
		]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
	}

	public function putRegularPickupsBadRequest(\ApiTester $I)
	{
		$time = '16:40:00';
		$dow = 3; /* above date is a wednesday */
		$fetcher = -1;

		$I->login($this->coordinator['email']);
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->sendPUT('/api/stores/' . $this->store['id'] . '/regularPickup', [
			['startTimeOfPickup' => $time, 'weekday' => 3, 'maxCountOfSlots' => $fetcher],
			['startTimeOfPickup' => $time, 'weekday' => 4, 'maxCountOfSlots' => $fetcher]
		]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
	}

	public function putRegularPickupsBadRequestInvalidTimestamp(\ApiTester $I)
	{
		$time = 'abc:40:00';
		$dow = 3; /* above date is a wednesday */
		$fetcher = -1;

		$I->login($this->coordinator['email']);
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->sendPUT('/api/stores/' . $this->store['id'] . '/regularPickup', [
			['startTimeOfPickup' => $time, 'weekday' => 3, 'maxCountOfSlots' => $fetcher],
			['startTimeOfPickup' => $time, 'weekday' => 4, 'maxCountOfSlots' => $fetcher]
		]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
	}

	public function putRegularPickupsBadRequestInvalidTimestamp2(\ApiTester $I)
	{
		$time = '16:40:ab';
		$dow = 3; /* above date is a wednesday */
		$fetcher = -1;

		$I->login($this->coordinator['email']);
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->sendPUT('/api/stores/' . $this->store['id'] . '/regularPickup', [
			['startTimeOfPickup' => $time, 'weekday' => 3, 'maxCountOfSlots' => $fetcher],
			['startTimeOfPickup' => $time, 'weekday' => 4, 'maxCountOfSlots' => $fetcher]
		]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
	}

	public function putRegularPickupsAsCoordinator(\ApiTester $I)
	{
		$date = '2018-07-18';
		$time = '16:40:00';
		$datetime = $date . ' ' . $time;
		$dow = 3; /* above date is a wednesday */
		$fetcher = 2;

		$I->login($this->coordinator['email']);
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->sendPUT('/api/stores/' . $this->store['id'] . '/regularPickup', [
			['startTimeOfPickup' => $time, 'weekday' => 3, 'maxCountOfSlots' => $fetcher],
			['startTimeOfPickup' => $time, 'weekday' => 4, 'maxCountOfSlots' => $fetcher]
		]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();

		$I->seeInDatabase('fs_abholzeiten', [
			'time' => $time,
			'dow' => 3,
			'fetcher' => $fetcher,
			'betrieb_id' => $this->store['id']
		]);

		$I->seeInDatabase('fs_abholzeiten', [
			'time' => $time,
			'dow' => 4,
			'fetcher' => $fetcher,
			'betrieb_id' => $this->store['id']
		]);
	}
}
