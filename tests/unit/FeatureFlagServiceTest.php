<?php

namespace Foodsharing\unit;

use Codeception\Test\Unit;
use Foodsharing\Modules\Development\FeatureFlags\DependencyInjection\FeatureFlagChecker;
use Foodsharing\Modules\Development\FeatureFlags\FeatureFlagService;
use UnitTester;

class FeatureFlagServiceTest extends Unit
{
    protected readonly UnitTester $tester;
    private readonly FeatureFlagChecker $featureFlagService;

    protected function _before(): void
    {
        $this->featureFlagService = $this->tester->get(FeatureFlagService::class);
    }

    public function testIsFeatureFlagActiveFeatureIsActiveTrue()
    {
        $featureFlagState = $this->featureFlagService->isFeatureFlagActive('always_true_for_testing_purposes');
        $this->assertTrue($featureFlagState);
    }

    public function testIsFeatureFlagActiveFeatureIsNotActiveTrue()
    {
        $featureFlagState = $this->featureFlagService->isFeatureFlagActive('always_false_for_testing_purposes');
        $this->assertFalse($featureFlagState);
    }
}
