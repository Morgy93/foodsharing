<?php

declare(strict_types=1);

namespace Tests\Api;

use Codeception\Util\HttpCode;
use Tests\Support\ApiTester;

$I = new ApiTester($scenario);
$I->wantTo('get a 404 response when I want to access not existant page');

$request = ['page' => 'thishopefullydoesnotexist'];
$I->sendGET('/', $request);
$I->seeResponseCodeIs(HttpCode::NOT_FOUND);

$request = ['page' => 'search'];
$I->sendGET('/', $request);
$I->seeResponseCodeIs(HttpCode::NOT_FOUND);
