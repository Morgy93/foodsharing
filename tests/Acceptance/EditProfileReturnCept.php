<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

$I = new AcceptanceTester($scenario);

$I->wantTo('Check if someone editing a profile sees return to profile button and if return to profile button points to the edited profile');

$region = $I->createRegion();

$member = $I->createFoodsaver();
$ambassador = $I->createAmbassador(null, ['bezirk_id' => $region['id']]);

$I->addRegionAdmin($region['id'], $ambassador['id']);
$I->addRegionMember($region['id'], $member['id']);

$I->login($ambassador['email']);

$I->amOnPage('/?page=foodsaver&a=edit&id=' . $member['id']);

$I->see('Zurück zum Profil');
$I->see($member['name']);
$I->click('Zurück zum Profil');

$I->seeCurrentUrlEquals('/profile/' . $member['id']);
