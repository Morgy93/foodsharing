<?php

namespace Foodsharing\Modules\Logout;

use Foodsharing\Modules\Core\Control;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LogoutControl extends Control
{
    private const PRIVATE_PAGES = [
            'betrieb',
            'bezirk',
            'event',
            'foodsaver',
            'fsbetrieb',
            'groups',
            'mailbox',
            'message',
            'quiz',
            'report',
            'settings',
            'store',
        ];

    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request, Response $response)
    {
        $refURI = $request->query->get('ref') ?? '/';
        $page = [];

        $isPregMatchResult = preg_match('~(?s)(?<=\/\?page\=).\w+~i', $refURI, $page);

        if ($isPregMatchResult && in_array($page[0], self::PRIVATE_PAGES)) {
            $refURI = '/';
        }

        $this->session->logout();
        header('Location: ' . $refURI);
        exit;
    }
}
