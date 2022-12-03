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
}
