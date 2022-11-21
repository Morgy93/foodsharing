<?php

use Faker\Factory;
use Faker\Generator;
use Foodsharing\Modules\Store\DTO\RegularPickup;
use Foodsharing\Modules\Store\RegularPickupGateway;

class RegularPickupGatewayTest extends \Codeception\Test\Unit
{
	protected UnitTester $tester;
	private Generator $faker;
	private RegularPickupGateway $gateway;

	private array $store;
	private array $foodsaver;
	private array $region;

	protected function _before()
	{
		$this->gateway = $this->tester->get(RegularPickupGateway::class);
		$this->region = $this->tester->createRegion();
		$this->store = $this->tester->createStore($this->region['id']);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->faker = Factory::create('de_DE');
	}

	public function testGetRegularPickupSettings()
	{
		$pickup_1 = new RegularPickup();
		$pickup_1->weekday = 4;
		$pickup_1->startTimeOfPickup = '16:40:00';
		$pickup_1->maxCountOfSlots = 2;
		$this->tester->addRecurringPickup($this->store['id'],
			['time' => $pickup_1->startTimeOfPickup, 'dow' => $pickup_1->weekday, 'fetcher' => $pickup_1->maxCountOfSlots]
		);

		$pickup_2 = new RegularPickup();
		$pickup_2->weekday = 3;
		$pickup_2->startTimeOfPickup = '16:50:00';
		$pickup_2->maxCountOfSlots = 4;
		$this->tester->addRecurringPickup($this->store['id'],
			['time' => $pickup_2->startTimeOfPickup, 'dow' => $pickup_2->weekday, 'fetcher' => $pickup_2->maxCountOfSlots]
		);

		$regularSlots = $this->gateway->getRegularPickup($this->store['id']);
		$this->assertEquals([
			$pickup_2,
			$pickup_1
		], $regularSlots);
	}

	public function testUpdateRegularPickupForANewStore()
	{
		$pickup = new RegularPickup();
		$pickup->weekday = 4;
		$pickup->startTimeOfPickup = '16:40:00';
		$pickup->maxCountOfSlots = 2;
		$this->tester->addRecurringPickup($this->store['id'],
			['time' => $pickup->startTimeOfPickup, 'dow' => $pickup->weekday, 'fetcher' => $pickup->maxCountOfSlots]
		);

		$pickup_max_slot_count = new RegularPickup();
		$pickup_max_slot_count->weekday = 4;
		$pickup_max_slot_count->startTimeOfPickup = '16:40:00';
		$pickup_max_slot_count->maxCountOfSlots = 4;

		$this->gateway->insertOrUpdateRegularPickup($this->store['id'], $pickup_max_slot_count);

		$regularSlots = $this->gateway->getRegularPickup($this->store['id']);
		$this->assertEquals([
			$pickup_max_slot_count
		], $regularSlots);
	}

	public function testInsertRegularPickupForANewStore()
	{
		$regularSlots = $this->gateway->getRegularPickup($this->store['id']);
		$this->assertEquals([], $regularSlots);

		$pickup = new RegularPickup();
		$pickup->weekday = 4;
		$pickup->startTimeOfPickup = '16:40:00';
		$pickup->maxCountOfSlots = 2;

		$this->gateway->insertOrUpdateRegularPickup($this->store['id'], $pickup);

		$regularSlots = $this->gateway->getRegularPickup($this->store['id']);
		$this->assertEquals([
			$pickup
		], $regularSlots);
	}

	public function deleteAllRegularPickups($storeId)
	{
		$pickup_1 = new RegularPickup();
		$pickup_1->weekday = 4;
		$pickup_1->startTimeOfPickup = '16:40:00';
		$pickup_1->maxCountOfSlots = 2;
		$this->tester->addRecurringPickup($this->store['id'],
			['time' => $pickup_1->startTimeOfPickup, 'dow' => $pickup_1->weekday, 'fetcher' => $pickup_1->maxCountOfSlots]
		);

		$pickup_2 = new RegularPickup();
		$pickup_2->weekday = 3;
		$pickup_2->startTimeOfPickup = '16:50:00';
		$pickup_2->maxCountOfSlots = 4;
		$this->tester->addRecurringPickup($this->store['id'],
			['time' => $pickup_2->startTimeOfPickup, 'dow' => $pickup_2->weekday, 'fetcher' => $pickup_2->maxCountOfSlots]
		);

		$this->gateway->deleteAllRegularPickups($this->store['id']);

		$regularSlots = $this->gateway->getRegularPickup($this->store['id']);
		$this->assertEquals([], $regularSlots);
	}
}
