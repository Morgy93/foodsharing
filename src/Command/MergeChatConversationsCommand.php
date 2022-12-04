<?php

namespace Foodsharing\Command;

use Foodsharing\Modules\Message\ChatConversationMergeService;
use Foodsharing\Modules\Message\MessageGateway;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * How does the command work?
 * --------------------------
 * START COMMAND EXECUTION
 *
 * 1. Fetch all already-duplicate-checked messages
 * 2. Fetch an amount of messages after a defined date [yyyy-mm-dd] and excluded already-duplicate-checked messages
 * 3. For each message, ...
 * 		1. check if the conversation is between two foodsaver [2 rows with same conversation_id existing in fs_has_conversation]
 * 		if not --> save message in already-duplicate-checked messages and TERMINATE ITERATION
 *
 * 		2. check if the two foodsaver have more than 1 conversation between them
 * 		if not --> save message in already-duplicate-checked messages and TERMINATE ITERATION
 *
 * 		3. For each conversation, ...
 * 			1. fetch the amount of messages and save it in a list
 * 			2. sort the list and get the conversation with the most messages
 * 		return conversations and the conversation with the most messages
 *
 * 		4. For each conversation that is not the conversation with the most messages, ...
 * 			1. move all messages in the conversation to the conversation with the most messages
 * 			2. delete conversation
 * 		the conversation with the most messages is now the merged conversation
 *
 * 		5. Fetch all messages of the merged conversation [fs_msg]
 * 		6. get the last message of the merged conversation [fs_msg]
 * 		7. Update the last message information for the merged conversation [fs_conversation]
 * 		8. add all messages of the merged conversation to the already-duplicate-checked messages
 *
 * 		TERMINATE ITERATION
 * 4. save all already-duplicate-checked messages
 *
 * TERMINATE COMMAND EXECUTION
 */

#[AsCommand(
	name: 'foodsharing:merge-chat-conversations',
	description: '',
	hidden: false
)]
class MergeChatConversationsCommand extends Command
{
	public function __construct(
		private readonly ChatConversationMergeService $chatConversationMergeService,
		private readonly MessageGateway $messageGateway,
	)
	{
		parent::__construct();
	}

	protected function configure()
	{

	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$messagesToAnalyze = $this->chatConversationMergeService->getMessages(amount: 10);
		$progressBar = new ProgressBar($output, count($messagesToAnalyze));

		foreach ($messagesToAnalyze as $message) {
			$conversationId = $message["conversation_id"];

			$memberIds = $this->chatConversationMergeService->getMemberIdsOfConversation($conversationId);
			if (!$this->chatConversationMergeService->isConversationBetweenTwoMembers($memberIds)) {
				$progressBar->advance();
				continue;
			}

			$commonConversationIds = $this->chatConversationMergeService->getCommonConversationIds($memberIds);
			$commonConversationIds = array_filter($commonConversationIds, function ($conversationId) {
				return !$this->chatConversationMergeService->isConversationAssociatedWithAStore($conversationId);
			});

			$commonConversationIdsWithAmountOfMessages = $this->chatConversationMergeService->getConversationIdsWithAmountOfMessages($commonConversationIds);
			$commonConversationWithMostMessages = array_shift($commonConversationIdsWithAmountOfMessages);

			foreach ($commonConversationIdsWithAmountOfMessages as $conversationIdWithAmountOfMessage) {
				$conversationId = $conversationIdWithAmountOfMessage["conversation_id"];
				$this->chatConversationMergeService->updateMessagesFromOldToNewConversation($conversationId, $commonConversationWithMostMessages["conversation_id"]);
				$this->messageGateway->deleteConversation($conversationId);
			}

			$mergedConversationId = &$commonConversationWithMostMessages["conversation_id"];

			$progressBar->advance();
		}

		return Command::SUCCESS;
	}
}