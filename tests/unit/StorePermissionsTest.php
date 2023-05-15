<?php

namespace Foodsharing\unit;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DatabaseNoValueFoundException;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Permissions\StorePermissions;
use PHPUnit\Framework\MockObject\MockObject;
use UnitTester;

final class StorePermissionsTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;
    protected MockObject $sessionMock;
    protected MockObject $regionGatewayMock;
    protected StorePermissions $storePermissions;

    protected function _before()
    {
        $this->sessionMock = $this->createMock(Session::class);
        $this->regionGatewayMock = $this->createMock(RegionGateway::class);
        $this->storePermissions = new StorePermissions($this->tester->get(StoreGateway::class), $this->sessionMock, $this->tester->get(GroupFunctionGateway::class), $this->tester->get(ProfilePermissions::class), $this->regionGatewayMock);
    }

    public function testListStoresLoadUserIdFromSession()
    {
        $this->sessionMock->expects($this->once())->method('id')->will($this->returnValue(10));
        $this->storePermissions->mayListStores();
    }

    public function testListStoresLoadUserIdFromSessionNoUserId()
    {
        $this->sessionMock->expects($this->once())->method('id')->will($this->returnValue(null));
        $this->assertFalse($this->storePermissions->mayListStores());
    }

    public function testListStoresForFoodSaverAndHigherIndependentFromVerificationStatus()
    {
        $this->sessionMock->expects($this->once())->method('mayRole')->with(Role::FOODSAVER)->will($this->returnValue(true));
        $this->assertTrue($this->storePermissions->mayListStores(1));
    }

    public function testListStoresForFoodSharer()
    {
        $this->sessionMock->expects($this->once())->method('mayRole')->with(Role::FOODSAVER)->will($this->returnValue(false));
        $this->assertFalse($this->storePermissions->mayListStores(1));
    }

    public function testCreatePermissionForFoodSharer()
    {
        $this->sessionMock->method('mayRole')->withConsecutive(
            [Role::ORGA], [Role::STORE_MANAGER]
        )->willReturnOnConsecutiveCalls(false, false);
        $this->assertFalse($this->storePermissions->mayCreateStore(1));
    }

    public function testCreatePermissionForStoreManagerRegionIndependent()
    {
        $this->sessionMock->method('mayRole')
            ->withConsecutive([Role::ORGA], [Role::STORE_MANAGER])
            ->willReturnOnConsecutiveCalls(false, true);
        $this->assertTrue($this->storePermissions->mayCreateStore());
    }

    public function testCreatePermissionForStoreManagerOfRegion()
    {
        $this->sessionMock->expects($this->once())->method('id')->will($this->returnValue(123));
        $this->sessionMock->method('mayRole')
            ->withConsecutive([Role::ORGA], [Role::STORE_MANAGER])
            ->willReturnOnConsecutiveCalls(false, true);
        $this->regionGatewayMock->method('hasMember')->with(123, 1)->will($this->returnValue(true));
        $this->assertTrue($this->storePermissions->mayCreateStore(1));
    }

    public function testCreatePermissionForStoreManagerOfOtherRegion()
    {
        $this->sessionMock->expects($this->once())->method('id')->will($this->returnValue(123));
        $this->sessionMock->method('mayRole')
            ->withConsecutive([Role::ORGA], [Role::STORE_MANAGER])
            ->willReturnOnConsecutiveCalls(false, true);
        $this->regionGatewayMock->method('hasMember')->with(123, 1)->will($this->returnValue(false));
        $this->assertFalse($this->storePermissions->mayCreateStore(1));
    }

    public function testCreatePermissionForStoreManagerOfInvalidRegion()
    {
        $this->sessionMock->expects($this->once())->method('id')->will($this->returnValue(123));
        $this->sessionMock->method('mayRole')
            ->withConsecutive([Role::ORGA], [Role::STORE_MANAGER])
            ->willReturnOnConsecutiveCalls(false, true);
        $this->regionGatewayMock->method('hasMember')->with(123, 1234)->will($this->throwException(new DatabaseNoValueFoundException()));
        $this->assertFalse($this->storePermissions->mayCreateStore(1234));
    }

    public function testCreatePermissionForOrga()
    {
        $this->sessionMock->expects($this->once())->method('mayRole')->with(Role::ORGA)->will($this->returnValue(true));
        $this->assertTrue($this->storePermissions->mayCreateStore(1));
    }
}
