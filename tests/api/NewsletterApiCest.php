<?php

class NewsletterApiCest
{
    private $tester;
    private $user;
    private $userOrga;

    public function _before(ApiTester $I)
    {
        $this->tester = $I;
        $this->user = $I->createFoodsharer();
        $this->userOrga = $I->createOrga();
    }

    public function foodsaverMayNotTestNewsletter(ApiTester $I): void
    {
        $I->login($this->user['email']);
        $I->sendPOST('api/newsletter/test', [
            'address' => 'test@abcdef.com',
            'subject' => 'Subject',
            'message' => 'Message'
        ]);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
    }

    public function invalidEmailAddressIsRejected(ApiTester $I): void
    {
        $I->login($this->userOrga['email']);
        $I->sendPOST('api/newsletter/test', [
            'address' => 'test',
            'subject' => 'Subject',
            'message' => 'Message'
        ]);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
    }

    public function validEmailAddressIsAccepted(ApiTester $I): void
    {
        $I->login($this->userOrga['email']);
        $I->sendPOST('api/newsletter/test', [
            'address' => 'test@abcdef.com',
            'subject' => 'Subject',
            'message' => 'Message'
        ]);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
    }
}
