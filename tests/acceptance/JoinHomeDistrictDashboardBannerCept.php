<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('join a home district via dashboard banner if none is choosen yet.');

/*
This foodsaver has bezirk_id 0, so no home district
*/
$foodsaver = $I->createFoodsaver();

$I->login($foodsaver['email']);

$I->amOnPage('/?page=dashboard');
$I->waitForActiveAPICalls();
$I->waitForElement('.testing-region-join');
$I->see('Bitte auswählen...', ['css' => '.testing-region-join-select']);
$I->click('.testing-region-join-close');
$I->click('Jetzt Stammbezirk auswählen');
$I->waitForElement('.testing-region-join');
