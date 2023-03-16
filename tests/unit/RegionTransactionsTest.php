<?php

use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Region\RegionTransactions;
use Foodsharing\Modules\Unit\DTO\UserUnit;
use Foodsharing\Modules\Unit\UnitGateway;
use PHPUnit\Framework\TestCase;

class RegionTransactionsTest extends TestCase
{
    private RegionTransactions $regionTransactions;

    private FoodsaverGateway $foodsaverGateway;
    private UnitGateway $unitGateway;
    private RegionGateway $regionGateway;

    protected function setUp(): void
    {
        $this->foodsaverGateway = $this->createMock(FoodsaverGateway::class);
        $this->unitGateway = $this->createMock(UnitGateway::class);
        $this->regionGateway = $this->createMock(RegionGateway::class);
        $this->regionTransactions = new RegionTransactions($this->foodsaverGateway, $this->unitGateway, $this->regionGateway);
    }

    public function testListFoodsaversRegionsEmpty()
    {
        $this->unitGateway->method('listAllDirectReleatedUnitsAndResponsibilitiesOfFoodsaver')->with($this->equalTo(1), UnitType::getRegionTypes())->willReturn([]);
        $this->assertEquals(
            [],
            $this->regionTransactions->getUserRegions(1)
        );
    }

    public function testListFoodsaversRegionsElement()
    {
        $units = [new UserUnit()];
        $this->unitGateway->method('listAllDirectReleatedUnitsAndResponsibilitiesOfFoodsaver')->with($this->equalTo(2), UnitType::getRegionTypes())->willReturn($units);
        $this->assertEquals(
            $units,
            $this->regionTransactions->getUserRegions(2)
        );
    }

    public function testNewFoodsaverVerified()
    {
        $this->assertSame(
            RegionTransactions::NEW_FOODSAVER_VERIFIED,
            $this->regionTransactions->getJoinMessage(['verified' => 1, 'id' => 1])
        );
    }

    public function testNewFoodsaverNeedsVerification()
    {
        $this->foodsaverGateway->method('foodsaverWasVerifiedBefore')->willReturn(true);
        $this->assertSame(
            RegionTransactions::NEW_FOODSAVER_NEEDS_VERIFICATION,
            $this->regionTransactions->getJoinMessage(['verified' => 0, 'id' => 1])
        );
    }

    public function testNewFoodsaverNeedsIntroduction()
    {
        $this->foodsaverGateway->method('foodsaverWasVerifiedBefore')->willReturn(false);
        $this->assertSame(
            RegionTransactions::NEW_FOODSAVER_NEEDS_INTRODUCTION,
            $this->regionTransactions->getJoinMessage(['verified' => 0, 'id' => 1])
        );
    }

    public function testInvalidUserData()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid user data. Id not set.');

        $this->regionTransactions->getJoinMessage([]);
    }
}
