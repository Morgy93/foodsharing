<?php

class BusinessCardControlCest
{
    public function GuestSeeLoginPage(AcceptanceTester $I)
    {
        $I->amOnPage('/?page=bcard');
        $I->wait(1);
        $I->see('Einloggen', 'button');
    }
}
