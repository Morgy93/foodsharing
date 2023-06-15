<?php

namespace Foodsharing\api;

use ApiTester;
use Codeception\Util\HttpCode;

class BlogApiCest
{
    private $user;
    private $blogPost;

    public function _before(ApiTester $I)
    {
        $this->user = $I->createFoodsharer();
        $this->blogPost = $I->addBlogPost($this->user['id'], 1);
    }

    public function canRequestBlogPost(ApiTester $I)
    {
        $I->sendGet('api/blog/' . $this->blogPost['id']);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'id' => $this->blogPost['id'],
            'title' => $this->blogPost['name'],
            'content' => $this->blogPost['body'],
            'picture' => $this->blogPost['picture'],
        ]);
    }

    public function cannotRequestNonExistingBlogPost(ApiTester $I)
    {
        $I->sendGet('api/blog/999999');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
