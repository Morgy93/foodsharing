<?php

namespace Foodsharing\Modules\Development\FeatureToggles\DTO;

final class FeatureToggleDto
{
    public function __construct(
        public readonly string $identifier,
        public readonly bool $isActive,
    ) {
    }
}
