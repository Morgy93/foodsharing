<?php

namespace Foodsharing\Modules\Mailbox;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Permissions\MailboxPermissions;

class MailboxControl extends Control
{
    private MailboxGateway $mailboxGateway;
    private MailboxPermissions $mailboxPermissions;

    public function __construct(
        MailboxView $view,
        MailboxGateway $mailboxGateway,
        MailboxPermissions $mailboxPermissions
    ) {
        $this->view = $view;
        $this->mailboxGateway = $mailboxGateway;
        $this->mailboxPermissions = $mailboxPermissions;

        parent::__construct();

        if (!$this->session->mayRole()) {
            $this->routeHelper->goLoginAndExit();
        }

        if (!$this->mailboxPermissions->mayHaveMailbox()) {
            $this->pageHelper->addContent($this->v_utils->v_info($this->translator->trans('mailbox.not-available', [
                '{role}' => '<a href="https://wiki.foodsharing.de/Betriebsverantwortliche*r">' . $this->translator->trans('terminology.storemanager.d') . '</a>',
                '{quiz}' => '<a href="/?page=settings&sub=up_bip">' . $this->translator->trans('mailbox.sm-quiz') . '</a>',
            ])));
        }
    }

    public function index()
    {
        $this->pageHelper->setContentWidth(8, 16);
        $this->pageHelper->addBread($this->translator->trans('mailbox.title'));

        $boxes = $this->mailboxGateway->getBoxes(
            $this->session->isAmbassador(),
            $this->session->id(),
            $this->session->mayRole(Role::STORE_MANAGER)
        );

        $mailboxIds = array_column($boxes, 'id');
        $this->pageHelper->addContent($this->view->vueComponent('vue-mailbox', 'Mailbox', [
            'hostname' => PLATFORM_MAILBOX_HOST,
            'mailboxes' => $this->mailboxGateway->getMailboxesWithUnreadCount($mailboxIds),
        ]));
    }
}
