<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('delete my account (being a foodsaver)');

$pass = sq('pass');

$foodsaver = $I->createFoodsaver($pass);

$I->login($foodsaver['email'], $pass);

$I->amOnPage('/?page=settings&sub=deleteaccount');

$I->click('#delete-account');
$I->waitForElement('#modal-delete-account');
$I->see('wirklich');
$I->executeJS("$('button:contains(Account löschen)').trigger('click')");
$I->waitForActiveAPICalls();

$I->seeInDatabase('fs_foodsaver', [
    'id' => $foodsaver['id'],
    'name' => null,
    'email' => null,
    'nachname' => null,
    'deleted_by' => $foodsaver['id']
]);

$I->seeInDatabase('fs_foodsaver_archive', [
    'id' => $foodsaver['id'],
    'name' => $foodsaver['name'],
    'email' => $foodsaver['email'],
    'nachname' => $foodsaver['nachname']
]);
