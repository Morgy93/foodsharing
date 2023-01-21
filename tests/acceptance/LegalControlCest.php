<?php

class LegalControlCest
{
    private $user;

    public function _before(AcceptanceTester $I)
    {
        $this->user = $I->createAmbassador();
        $I->login($this->user['email']);
        $I->amOnPage('/?page=legal');
        $I->see('Datenschutzerklärung');
    }

    public function _after(AcceptanceTester $I)
    {
        $lastModified = $I->updateThePrivacyPolicyDate();
        $I->resetThePrivacyPolicyDate($lastModified);
        $I->logMeOut();
        $I->seeCurrentUrlEquals('/');
    }

    public function testGivenIAmNotLoggedInThenTheLegalPageShowsThePrivacyPolicyWithoutAskingForConsent(AcceptanceTester $I)
    {
        $I->logMeOut();
        $I->amOnPage('/?page=legal');
        $I->see('Datenschutzerklärung');
        $I->dontSee('Nimmst du die Vereinbarung zur Kenntnis?');
    }

    public function testGivenIAmLoggedInThenICanAcceptThePrivacyPolicy(AcceptanceTester $I)
    {
        $I->checkOption('#legal_form_privacyPolicyAcknowledged');
        $I->click('Einstellungen übernehmen');
        $I->waitForActiveAPICalls();
        $I->seeCurrentUrlEquals('/?page=legal');
    }

    public function testGivenIAmLoggedInAndIDontAcceptThePrivacyPolicyThenIAmStillAskedForConsent(AcceptanceTester $I)
    {
        $I->uncheckOption('#legal_form_privacyPolicyAcknowledged');
        $I->click('Einstellungen übernehmen');
        $I->see('Nimmst du die Vereinbarung zur Kenntnis?');
    }

    public function testGivenIAmLoggedInAndWantToDeleteMyAccountThenIGetRedirectedToTheDeleteAccountPage(AcceptanceTester $I)
    {
        $I->click('Ich möchte meinen Account löschen.');
        $I->seeCurrentUrlEquals('/?page=settings&sub=deleteaccount');
    }

    public function testGivenIAmLoggedInAndHaveARoleHigherThanOneThenICanAcceptThePrivacyPolicyAndNotice(AcceptanceTester $I)
    {
        $I->checkOption('#legal_form_privacyPolicyAcknowledged');
        $I->selectOption('#legal_form_privacyNoticeAcknowledged', 'Ich stimme zu');
        $I->click('Einstellungen übernehmen');
        $I->seeCurrentUrlEquals('/?page=legal');
        $I->seeInDatabase('fs_foodsaver', ['id' => $this->user['id'], 'rolle' => 3]);
    }

    public function testGivenIAmLoggedInAndAHaveRoleHigherThanOneThenICanDegradeToFoodsaver(AcceptanceTester $I)
    {
        $I->checkOption('#legal_form_privacyPolicyAcknowledged');
        $I->selectOption('#legal_form_privacyNoticeAcknowledged', 'Ich stimme nicht zu');
        $I->click('Einstellungen übernehmen');
        $I->seeInPopup('Bist du dir sicher?');
        $I->cancelPopup();
        $I->click('Einstellungen übernehmen');
        $I->seeInPopup('Bist du dir sicher?');
        $I->seeInDatabase('fs_foodsaver', ['id' => $this->user['id'], 'rolle' => 3]);
        $I->acceptPopup();
        $I->seeCurrentUrlEquals('/?page=legal');
        $I->seeInDatabase('fs_foodsaver', ['id' => $this->user['id'], 'rolle' => 1]);
    }
}
