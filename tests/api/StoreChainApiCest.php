<?php

namespace api;

use ApiTester;
use Codeception\Example;
use Codeception\Util\HttpCode as Http;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;

/**
 * Tests for the store chain API.
 */
class StoreChainApiCest
{
    private $store;
    private $storeWithoutChain;
    private $foodsharer;
    private $user;
    private $unverifiedUser;
    private $storeTeamMember;
    private $storeManager;
    private $storeTeamMemberWithoutChain;
    private $storeManagerWithoutChain;
    private $storeManagerAgChainMember;
    private $chainMember;
    private $chainKeyAccountManager;
    private $chainKeyAccountManagerOtherChain;
    private $chainManager;

    private $orga;
    private $region;
    private $agChain;
    private $chainForum;

    private const API_BASE = 'api/chains';
    private const CHAIN_ID = 40;
    private const CHAIN_ID_1 = 41;

    public function _before(ApiTester $I)
    {
        $this->region = $I->createRegion();
        $this->agChain = $I->createWorkingGroup('AG Betriebsketten', ['id' => RegionIDs::STORE_CHAIN_GROUP]);

        $I->haveInDatabase('fs_chain', ['id' => StoreChainApiCest::CHAIN_ID, 'name' => 'Chain']);
        $I->haveInDatabase('fs_chain', ['id' => StoreChainApiCest::CHAIN_ID_1, 'name' => 'Chain 1']);
        $I->haveInDatabase('fs_betrieb_kategorie', ['id' => 20, 'name' => 'Category']);
        $this->foodsharer = $I->createFoodsharer(null, ['verified' => 0]);
        $this->user = $I->createFoodsaver(null, ['verified' => 1]);
        $this->unverifiedUser = $I->createFoodsaver(null, ['verified' => 0]);
        $this->storeTeamMember = $I->createFoodsaver();
        $this->storeManager = $I->createStoreCoordinator(null, ['bezirk_id' => $this->region['id']]);
        $this->orga = $I->createOrga();
        $this->storeTeamMemberWithoutChain = $I->createFoodsaver();
        $this->storeManagerWithoutChain = $I->createStoreCoordinator(null, ['bezirk_id' => $this->region['id']]);
        $this->storeManagerAgChainMember = $I->createStoreCoordinator(null, ['bezirk_id' => $this->region['id']]);

        $this->store = $I->createStore($this->region['id'], null, null, ['kette_id' => StoreChainApiCest::CHAIN_ID]);
        $I->addStoreTeam($this->store['id'], $this->storeTeamMember['id'], false);
        $I->addStoreTeam($this->store['id'], $this->storeManager['id'], true);

        $this->storeWithoutChain = $I->createStore($this->region['id'], null, null, ['kette_id' => null]);
        $I->addStoreTeam($this->store['id'], $this->storeTeamMemberWithoutChain['id'], false);
        $I->addStoreTeam($this->store['id'], $this->storeManagerWithoutChain['id'], true);

        $I->addRegionMember(RegionIDs::STORE_CHAIN_GROUP, $this->storeManagerAgChainMember['id'], true);
        $this->chainMember = $I->createFoodsaver();
        $I->addRegionMember(RegionIDs::STORE_CHAIN_GROUP, $this->chainMember['id'], true);
        $this->chainKeyAccountManager = $I->createFoodsaver();
        $I->haveInDatabase('fs_key_account_manager', ['foodsaver_id' => $this->chainKeyAccountManager['id'], 'chain_id' => StoreChainApiCest::CHAIN_ID]);
        $I->addRegionMember(RegionIDs::STORE_CHAIN_GROUP, $this->chainKeyAccountManager['id'], true);

        $this->chainKeyAccountManagerOtherChain = $I->createFoodsaver();
        $I->haveInDatabase('fs_key_account_manager', ['foodsaver_id' => $this->chainKeyAccountManagerOtherChain['id'], 'chain_id' => StoreChainApiCest::CHAIN_ID_1]);
        $I->addRegionMember(RegionIDs::STORE_CHAIN_GROUP, $this->chainKeyAccountManagerOtherChain['id'], true);

        $this->chainManager = $I->createFoodsaver();
        $I->addRegionAdmin(RegionIDs::STORE_CHAIN_GROUP, $this->chainManager['id'], true);

        $this->chainForum = $I->addForumThread(RegionIDs::STORE_CHAIN_GROUP, $this->chainManager['id']);
    }

    /**
     * Returns a user based on the type of user.
     *
     * - "foodsharer"
     * - "verifiedFoodsaver"
     * - "unverifiedFoodsaver"
     * - "storeTeamMember"
     * - "storeTeamMemberWithoutChain"
     * - "storeManagerWithoutChain"
     * - "storeManager"
     * - "orga"
     * - "storeManagerAgChainMember"
     * - "chainMember"
     * - "chainManager"
     * - "chainKeyAccountManager"
     * - "chainKeyAccountManagerOtherChain"
     */
    private function getUserByRole($type)
    {
        $user = null;
        switch ($type) {
            case 'foodsharer':
                $user = $this->foodsharer;
                break;
            case 'verifiedFoodsaver':
                $user = $this->user;
                break;
            case 'unverifiedFoodsaver':
                $user = $this->unverifiedUser;
                break;
            case 'storeTeamMember':
                $user = $this->storeTeamMember;
                break;
            case 'storeTeamMemberWithoutChain':
                $user = $this->storeTeamMemberWithoutChain;
                break;
            case 'storeManagerWithoutChain':
                $user = $this->storeManagerWithoutChain;
                break;
            case 'storeManager':
                $user = $this->storeManager;
                break;
            case 'orga':
                $user = $this->orga;
                break;
            case 'storeManagerAgChainMember':
                $user = $this->storeManagerAgChainMember;
                break;
            case 'chainMember':
                $user = $this->chainMember;
                break;
            case 'chainManager':
                $user = $this->chainManager;
                break;
            case 'chainKeyAccountManager':
                $user = $this->chainKeyAccountManager;
                break;
            case 'chainKeyAccountManagerOtherChain':
                $user = $this->chainKeyAccountManagerOtherChain;
                break;
            default:
                assert(false, 'Undefined user role');
        }

        return $user;
    }

    /**
     * Test access of user to create store chain.
     *
     * Expect that user get an error 403 on HTTP level
     *
     * Forbidden roles:
     * anonym
     *
     * @example { "role": "foodsharer", "access": false}
     * @example { "role": "verifiedFoodsaver", "access": false}
     * @example { "role": "unverifiedFoodsaver", "access": false}
     * @example { "role": "storeTeamMemberWithoutChain", "access": false}
     * @example { "role": "storeManagerWithoutChain", "access": false}
     * @example { "role": "storeTeamMember", "access": false}
     * @example { "role": "storeManagerAgChainMember", "access": false}
     * @example { "role": "chainMember", "access": false}
     * @example { "role": "storeManager", "access": false}
     * @example { "role": "chainKeyAccountManager", "access": false}
     * @example { "role": "chainKeyAccountManagerOtherChain", "access": false}
     *
     * Allowed roles:
     * @example { "role": "chainManager", "access": true}
     * @example { "role": "orga", "access": true}
     */
    public function canAccessCreateChainPOSTEndpoint(ApiTester $I, Example $example)
    {
        $role = $example['role'];
        $responseCode = $example['access'] ? Http::BAD_REQUEST : Http::FORBIDDEN;

        // Anonym
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::API_BASE);
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);

        $I->login($this->getUserByRole($role)['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::API_BASE);
        $I->seeResponseCodeIs($responseCode);
    }

    /**
     * Test access of user to edit store chain.
     *
     * Expect that user get an error 403 on HTTP level
     *
     * Forbidden roles:
     * anonym
     *
     * @example { "role": "foodsharer", "access": false}
     * @example { "role": "verifiedFoodsaver", "access": false}
     * @example { "role": "unverifiedFoodsaver", "access": false}
     * @example { "role": "storeTeamMemberWithoutChain", "access": false}
     * @example { "role": "storeManagerWithoutChain", "access": false}
     * @example { "role": "storeTeamMember", "access": false}
     * @example { "role": "storeManager", "access": false}
     * @example { "role": "storeManagerAgChainMember", "access": false}
     * @example { "role": "chainMember", "access": false}
     * @example { "role": "chainKeyAccountManagerOtherChain", "access": false}
     * Allowed roles:
     * @example { "role": "orga", "access": true}
     * @example { "role": "chainManager", "access": true}
     * @example { "role": "chainKeyAccountManager", "access": true}
     */
    public function canAccessUpdateChainPATCHEndpoint(ApiTester $I, Example $example)
    {
        $role = $example['role'];
        $responseCode = $example['access'] ? Http::BAD_REQUEST : Http::FORBIDDEN;

        // Anonym
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID);
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);

        $I->login($this->getUserByRole($role)['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID);
        $I->seeResponseCodeIs($responseCode);
    }

    /**
     * Test access of user to list store of store chain.
     *
     * Expect that user get an error 403 on HTTP level
     *
     * Forbidden roles:
     * anonym
     *
     * @example { "role": "foodsharer", "access": false}
     * @example { "role": "verifiedFoodsaver", "access": false}
     * @example { "role": "unverifiedFoodsaver", "access": false}
     * @example { "role": "storeTeamMemberWithoutChain", "access": false}
     * @example { "role": "storeManagerAgChainMember", "access": false}
     * @example { "role": "chainMember", "access": false}
     * @example { "role": "storeTeamMember", "access": false}
     * @example { "role": "storeManagerWithoutChain", "access": false}
     * @example { "role": "storeManager", "access": false}
     * @example { "role": "chainKeyAccountManagerOtherChain", "access": false}
     *
     * Allowed roles:
     * @example { "role": "chainManager", "access": true}
     * @example { "role": "orga", "access": true}
     * @example { "role": "chainKeyAccountManager", "access": true}
     */
    public function testAccessGetStoresOfStoreChainEndpoint(ApiTester $I, Example $example)
    {
        $role = $example['role'];
        $forbidden = $example['access'] ? Http::OK : Http::FORBIDDEN;
        // Anonym
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID . '/stores');
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);

        $I->login($this->getUserByRole($role)['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID . '/stores');
        $I->seeResponseCodeIs($forbidden);
    }

    /**
     * Test access of user role to list store chains.
     *
     * Expect that user get an error 403 on HTTP level
     *
     * Forbidden roles:
     * anonym
     *
     * @example { "role": "foodsharer", "access": false}
     *
     * Allowed roles:
     * @example { "role": "verifiedFoodsaver", "access": true}
     * @example { "role": "unverifiedFoodsaver", "access": true}
     * @example { "role": "storeTeamMemberWithoutChain", "access": true}
     * @example { "role": "orga", "access": true}
     * @example { "role": "chainManager", "access": true}
     * @example { "role": "storeManagerAgChainMember", "access": true}
     * @example { "role": "chainMember", "access": true}
     * @example { "role": "storeTeamMember", "access": true}
     * @example { "role": "storeManagerWithoutChain", "access": true}
     * @example { "role": "storeManager", "access": true}
     * @example { "role": "chainKeyAccountManager", "access": true}
     * @example { "role": "chainKeyAccountManagerOtherChain", "access": true}
     */
    public function testAccessGetStoreChainsEndpoint(ApiTester $I, Example $example)
    {
        $role = $example['role'];
        $forbidden = $example['access'] ? Http::OK : Http::FORBIDDEN;
        // Anonym
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::API_BASE);
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);

        $I->login($this->getUserByRole($role)['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::API_BASE);
        $I->seeResponseCodeIs($forbidden);
    }

    /**
     * Test access of user role to store chain details (no content check).
     *
     * Expect that user get an error 403 on HTTP level
     *
     * Forbidden roles:
     * anonym
     *
     * @example { "role": "foodsharer", "access": false}
     *
     * Allowed roles:
     * @example { "role": "verifiedFoodsaver", "access": true}
     * @example { "role": "unverifiedFoodsaver", "access": true}
     * @example { "role": "storeTeamMemberWithoutChain", "access": true}
     * @example { "role": "orga", "access": true}
     * @example { "role": "chainManager", "access": true}
     * @example { "role": "storeManagerAgChainMember", "access": true}
     * @example { "role": "chainMember", "access": true}
     * @example { "role": "storeTeamMember", "access": true}
     * @example { "role": "storeManagerWithoutChain", "access": true}
     * @example { "role": "storeManager", "access": true}
     * @example { "role": "chainKeyAccountManager", "access": true}
     * @example { "role": "chainKeyAccountManagerOtherChain", "access": true}
     */
    public function testAccessGetSingleStoreChainInformationEndpoint(ApiTester $I, Example $example)
    {
        $role = $example['role'];
        $forbidden = $example['access'] ? Http::OK : Http::FORBIDDEN;
        // Anonym
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID);
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);

        $I->login($this->getUserByRole($role)['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID);
        $I->seeResponseCodeIs($forbidden);
    }

    public function testValidCreationOfChain(ApiTester $I)
    {
        $I->login($this->getUserByRole('chainManager')['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::API_BASE,
            [
               'name' => 'New Chain',
               'status' => 2,
               'headquarters_zip' => '4312',
               'headquarters_city' => 'Ried in der Riedmark',
               'allow_press' => true,
               'forum_thread' => $this->chainForum['id'],
               'notes' => 'Notizen',
               'common_store_information' => 'Common Store information',
               'kams' => [$this->getUserByRole('chainKeyAccountManager')['id'], $this->getUserByRole('chainKeyAccountManagerOtherChain')['id']]
            ]
        );
        $I->seeResponseCodeIs(Http::CREATED);
        $I->seeInDatabase('fs_chain', [
            'name' => 'New Chain',
            'status' => 2,
            'headquarters_zip' => '4312',
            'headquarters_city' => 'Ried in der Riedmark',
            'allow_press' => true,
            'forum_thread' => $this->chainForum['id'],
            'notes' => 'Notizen',
            'common_store_information' => 'Common Store information']);
        // Test KAMS
    }
}
