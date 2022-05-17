<?php

class StoreUserCest
{
	public function _before(AcceptanceTester $I)
	{
		$this->bezirk_id = $I->createRegion('A region I test with');
		$this->storeCoordinator = $I->createStoreCoordinator(null, ['bezirk_id' => $this->bezirk_id['id']]);
		$I->login($this->storeCoordinator['email']);
	}

	public function SeeTheFetchedQuantity(AcceptanceTester $I)
	{
		$this->store = $I->createStore($this->bezirk_id['id'], null, null, ['abholmenge' => '1']);
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);
		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->see('Abholmenge im Schnitt');
		$I->see('1-3 kg');
	}

	/**
	 * @example[0, "private"]
	 * @example[1, "public"]
	 */
	public function SeeStoreMentioning(AcceptanceTester $I, Codeception\Example $example): void
	{
		$this->store = $I->createStore($this->bezirk_id['id'], null, null, ['presse' => $example[0]]);
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);

		$I->amOnPage($I->storeUrl($this->store['id']));

		$I->see('Namensnennung');
	}
}
