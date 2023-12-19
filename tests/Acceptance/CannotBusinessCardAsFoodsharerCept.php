<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

$I = new AcceptanceTester($scenario);

$I->wantTo('create a businesscard being a foodsharer');

$pass = sq('pass');

$foodsaver = $I->createFoodsharer($pass);

$I->login($foodsaver['email'], $pass);

$I->amOnPage('/?page=bcard');

$I->seeCurrentUrlEquals('/?page=settings&sub=general'); // it redirects

$I->see('Persönliche Visitenkarte');
