<?php

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\Database;

class EmojiTest extends \Codeception\Test\Unit
{
	protected UnitTester $tester;
	private Database $db;
	private Db $db2;

	private $user1;
	private $user2;
	private $conversation;
	private $messageBody;
	private $messageId;

	protected function _before()
	{
		$this->db = $this->tester->get(Database::class);
		$this->db2 = $this->tester->get(Db::class);
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

	public function testEmojiHandlingWithPDO()
	{
		$body = $this->db->fetchValueByCriteria('fs_msg', 'body', ['id' => $this->messageId]);
		$this->assertEquals($this->messageBody, $body);
	}

	public function testEmojiHandlingWithMysqli()
	{
		$body = $this->db2->qOne('select body from fs_msg where id = ' . $this->messageId);
		$this->assertEquals($this->messageBody, $body);
	}

	public function testEmojiHandlingWithCodeceptionDB()
	{
		$body = $this->tester->grabColumnFromDatabase('fs_msg', 'body', ['id' => $this->messageId])[0];
		$this->assertEquals($this->messageBody, $body);
	}
}
