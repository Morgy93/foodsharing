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
		$I->executeJS('window.localStorage.clear();');
		$I->waitForElement('.testing-login-dropdown');
		$I->click('.testing-login-dropdown');
		$I->fillField('.testing-login-input-email', $this->foodsaver['email']);
		$I->fillField('.testing-login-input-password', $this->pass);
		$I->click('.testing-login-click-submit');
		$I->waitForActiveAPICalls();
		$I->waitForElementNotVisible('#pulse-success');
		$I->waitForPageBody();
		$I->waitForElement('.testing-intro-field');
		$I->see('Hallo ' . $this->foodsaver['name'], '.testing-intro-field');
		$I->seeCookieHasSessionExpiry('PHPSESSID');

		$I = $this;
	}

	public function testRememberLogin(AcceptanceTester $I)
	{
		$I->amOnPage('/');
		$I->executeJS('window.localStorage.clear();');
		$I->waitForElement('.testing-login-dropdown');
		$I->click('.testing-login-dropdown');
		$I->fillField('.testing-login-input-email', $this->foodsaver['email']);
		$I->fillField('.testing-login-input-password', $this->pass);
		$I->seeInField('.testing-login-input-remember', false);
		$I->click('.testing-login-input-remember');
		$I->seeInField('.testing-login-input-remember', true);
		$I->click('.testing-login-click-submit');
		$I->waitForActiveAPICalls();
		$I->waitForElementNotVisible('#pulse-success');
		$I->waitForPageBody();
		$I->waitForElement('.testing-intro-field');
		$I->see('Hallo ' . $this->foodsaver['name'], '.testing-intro-field');
		$I->seeCookieHasNoSessionExpiry('PHPSESSID');

		$I->amOnPage('/?page=logout');

		$I->amOnPage('/');
		$I->click('.testing-login-dropdown');
		$I->fillField('.testing-login-input-email', $this->foodsaver['email']);
		$I->seeInField('.testing-login-input-remember', true);
	}
}
