<?php

namespace Foodsharing\Modules\FeatureToggles;

use Foodsharing\Modules\Core\Control;

class FeatureTogglesControl extends Control
{
    public function __construct(
        FeatureTogglesView $view,
    ) {
        $this->view = $view;

        parent::__construct();

        if (!$this->session->mayRole()) {
            $this->routeHelper->goLoginAndExit();
        }
    }

    public function index()
    {
        $this->pageHelper->addContent($this->view->index());
    }
}
