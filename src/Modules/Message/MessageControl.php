<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Modules\Core\Control;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MessageControl extends Control
{
    public function __construct(
        MessageView $view
    ) {
        $this->view = $view;

        parent::__construct();

        if (!$this->session->mayRole()) {
            $this->routeHelper->goLoginAndExit();
        }
    }

    public function index(Request $request, Response $response): void
    {
        $this->pageHelper->addContent($this->view->index());

        $response->setContent($this->render('layouts/msg.twig'));
    }
}
