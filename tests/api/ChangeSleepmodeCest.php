<?php

namespace api;

use ApiTester;

class ChangeSleepmodeCest
{
    //https://foodsharing.de/?page=settings&sub=sleeping
    public function pageDisplaysWithNullValues(ApiTester $I)
    {
        $user = $I->createFoodsaver(null, ['sleep_from' => null, 'sleep_status' => 1]);
        $I->login($user['email']);
        $request = ['page' => 'settings',
            'sub' => 'sleeping'];
        $I->sendGET('/', $request);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
    }
}
