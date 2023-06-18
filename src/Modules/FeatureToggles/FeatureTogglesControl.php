<?php

namespace Foodsharing\Modules\FeatureToggles;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Development\FeatureToggles\DependencyInjection\FeatureToggleChecker;
use Foodsharing\Modules\Development\FeatureToggles\FeatureToggleDefinitions;

class FeatureTogglesControl extends Control
{
    public function __construct(
        FeatureTogglesView $view,
        private readonly FeatureToggleChecker $featureToggleChecker,
    ) {
        $this->view = $view;

        parent::__construct();

        if (!$this->session->mayRole()) {
            $this->routeHelper->goLoginAndExit();
        }
    }

    public function index()
    {
        if (!$this->featureToggleChecker->isFeatureToggleActive(FeatureToggleDefinitions::SHOW_FEATURE_TOGGLE_VUE_PAGE)) {
            $this->routeHelper->goLoginAndExit();
        }

        $this->pageHelper->addContent($this->view->index());
    }
}
