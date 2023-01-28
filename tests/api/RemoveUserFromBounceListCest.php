<?php

namespace Foodsharing\api;

use ApiTester;
use Carbon\Carbon;
use Codeception\Util\HttpCode;

class RemoveUserFromBounceListCest
{
    private $region;
    private $foodsharer;

    public function _before(ApiTester $I)
    {
        $this->region = $I->createRegion();
        $regionId = $this->region['id'];
        $this->foodsharer = $I->createFoodsharer();
        $I->addRegionMember($regionId, $this->foodsharer['id']);
    }

    public function removeFoodsharerFromBounceList(ApiTester $I)
    {
        $fsId = $this->foodsharer['id'];
        $dateTime = Carbon::now();
        $I->haveInDatabase('fs_email_bounces', [
            'email' => $this->foodsharer['email'],
            'bounced_at' => $dateTime,
            'bounce_category' => 'spam'
        ]);
        $I->seeInDatabase('fs_email_bounces', [
            'email' => $this->foodsharer['email'],
            'bounced_at' => $dateTime,
            'bounce_category' => 'spam'
        ]);

        $I->login($this->foodsharer['email']);

        $I->sendDelete('api/user/' . $fsId . '/emailbounce');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
