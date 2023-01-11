<?php

use Codeception\Test\Unit;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Modules\Store\StoreGateway;

class StoreGatewayLogTest extends Unit
{
    protected UnitTester $tester;
    private StoreGateway $gateway;

    private array $store;
    private array $foodsaver1;
    private array $foodsaver2;
    private array $region;

    protected function _before(): void
    {
        $this->gateway = $this->tester->get(StoreGateway::class);
        $this->region = $this->tester->createRegion();
        $this->store = $this->tester->createStore($this->region['id']);
        $this->foodsaver1 = $this->tester->createFoodsaver();
        $this->foodsaver2 = $this->tester->createFoodsaver();
    }

    public function testDatabaseTimezoneIsBerlin(): void
    {
        $referenceData = DateTime::createFromFormat('Y-m-d H:i:s', '2022-01-11 10:01:11', new \DateTimeZone('UTC'));

        $this->gateway->addStoreLog($this->store['id'], $this->foodsaver1['id'], $this->foodsaver2['id'], $referenceData, StoreLogAction::LEFT_STORE);

        $this->tester->seeInDatabase('fs_store_log', [
            'store_id' => $this->store['id'],
            'fs_id_a' => $this->foodsaver1['id'],
            'fs_id_p' => $this->foodsaver2['id'],
            'action' => StoreLogAction::LEFT_STORE->value,
            'date_reference' => '2022-01-11 11:01:11'
        ]);
    }
}
