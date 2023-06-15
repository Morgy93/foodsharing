<?php

namespace api;

use ApiTester;
use Codeception\Util\HttpCode as Http;

class ApplicationsApiCest
{
    private $group;
    private $userApplicant;
    private $userMember;
    private $userAdmin;

    public function _before(ApiTester $I)
    {
        $this->group = $I->createWorkingGroup('Test working group');

        $this->userApplicant = $I->createFoodsaver();
        $this->userMember = $I->createFoodsaver();
        $I->addRegionMember($this->group['id'], $this->userMember['id']);
        $this->userAdmin = $I->createAmbassador();
        $I->addRegionMember($this->group['id'], $this->userAdmin['id']);
        $I->addRegionAdmin($this->group['id'], $this->userAdmin['id']);
    }

    public function canListApplicationsAsAdmin(ApiTester $I)
    {
        $this->makeApplication($I, $this->userApplicant['id'], $this->group['id']);

        $I->login($this->userAdmin['email']);
        $I->sendGet('api/applications/' . $this->group['id']);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'id' => $this->userApplicant['id']
        ]);
    }

    public function cannotListApplicationsAsMember(ApiTester $I)
    {
        $I->login($this->userMember['email']);
        $I->sendGet('api/applications/' . $this->group['id']);
        $I->seeResponseCodeIs(Http::FORBIDDEN);
    }

    public function canAcceptApplications(ApiTester $I)
    {
        $this->makeApplication($I, $this->userApplicant['id'], $this->group['id']);

        $I->login($this->userAdmin['email']);
        $I->sendPatch('api/applications/' . $this->group['id'] . '/' . $this->userApplicant['id']);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeInDatabase('fs_foodsaver_has_bezirk', [
            'foodsaver_id' => $this->userApplicant['id'],
            'bezirk_id' => $this->group['id'],
            'active' => 1,
        ]);
    }

    public function canDeclineApplications(ApiTester $I)
    {
        $this->makeApplication($I, $this->userApplicant['id'], $this->group['id']);

        $I->login($this->userAdmin['email']);
        $I->sendDelete('api/applications/' . $this->group['id'] . '/' . $this->userApplicant['id']);
        $I->seeResponseCodeIs(Http::OK);
        $I->dontSeeInDatabase('fs_foodsaver_has_bezirk', [
            'foodsaver_id' => $this->userApplicant['id'],
            'bezirk_id' => $this->group['id'],
        ]);
    }

    /**
     * Sets the user's status in the group to applied.
     */
    private function makeApplication(ApiTester $I, int $userId, int $groupId)
    {
        $I->haveInDatabase('fs_foodsaver_has_bezirk', [
            'foodsaver_id' => $userId,
            'bezirk_id' => $groupId,
            'active' => 0,
        ]);
        $I->updateInDatabase('fs_foodsaver_has_bezirk', [
            'active' => 0
        ], [
            'foodsaver_id' => $userId,
            'bezirk_id' => $groupId
        ]);
    }
}
