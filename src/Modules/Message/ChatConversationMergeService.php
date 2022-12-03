<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Modules\Core\Database;

class ChatConversationMergeService
{
	public function __construct(
		private readonly Database $database,
		private readonly MessageGateway $messageGateway,
		private readonly MessageTransactions $messageTransactions,
	) {}

	public function getMessages(int $offset = 0, int $amount = 20): array
	{
		return $this->database->fetchAll("SELECT id, conversation_id FROM fs_msg ORDER BY id LIMIT :amount OFFSET :offset", [
			"amount" => $amount,
			"offset" => $offset,
		]);
	}

	public function isConversationBetweenTwoMembers(int $conversationId): bool
	{
		$conversationMemberRecords = $this->database->fetchAll(
			"SELECT id FROM fs_foodsaver_has_conversation WHERE conversation_id = :conversation_id",
			["conversation_id" => $conversationId]
		);

		$amountOfMembers = count($conversationMemberRecords);
		return $amountOfMembers === 2;
	}

	public function getMemberIdsOfConversation(int $conversationId): array
	{
		$conversationWithMembers = $this->database->fetch("
			SELECT GROUP_CONCAT(foodsaver_id) AS foodsaver_ids, conversation_id FROM fs_foodsaver_has_conversation
			WHERE conversation_id = :conversation_id GROUP BY conversation_id",
			["conversation_id" => $conversationId]
		);

		return explode(",", $conversationWithMembers["foodsaver_ids"]);
	}
}
