<?php

use Foodsharing\Modules\Region\UnitGateway;

class UnitGatewayTest extends \Codeception\Test\Unit
{
	protected UnitTester $tester;
	private UnitGateway $gateway;
	private array $foodsaver;
	private array $otherFoodsaver;
	private array $foodSharePoint;
	private array $region;
	private array $childRegion;
	private array $childChildRegion;
	private array $unrelatedRegion;

	protected function _before()
	{
		$this->gateway = $this->tester->get(UnitGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->region = $this->tester->createRegion('God');
		$this->tester->addRegionMember($this->region['id'], $this->foodsaver['id']);
		$this->childRegion = $this->tester->createRegion('Jesus', ['parent_id' => $this->region['id']]);
		$this->childChildRegion = $this->tester->createRegion('Human', ['parent_id' => $this->childRegion['id']]);
		$this->unrelatedRegion = $this->tester->createRegion('Something else');
	}

	public function testGetAllRegions()
	{
		$regions = $this->gateway->listIdsForFoodsaverWithDescendants($this->foodsaver['id']);
		$this->assertEquals(3, count($regions));
		$this->assertEquals([$this->region['id'], $this->childRegion['id'], $this->childChildRegion['id']], $regions);
	}

	public function testGetRegions()
	{
		$regions = $this->gateway->listForFoodsaver($this->foodsaver['id']);
		$this->assertEquals(1, count($regions));
		$this->assertEquals([$this->region['id']], array_keys($regions));
		$this->assertEquals([
			'id' => $this->region['id'],
			'name' => $this->region['name'],
			'type' => $this->region['type'],
			'parent_id' => $this->region['parent_id'],
		], $regions[$this->region['id']]);
	}
}
