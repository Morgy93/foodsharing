<?php

class LoginCest
{
	private $password;
	private $foodsaver;

	public function _before(AcceptanceTester $I)
	{
		$this->pass = sq('pass');
		$this->foodsaver = $I->createFoodsharer($this->pass);
	}

	public function testLogin(AcceptanceTester $I)
	{
		$I->wantTo('ensure you can login');
		$I->amOnPage('/');
		$I->click('#login');
		$I->waitForElement('#login-email');
		$I->fillField('#login-email', $this->foodsaver['email']);
		$I->fillField('#login-password', $this->pass);
		$I->click('#login-btn');
		$I->waitForActiveAPICalls();
		$I->waitForElementNotVisible('#pulse-success');
		$I->waitForPageBody();
		$I->waitForText('Hallo ' . $this->foodsaver['name'] . '!');
		$I->seeCookieHasSessionExpiry('PHPSESSID');
	}

	public function testRememberLogin(AcceptanceTester $I)
	{
		$I->wantTo('ensure you can login and be remembered');
		$I->amOnPage('/');
		$I->click('#login');
		$I->waitForElement('#login-email');
		$I->fillField('#login-email', $this->foodsaver['email']);
		$I->fillField('#login-password', $this->pass);
		$I->seeInField('#login-rememberme', false);
		$I->click('.login-rememberme');
		$I->seeInField('#login-rememberme', true);
		$I->click('#login-btn');
		$I->waitForActiveAPICalls();
		$I->seeCookieHasNoSessionExpiry('PHPSESSID');

		$I->amOnPage('/?page=logout');

		$I->amOnPage('/');
		$I->click('#login');
		$I->waitForElement('#login-email');
		$I->seeInField('#login-rememberme', true);
	}
}
