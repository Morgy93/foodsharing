<?php

declare(strict_types=1);

namespace Foodsharing\Command;

use Foodsharing\Modules\Message\ChatConversationMergeService;
use Foodsharing\Modules\Message\MessageGateway;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
	name: 'foodsharing:chat:merge-two-conversations',
	description: 'This command merges two chat conversations into one.',
	hidden: false
)]
class MergeTwoChatConversationsCommand extends Command
{
	public function __construct(
		private readonly ChatConversationMergeService $chatConversationMergeService,
		private readonly MessageGateway $messageGateway,
	) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setHelp('Merges the 2nd chat conversation into the 1st conversation. If specified, delete 2nd conversation.');

		$this->addArgument('conversation1', InputArgument::REQUIRED, 'First conversation id');
		$this->addArgument('conversation2', InputArgument::REQUIRED, 'Second conversation id');

		$this->addOption(
			'delete-old-conversation',
			'd',
			InputOption::VALUE_NONE,
			'Delete 2nd conversation after merge?'
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$conversation1 = (int)$input->getArgument('conversation1');
		$conversation2 = (int)$input->getArgument('conversation2');
		$isDeletionRequired = $input->getOption('delete-old-conversation');
		$foodsaverIdsOfConversation1 = $this->chatConversationMergeService->getMemberIdsOfConversation($conversation1);
		$foodsaverIdsOfConversation2 = $this->chatConversationMergeService->getMemberIdsOfConversation($conversation2);

		$isAnyConversationStoreConversation =
			$this->chatConversationMergeService->isConversationAssociatedWithAStore($conversation1)
			|| $this->chatConversationMergeService->isConversationAssociatedWithAStore($conversation2);

		if ($isAnyConversationStoreConversation) {
			$output->writeln('<error>One of the conversations is associated with a store.</error>');
			$output->writeln('<info>Merging not executed.</info>');

			return Command::FAILURE;
		}

		$output->writeln('Start simple merging');
		$output->writeln("Move messages from conversation2 ($conversation2) into conversation1 ($conversation1)");
		$this->chatConversationMergeService->updateMessagesFromOldToNewConversation($conversation2, $conversation1);

		$output->writeln("Move member from conversation2 ($conversation2) into conversation1 ($conversation1)");
		foreach ($foodsaverIdsOfConversation2 as $foodsaverId) {
			if (!in_array($foodsaverId, $foodsaverIdsOfConversation1)) {
				$this->messageGateway->addUserToConversation($conversation1, $foodsaverId);
			}
		}

		if ($isDeletionRequired) {
			$output->writeln("Delete conversation2 ($conversation2)");
			$this->messageGateway->deleteConversation($conversation2);
		}

		$output->writeln('<info>Done</info>');

		return Command::SUCCESS;
	}
}
