<?php

declare(strict_types=1);

namespace Tests\Unit;

use Codeception\Test\Unit;
use Foodsharing\Modules\Report\ReportGateway;
use Tests\Support\UnitTester;

class ReportGatewayTest extends Unit
{
    protected UnitTester $tester;
    protected ReportGateway $gateway;
    protected $region;
    protected $childRegion;
    protected $childChildRegion;

    final public function _before(): void
    {
        $this->gateway = $this->tester->get(ReportGateway::class);
        $this->region = $this->tester->createRegion('Computer');
        $this->childRegion = $this->tester->createRegion('Motherboard', ['parent_id' => $this->region['id']]);
        $this->childChildRegion = $this->tester->createRegion('CPU', ['parent_id' => $this->childRegion['id']]);
    }
}
