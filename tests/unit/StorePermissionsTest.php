<?php

namespace Foodsharing\unit;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Permissions\StorePermissions;
use PHPUnit\Framework\MockObject\MockObject;
use UnitTester;

final class StorePermissionsTest extends \Codeception\Test\Unit
{
	protected UnitTester $tester;
	protected MockObject $sessionMock;
	protected StorePermissions $storePermissions;

	protected function _before()
	{
		$this->sessionMock = $this->createMock(Session::class);
		$this->storePermissions = new StorePermissions($this->tester->get(StoreGateway::class), $this->sessionMock, $this->tester->get(GroupFunctionGateway::class), $this->tester->get(ProfilePermissions::class));
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
}
