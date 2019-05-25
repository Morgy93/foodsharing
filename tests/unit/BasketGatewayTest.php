<?php

class BasketGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Basket\BasketGateway
	 */
	private $gateway;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Basket\BasketGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->basketsIds = [];
		foreach (range(1, 10) as $num) {
			$basketId = $this->tester->haveInDatabase('fs_basket', [
				'foodsaver_id' => $this->foodsaver['id']
			]);
			$this->tester->haveInDatabase('fs_basket_anfrage', [
				'basket_id' => $basketId,
				'foodsaver_id' => $this->foodsaver['id'],
				'status' => 0
			]);
			$this->basketIds[] = $basketId;
		}
	}

	protected function _after()
	{
	}

	public function testGetUpdateCount()
	{
		$this->assertEquals(10, $this->gateway->getUpdateCount($this->foodsaver['id']));
	}

	public function testGetBasket()
	{
		//existing basket
		$result = $this->gateway->getBasket($this->basketIds[0]);
		$this->assertIsArray($result);

		//non-existing basket
		$this->assertEquals(false, $this->gateway->getBasket(99999));
	}
}
