<?php

namespace Foodsharing\Modules\Mailbox;

class MailboxTransactions
{
    public function __construct(
        private readonly MailboxGateway $mailboxGateway,
    ) {
    }

    public function createMailboxAndAddUser(string $name, array $userIds): void
    {
        $mailbox = $this->mailboxGateway->createMailbox($name);
        $this->mailboxGateway->updateMember($mailbox['mailbox_id'], $userIds);
    }
}
