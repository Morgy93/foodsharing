<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class PagesAsFoodsharerCest
{
    private $emptyFoodsharer;

    public function _before(AcceptanceTester $I): void
    {
        $this->emptyFoodsharer = $I->createFoodsharer(null, ['plz' => '', 'stadt' => '', 'anschrift' => '']);
        $this->foodsaver = $I->createFoodsaver();
        $I->login($this->emptyFoodsharer['email']);
    }

    public function canVisitSettingsPage(AcceptanceTester $I): void
    {
        $I->amOnPage($I->settingsUrl());
        $I->see('Account löschen');
    }
}
