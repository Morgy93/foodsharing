<?php

declare(strict_types=1);

namespace Tests\Unit;

use Codeception\Test\Unit;
use Foodsharing\Lib\Routing;
use Tests\Support\UnitTester;

class RouterReturnsCorrectClassNameTest extends Unit
{
    protected UnitTester $tester;

    // tests
    final public function testReturnNullOnInvalidAppName(): void
    {
        $this->assertNull(Routing::getClassName('IAmaSurelyNotExistingApp'));
    }

    final public function testReturnFqcnForControlClass(): void
    {
        $actual = Routing::getClassName('settings', 'Control');
        $this->assertEquals('Foodsharing\\Modules\\Settings\\SettingsControl', $actual);
    }

    final public function testReturnFqcnForXhrClass(): void
    {
        $actual = Routing::getClassName('settings', 'Xhr');
        $this->assertEquals('Foodsharing\\Modules\\Settings\\SettingsXhr', $actual);
    }
}
