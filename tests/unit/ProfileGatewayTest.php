<?php

use Carbon\Carbon;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Modules\Profile\ProfileGateway;

class ProfileGatewayTest extends \Codeception\Test\Unit
{
	protected UnitTester $tester;
	private ProfileGateway $profileGateway;
	private $foodsaver;
	private $store;
	private $region;

	protected function _before()
	{
		$this->profileGateway = $this->tester->get(ProfileGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->region = $this->tester->createRegion();
		$this->store = $this->tester->createStore($this->region['id']);
		$pickupBaseDate = Carbon::now();
		$pickupBaseDate->hours(14)->minutes(45)->seconds(0);

		$date_act = Carbon::now();
		$date_act->hours(10)->minutes(45)->seconds(0);

		$date_ref = Carbon::now();
		$date_ref->hours(13)->minutes(45)->seconds(0);

		$this->tester->addPicker($this->store['id'], $this->foodsaver['id'], ['date' => $pickupBaseDate]);
		$this->tester->addStoreLog($this->store['id'], $this->foodsaver['id'], $this->foodsaver['id'], StoreLogAction::SIGN_UP_SLOT, ['date_reference' => $date_ref, 'date_activity' => $date_act]);
	}

	public function testgetSecuredPickupsCount()
	{
		$count = $this->profileGateway->getSecuredPickupsCount($this->foodsaver['id'], 0);
		$this->assertEquals(1, $count);
	}
}
