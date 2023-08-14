<?php

namespace Foodsharing\Modules\StoreUser;

use Exception;
use Foodsharing\Modules\Core\Control;

class StoreUserControl extends Control
{
    public function __construct(
        StoreUserView $view,
    ) {
        $this->view = $view;

        parent::__construct();

        if (!$this->session->mayRole()) {
            $this->routeHelper->goLoginAndExit();
        }
    }

    /**
     * @throws Exception
     */
    public function index(): void
    {
        if (isset($_GET['id'])) {
            $storeId = intval($_GET['id']);
            $this->routeHelper->goAndExit('/store/' . $storeId);
        }
    }
}
