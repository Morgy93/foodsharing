<?php

class StoreUserCest
{
	public function _before(HtmlAcceptanceTester $I)
	{
		$this->bezirk_id = $I->createRegion('A region I test with');
		$this->storeCoordinator = $I->createStoreCoordinator(null, ['bezirk_id' => $this->bezirk_id['id']]);
		$I->login($this->storeCoordinator['email']);
	}

	/**
	 * @param HtmlAcceptanceTester $I
	 * @param \Codeception\Example $example
	 * @example[1, "1-3 kg"]
	 * @example[2, "3-5 kg"]
	 * @example[3, "5-10 kg"]
	 * @example[4, "10-20 kg"]
	 * @example[5, "20-30 kg"]
	 * @example[6, "40-50 kg"]
	 * @example[7, "mehr als 50 kg"]
	 */
	public function SeeTheFetchedQuantity(HtmlAcceptanceTester $I, \Codeception\Example $example)
	{
		$this->store = $I->createStore($this->bezirk_id['id'], null, null, ['abholmenge' => $example[0]]);
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);

		$I->amOnPage($I->storeUrl($this->store['id']));

		$I->see('Abholmenge im Schnitt');
		$I->see($example[1]);
	}

	/**
	 * @param HtmlAcceptanceTester $I
	 * @param \Codeception\Example $example
	 * @example[0, "public"]
	 * @example[1, "private"]
	 */
	public function SeeStoreMentioning(HtmlAcceptanceTester $I, \Codeception\Example $example)
	{
		$this->store = $I->createStore($this->bezirk_id['id'], null, null, ['presse' => $example[0]]);
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);

		$I->amOnPage($I->storeUrl($this->store['id']));

		$I->see('Namensnennung');
	}
}
