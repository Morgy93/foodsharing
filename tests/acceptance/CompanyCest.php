<?php

class CompanyCest
{
	private $bezirk;
	private $bezirk_id;

	private function createStoreAndUsers()
	{
		$I = $this->tester;
		$this->bezirk = $this->tester->createRegion();
		$this->bezirk_id = $this->bezirk['id'];
		$this->store = $I->createStore($this->bezirk_id);
		$this->storeCoordinator = $I->createStoreCoordinator(null, ['bezirk_id' => $this->bezirk_id]);
		$this->participatorA = $I->createFoodsaver(null, ['bezirk_id' => $this->bezirk_id]);
		$this->participatorB = $I->createFoodsaver(null, ['bezirk_id' => $this->bezirk_id]);
		$this->sameRegionFoodsaver = $I->createFoodsaver(null, ['bezirk_id' => $this->bezirk_id]);
		$this->unconnectedFoodsaver = $I->createFoodsaver();
		$this->unconnectedFoodsharer = $I->createFoodsharer();
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);
		$I->addStoreTeam($this->store['id'], $this->participatorA['id']);
		$I->addStoreTeam($this->store['id'], $this->participatorB['id']);
	}

	private function loginAsCoordinator()
	{
		$I = $this->tester;
		$I->login($this->storeCoordinator['email']);
	}

	private function loginAsMember()
	{
		$I = $this->tester;
		$I->login($this->participatorA['email']);
	}

	private function loginAsFoodsharer()
	{
		$I = $this->tester;
		$I->login($this->unconnectedFoodsharer['email']);
	}

	private function loginAsUnconnectedFoodsaver()
	{
		$I = $this->tester;
		$I->login($this->unconnectedFoodsaver['email']);
	}

	private function loginAsSameRegionFoodsaver()
	{
		$I = $this->tester;
		$I->login($this->sameRegionFoodsaver['email']);
	}

	/**
	 * @example["loginAsCoordinator", true]
	 * @example["loginAsMember", false]
	 */
	public function CoordinatorCanSeeCompanyOnDashboard(\AcceptanceTester $I, \Codeception\Example $example)
	{
		$canManage = $example[1];
		call_user_func([$this, $example[0]]);
		if ($canManage) {
			$I->see('Du bist verantwortlich', 'div.head.ui-widget-header.ui-corner-top');
		} else {
			$I->see('Du holst Lebensmittel ab bei', 'div.head.ui-widget-header.ui-corner-top');
		}
		$I->see($this->store['name'], 'a.ui-corner-all');
	}

	/**
	 * @param AcceptanceTester $I
	 * @example["loginAsCoordinator", true]
	 * @example["loginAsMember", true]
	 * @example["loginAsFoodsharer", false]
	 * @example["loginAsUnconnectedFoodsaver", false]
	 * @example["loginAsSameRegionFoodsaver", false]
	 */
	public function CanAccessCompanyPage(\AcceptanceTester $I, \Codeception\Example $example)
	{
		$canAccess = $example[1];
		call_user_func([$this, $example[0]]);
		$I->amOnPage($I->storeUrl($this->store['id']));
		if ($canAccess) {
			$I->see($this->store['name'] . '-Team', 'div.head.ui-widget-header.ui-corner-top');
		} else {
			$I->cantSeeInCurrentUrl('fsbetrieb');
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @example["loginAsCoordinator", true]
	 * @example["loginAsMember", false]
	 */
	public function CanAccessCompanyEditPage(\AcceptanceTester $I, \Codeception\Example $example)
	{
		$canAccess = $example[1];
		call_user_func([$this, $example[0]]);
		$I->amOnPage($I->storeEditUrl($this->store['id']));
		if ($canAccess) {
			$I->see('Stammbezirk');
			$I->see('unabgesprochen', '.alert');
			$I->click('Kooperation', '.store-edit ul.nav');
			$I->see('Überzeugungsarbeit');
		} else {
			$I->dontSee('Stammbezirk');
			$I->dontSee('unabgesprochen', '.alert');
		}
	}

	public function _before(AcceptanceTester $I)
	{
		$this->tester = $I;
		$this->createStoreAndUsers();
	}

	public function _after(AcceptanceTester $I)
	{
	}
}
