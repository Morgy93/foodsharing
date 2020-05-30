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
			$I->see('Texte', '.card-header');
			$I->see('Name des Betriebs');
			$I->see('(max. 180 Zeichen)', '.alert');
			$I->dontSee('Stammbezirk');
			$I->click('Abholung', '.card-header');
			$I->see('mittags/nachmittags');
			$I->click('Kooperation', '.card-header');
			$I->see('Überzeugungsarbeit');
			$I->click('Betrieb', '.card-header');
			// TODO the text is there, this should be found:
			// $I->see('Nur kooperationswillige Betriebe', '.alert');
			$I->see('kooperiert bereits');
			$I->click('Standort', '.card-header');
			$I->see('Stammbezirk', 'form');
			// The button is there, should be found:
			// $I->see('Senden');
			$I->see('Postleitzahl', 'form');
		} else {
			$I->dontSee('Name des Betriebs');
			$I->dontSee('180 Zeichen', '.alert');
			$I->dontSee('Postleitzahl');
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
