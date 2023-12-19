<?php

declare(strict_types=1);

namespace Tests\Api;

use Codeception\Util\HttpCode;
use Tests\Support\ApiTester;

$I = new ApiTester($scenario);
$I->wantTo('see the changelog being rendered into html');

$request = ['page' => 'content', 'sub' => 'changelog'];
$I->sendGET('/', $request);
$I->seeResponseCodeIs(HttpCode::OK);
$I->seeResponseContains('href="https://wiki.foodsharing.de/Foodsharing.de_Plattform:_%C3%84nderungshistorie"');
$I->seeResponseContains('href="https://gitlab.com/NerdyProjects"');
$I->seeResponseContains('Changelog');
