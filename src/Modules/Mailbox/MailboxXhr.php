<?php

namespace Foodsharing\Modules\Mailbox;

use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Permissions\MailboxPermissions;
use Foodsharing\RestApi\Models\Mailbox\EmailSendData;
use Foodsharing\Utility\TimeHelper;

class MailboxXhr extends Control
{
    private TimeHelper $timeHelper;
    private MailboxGateway $mailboxGateway;
    private MailboxPermissions $mailboxPermissions;
    private MailboxTransactions $mailboxTransactions;

    public function __construct(
        MailboxView $view,
        TimeHelper $timeHelper,
        MailboxGateway $mailboxGateway,
        MailboxPermissions $mailboxPermissions,
        MailboxTransactions $mailboxTransactions
    ) {
        $this->view = $view;
        $this->timeHelper = $timeHelper;
        $this->mailboxGateway = $mailboxGateway;
        $this->mailboxPermissions = $mailboxPermissions;
        $this->mailboxTransactions = $mailboxTransactions;

        parent::__construct();
    }

    public function quickreply()
    {
        if (!isset($_GET['mid']) || !$this->mailboxPermissions->mayMessage($_GET['mid'])) {
            return XhrResponses::PERMISSION_DENIED;
        }
        $mailboxId = $this->mailboxGateway->getMailboxId($_GET['mid']);
        if ($this->mailboxPermissions->mayMailbox($mailboxId)) {
            $message = $this->mailboxGateway->getMessage($_GET['mid']);
            $sender = $message['sender'];
            if ($sender != null) {
                $subject = 'Re: ' . trim(str_replace(['Re:', 'RE:', 're:', 'aw:', 'Aw:', 'AW:'], '', $message['subject']));

                $data = json_decode(file_get_contents('php://input'), true);
                $body = strip_tags($data['msg'])
                    . "\n\n\n\n--------- "
                    . $this->translator->trans('mailbox.signature', ['{date}' => $this->timeHelper->niceDate($message['time_ts'])])
                    . " ---------\n\n>\t"
                    . str_replace("\n", "\n>\t", $message['body']);

                $emailData = new EmailSendData();
                $emailData->to = [$sender->getAddress()];
                $emailData->subject = $subject;
                $emailData->body = $body;

                $this->mailboxTransactions->sendAndSaveEmail($emailData, $mailboxId);

                echo json_encode([
                    'status' => 1,
                    'message' => $this->translator->trans('mailbox.okay'),
                ]);
                exit;
            }
        }

        echo json_encode([
            'status' => 0,
            'message' => $this->translator->trans('mailbox.failed'),
        ]);
        exit;
    }
}
