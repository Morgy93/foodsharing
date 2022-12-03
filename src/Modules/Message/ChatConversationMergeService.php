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

	public function getMessagesIds(int $offset = 0, int $amount = 20): array
	{
		return $this->database->fetchAllValues("SELECT id FROM fs_msg ORDER BY id LIMIT :amount OFFSET :offset", [
			"amount" => $amount,
			"offset" => $offset
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
}
