<?php

namespace Foodsharing\Lib\Mail;

use Foodsharing\Lib\Db\Mem;

/**
 * Data structure to store mail data for asynchronous queue.
 *
 * @author ra
 */
class AsyncMail
{
    private $mem;
    private $data;
    private bool $highPriority;

    public function __construct(Mem $mem)
    {
        $this->mem = $mem;
        $this->data = [
            'recipients' => [],
            'attachments' => [],
            'from' => [DEFAULT_EMAIL, DEFAULT_EMAIL_NAME],
            'body' => '',
            'html' => false,
            'subject' => DEFAULT_EMAIL_NAME,
            'identifier' => '',
            'queuedAt' => new \DateTime()];
        $this->highPriority = false;
    }

    public function addRecipient($email, $name = null)
    {
        $this->data['recipients'][] = [$email, $name];
    }

    public function setFrom($email, $name = null)
    {
        $this->data['from'] = [$email, $name];
    }

    public function setBody($body)
    {
        $this->data['body'] = $body;
    }

    public function setSubject($subject)
    {
        $subject = str_replace(['\n'], ' ', $subject);
        $this->data['subject'] = $subject;
    }

    public function setHtmlBody($html)
    {
        $this->data['html'] = $html;
    }

    public function addAttachment($file, $name = null)
    {
        if ($name == null) {
            $name = explode('/', $file);
            $name = end($name);
        }

        if (file_exists($file)) {
            $this->data['attachments'][] = [$file, $name];
        }
    }

    public function clearRecipients()
    {
        return $this->data['recipients'] = [];
    }

    public function setIdentifier($identifier)
    {
        $this->data['identifier'] = $identifier;
    }

    public function setHighPriority(bool $highPriority)
    {
        $this->highPriority = $highPriority;
    }

    public function toArray()
    {
        return $this->data;
    }

    public function send($type)
    {
        $this->mem->queueWork($type, $this->toArray(), $this->highPriority);
    }
}
