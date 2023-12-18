<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class PasswordResetCest
{
    final public function testPasswordReset(AcceptanceTester $I): void
    {
        $I->wantTo('do a password reset');

        $newPass = 'TEEEEST';

        $user = $I->createFoodsaver();

        $I->amOnPage('/');
        $I->see('Einloggen', ['css' => '.testing-login-dropdown']);
        $I->click('.testing-login-dropdown > .nav-link');
        $I->waitForText('Passwort vergessen?');
        $I->click('.testing-login-click-password-reset');

        $I->see('Gib deine E-Mail-Adresse ein');
        $I->fillField('#email', $user['email']);
        $I->click('Senden');

        $I->see('Alles klar, dir wurde ein Link zum Passwortändern per E-Mail zugeschickt');

        // receive a mail
        $I->expectNumMails(1, 5);
        $mail = $I->getMails()[0];

        $I->assertEquals($mail->headers->to, $user['email'], 'correct recipient');

        $I->assertRegExp('/http:\/\/.*passwordReset.*&amp;k=[a-f0-9]+/', $mail->html, 'mail should contain a link');
        preg_match('/http:\/\/.*?\/(.*?)"/', $mail->html, $matches);
        $link = $matches[1];

        // there was a strange %20-whitespace appended to the link in the template.
        // the template got updated, but test may fail when there is still the old template in the database
        // -> see commit 84ea2f1868b91a0cfabd85caa31139364b93f7f7

        // go to link in the mail
        $I->amOnPage(html_entity_decode($link));
        $I->see('Jetzt kannst du dein Passwort ändern');
        $I->fillField('#pass1', $newPass);
        $I->fillField('#pass2', 'INVALID');
        $I->click('Speichern');
        $I->see('die Passwörter stimmen nicht überein');

        $I->fillField('#pass1', $newPass);
        $I->fillField('#pass2', $newPass);
        $I->click('Speichern');

        $I->seeCurrentUrlEquals('/?page=login');

        // password got replaced after login
        $I->seeInDatabase('fs_foodsaver', [
            'email' => $user['email']
        ]);

        // new hash is valid
        $newHash = $I->grabFromDatabase('fs_foodsaver', 'password', ['email' => $user['email']]);
        $I->assertTrue(password_verify($newPass, $newHash));
    }
}
