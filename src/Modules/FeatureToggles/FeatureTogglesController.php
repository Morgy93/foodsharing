<?php

namespace Foodsharing\Modules\FeatureToggles;

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Development\FeatureToggles\DependencyInjection\FeatureToggleChecker;
use Foodsharing\Modules\Development\FeatureToggles\FeatureToggleDefinitions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeatureTogglesController extends \Foodsharing\Lib\FoodsharingController
{
    public function __construct()
    {
        parent::__construct();
    }

    #[Route('featuretoggles/')]
    public function index(FeatureToggleChecker $featureToggleChecker): Response
    {
        $this->pageHelper->addTitle('FeatureToggle Management');

        if (!$this->session->mayRole(Role::ORGA)) {
            $this->routeHelper->goLoginAndExit();
        }

        if ($featureToggleChecker->isFeatureToggleActive(FeatureToggleDefinitions::SHOW_FEATURE_TOGGLE_VUE_PAGE)) {
            $featureTogglePage = $this->prepareVueComponent('vue-feature-toggles', 'FeatureToggles');
            $this->pageHelper->addContent($featureTogglePage);

            return $this->renderGlobal();
        }

        $this->routeHelper->goLoginAndExit();
    }
}
