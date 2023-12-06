<?php

namespace Foodsharing\api;

use ApiTester;
use Codeception\Util\HttpCode;
use Foodsharing\Modules\Core\DBConstants\Content\ContentId;

class ContentApiCest
{
    private $user;
    private $userOrga;

    public function _before(ApiTester $I)
    {
        $this->user = $I->createFoodsharer();
        $this->userOrga = $I->createOrga();
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

    public function canRequestContentList(ApiTester $I)
    {
        $I->login($this->userOrga['email']);
        $I->sendGET('api/content');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function canNotRequestContentListWithoutLogin(ApiTester $I)
    {
        $I->sendGET('api/content');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    public function canNotRequestContentListAsFoodsaver(ApiTester $I)
    {
        $I->login($this->user['email']);
        $I->sendGET('api/content');
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function canDeleteContent(ApiTester $I)
    {
        $id = 10000;

        $I->haveInDatabase('fs_content', [
            'id' => $id,
            'name' => '',
            'title' => '',
            'body' => '',
        ]);
        $I->login($this->userOrga['email']);
        $I->sendDELETE('api/content/' . $id);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->dontSeeInDatabase('fs_content', ['id' => $id]);
    }

    public function canNotDeleteContentWithoutLogin(ApiTester $I)
    {
        $I->sendDELETE('api/content/' . ContentId::ABOUT);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    public function canNotDeleteContentAsFoodsaver(ApiTester $I)
    {
        $I->login($this->user['email']);
        $I->sendDELETE('api/content/' . ContentId::ABOUT);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }
}
