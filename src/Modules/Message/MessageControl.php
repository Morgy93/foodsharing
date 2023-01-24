<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Modules\Core\Control;

final class MessageControl extends Control
{
    public function __construct(
        MessageView $view
    ) {
        $this->view = $view;

        parent::__construct();

        if (!$this->session->mayRole()) {
            $this->routeHelper->goLogin();
        }
    }

    public function index(): void
    {
        $this->setTemplate('msg');

        $this->pageHelper->addContent($this->view->index(), CNT_MAIN);

        return;
    }
}
