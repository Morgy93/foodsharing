<?php
declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureFlags;

use Flagception\Model\Context;

class FeatureFlagContextGenerator
{

    public function generate(int $foodsaverId): Context
    {
        $context = new Context();

        return $context;
    }
}
