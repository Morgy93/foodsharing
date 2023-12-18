<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class BusinessCardControlCest
{
    public function GuestSeeLoginPage(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=bcard');
        $I->wait(1);
        $I->see('Einloggen', 'button');
    }
}
