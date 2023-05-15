<?php

namespace Foodsharing\api;

use ApiTester;
use Codeception\Util\HttpCode;
use Foodsharing\Modules\Core\DBConstants\Content\ContentId;

class ContentApiCest
{
    private $user;

    public function _before(ApiTester $I)
    {
        $this->user = $I->createFoodsharer();
    }

    public function canNotRequestContentWithoutLogin(ApiTester $I)
    {
        $I->sendGET('api/content/' . ContentId::ABOUT);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    public function canRequestContent(ApiTester $I)
    {
        $I->login($this->user['email']);
        $I->sendGET('api/content/' . ContentId::ABOUT);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'title' => $I->grabFromDatabase('fs_content', 'title', ['id' => ContentId::ABOUT]),
            'body' => $I->grabFromDatabase('fs_content', 'body', ['id' => ContentId::ABOUT]),
        ]);
    }

    public function canNontRequestNotExistingContent(ApiTester $I)
    {
        $I->login($this->user['email']);
        $I->sendGET('api/content/9999999');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
