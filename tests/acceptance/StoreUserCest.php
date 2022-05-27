<?php

class StoreUserCest
{
	public function _before(AcceptanceTester $I)
	{
		$this->bezirk_id = $I->createRegion('A region I test with');
		$this->storeCoordinator = $I->createStoreCoordinator(null, ['bezirk_id' => $this->bezirk_id['id']]);
		$I->login($this->storeCoordinator['email']);
	}

	/**
	 * @example{ "value": 1, "name": "1-3 kg"}
	 * @example{ "value": 2, "name": "3-5 kg"}
	 * @example{ "value": 3, "name": "5-10 kg"}
	 * @example{ "value": 4, "name": "10-20 kg"}
	 * @example{ "value": 5, "name": "20-30 kg"}
	 * @example{ "value": 6, "name": "30-40 kg"}
	 * @example{ "value": 7, "name": "40-50 kg"}
	 * @example{ "value": 8, "name": "50-75 kg"}
	 * @example{ "value": 9, "name": "75-100 kg"}
	 * @example{ "value":10, "name": "mehr als 100 kg"}
	 */
	public function SeeTheFetchedQuantity(AcceptanceTester $I, Codeception\Example $example, Codeception\Example $examplebla)
	{
		$this->store = $I->createStore($this->bezirk_id['id'], null, null, ['abholmenge' => $example['value']]);
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);
		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->see('Abholmenge im Schnitt');
		$I->see($example['name']);
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
