<?php

namespace Foodsharing\Modules\Mailbox;

use Foodsharing\RestApi\Models\Mailbox\Creation;

class MailboxTransactions
{
    public function __construct(
        private readonly MailboxGateway $mailboxGateway,
    ) {
    }

    public function createMailboxAndAddUser(Creation $mailboxCreation): void
    {
        $mailbox = $this->mailboxGateway->createMailbox($mailboxCreation->name);
        $this->mailboxGateway->updateMember($mailbox['mailbox_id'], $mailboxCreation->userIds);
    }
}
