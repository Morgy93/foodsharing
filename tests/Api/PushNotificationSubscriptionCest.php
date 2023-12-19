<?php

declare(strict_types=1);

namespace Tests\Api;

use Codeception\Util\HttpCode;
use Tests\Support\ApiTester;

class PushNotificationSubscriptionCest
{
    /**
     * @var string
     */
    private $testSubscription;

    public function _before(ApiTester $I): void
    {
        $this->tester = $I;
        $this->user = $I->createFoodsaver();

        $this->testSubscription = '
		{
			"endpoint": "https://some.pushservice.com/something-unique",
			"keys": {
				"p256dh": "BIPUL12DLfytvTajnryr2PRdAgXS3HGKiLqndGcJGabyhHheJYlNGCeXl1dn18gSJ1WAkAPIxr4gK0_dQds4yiI=",
				"auth":"FPssNDTKnInHVndSTdbKFw=="
			}
		}';
    }

    public function subscriptionSucceedsIfLoggedIn(ApiTester $I): void
    {
        $I->login($this->user['email']);
        $I->sendPOST('api/pushnotification/webpush/subscription', $this->testSubscription);

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function subscriptionFailsIfNotLoggedIn(ApiTester $I): void
    {
        $I->sendPOST('api/pushnotification/webpush/subscription', $this->testSubscription);

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }
}
