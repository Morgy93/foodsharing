<?php

namespace Foodsharing\Command;

use Foodsharing\Modules\Message\ChatConversationMergeService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
	name: 'foodsharing:merge-chat-conversations',
	description: '',
	hidden: false
)]
class MergeChatConversationsCommand extends Command
{
	public function __construct(
		private readonly ChatConversationMergeService $chatConversationMergeService,
	)
	{
		parent::__construct();
	}

	protected function configure()
	{

	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{

		return Command::SUCCESS;
	}
}
