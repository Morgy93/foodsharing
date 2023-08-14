<?php

class SettingsCest
{
    private $region;
    private $foodSharePoint;

    private $fspAdmin;
    private $foodsaver;

    final public function _before(AcceptanceTester $I): void
    {
        $this->foodsaver = $I->createFoodsaver();
        $this->fspAdmin = $I->createFoodsaver();
        $this->region = $I->createRegion();
        $this->foodSharePoint = $I->createFoodSharePoint($this->fspAdmin['id'], $this->region['id']);
    }

    final public function canEditInternalSelfDescription(AcceptanceTester $I): void
    {
        $newSelfDesc = 'This is a new self description!';
        $I->login($this->foodsaver['email']);
        $I->amOnPage('/?page=settings&sub=general');
        $I->waitForPageBody();
        $I->fillField('#about_me_intern', $newSelfDesc);
        $I->click('Speichern');
        $I->waitForPageBody();

        $I->amOnPage('/profile/' . $this->foodsaver['id']);
        $I->waitForPageBody();
        $I->see($newSelfDesc);
    }

    final public function canEditLocation(AcceptanceTester $I): void
    {
        $address = 'Teststraße 1 37073 Teststadt Deutschland';
        $I->login($this->foodsaver['email']);
        $I->amOnPage('/?page=settings&sub=general');
        $I->waitForPageBody();
        $I->fillField('#searchinput', $address);
        $I->waitForElementVisible('#searchinput_listbox');
        $I->click("//*[@id='searchinput_listbox']//*[contains(text(), 'Teststraße 1')]");
        $I->click('Speichern');
        $I->waitForPageBody();

        $I->amOnPage('/?page=settings&sub=general');
        $I->waitForPageBody();
        $I->seeInField('#input-street', 'Teststraße 1');
        $I->seeInField('#input-postal', '37073');
        $I->seeInField('#input-city', 'Teststadt');
        $I->assertEqualsWithDelta($I->grabValueFrom('input[name=lat]'), 51.0, 0.001);
        $I->assertEqualsWithDelta($I->grabValueFrom('input[name=lon]'), 9.0, 0.001);
    }

    private function createSelector(string $field): string
    {
        return 'input[name="' . $field . '"]';
    }
}
