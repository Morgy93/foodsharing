<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Message;

use Foodsharing\Modules\Core\Database;

class ChatConversationMergeService
{
	public function __construct(
		private readonly Database $database,
		private readonly MessageGateway $messageGateway,
		private readonly MessageTransactions $messageTransactions,
	) {
	}

	public function getMessages(int $offset = 0, int $amount = 20): array
	{
		return $this->database->fetchAll('SELECT id, conversation_id FROM fs_msg ORDER BY id LIMIT :amount OFFSET :offset', [
			'amount' => $amount,
			'offset' => $offset,
		]);
	}

	public function isConversationBetweenTwoMembers(array $memberIds): bool
	{
		return count($memberIds) === 2;
	}

	public function getMemberIdsOfConversation(int $conversationId): array
	{
		$conversationMemberIds = $this->database->fetchAllValues('
			SELECT foodsaver_id FROM fs_foodsaver_has_conversation
			WHERE conversation_id = :conversation_id',
			['conversation_id' => $conversationId]
		);

		return $conversationMemberIds;
	}

	public function getCommonConversationIds(array $memberIds): array
	{
		$memberIds = implode(',', $memberIds);
		$membersWithConversationLists = $this->database->fetchAll("
			SELECT foodsaver_id, GROUP_CONCAT(conversation_id) AS conversation_ids FROM fs_foodsaver_has_conversation
			WHERE foodsaver_id IN ($memberIds) GROUP BY foodsaver_id"
		);

		$conversationIdsOfAllMembers = [];
		foreach ($membersWithConversationLists as $memberWithConversationList) {
			$conversationIds = explode(',', $memberWithConversationList['conversation_ids']);
			$conversationIdsOfAllMembers[] = $conversationIds;
		}

		return array_intersect(...$conversationIdsOfAllMembers);
	}

	public function getConversationIdsWithAmountOfMessages(array $conversationIds): array
	{
		$conversationIds = implode(',', $conversationIds);

		return $this->database->fetchAll("
			SELECT conversation_id, COUNT(body) AS amount_of_messages FROM fs_msg
			WHERE conversation_id IN ($conversationIds) GROUP BY conversation_id ASC"
		);
	}

	public function isConversationAssociatedWithAStore(int $conversationId): bool
	{
		$storesThatBelongsToTheConversationId = $this->database->fetchAll("
			SELECT id FROM fs_betrieb
			WHERE springer_conversation_id = $conversationId OR team_conversation_id = $conversationId
		");

		return !empty($storesThatBelongsToTheConversationId);
	}

	public function updateMessagesFromOldToNewConversation(int $oldConversationId, int $newConversationId): void
	{
		$this->database->update('fs_msg', ['conversation_id' => $newConversationId], ['conversation_id' => $oldConversationId]);
	}
}
