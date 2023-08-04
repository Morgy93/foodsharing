<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('Change mail in profile');
$pass = sq('pass');
$newmail = 'test@blaa.com';

$user = $I->createFoodsaver($pass);

$I->login($user['email'], $pass);

// request mail with link
$I->amOnPage('/?page=settings&sub=general');
$I->click('E-Mail-Adresse ändern');
$I->waitForElementVisible('#new-email', 5);
$I->fillField('#new-email', $newmail);
$I->fillField('#new-email-confirm', $newmail);
$I->fillField('#password', $pass);
$I->executeJS("$('button:contains(E-Mail-Adresse ändern)').trigger('click')");
$I->waitForElementVisible('#pulse-info', 5);
$I->see('Gehe jetzt zu deinem');

// receive the mails: notification sent to the  old address and confirmation link sent to the new one
$I->expectNumMails(2, 10);
$I->assertEquals($I->getMails()[0]->headers->to, $user['email'], 'correct recipient');

$mail = $I->getMails()[1];
$I->assertEquals($mail->headers->to, $newmail, 'correct recipient');
$I->assertRegExp('/http:\/\/.*&amp;newmail=[a-f0-9]+/', $mail->html, 'mail should contain a link');
preg_match('/http:\/\/.*?(\/.*?)"/', $mail->html, $matches);
$link = $matches[1];

// open link, fill in password and submit
$I->amOnPage(html_entity_decode($link));
$I->waitForElementVisible('.xhrDialog', 5);
$I->executeJS("$('button:contains(Bestätigen)').trigger('click')");
$I->waitForElementVisible('#pulse-info', 5);
$I->see('Deine E-Mail-Adresse wurde geändert');

$I->seeInDatabase('fs_foodsaver', ['id' => $user['id'], 'email' => $newmail]);
