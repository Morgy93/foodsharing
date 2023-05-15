<?php

namespace Foodsharing\Modules\Relogin;

use Foodsharing\Modules\Core\Control;

class ReloginControl extends Control
{
    public function index()
    {
        try {
            $this->session->refreshFromDatabase();

            if (isset($_GET['url']) && !empty($_GET['url'])) {
                $url = urldecode($_GET['url']);
                if (substr($url, 0, 4) !== 'http') {
                    $this->routeHelper->goAndExit($url);
                }
            }
            $this->routeHelper->goAndExit('/?page=dashboard');
        } catch (\Exception $e) {
            $this->routeHelper->goPageAndExit('logout');
        }
    }
}
