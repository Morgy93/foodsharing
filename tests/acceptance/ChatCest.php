<?php

class ChatCest
{
	private $foodsaver1;
	private $foodsaver2;

	public function _before(AcceptanceTester $I)
	{
		$this->createUsers($I);
	}

	public function _after(AcceptanceTester $I)
	{
	}

	private function createUsers(AcceptanceTester $I)
	{
		$this->foodsaver1 = $I->createFoodsaver(null);
		$this->foodsaver2 = $I->createFoodsaver(null);
	}

	/**
	 * @skip The test has been disabled because it fails without a traceable reason
	 */
	public function CanSendAndReceiveChatMessages(AcceptanceTester $I)
	{
		// Activate chat notifications by mail
		$I->login($this->foodsaver2['email']);

		$I->amOnPage('/?page=settings&sub=info');
		$I->selectOption('form input[name=infomail_message]', '1');
		$I->click('Speichern');
		$I->see('Änderungen wurden gespeichert.');
		$I->seeInDatabase('fs_foodsaver', [
			'id' => $this->foodsaver2['id'],
			'infomail_message' => '1'
		]);
		$I->logMeOut();

		$I->login($this->foodsaver1['email']);

		// view the other users profile and start a chat
		$I->amOnPage('/profile/' . $this->foodsaver2['id']);
		$I->click('Nachricht schreiben');
		$I->waitForElementVisible('.chatboxtextarea', 15);

		// write a message to them
		$I->fillField('.chatboxtextarea', 'is anyone there?');
		$I->pressKey('.chatboxtextarea', Facebook\WebDriver\WebDriverKeys::ENTER);
		$I->waitForText('is anyone there?', 20, '.chatboxcontent');

		$I->seeInDatabase('fs_msg', [
			'foodsaver_id' => $this->foodsaver1['id'],
			'body' => 'is anyone there?'
		]);

		$I->expectNumMails(1, 10);
		$mail = $I->getMails()[0];
		$I->assertStringContainsString('is anyone there?', $mail->text);
		$I->assertStringContainsString($this->foodsaver1['name'], $mail->subject);

		$matthias = $I->haveFriend('matthias');
		$matthias->does(function (AcceptanceTester $I) {
			$I->login($this->foodsaver2['email']);
			$I->amOnPage('/');

			$I->waitForActiveAPICalls();
			// check they have the nice little notification badge
			$I->see('1', '.topbar-messages .badge');

			// open the conversation menu and open the new conversation
			$I->click('.topbar-messages > a');
			$I->waitForElementVisible('.topbar-messages .list-group-item-warning', 4);
			$I->click('.topbar-messages .list-group-item-warning');
			$I->waitForElementVisible('.chatboxtextarea', 4);

			// write a nice reply
			$I->fillField('.chatboxtextarea', 'yes! I am here!');
			$I->pressKey('.chatboxtextarea', Facebook\WebDriver\WebDriverKeys::ENTER);
		});

		$I->waitForText('yes! I am here!', 10, '.chatboxcontent');

		$I->seeInDatabase('fs_msg', [
			'foodsaver_id' => $this->foodsaver2['id'],
			'body' => 'yes! I am here!'
		]);
	}
}
