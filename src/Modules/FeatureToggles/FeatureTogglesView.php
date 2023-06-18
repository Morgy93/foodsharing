<?php

namespace Foodsharing\Modules\FeatureToggles;

use Foodsharing\Modules\Core\View;

class FeatureTogglesView extends View
{
    public function index(): string
    {
        return $this->vueComponent('vue-feature-toggles', 'FeatureToggles');
    }
}
