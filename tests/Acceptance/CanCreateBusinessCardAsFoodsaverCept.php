<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

$I = new AcceptanceTester($scenario);

$I->wantTo('create a businesscard being a foodsaver');

$pass = sq('pass');

$foodsaver = $I->createFoodsaver($pass, ['handy' => '+4915100000']);

$I->login($foodsaver['email'], $pass);

$I->amOnPage('/?page=bcard');

$I->waitForText('Deine foodsharing-Visitenkarte');
