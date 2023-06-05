<?php

use Faker\Generator as FakerGenerator;

class RegionApiCest
{
    private FakerGenerator $faker;
    private array $user;
    private array $userOrga;
    private array $region;

    public function _before(ApiTester $I)
    {
        $this->faker = Faker\Factory::create('de_DE');

        $this->user = $I->createFoodsaver();
        $this->userOrga = $I->createOrga();
        $this->region = $I->createRegion();
        // $this->workingGroup = $I->createWorkingGroup($this->faker->realTextBetween(10, 30));
    }

    public function canNotSeeRegionDetailsAsFoodsaver(ApiTester $I)
    {
        $I->login($this->user['email']);
        $I->sendGET('api/region/' . $this->region['id']);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
    }

    public function canSeeRegionDetailsAsOrga(ApiTester $I)
    {
        $I->sendGET('api/region/' . $this->region['id']);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);

        $I->login($this->userOrga['email']);
        $I->sendGET('api/region/' . $this->region['id']);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'id' => $this->region['id'],
            'name' => $this->region['name'],
            'parentId' => $this->region['parent_id'],
            'type' => $this->region['type']
        ]);
    }

    public function canNotSeeNonExistingRegion(ApiTester $I)
    {
        $I->login($this->userOrga['email']);
        $I->sendGET('api/region/99999999');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
    }

    public function canNotAddRegionAsFoodsaver(ApiTester $I)
    {
        $I->login($this->user['email']);
        $I->sendPOST('api/region/' . $this->region['id'] . '/children');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
        $I->dontSeeInDatabase('fs_bezirk', ['parent_id' => $this->region['id']]);
    }

    public function canAddRegion(ApiTester $I)
    {
        $I->sendPOST('api/region/' . $this->region['id'] . '/children');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $I->dontSeeInDatabase('fs_bezirk', ['parent_id' => $this->region['id']]);

        $I->login($this->userOrga['email']);
        $I->sendPOST('api/region/' . $this->region['id'] . '/children');
        $newRegionId = $I->grabDataFromResponseByJsonPath('id')[0];
        $I->seeInDatabase('fs_bezirk', ['id' => $newRegionId, 'parent_id' => $this->region['id']]);
    }
}
