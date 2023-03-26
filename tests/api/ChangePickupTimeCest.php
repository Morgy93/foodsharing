<?php

/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 03.01.17
 * Time: 22:38.
 */
class ChangePickupTimeCest
{
    /**
     * @example ["createStoreCoordinator", true]
     */
    public function createRequestSucceeds(ApiTester $I, Codeception\Example $example)
    {
        $store = $I->createStore(1);
        $user = call_user_func([$I, $example[0]]);
        $I->addStoreTeam($store['id'], $user['id'], true);

        $I->login($user['email']);
        $request = [
            'f' => 'editpickups',
            'newfetchtime[]' => 0,
            'nfttime[hour][]' => 20,
            'nfttime[min][]' => 0,
            'nft-count[]' => 2,
            'newfetchtime[]' => 0,
            'nfttime[hour][]' => 7,
            'nfttime[min][]' => 0,
            'nft-count[]' => 2,
            'newfetchtime[]' => 0,
            'nfttime[hour][]' => 20,
            'nfttime[min][]' => 0,
            'nft-count[]' => 2,
            'bid' => $store['id'],
            'team' => $user['id'],
            'id' => 1
        ];

        $I->sendGET('/xhr', $request);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
    }
}
