<?php

namespace Foodsharing\api;

class GroupApiCest
{
	private $region;

	public function _before(\ApiTester $I)
	{
		$this->region = $I->createRegion();
	}

	public function deleteGroupFailsForAmbassador(\ApiTester $I)
	{
		$ambassador = $I->createAmbassador();
		$I->login($ambassador['email']);
		$I->sendDELETE("api/groups/{$this->region['id']}");
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
		$I->seeResponseIsJson();
		$I->seeInDatabase('fs_bezirk', ['id' => $this->region['id']]);
	}

	public function deleteGroupWorksForOrga(\ApiTester $I)
	{
		$orga = $I->createOrga();
		$I->login($orga['email']);
		$I->sendDELETE("api/groups/{$this->region['id']}");
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->dontSeeInDatabase('fs_bezirk', ['id' => $this->region['id']]);
	}

	public function listOwnGroups(\ApiTester $I)
	{
		$user = $I->createFoodsaver();

		// Group
		$group = $I->createWorkingGroup('Unit test AG');
		$I->addRegionMember($group['id'], $user['id']);
		$I->addRegionAdmin($group['id'], $user['id']);

		$group2 = $I->createWorkingGroup('Teaching AG');
		$I->addRegionMember($group2['id'], $user['id']);

		$I->login($user['email']);
		$I->sendGET('api/user/current/groups');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();

		$responseItems = $I->grabDataFromResponseByJsonPath('$[*].id');
		$I->assertEquals(2, count($responseItems));
		$I->assertEquals($group['id'], $responseItems[0]);
		$I->assertEquals($group2['id'], $responseItems[1]);

		$responseItems = $I->grabDataFromResponseByJsonPath('$[*].name');
		$I->assertEquals(2, count($responseItems));
		$I->assertEquals($group['name'], $responseItems[0]);
		$I->assertEquals($group2['name'], $responseItems[1]);

		$responseItems = $I->grabDataFromResponseByJsonPath('$[*].isResponsible');
		$I->assertEquals(2, count($responseItems));
		$I->assertTrue($responseItems[0]);
		$I->assertFalse($responseItems[1]);
	}
}
