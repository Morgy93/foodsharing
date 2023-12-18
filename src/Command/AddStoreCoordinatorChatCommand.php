<?php

namespace Foodsharing\Command;

use Foodsharing\Modules\Message\MessageGateway;
use Foodsharing\Modules\Store\ConversationType;
use Foodsharing\Modules\Store\StoreGateway;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AddStoreCoordinatorChatCommand extends Command
{
    private readonly MessageGateway $messageGateway;
    private readonly TranslatorInterface $translator;
    private readonly StoreGateway $storeGateway;

    public function __construct(StoreGateway $storeGateway, MessageGateway $messageGateway, TranslatorInterface $translator)
    {
        $this->storeGateway = $storeGateway;
        $this->messageGateway = $messageGateway;
        $this->translator = $translator;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Adds store coordinator conversations if not yet created');
        $this->setHelp('This command should just be needed as a one-time fix to add coordinator conversation to all stores.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stores = $this->storeGateway->getStores();
        foreach ($stores as $store) {
            $biebsArray = $this->storeGateway->getBiebsForStore($store['Id']);
            $biebs = array_column($biebsArray, 'id');

            if ($store['coordinator_conversation_id'] == null) {
                $coordinateChatId = $this->messageGateway->createConversation([$biebs], true);
                $coordinatorConversationName = $this->translator->trans('store.coordinator_conversation_name', ['{name}' => $store['name']]);
                $this->messageGateway->renameConversation($coordinateChatId, $coordinatorConversationName);
                $this->storeGateway->updateStoreConversation($store['Id'], $coordinateChatId, ConversationType::Coordinator);
            }
        }

        return 0;
    }
}
