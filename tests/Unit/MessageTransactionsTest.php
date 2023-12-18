<?php

declare(strict_types=1);

namespace Tests\Unit;

use Codeception\Test\Unit;
use Foodsharing\Modules\Message\MessageTransactions;
use Tests\Support\UnitTester;

class MessageTransactionsTest extends Unit
{
    protected UnitTester $tester;
    private MessageTransactions $service;

    private $testFoodsaver1;
    private $testFoodsaver2;

    public function _before()
    {
        $this->service = $this->tester->get(MessageTransactions::class);

        $this->testFoodsaver1 = $this->tester->createFoodsaver();
        $this->testFoodsaver2 = $this->tester->createFoodsaver();
    }

    public function testGetProperConversationNameReturnsProperConversationNameForNamedConversations(): void
    {
        $testConversationName = 'conversationName';
        $members = [$this->testFoodsaver1['id'], $this->testFoodsaver2['id']];

        $testConversation = $this->tester->createConversation(
            $members,
            ['name' => $testConversationName]
        );

        $this->tester->assertEquals(
            $testConversationName,
            $this->service->getProperConversationNameForFoodsaver($this->testFoodsaver1['id'], $testConversationName, $members)
        );
    }

    public function testGetProperConversationNameReturnsProperConverationNameForStoreTeamConversation(): void
    {
        $members = [$this->testFoodsaver1['id'], $this->testFoodsaver2['id']];
        $testConversation = $this->tester->createConversation($members);
        $testConversationName = 'blablub';
        $testStore = $this->tester->createStore(
            $this->tester->createRegion()['id'],
            $testConversation['id']
        );

        $this->assertEquals(
            'blablub',
            $this->service->getProperConversationNameForFoodsaver($this->testFoodsaver1['id'], $testConversationName, $members)
        );
    }

    public function testGetProperConversationNameReturnsProperConversationNameForTwoMemberConversation(): void
    {
        $members = [$this->testFoodsaver1['id'], $this->testFoodsaver2['id']];
        $testConversation = $this->tester->createConversation($members);

        $this->assertEquals(
            $this->testFoodsaver2['name'],
            $this->service->getProperConversationNameForFoodsaver($this->testFoodsaver1['id'], null, [$this->testFoodsaver1, $this->testFoodsaver2])
        );
    }
}
