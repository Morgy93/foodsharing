<?php

declare(strict_types=1);

namespace Tests\Unit;

use Codeception\Test\Unit;
use Foodsharing\Modules\Buddy\BuddyGateway;
use Tests\Support\UnitTester;

class BuddyGatewayTest extends Unit
{
    protected UnitTester $tester;

    /**
     * @var BuddyGateway|null
     */
    private $gateway;
    private array $foodsaver;
    private array $otherFoodsaver;

    public function _before()
    {
        $this->gateway = $this->tester->get(BuddyGateway::class);
        $this->foodsaver = $this->tester->createFoodsaver();
        $this->otherFoodsaver = $this->tester->createFoodsaver();
        $this->basketIds = [];
    }

    protected function _after()
    {
    }

    public function testRequestAndConfim(): void
    {
        $this->gateway->buddyRequest($this->foodsaver['id'], $this->otherFoodsaver['id']);
        $this->tester->seeInDatabase('fs_buddy', [
            'foodsaver_id' => $this->otherFoodsaver['id'],
            'buddy_id' => $this->foodsaver['id'],
            'confirmed' => 0
        ]);

        $this->gateway->confirmBuddy($this->foodsaver['id'], $this->otherFoodsaver['id']);
        $this->tester->seeInDatabase('fs_buddy', [
            'foodsaver_id' => $this->foodsaver['id'],
            'buddy_id' => $this->otherFoodsaver['id'],
            'confirmed' => 1
        ]);
        $this->tester->seeInDatabase('fs_buddy', [
            'foodsaver_id' => $this->otherFoodsaver['id'],
            'buddy_id' => $this->foodsaver['id'],
            'confirmed' => 1
        ]);
    }
}
