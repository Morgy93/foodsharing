<?php

namespace Foodsharing\Modules\Development\FeatureToggles;

use Flagception\Activator\FeatureActivatorInterface;
use Flagception\Model\Context;
use Foodsharing\Modules\Development\FeatureToggles\Querys\GetFeatureToggleStateQuery;

class DatabaseActivator implements FeatureActivatorInterface
{
    public function __construct(
        private readonly GetFeatureToggleStateQuery $featureToggleStateQuery,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'database-activator';
    }

    /**
     * @inheritDoc
     */
    public function isActive($name, Context $context): bool
    {
        $featureToggleIdentifier = $name;
        return $this->featureToggleStateQuery->execute($featureToggleIdentifier);
    }
}
