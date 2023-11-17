<?php

namespace Foodsharing\Modules\StoreUser;

use Foodsharing\Modules\Core\Control;
use Symfony\Component\HttpFoundation\Request;

// forward old links to the new path
class StoreUserControl extends Control
{
    public function index(Request $request): void
    {
        if (!$this->session->mayRole()) {
            $this->routeHelper->goLoginAndExit();
        }
        if ($id = $request->query->get('id')) {
            $this->routeHelper->goAndExit('/store/' . intval($id));
        }
    }
}
