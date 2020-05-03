<?php

class StoreCest
{
	private $bezirk_id = 241;

	private function createStoreAndUsers()
	{
		$I = $this->tester;
		$this->store = $I->createStore($this->bezirk_id);
		$this->storeCoordinator = $I->createStoreCoordinator(null, ['bezirk_id' => $this->bezirk_id]);
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);
	}

	private function loginAsCoordinator()
	{
		$I = $this->tester;
		$I->login($this->storeCoordinator['email']);
	}

	public function WillKeepApproxPickupTime(\AcceptanceTester $I)
	{
		$this->loginAsCoordinator();

		// Check original value
		$I->amOnPage('/?page=betrieb&a=edit&id=' . $this->store['id']);
		$I->click('Abholung', '.store-edit ul.nav');
		$I->waitForText('Keine Angabe', 5, '.store-time');

		// TODO vue-B selected / selectOption
		// document.getElementById('id').selectedOptions might work but where's the document?
		// $I->selectOption('.store-time select', 'morgens');
		// //$I->dontSee('abends', '.store-time');

		// Change option and save the page by navigating away
		$I->selectOption('.store-time select', 'morgens');
		$I->click('Texte', '.store-edit ul.nav');

		// Reload + check the page again, to make sure our option was saved
		$I->amOnPage('/?page=betrieb&a=edit&id=' . $this->store['id']);
		$I->click('Abholung', '.store-edit ul.nav');
		$I->waitForText('morgens', 5, '.store-time');
	}

	public function _before(AcceptanceTester $I)
	{
		$this->tester = $I;
		$this->createStoreAndUsers();
	}
}
