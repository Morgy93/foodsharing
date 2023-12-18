<?php

declare(strict_types=1);

namespace Tests\Api;

use Codeception\Util\HttpCode;
use Tests\Support\ApiTester;

$I = new ApiTester($scenario);
$I->wantTo('see the release notes being rendered into html');

$request = ['page' => 'content', 'sub' => 'releaseNotes'];
$I->sendGET('/', $request);
$I->seeResponseCodeIs(HttpCode::OK);
$I->seeResponseContains('Was ist neu?');
