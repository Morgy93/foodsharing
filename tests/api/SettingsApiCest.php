<?php

namespace Foodsharing\api;

use ApiTester;
use Carbon\Carbon;
use Codeception\Example;
use Codeception\Util\HttpCode;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\SleepStatus;

class SettingsApiCest
{
	private $user;

	public function _before(ApiTester $I)
	{
		$this->user = $I->createFoodsaver();
	}

	public function canOnlySetSleepStatusWhenLoggedIn(ApiTester $I)
	{
		$I->sendPATCH('api/user/sleepmode', ['mode' => SleepStatus::NONE]);
		$I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
		$I->seeInDatabase('fs_foodsaver', [
			'id' => $this->user['id'],
			'sleep_status' => SleepStatus::NONE
		]);
	}

	public function canSetSleepStatus(ApiTester $I)
	{
		// full sleep mode
		$I->login($this->user['email']);
		$I->sendPATCH('api/user/sleepmode', ['mode' => SleepStatus::FULL]);
		$I->seeResponseCodeIs(HttpCode::NO_CONTENT);
		$I->seeInDatabase('fs_foodsaver', [
			'id' => $this->user['id'],
			'sleep_status' => SleepStatus::FULL
		]);

		// temporary sleep mode
		$I->login($this->user['email']);
		$I->sendPATCH('api/user/sleepmode', [
			'mode' => SleepStatus::TEMP,
			'from' => Carbon::today()->addDay()->format('d.m.Y'),
			'to' => Carbon::today()->addWeek()->format('d.m.Y')
		]);
		$I->seeResponseCodeIs(HttpCode::NO_CONTENT);
		$I->seeInDatabase('fs_foodsaver', [
			'id' => $this->user['id'],
			'sleep_status' => SleepStatus::TEMP
		]);

		// no sleeping
		$I->login($this->user['email']);
		$I->sendPATCH('api/user/sleepmode', ['mode' => SleepStatus::NONE]);
		$I->seeResponseCodeIs(HttpCode::NO_CONTENT);
		$I->seeInDatabase('fs_foodsaver', [
			'id' => $this->user['id'],
			'sleep_status' => SleepStatus::NONE
		]);
	}

	public function cannotSetTemporarySleepStatusWithoutLimits(ApiTester $I)
	{
		$I->updateInDatabase('fs_foodsaver', ['sleep_status' => SleepStatus::NONE], ['id' => $this->user['id']]);

		// without 'from'
		$I->login($this->user['email']);
		$I->sendPATCH('api/user/sleepmode', [
			'mode' => SleepStatus::TEMP,
			'to' => Carbon::today()->addWeek()->format('d.m.Y')
		]);
		$I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
		$I->seeInDatabase('fs_foodsaver', [
			'id' => $this->user['id'],
			'sleep_status' => SleepStatus::NONE
		]);

		// without 'to'
		$I->login($this->user['email']);
		$I->sendPATCH('api/user/sleepmode', [
			'mode' => SleepStatus::TEMP,
			'from' => Carbon::today()->addDay()->format('d.m.Y'),
		]);
		$I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
		$I->seeInDatabase('fs_foodsaver', [
			'id' => $this->user['id'],
			'sleep_status' => SleepStatus::NONE
		]);
	}

	public function cannotUseInvalidSleepStatusDates(ApiTester $I)
	{
		$I->updateInDatabase('fs_foodsaver', ['sleep_status' => SleepStatus::NONE], ['id' => $this->user['id']]);

		$I->login($this->user['email']);
		$I->sendPATCH('api/user/sleepmode', [
			'mode' => SleepStatus::TEMP,
			'from' => 'abcdefg',
			'to' => Carbon::today()->addWeek()->format('d.m.Y')
		]);
		$I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
		$I->seeInDatabase('fs_foodsaver', [
			'id' => $this->user['id'],
			'sleep_status' => SleepStatus::NONE
		]);
	}

	/**
	 * @example [-1]
	 * @example [5]
	 * @example [100]
	 * @example [null]
	 * @example ["abc"]
	 * @example [""]
	 */
	public function cannotUseInvalidStatus(ApiTester $I, Example $example)
	{
		$I->updateInDatabase('fs_foodsaver', ['sleep_status' => SleepStatus::NONE], ['id' => $this->user['id']]);

		$I->login($this->user['email']);
		$I->sendPATCH('api/user/sleepmode', ['mode' => $example[0]]);
		$I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
		$I->seeInDatabase('fs_foodsaver', [
			'id' => $this->user['id'],
			'sleep_status' => SleepStatus::NONE
		]);
	}
}
