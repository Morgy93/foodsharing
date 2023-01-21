<?php

namespace Foodsharing\Modules\Profile;

use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Bell\BellType;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Permissions\ReportPermissions;

class ProfileXhr extends Control
{
    private $foodsaver;
    private BellGateway $bellGateway;
    private MailboxGateway $mailboxGateway;
    private ProfileGateway $profileGateway;
    private ReportPermissions $reportPermissions;
    private ProfilePermissions $profilePermissions;

    public function __construct(
        ProfileView $view,
        BellGateway $bellGateway,
        MailboxGateway $mailboxGateway,
        ProfileGateway $profileGateway,
        ReportPermissions $reportPermissions,
        ProfilePermissions $profilePermissions
    ) {
        $this->view = $view;
        $this->bellGateway = $bellGateway;
        $this->mailboxGateway = $mailboxGateway;
        $this->profileGateway = $profileGateway;
        $this->reportPermissions = $reportPermissions;
        $this->profilePermissions = $profilePermissions;

        parent::__construct();

        if (isset($_GET['id'])) {
            $fs = $this->profileGateway->getData($_GET['id'], $this->session->id(), $this->reportPermissions->mayHandleReports());

            if (isset($fs['id'])) {
                $this->foodsaver = $fs;
                $this->foodsaver['mailbox'] = false;
                if ((int)$fs['mailbox_id'] > 0 && $this->profilePermissions->maySeeEmailAddress($fs['id'])) {
                    $this->foodsaver['mailbox'] = $this->mailboxGateway->getMailboxname(
                        $fs['mailbox_id']
                    ) . '@' . PLATFORM_MAILBOX_HOST;
                }

                $this->foodsaver['buddy'] = $this->profileGateway->buddyStatus($this->foodsaver['id'], $this->session->id());

                $this->view->setData($this->foodsaver);
            } else {
                $this->bellGateway->delBellsByIdentifier(BellType::createIdentifier(BellType::NEW_FOODSAVER_IN_REGION, (int)$_GET['id']));
            }
        }
    }

    public function history(): array
    {
        if ($this->profilePermissions->maySeeHistory($_GET['fsid'])) {
            $dia = new XhrDialog();
            if ($_GET['type'] == 0) {
                $history = $this->profileGateway->getVerifyHistory($_GET['fsid']);
                $dia->setTitle($this->translator->trans('profile.nav.verificationHistory'));
                $dia->addContent($this->view->getHistory($history, $_GET['type']));
            }
            if ($_GET['type'] == 1) {
                $history = $this->profileGateway->getPassHistory($_GET['fsid']);
                $dia->setTitle($this->translator->trans('profile.nav.history'));
                $dia->addContent($this->view->getHistory($history, $_GET['type']));
            }
            $dia->noOverflow();

            return $dia->xhrout();
        }

        return [];
    }
}
