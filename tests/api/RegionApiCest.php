<?php

use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;

class RegionApiCest
{
	private $user;
	private $userOrga;
	private $region;

	public function _before(ApiTester $I)
	{
		$this->tester = $I;
		$this->user = $I->createFoodsaver();
		$this->userOrga = $I->createOrga();
		$this->region = $I->createRegion();
	}

	public function canJoinRegion(ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->dontSeeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $this->user['id'],
			'bezirk_id' => $this->region['id']
			]);
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->seeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $this->user['id'],
			'bezirk_id' => $this->region['id']
			]);
	}

	public function joinNotExistingRegionIs404(ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/999999999/join');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
		$I->seeResponseIsJson();
		$I->dontSeeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $this->user['id'],
			'bezirk_id' => 999999999
			]);
	}

	public function canNotJoinRegionAsFoodsharer(ApiTester $I)
	{
		$foodsharer = $I->createFoodsharer();
		$I->login($foodsharer['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
		$I->seeResponseIsJson();
		$I->dontSeeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $this->user['id'],
			'bezirk_id' => $this->region['id']
			]);
	}

	public function canJoinRegionTwice(ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		// database entry is not interesting, already tested that in other test
	}

	public function canNotLeaveRegionWithoutLogin(ApiTester $I)
	{
		$I->sendPOST('api/region/' . $this->region['id'] . '/leave');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
		$I->seeResponseIsJson();
		// cannot test whether leaving did not change database since
		// there is no user to look at
	}

	public function canLeaveRegionWithoutJoiningFirst(ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/leave');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->dontSeeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $this->user['id'],
			'bezirk_id' => $this->region['id']
			]);
	}

	public function canLeaveRegion(ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

		$I->login($this->user['email']);
		// second login necessary since the list of regions of the current
		// user are saved in the session and not updated there by the
		// join request. So without relogin the leave would fail.
		$I->sendPOST('api/region/' . $this->region['id'] . '/leave');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->dontSeeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $this->user['id'],
			'bezirk_id' => $this->region['id']
			]);
	}

	public function canNotLeaveDifferentRegionThanJoined(ApiTester $I)
	{
		$region2 = $I->createRegion();

		$I->login($this->user['email']);
		$I->addRegionMember($this->region['id'], $this->user['id'], true);
		$I->sendPOST('api/region/' . $region2['id'] . '/leave');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->seeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $this->user['id'],
			'bezirk_id' => $this->region['id']
		]);
	}

	public function canNotLeaveNonExistingRegion(ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/999999999/leave');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
		$I->seeResponseIsJson();
	}

	public function canNotLeaveRootRegion(ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/' . RegionIDs::ROOT . '/leave');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
		$I->seeResponseIsJson();
	}

	public function canNotLeaveRegionIfActiveStoreManager(ApiTester $I)
	{
		$store = $I->createStore($this->region['id']);
		$coordinator = $I->createStoreCoordinator();
		$I->addRegionMember($this->region['id'], $coordinator['id'], true);
		$I->addStoreTeam($store['id'], $coordinator['id'], true, false, true);

		$I->login($coordinator['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/leave');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::CONFLICT);
	}

	public function canOnlyListRegionMembersAsMember(ApiTester $I)
	{
		// test before being a member
		$I->login($this->user['email']);
		$I->sendGET('api/region/' . $this->region['id'] . '/members');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);

		// test when being a member
		$I->addRegionMember($this->region['id'], $this->user['id']);
		$I->login($this->user['email']); // relogin needed to initialise the session
		$I->sendGET('api/region/' . $this->region['id'] . '/members');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->user['id']]);
	}

	public function canListRegionMembersAsOrga(ApiTester $I)
	{
		$I->login($this->userOrga['email']);
		$I->sendGET('api/region/' . $this->region['id'] . '/members');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}
}
