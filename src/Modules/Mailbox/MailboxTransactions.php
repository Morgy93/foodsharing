<?php

namespace Foodsharing\Modules\Mailbox;

use Foodsharing\RestApi\Models\Mailbox\Creation;

class MailboxTransactions
{
    public function __construct(
        private readonly MailboxGateway $mailboxGateway,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function createMailboxAndAddUser(Creation $mailboxCreation): void
    {
        $mailboxId = $this->mailboxGateway->createMailbox($mailboxCreation->name);
        $this->mailboxGateway->updateMember($mailboxId, $mailboxCreation->userIds);
    }
}
