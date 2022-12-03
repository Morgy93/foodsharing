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

	public function getNotManagedMessagesIds(int $amount = 20, array $alreadyManagedMessageIds = [0]): array
	{
		$alreadyManagedMessageIds = implode(',', $alreadyManagedMessageIds);
		return $this->database->fetchAllValues(
			"SELECT id FROM fs_msg WHERE id NOT IN ({$alreadyManagedMessageIds}) LIMIT :amount",
			[
				"amount" => $amount
			]
		);
	}
}
