<?php

namespace Foodsharing\Modules\Mailbox;

use Carbon\Carbon;
use DateTime;
use Ddeboer\Imap\Message\EmailAddress;
use Foodsharing\Modules\Core\DBConstants\Mailbox\MailboxFolder;

class Email
{
    /**
     * Internal unique ID of this email. This is only used in the foodsharing mailbox system.
     */
    public int $id;
    /**
     * ID of the mailbox in which this email is.
     */
    public int $mailboxId;
    /**
     * Folder in the mailbox in which this email is, see {@link MailboxFolder}.
     */
    public int $mailboxFolder;
    /**
     * The sender's email address.
     */
    public EmailAddress $from;
    /**
     * A list of recipients. Should never be empty.
     *
     * @var EmailAddress[]
     */
    public array $to;
    /**
     * An optional list of CC addresses. Can be empty or null.
     *
     * @var EmailAddress[]
     */
    public ?array $cc;
    /**
     * An optional list of BCC addresses. Can be empty or null.
     *
     * @var EmailAddress[]
     */
    public ?array $bcc;
    /**
     * Time at which this email was sent or received.
     */
    public DateTime $time;
    /**
     * Subject of the email. Can be empty but not null.
     */
    public string $subject;
    /**
     * Body of this email.
     */
    public ?string $body;
    /**
     * Body of this email in which HTML tags have not been stripped.
     */
    public ?string $bodyHtml;
    /**
     * Optional list of attachments. Can be empty or null.
     *
     * @var EmailAttachment[]
     */
    public ?array $attachments;
    /**
     * Whether this email has been read.
     */
    public bool $isRead;
    /**
     * Whether this email has been answered.
     */
    public bool $isAnswered;

    public function __construct(
    ) {
        $this->id = -1;
        $this->mailboxId = -1;
        $this->mailboxFolder = -1;
        $this->from = new EmailAddress('');
        $this->to = [];
        $this->cc = null;
        $this->bcc = null;
        $this->time = Carbon::now();
        $this->subject = '';
        $this->body = null;
        $this->attachments = null;
        $this->isRead = false;
        $this->isAnswered = false;
    }

    /**
     * @param EmailAddress[] $to
     */
    public static function create(
        int $id,
        int $mailboxId,
        int $mailboxFolder,
        EmailAddress $from,
        array $to,
        DateTime $time,
        string $subject,
        ?string $body,
        ?string $bodyHtml,
        bool $isRead = false,
        bool $isAnswered = false
    ): Email {
        $e = new Email();
        $e->id = $id;
        $e->mailboxId = $mailboxId;
        $e->mailboxFolder = $mailboxFolder;
        $e->from = $from;
        $e->to = $to;
        $e->cc = null;
        $e->bcc = null;
        $e->time = $time;
        $e->subject = $subject;
        $e->body = $body;
        $e->bodyHtml = $bodyHtml;
        $e->isRead = $isRead;
        $e->isAnswered = $isAnswered;

        return $e;
    }
}
