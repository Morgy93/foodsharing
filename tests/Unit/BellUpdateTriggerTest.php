<?php

declare(strict_types=1);

namespace Tests\Unit;

use Codeception\Test\Unit;
use Foodsharing\Modules\Bell\BellUpdaterInterface;
use Foodsharing\Modules\Bell\BellUpdateTrigger;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Support\UnitTester;

class BellUpdateTriggerTest extends Unit
{
    protected UnitTester $tester;
    private BellUpdateTrigger $bellUpdateTrigger;

    public function _before()
    {
        $this->bellUpdateTrigger = $this->tester->get(BellUpdateTrigger::class);
    }

    protected function _after()
    {
    }

    // tests
    public function testBellUpdateGetsTriggered(): void
    {
        /**
         * @var BellUpdaterInterface|MockObject
         */
        $bellUpdater = $this->getMockBuilder(BellUpdaterInterface::class)->getMock();
        $bellUpdater->expects($this->once())->method('updateExpiredBells');

        $this->bellUpdateTrigger->subscribe($bellUpdater);
        $this->bellUpdateTrigger->triggerUpdate();
    }
}
