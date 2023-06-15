<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureToggles;

use Foodsharing\Modules\Development\FeatureToggles\DependencyInjection\FeatureToggleChecker;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FeatureToggleTwigFunctionExtension extends AbstractExtension
{
    public function __construct(
        private readonly FeatureToggleChecker $featureToggleChecker,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isFeatureToggleActive', function (string $featureToggleIdentifier): bool {
                return $this->featureToggleChecker->isFeatureToggleActive($featureToggleIdentifier);
            })
        ];
    }
}
