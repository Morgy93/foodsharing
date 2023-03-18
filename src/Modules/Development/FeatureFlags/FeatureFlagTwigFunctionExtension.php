<?php

declare(strict_types=1);

namespace Foodsharing\Modules\Development\FeatureFlags;

use Foodsharing\Modules\Development\FeatureFlags\DependencyInjection\FeatureFlagChecker;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FeatureFlagTwigFunctionExtension extends AbstractExtension
{
    public function __construct(
        private readonly FeatureFlagChecker $featureFlagChecker,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isFeatureFlagActive', function (string $featureFlagIdentifier): bool {
                return $this->featureFlagChecker->isFeatureFlagActive($featureFlagIdentifier);
            })
        ];
    }
}
