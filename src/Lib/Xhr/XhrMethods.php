<?php

namespace Foodsharing\Lib\Xhr;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Email\EmailStatus;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Email\EmailGateway;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\Modules\Group\GroupGateway;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Permissions\NewsletterEmailPermissions;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\Utility\EmailHelper;
use Foodsharing\Utility\Sanitizer;
use Symfony\Contracts\Translation\TranslatorInterface;

class XhrMethods
{
    private Db $model;
    private Session $session;
    private GroupFunctionGateway $groupFunctionGateway;
    private GroupGateway $groupGateway;
    private RegionGateway $regionGateway;
    private StoreGateway $storeGateway;
    private EmailGateway $emailGateway;
    private MailboxGateway $mailboxGateway;
    private Sanitizer $sanitizerService;
    private EmailHelper $emailHelper;
    private NewsletterEmailPermissions $newsletterEmailPermissions;
    private RegionPermissions $regionPermissions;
    private TranslatorInterface $translator;

    public function __construct(
        Session $session,
        Db $model,
        GroupFunctionGateway $groupFunctionGateway,
        GroupGateway $groupGateway,
        RegionGateway $regionGateway,
        StoreGateway $storeGateway,
        EmailGateway $emailGateway,
        MailboxGateway $mailboxGateway,
        Sanitizer $sanitizerService,
        EmailHelper $emailHelper,
        NewsletterEmailPermissions $newsletterEmailPermissions,
        RegionPermissions $regionPermission,
        TranslatorInterface $translator
    ) {
        $this->session = $session;
        $this->model = $model;
        $this->groupFunctionGateway = $groupFunctionGateway;
        $this->groupGateway = $groupGateway;
        $this->regionGateway = $regionGateway;
        $this->storeGateway = $storeGateway;
        $this->emailGateway = $emailGateway;
        $this->mailboxGateway = $mailboxGateway;
        $this->sanitizerService = $sanitizerService;
        $this->emailHelper = $emailHelper;
        $this->newsletterEmailPermissions = $newsletterEmailPermissions;
        $this->regionPermissions = $regionPermission;
        $this->translator = $translator;
    }

    public function xhr_continueMail($data)
    {
        if ($this->newsletterEmailPermissions->mayAdministrateNewsletterEmail()) {
            $mail_id = (int)$data['id'];

            $mail = $this->emailGateway->getOne_send_email($mail_id);
            $recip = $this->emailGateway->getMailNext($mail_id);

            if (empty($recip)) {
                return json_encode([
                    'status' => 2,
                    'comment' => $this->translator->trans('recipients.done'),
                ]);
            }

            $mailbox = $this->mailboxGateway->getMailbox((int)$mail['mailbox_id']);
            $mailbox['email'] = $mailbox['name'] . '@' . PLATFORM_MAILBOX_HOST;

            $sender = $this->model->getValues(['geschlecht', 'name'], 'foodsaver', $this->session->id());

            $this->emailGateway->setEmailStatus($mail['id'], $recip, EmailStatus::STATUS_INITIALISED);

            foreach ($recip as $fs) {
                $anrede = $this->translator->trans('salutation.' . $fs['geschlecht']);

                $search = ['{NAME}', '{ANREDE}', '{EMAIL}'];
                $replace = [$fs['name'], $anrede, $fs['email']];

                $attach = false;
                if (!empty($mail['attach'])) {
                    $attach = json_decode($mail['attach'], true);
                }

                $message = str_replace($search, $replace, $mail['message']);
                $subject = str_replace($search, $replace, $mail['name']);

                $check = false;
                if ($this->emailHelper->libmail($mailbox, $fs['email'], $subject, $message, $attach, $fs['token'])) {
                    $check = true;
                }

                if (!$check) {
                    $this->emailGateway->setEmailStatus($mail['id'], [$fs['id']], EmailStatus::STATUS_INVALID_MAIL);
                } else {
                    $this->emailGateway->setEmailStatus($mail['id'], [$fs['id']], EmailStatus::STATUS_SENT);
                }
            }

            $mails_left = $this->emailGateway->getMailsLeft($mail['id']);
            if ($mails_left) {
                // throttle to 5 mails per second here to avoid queue bloat
                sleep(2);
            }
            $current = $fs['email'] ?? $this->translator->trans('recipients.unknown');

            return json_encode([
                'left' => $mails_left,
                'status' => 1,
                'comment' => $this->translator->trans('recipients.status', ['{current}' => $current]),
            ]);
        }

        return 0;
    }

    public function xhr_bezirkTree($data)
    {
        $region = $this->regionGateway->getBezirkByParent(
            $data['p'],
            $this->regionPermissions->mayAdministrateRegions()
                || $this->newsletterEmailPermissions->mayAdministrateNewsletterEmail()
        );
        if (!$region) {
            $out = ['status' => 0];
        } else {
            $out = [];
            foreach ($region as $r) {
                $hasChildren = false;
                if ($r['has_children'] == 1) {
                    $hasChildren = true;
                }
                $out[] = [
                    'title' => $r['name'],
                    'isLazy' => $hasChildren,
                    'isFolder' => $hasChildren,
                    'ident' => $r['id'],
                    'type' => $r['type'],
                ];
            }
        }

        return json_encode($out);
    }

    public function xhr_saveBezirk($data)
    {
        $oldRegionData = $this->groupGateway->getGroupLegacy($regionId);

        $mbid = (int)$this->model->qOne('SELECT mailbox_id FROM fs_bezirk WHERE id = ' . $regionId);

        if (strlen($g_data['mailbox_name']) > 1) {
            if ($mbid > 0) {
                $this->model->update('UPDATE fs_mailbox SET name = ' . $this->model->strval($g_data['mailbox_name']) . ' WHERE id = ' . (int)$mbid);
            } else {
                $mbid = $this->model->insert('INSERT INTO fs_mailbox(`name`)VALUES(' . $this->model->strval($g_data['mailbox_name']) . ')');
                $this->model->update('UPDATE fs_bezirk SET mailbox_id = ' . (int)$mbid . ' WHERE id = ' . $regionId);
            }
        }

        $this->sanitizerService->handleTagSelect('botschafter');

        // If the workgroup is moved it loses the old functions.
        // else a region is moved, all workgroups loose their related targets
        if ($oldRegionData['parent_id'] != $parentId) {
            if (UnitType::isGroup($oldRegionData['type'])) {
                if ($oldRegionData['workgroup_function']) {
                    $this->groupFunctionGateway->deleteRegionFunction($regionId, $oldRegionData['workgroup_function']);
                }
            } else {
                $this->groupFunctionGateway->deleteTargetFunctions($regionId);
            }
            $oldRegionData = $this->groupGateway->getGroupLegacy($regionId);
        }

        $this->regionGateway->update_bezirkNew($regionId, $g_data);
    }

    public function xhr_abortEmail($data)
    {
        $mailOwnerId = $this->emailGateway->getOne_send_email($data['id'])['foodsaver_id'];
        if ($this->session->id() == $mailOwnerId) {
            $this->emailGateway->setEmailStatus($data['id'], $mailOwnerId, EmailStatus::STATUS_CANCELED);
        }
    }
}
