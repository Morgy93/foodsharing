<?php

namespace Foodsharing\api;

use ApiTester;
use Codeception\Util\HttpCode;
use Foodsharing\Modules\Event\InvitationStatus;

class CalendarApiCest
{
    private const TEST_TOKEN = '1234567890';
    private $user;
    private $user2;
    private $region;
    private array $acceptedEvent;
    private array $invitedEvent;

    public function _before(ApiTester $I)
    {
        $this->region = $I->createRegion();

        $this->user = $I->createFoodsharer();
        $I->addRegionMember($this->region['id'], $this->user['id']);

        $this->user2 = $I->createFoodsharer();
        $I->addRegionMember($this->region['id'], $this->user2['id']);

        $this->acceptedEvent = $I->createEvents($this->region['id'], $this->user2['id']);
        $I->addEventInvitation($this->acceptedEvent['id'], $this->user['id'], [
            'status' => InvitationStatus::ACCEPTED
        ]);

        $this->invitedEvent = $I->createEvents($this->region['id'], $this->user2['id']);
        $I->addEventInvitation($this->invitedEvent['id'], $this->user['id'], [
            'status' => InvitationStatus::INVITED
        ]);
    }

    public function canNotAccessApiWithoutLogin(ApiTester $I)
    {
        $I->sendGet('api/calendar/token');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);

        $I->sendPut('api/calendar/token');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);

        $I->sendDelete('api/calendar/token');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    public function cannotRequestNonExistingToken(ApiTester $I)
    {
        $I->login($this->user['email']);

        $I->sendGet('api/calendar/token');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['token' => null]);
    }

    public function canRequestExistingToken(ApiTester $I)
    {
        $I->login($this->user['email']);

        $I->haveInDatabase('fs_apitoken', [
            'foodsaver_id' => $this->user['id'],
            'token' => self::TEST_TOKEN
        ]);
        $I->sendGet('api/calendar/token');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['token' => self::TEST_TOKEN]);
    }

    public function canCreateToken(ApiTester $I)
    {
        $I->login($this->user['email']);

        // create a token
        $I->sendPut('api/calendar/token');
        $I->seeResponseCodeIs(HttpCode::OK);
        $token1 = json_decode($I->grabResponse(), true)['token'];

        // check if the token was set
        $I->seeInDatabase('fs_apitoken', [
            'foodsaver_id' => $this->user['id'],
            'token' => $token1
        ]);

        // create a new token
        $I->sendPut('api/calendar/token');
        $I->seeResponseCodeIs(HttpCode::OK);
        $token2 = json_decode($I->grabResponse(), true)['token'];

        // check if the token was overwritten
        $I->seeInDatabase('fs_apitoken', [
            'foodsaver_id' => $this->user['id'],
            'token' => $token2
        ]);
        $I->dontSeeInDatabase('fs_apitoken', [
            'foodsaver_id' => $this->user['id'],
            'token' => $token1
        ]);
    }

    public function canDeleteToken(ApiTester $I)
    {
        $I->haveInDatabase('fs_apitoken', [
            'foodsaver_id' => $this->user['id'],
            'token' => self::TEST_TOKEN
        ]);

        $I->login($this->user['email']);
        $I->sendDelete('api/calendar/token');
        $I->dontSeeInDatabase('fs_apitoken', [
            'foodsaver_id' => $this->user['id']
        ]);
    }

    public function canRequestCalendar(ApiTester $I)
    {
        $I->login($this->user['email']);
        $I->sendGet('api/calendar/' . self::TEST_TOKEN);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $I->haveInDatabase('fs_apitoken', [
            'foodsaver_id' => $this->user['id'],
            'token' => self::TEST_TOKEN
        ]);
        $I->sendGet('api/calendar/' . self::TEST_TOKEN);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContains('BEGIN:VCALENDAR');
        $I->seeResponseContains(substr($this->invitedEvent['name'], 0, 10));
        $I->seeResponseContains(substr($this->acceptedEvent['name'], 0, 10));
    }

    public function canFilterOutInvitations(ApiTester $I)
    {
        $I->haveInDatabase('fs_apitoken', [
            'foodsaver_id' => $this->user['id'],
            'token' => self::TEST_TOKEN
        ]);

        $I->login($this->user['email']);
        $I->sendGet('api/calendar/' . self::TEST_TOKEN);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContains(substr($this->acceptedEvent['name'], 0, 10));
        $I->seeResponseContains(substr($this->invitedEvent['name'], 0, 10));

        $I->sendGet('api/calendar/' . self::TEST_TOKEN . '?events=answered');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContains(substr($this->acceptedEvent['name'], 0, 10));
        $I->cantSeeResponseContains(substr($this->invitedEvent['name'], 0, 10));
    }
}
