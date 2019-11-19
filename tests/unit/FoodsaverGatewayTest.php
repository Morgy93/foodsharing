<?php

class FoodsaverGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Foodsaver\FoodsaverGateway
	 */
	private $gateway;
	private $foodsharer;
	private $foodsaver;
	private $region;
	private $regionMember;
	private $regionAdmin;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Foodsaver\FoodsaverGateway::class);

		$this->foodsharer = $this->tester->createFoodsharer();
		$this->foodsaver = $this->tester->createFoodsaver(null, ['newsletter' => 1]);

		$this->region = $this->tester->createRegion('TestRegion');
		$this->regionMember = $this->tester->createFoodsaver();
		$this->tester->addBezirkMember($this->region['id'], $this->regionMember['id']);
		$this->regionAdmin = $this->tester->createAmbassador();
		$this->tester->addBezirkMember($this->region['id'], $this->regionAdmin['id']);
		$this->tester->addBezirkAdmin($this->region['id'], $this->regionAdmin['id']);
	}

	public function testUpdateProfile()
	{
		$this->gateway->updateProfile($this->foodsaver['id'], ['anschrift' => 'my new street']);
		$this->tester->seeInDatabase('fs_foodsaver', [
			'id' => $this->foodsaver['id'],
			'anschrift' => 'my new street'
		]);

		// strips tags
		$this->gateway->updateProfile($this->foodsaver['id'], ['anschrift' => '<script>my new street']);
		$this->tester->seeInDatabase('fs_foodsaver', [
			'id' => $this->foodsaver['id'],
			'anschrift' => 'my new street'
		]);
	}

	public function testGetPhoto()
	{
		$this->gateway->updatePhoto($this->foodsaver['id'], 'mypicture.png');
		$this->tester->assertEquals(
			$this->gateway->getPhoto($this->foodsaver['id']),
			'mypicture.png'
		);
	}

	public function testUpdateGroupMembersByAdditionWhileLeavingAdmin()
	{
		$regionId = $this->region['id'];
		$newFoodsaver = $this->tester->createFoodsaver();

		$fsIds = [$newFoodsaver['id'], $this->regionMember['id']];
		$updates = $this->gateway->updateGroupMembers($regionId, $fsIds, true);

		$this->tester->seeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $newFoodsaver['id'], 'bezirk_id' => $regionId]);
		$this->tester->seeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $this->regionAdmin['id'], 'bezirk_id' => $regionId]);
		$this->tester->assertEquals(1, $updates['inserts'], 'Wrong number of inserts for!');
		$this->tester->assertEquals(0, $updates['deletions'], 'Wrong number of deletions!');
	}

	public function testUpdateGroupMembersByAdditionWhileNotLeavingAdmin()
	{
		$regionId = $this->region['id'];
		$newFoodsaver = $this->tester->createFoodsaver();

		$fsIds = [$newFoodsaver['id'], $this->regionMember['id']];
		$updates = $this->gateway->updateGroupMembers($regionId, $fsIds, false);

		$this->tester->seeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $newFoodsaver['id'], 'bezirk_id' => $regionId]);
		$this->tester->dontSeeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $this->regionAdmin['id'], 'bezirk_id' => $regionId]);
		$this->tester->assertEquals(1, $updates['inserts'], 'Wrong number of inserts!');
		$this->tester->assertEquals(1, $updates['deletions'], 'Wrong number of deletions!');
	}

	public function testUpdateGroupMembersByDeletionWhileLeavingAdmin()
	{
		$regionId = $this->region['id'];

		$updates = $this->gateway->updateGroupMembers($regionId, [], true);

		$this->tester->dontSeeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $this->regionMember['id'], 'bezirk_id' => $regionId]);
		$this->tester->seeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $this->regionAdmin['id'], 'bezirk_id' => $regionId]);
		$this->tester->assertEquals(0, $updates['inserts'], 'Wrong number of inserts!');
		$this->tester->assertEquals(1, $updates['deletions'], 'Wrong number of deletions!');
	}

	public function testUpdateGroupMembersByDeletionWhileNotLeavingAdmin()
	{
		$regionId = $this->region['id'];

		$updates = $this->gateway->updateGroupMembers($regionId, [], false);

		$this->tester->dontSeeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $this->regionMember['id'], 'bezirk_id' => $regionId]);
		$this->tester->dontSeeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $this->regionAdmin['id'], 'bezirk_id' => $regionId]);
		$this->tester->assertEquals(0, $updates['inserts'], 'Wrong number of inserts!');
		$this->tester->assertEquals(2, $updates['deletions'], 'Wrong number of deletions!');
	}

	public function testGetRegionBotsEmailList()
	{
		$regions = [0 => $this->region['id'], 1 => 0];

		$emails = $this->gateway->getEmailBotFromBezirkList($regions);

		$this->tester->assertIsArray($emails);
		$this->tester->assertCount(1, $emails);
		$bot = $this->regionAdmin;
		$this->tester->assertArrayHasKey($bot['id'], $emails);
		$this->tester->assertEquals($bot['email'], $emails[$bot['id']]['email']);
	}

	public function testGetRegionFoodsaversEmailList()
	{
		$regions = [0 => $this->region['id'], 1 => 0];

		$emails = $this->gateway->getEmailFoodSaverFromBezirkList($regions);

		$this->tester->assertIsArray($emails);
		$this->tester->assertCount(2, $emails);
		$bot = $this->regionAdmin;
		$this->tester->assertArrayHasKey($bot['id'], $emails);
		$this->tester->assertEquals($bot['email'], $emails[$bot['id']]['email']);
		$fs = $this->regionMember;
		$this->tester->assertArrayHasKey($fs['id'], $emails);
		$this->tester->assertEquals($fs['email'], $emails[$fs['id']]['email']);
	}

	public function testGetAllEmailFromFoodsaversIgnoringNewsletters()
	{
		$foodsavers = [$this->foodsharer, $this->foodsaver, $this->regionAdmin, $this->regionMember];
		$expectedResult = [];
		foreach ($foodsavers as $fs) {
			$expectedResult[] = serialize(['id' => $fs['id'], 'email' => $fs['email']]);
		}

		$emails = $this->gateway->getAllEmailFoodsaver(false, false);

		$this->tester->assertCount(count($expectedResult), $emails);
		$result = $this->serializeEmails($emails);
		$intersection = array_intersect($expectedResult, $result);
		$this->tester->assertCount(count($foodsavers), $intersection, 'Result does not match expactations: ' . serialize($result));
	}

	public function testGetAllEmailFromFoodsaversWithNewsletters()
	{
		$foodsavers = [$this->foodsaver];
		$expectedResult = [];
		foreach ($foodsavers as $fs) {
			$expectedResult[] = serialize(['id' => $fs['id'], 'email' => $fs['email']]);
		}

		$emails = $this->gateway->getAllEmailFoodsaver(true, false);

		$this->tester->assertCount(count($expectedResult), $emails);
		$result = $this->serializeEmails($emails);
		$intersection = array_intersect($expectedResult, $result);
		$this->tester->assertCount(count($foodsavers), $intersection, 'Result does not match expactations: ' . serialize($result));
	}

	public function testGetAllEmailFromOnlyFoodsavers()
	{
		$foodsavers = [$this->foodsaver, $this->regionAdmin, $this->regionMember];
		$expectedResult = [];
		foreach ($foodsavers as $fs) {
			$expectedResult[] = serialize(['id' => $fs['id'], 'email' => $fs['email']]);
		}

		$emails = $this->gateway->getAllEmailFoodsaver();

		$this->tester->assertCount(count($expectedResult), $emails);
		$result = $this->serializeEmails($emails);
		$intersection = array_intersect($expectedResult, $result);
		$this->tester->assertCount(count($foodsavers), $intersection, 'Result does not match expactations: ' . serialize($result));
	}

	private function serializeEmails(array $email): array
	{
		$out = [];
		foreach ($email as $e) {
			$out[] = serialize($e);
		}

		return $out;
	}
}
