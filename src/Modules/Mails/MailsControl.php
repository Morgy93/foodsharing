<?php

namespace Foodsharing\Modules\Mails;

use Ddeboer\Imap\Server;
use Foodsharing\Modules\Console\ConsoleControl;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\InfluxMetrics;
use Foodsharing\Utility\EmailHelper;
use Foodsharing\Utility\RouteHelper;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailsControl extends ConsoleControl
{
    private MailsGateway $mailsGateway;
    private Database $database;
    private MailerInterface $mailer;
    private InfluxMetrics $metrics;
    private RouteHelper $routeHelper;
    private EmailHelper $emailHelper;

    /*
     * todo move this to config file as a constant if this becomes a permanent solution
     * until then we need to be able to configure this rather flexible in here
     * 45,11 mails/minute = 1330 milli seconds between mails
     * */

    public function __construct(
        MailsGateway $mailsGateway,
        Database $database,
        InfluxMetrics $metrics,
        MailerInterface $mailer,
        RouteHelper $routeHelper,
        EmailHelper $emailHelper
    ) {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        $this->mailsGateway = $mailsGateway;
        $this->database = $database;
        $this->mailer = $mailer;
        $this->metrics = $metrics;
        $this->routeHelper = $routeHelper;
        $this->emailHelper = $emailHelper;
        parent::__construct();
    }

    public function queueWorker()
    {
        $this->mem->ensureConnected();
        $running = true;
        while ($running) {
            $elem = $this->mem->cache->brpoplpush('workqueue', 'workqueueprocessing', 10);
            if ($elem !== false && $e = unserialize($elem)) {
                if ($e['type'] == 'email') {
                    $res = $this->handleEmailRateLimited($e['data']);
                } else {
                    $res = false;
                }

                if ($res) {
                    $this->mem->cache->lrem('workqueueprocessing', $elem, 1);
                } else {
                    sleep(3);
                    /* trigger a restart as there is the database and SMTP connection that can hang :-( */
                    $running = false;
                    // TODO handle failed tasks?
                }
            }
        }
    }

    public function fetchMails()
    {
        foreach (IMAP as $imap) {
            $stats = $this->mailboxupdate($imap['host'], $imap['user'], $imap['password']);
            $this->metrics->addPoint('fetch_mails', ['account' => $imap['user']], $stats);
        }
    }

    /**
     * This Method will check for new E-Mails and sort it to the mailboxes.
     */
    public function mailboxupdate($host, $user, $password)
    {
        $server = new Server($host);
        $connection = $server->authenticate($user, $password);

        $mailbox = $connection->getMailbox('INBOX');
        if (!$connection->hasMailbox(IMAP_FAILED_BOX)) {
            $connection->createMailbox(IMAP_FAILED_BOX);
        }
        $failedMailbox = $connection->getMailbox(IMAP_FAILED_BOX);

        $messages = $mailbox->getMessages();
        $stats = ['unknown-recipient' => 0, 'failure' => 0, 'delivered' => 0, 'has-attachment' => 0];
        if (count($messages) <= 0) {
            return $stats;
        }

        $have_send = [];
        foreach ($messages as $msg) {
            try {
                $mboxes = [];
                $recipients = $msg->getTo() + $msg->getCc() + $msg->getBcc() + $msg->getReplyTo();
                foreach ($recipients as $to) {
                    if (in_array(strtolower($to->getHostname()), MAILBOX_OWN_DOMAINS)) {
                        $mboxes[] = $to->getMailbox();
                    }
                }

                if (empty($mboxes)) {
                    $msg->delete();
                    continue;
                }

                $mb_ids = $this->mailsGateway->getMailboxIds($mboxes);

                if (!$mb_ids) {
                    // send auto-reply message
                    $return_path = $msg->getReturnPath();
                    if (!$return_path) {
                        $return_path = $msg->getFrom();
                    } else {
                        $return_path = $return_path[0];
                    }
                    if ($return_path && $return_path != DEFAULT_EMAIL) {
                        $this->emailHelper->tplMail('general/invalid_email_address', $return_path->getAddress(), ['address' => implode(', ', $mboxes)]);
                    }
                    ++$stats['unknown-recipient'];
                } else {
                    try {
                        $html = $msg->getBodyHtml();
                    } catch (\Exception $e) {
                        $html = null;
                        self::error('Could not get HTML body ' . $e->getMessage() . ', continuing with PLAIN TEXT\n');
                    }

                    if ($html) {
                        $h2t = new \Html2Text\Html2Text($html);
                        $body = $h2t->get_text();
                        $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
                    } else {
                        try {
                            $text = $msg->getBodyText();
                        } catch (\Exception $e) {
                            $text = null;
                            self::error('Could not get PLAIN TEXT body ' . $e->getMessage() . ', skipping mail.\n');
                        }
                        if ($text != null) {
                            $body = $text;
                            $html = nl2br($this->routeHelper->autolink($text));
                        } else {
                            $body = '';
                            $html = '';
                        }
                    }

                    $attach = [];
                    foreach ($msg->getAttachments() as $i => $attachment) {
                        $filename = $attachment->getFilename();
                        if ($filename === null) {
                            $filename = 'unknown_' . $i;
                            self::info('Attachment without(?) a specified filename encountered. gave it a generic one (' . $filename . ')\n');
                        }
                        if ($this->isAttachmentAllowed($filename)) {
                            $new_filename = bin2hex(random_bytes(16));
                            $path = 'data/mailattach/';
                            $j = 0;
                            while (file_exists($path . $new_filename)) {
                                ++$j;
                                $new_filename = $j . '-' . $filename;
                            }
                            try {
                                file_put_contents($path . $new_filename, $attachment->getDecodedContent());
                                $attach[] = [
                                    'filename' => $new_filename,
                                    'origname' => $filename,
                                    'mime' => mime_content_type($path . $new_filename)
                                ];
                            } catch (\Exception $e) {
                                self::error('Could not parse/save an attachment (' . $e->getMessage() . "), skipping that one...\n");
                            }
                        }
                    }
                    if ($attach) {
                        ++$stats['has-attachment'];
                    }
                    $attach = json_encode($attach);

                    $date = null;
                    try {
                        $date = $msg->getDate();
                    } catch (\Exception $e) {
                        self::error('Error parsing date: ' . $e->getMessage() . ", continuing with 'now'\n");
                    }
                    if ($date === null) {
                        $date = new \DateTime();
                    }

                    $md = $date->format('Y-m-d H:i:s') . ':' . $msg->getSubject();

                    $delivered = false;

                    foreach ($mb_ids as $id) {
                        if (!isset($have_send[$id])) {
                            $have_send[$id] = [];
                        }

                        if (!isset($have_send[$id][$md])) {
                            $delivered = true;
                            $have_send[$id][$md] = true;
                            $from = [];
                            $from['mailbox'] = $msg->getFrom()->getMailbox();
                            $from['host'] = $msg->getFrom()->getHostname();
                            $name = $msg->getFrom()->getName();
                            if ($name) {
                                $from['personal'] = $msg->getFrom()->getName();
                            }

                            $this->mailsGateway->saveMessage(
                                $id, // mailbox id
                                1, // folder
                                json_encode($from), // sender
                                json_encode(array_map(function ($r) {
                                    return ['mailbox' => $r->getMailbox(), 'host' => $r->getHostname()];
                                }, $recipients)), // all recipients
                                $msg->getSubject() ?? '',
                                $body,
                                $html,
                                $date->format('Y-m-d H:i:s'),
                                $attach
                            );
                        }
                    }
                    if ($delivered) {
                        ++$stats['delivered'];
                    } else {
                        ++$stats['failure'];
                    }
                }

                $msg->delete(); // message has been processed at this point, mark it for deletion
            } catch (\Exception $e) {
                self::error('Something went wrong, ' . $e->getMessage() . "\n");
                \Sentry\captureException($e);
                $msg->move($failedMailbox);
            }
        }

        // actually delete all messages that were processed
        $connection->expunge();

        return $stats;
    }

    private function getMailAddressParts($str)
    {
        $parts = explode('@', trim($str));
        if (count($parts) != 2) {
            throw new \Exception($str . ' is not a valid email address');
        }
        $part['mailbox'] = $parts[0];
        $part['host'] = $parts[1];

        return $part;
    }

    public function fixWrongMailSenderFormat()
    {
        $res = $this->database->fetchAll('SELECT id, sender, `to` FROM fs_mailbox_message WHERE id < 185882 AND id > 175000');
        foreach ($res as $r) {
            $sender = json_decode($r['sender']);
            $to = json_decode($r['to']);
            if (is_string($sender)) {
                $newSender = json_encode($this->getMailAddressParts($sender));
                $newTo = [];
                foreach ($to as $recip) {
                    if (strpos($recip, ';')) {
                        foreach (explode(';', $recip) as $rp) {
                            $newTo[] = $this->getMailAddressParts($rp);
                        }
                    } else {
                        $newTo[] = $this->getMailAddressParts($recip);
                    }
                }
                $newTo = json_encode($newTo);
                $this->database->update('fs_mailbox_message', ['sender' => $newSender, 'to' => $newTo], ['id' => $r['id']]);
            }
        }
    }

    private function isAttachmentAllowed(string $filename): bool
    {
        if (strlen($filename) < 300) {
            $ext = explode('.', $filename);
            $ext = end($ext);
            $ext = strtolower($ext);
            $notallowed = [
                'php' => true,
                'html' => true,
                'htm' => true,
                'php5' => true,
                'php4' => true,
                'php3' => true,
                'php2' => true,
                'php1' => true
            ];

            if (!isset($notallowed[$ext])) {
                return true;
            }
        }

        return false;
    }

    public function handleEmailRateLimited($data)
    {
        self::info('Mail from: ' . $data['from'][0] . ' (' . $data['from'][1] . ')');
        $email = new Email();

        $mailParts = explode('@', $data['from'][0]);
        $fromDomain = end($mailParts);

        if (in_array($fromDomain, MAILBOX_OWN_DOMAINS, true)) {
            $email->from(new Address($data['from'][0], $data['from'][1] ?? ''));
        } else {
            $email->from(new Address(DEFAULT_EMAIL, $data['from'][1] ?? ''));
            $email->replyTo(new Address($data['from'][0], $data['from'][1] ?? ''));
        }

        $subject = preg_replace('/\s+/', ' ', trim($data['subject']));
        if (!$subject) {
            $subject = '[Leerer Betreff]';
        }
        $email->subject($subject);
        $email->html($data['html']);
        $email->text($data['body']);

        if (!empty($data['attachments'])) {
            foreach ($data['attachments'] as $a) {
                $email->attachFromPath($a[0], $a[1]);
            }
        }
        $mailCount = 0;
        $recipients = [];
        foreach ($data['recipients'] as $r) {
            $r[0] = strtolower($r[0]);
            self::info('To: ' . $r[0]);
            $address = explode('@', $r[0]);
            if (count($address) != 2) {
                self::error('invalid address');
                continue;
            }
            if (!$this->mailsGateway->emailIsBouncing($r[0])) {
                if (!empty($r[1])) {
                    $recipients[] = new Address($r[0], $r[1]);
                } else {
                    $recipients[] = new Address($r[0]);
                }
                ++$mailCount;
            } else {
                self::error('bouncing address');
            }
        }
        $email->to(...$recipients);
        if ($mailCount < 1) {
            return true;
        }

        for ($max_try = 2; $max_try > 0; --$max_try) {
            try {
                self::info('send email tries remaining ' . $max_try);
                $this->mailer->send($email);
                self::success('email send OK');

                // remove attachments from temp folder
                if (!empty($data['attachments'])) {
                    foreach ($data['attachments'] as $a) {
                        @unlink($a[0]);
                    }
                }

                break;
            } catch (\Throwable $e) {
                self::error('email send error: ' . $e->getMessage());
                self::error(print_r($data, true));
            }

            if ($max_try == 1) {
                return false;
            }
        }
        // rate limiting
        usleep($mailCount * DELAY_MICRO_SECONDS_BETWEEN_MAILS);

        return true;
    }

    public static function parseEmailAddress($email, $name = false)
    {
        $p = explode('@', $email);

        if ($name === false) {
            $name = $email;
        }

        return [
            'personal' => $name,
            'mailbox' => $p[0],
            'host' => $p[1]
        ];
    }
}
