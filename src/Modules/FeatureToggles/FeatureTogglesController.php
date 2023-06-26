<?php

namespace Foodsharing\Modules\FeatureToggles;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeatureTogglesController extends \Foodsharing\Lib\FoodsharingController
{
    public function __construct()
    {
        parent::__construct();
    }

    #[Route('featuretoggles/')]
    public function index(): Response
    {
        $featureTogglePage = $this->prepareVueComponent('vue-feature-toggles', 'FeatureToggles');
        $this->pageHelper->addContent($featureTogglePage);
        return $this->renderGlobal();
    }
}
