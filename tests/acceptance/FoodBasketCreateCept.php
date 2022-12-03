<?php

$I = new AcceptanceTester($scenario);

$description = sq('yay');
$updateDescription = sq('upd');
$pass = sq('pass');

$foodsaver = $I->createFoodsaver($pass);

$I->wantTo('Ensure I can create a food basket');

$I->login($foodsaver['email'], $pass);

$I->amOnPage('/');
$I->see('Essenskörbe', ['css' => '.testing-basket-dropdown']);
$I->click('.testing-basket-dropdown > .nav-link');
$I->waitForText('Essenskorb anlegen');
$I->click('.testing-basket-create');
$I->waitForText('Wie lange soll dein Essenskorb gültig sein?');
/*
 * Check for default options on the foodbasket create form.
 * this was implemented mainly to check the v_components when refactoring default options.
 */
$I->seeCheckboxIsChecked('.input.cb-contact_type[value="1"]');
$I->dontSeeCheckboxIsChecked('.input.cb-contact_type[value="2"]');
$I->seeOptionIsSelected('#weight', '3 kg');
$I->dontSeeElement('#handy');
$I->checkOption('.input.cb-contact_type[value="2"]');
$I->waitForElement('#handy');
$I->seeInField('#handy', $foodsaver['handy']);
$I->seeOptionIsSelected('#lifetime', 'eine Woche');

$I->fillField('description', $description);

// /* This line should not be necessary - actually the window should not get too big! */
// $I->scrollTo('//*[contains(text(),"Essenskorb veröffentlichen")]');
$I->click('Essenskorb veröffentlichen');

$I->waitForElementVisible('#pulse-info', 4);
$I->see('Danke dir, der Essenskorb wurde veröffentlicht');

$I->seeInDatabase('fs_basket', [
	'description' => $description,
	'foodsaver_id' => $foodsaver['id']
]);

$id = $I->grabFromDatabase('fs_basket', 'id', ['description' => $description,
	'foodsaver_id' => $foodsaver['id']]);

//Check update of the foodbasket
$I->amOnPage($I->foodBasketInfoUrl($id));
$I->waitForActiveAPICalls();
$I->waitForElementNotVisible('#fancybox-loading');
$I->click('Essenskorb bearbeiten');
$I->waitForElementNotVisible('#fancybox-loading', 3);
$I->waitForText('Essenskorb bearbeiten', 3);
$I->waitForText('Essenskorb veröffentlichen', 3);
$I->waitForElement('#description');
$I->fillField('description', $description . $updateDescription);
$I->click('Essenskorb veröffentlichen');
$I->waitForActiveAPICalls();
$I->waitForText('Aktualisiert am');

$I->see($updateDescription);
$I->seeInDatabase('fs_basket', [
	'description' => $description . $updateDescription,
	'foodsaver_id' => $foodsaver['id']
]);

$picker = $I->createFoodsaver();

$nick = $I->haveFriend('nick');
$nick->does(
	static function (AcceptanceTester $I) use ($id, $picker) {
		$I->login($picker['email']);
		$I->amOnPage($I->foodBasketInfoUrl($id));

		$I->waitForText('Essenskorb anfragen');
		$I->click('Essenskorb anfragen');
		$I->waitForText('Anfrage absenden');
		$I->fillField('#contactmessage', 'Hi friend, can I have the basket please?');
		$I->click('Anfrage absenden');
		$I->waitForActiveAPICalls();
		$I->waitForText('Anfrage wurde versendet');
	});

$I->amOnPage($I->foodBasketInfoUrl($id));
$I->waitForActiveAPICalls();
$I->waitForElementNotVisible('#fancybox-loading');
$I->waitForText('Anfragen (1)');
// Open the dropdown menu
$I->see('Essenskörbe', ['css' => '.testing-basket-dropdown']);
$I->click('.testing-basket-dropdown > .nav-link');
$I->see('Essenskorb anlegen', ['css' => '.testing-basket-create']);
// Open chat
$I->click('.testing-basket-requests');
// TODO: https://gitlab.com/foodsharing-dev/foodsharing/-/issues/1466 $I->see('Hi friend, can I have', ['css' => 'vue-advanced-chat']);
// Reject request
$I->wait(2);
$I->click('.testing-basket-dropdown > .nav-link');
$I->click('.testing-basket-requests-close');
$I->waitForText('Essenskorbanfrage von ' . $picker['name'] . ' abschließen');
$I->see('Hat alles gut geklappt?');
$I->seeOptionIsSelected('#fetchstate-wrapper input[name=fetchstate]', '2');
$I->click('Weiter');
$I->waitForText('Danke');
