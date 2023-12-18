<?php

declare(strict_types=1);

namespace Tests\Api;

use Codeception\Util\HttpCode;
use Tests\Support\ApiTester;

class ChangeSleepmodeCest
{
    //https://foodsharing.de/?page=settings&sub=sleeping
    public function pageDisplaysWithNullValues(ApiTester $I): void
    {
        $user = $I->createFoodsaver(null, ['sleep_from' => null, 'sleep_status' => 1]);
        $I->login($user['email']);
        $request = ['page' => 'settings',
            'sub' => 'sleeping'];
        $I->sendGET('/', $request);
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
