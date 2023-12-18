<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

$I = new AcceptanceTester($scenario);
$I->wantTo('ensure the homepage works');
$I->amOnPage('/');
$I->see('Lebensmittel');
