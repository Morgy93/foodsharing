<?php

declare(strict_types=1);

namespace Tests\Unit;

use Codeception\Test\Unit;
use Foodsharing\Modules\Core\Database;
use Tests\Support\UnitTester;

class EmojiTest extends Unit
{
    protected UnitTester $tester;
    private Database $db;

    private $user1;
    private $user2;
    private $conversation;
    private $messageBody;
    private $messageId;

    public function _before()
    {
        $this->db = $this->tester->get(Database::class);
        $this->user1 = $this->tester->createFoodsharer();
        $this->user2 = $this->tester->createFoodsharer();
        $this->conversation = $this->tester->createConversation([
            $this->user1['id'],
            $this->user2['id']
        ]);
        $this->messageBody = 'Hey dude 😂! You are such a ★ :)';
        $this->messageId = $this->db->insert('fs_msg', [
            'conversation_id' => $this->conversation['id'],
            'foodsaver_id' => $this->user1['id'],
            'body' => $this->messageBody,
            'time' => $this->db->now()
        ]);
    }

    public function testEmojiHandlingWithPDO(): void
    {
        $body = $this->db->fetchValueByCriteria('fs_msg', 'body', ['id' => $this->messageId]);
        $this->assertEquals($this->messageBody, $body);
    }

    public function testEmojiHandlingWithCodeceptionDB(): void
    {
        $body = $this->tester->grabColumnFromDatabase('fs_msg', 'body', ['id' => $this->messageId])[0];
        $this->assertEquals($this->messageBody, $body);
    }
}
