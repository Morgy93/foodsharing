<?php

namespace api;

use ApiTester;
use Codeception\Example;
use Codeception\Util\HttpCode as Http;
use DateTime;
use DateTimeZone;
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
    private $deletedKeyAccountManager;

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

        $I->haveInDatabase('fs_chain', ['id' => StoreChainApiCest::CHAIN_ID, 'name' => 'Chain', 'status' => 2]);
        $I->haveInDatabase('fs_chain', ['id' => StoreChainApiCest::CHAIN_ID_1, 'name' => 'Chain 1']);
        $I->haveInDatabase('fs_betrieb_kategorie', ['id' => 20, 'name' => 'Category']);
        $delete_at = new DateTime('now', new DateTimeZone('Europe/Berlin'));
        $this->deletedKeyAccountManager = $I->createStoreCoordinator(null, ['deleted_at' => $delete_at->format('c')]);
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
        $I->addStoreTeam($this->storeWithoutChain['id'], $this->storeTeamMemberWithoutChain['id'], false);
        $I->addStoreTeam($this->storeWithoutChain['id'], $this->storeManagerWithoutChain['id'], true);

        $I->addRegionMember(RegionIDs::STORE_CHAIN_GROUP, $this->storeManagerAgChainMember['id'], true);
        $this->chainMember = $I->createFoodsaver();
        $I->addRegionMember(RegionIDs::STORE_CHAIN_GROUP, $this->chainMember['id'], true);
        $this->chainKeyAccountManager = $I->createFoodsaver();
        $I->haveInDatabase('fs_key_account_manager', ['foodsaver_id' => $this->chainKeyAccountManager['id'], 'chain_id' => StoreChainApiCest::CHAIN_ID]);
        $I->addRegionMember(RegionIDs::STORE_CHAIN_GROUP, $this->chainKeyAccountManager['id'], true);
        $I->addRegionMember(RegionIDs::STORE_CHAIN_GROUP, $this->deletedKeyAccountManager['id'], true);

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

    private function getRandomString(int $n): string
    {
        $randomString = '';

        for ($i = 0; $i < $n; ++$i) {
            $randomString .= rand(0, 9);
        }

        return $randomString;
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

    public function testValidCreationOfChain(ApiTester $I)
    {
        $I->login($this->getUserByRole('chainManager')['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            self::API_BASE,
            [
               'name' => 'New Chain & next chains',
               'status' => 2,
               'headquartersZip' => '4312',
               'headquartersCity' => 'Ried in der Riedmark',
               'headquartersCountry' => 'Germany',
               'allowPress' => true,
               'forumThread' => $this->chainForum['id'],
               'estimatedStoreCount' => 20,
               'notes' => 'Notizen',
               'commonStoreInformation' => 'Common Store information',
               'kams' => [$this->getUserByRole('chainKeyAccountManager')['id'], $this->getUserByRole('chainKeyAccountManagerOtherChain')['id']]
            ]
        );
        $I->seeResponseCodeIs(Http::CREATED);
        $I->seeResponseIsJson();

        $id = $I->grabFromDatabase('fs_chain', 'id', [
            'name' => 'New Chain & next chains',
            'status' => 2,
            'headquarters_zip' => '4312',
            'headquarters_city' => 'Ried in der Riedmark',
            'headquarters_country' => 'Germany',
            'estimated_store_count' => 20,
            'allow_press' => true,
            'forum_thread' => $this->chainForum['id'],
            'notes' => 'Notizen',
            'common_store_information' => 'Common Store information']);

        $modificationDate = $I->grabFromDatabase('fs_chain', 'modification_date', [
                'id' => $id]);
        $modificationDate = DateTime::createFromFormat('Y-m-d H:i:s', $modificationDate . ' 00:00:00', new DateTimeZone('Europe/Berlin'));
        $modificationDateString = $modificationDate->format('c');

        $I->seeResponseContainsJson([
                'chain' => [
                  'regionId' => RegionIDs::STORE_CHAIN_GROUP,
                  'id' => $id,
                  'name' => 'New Chain & next chains',
                  'status' => 2,
                  'headquartersZip' => '4312',
                  'headquartersCity' => 'Ried in der Riedmark',
                  'headquartersCountry' => 'Germany',
                  'allowPress' => true,
                  'forumThread' => $this->chainForum['id'],
                  'notes' => 'Notizen',
                  'estimatedStoreCount' => 20,
                  'commonStoreInformation' => 'Common Store information',
                  'kams' => [
                    [
                      'id' => $this->getUserByRole('chainKeyAccountManager')['id'],
                      'name' => $this->getUserByRole('chainKeyAccountManager')['name'],
                      'avatar' => null
                    ],
                    [
                        'id' => $this->getUserByRole('chainKeyAccountManagerOtherChain')['id'],
                        'name' => $this->getUserByRole('chainKeyAccountManagerOtherChain')['name'],
                      'avatar' => null
                    ]
                    ],
                    'modificationDate' => $modificationDateString
                ],
                'storeCount' => 0
        ]);

        // Test KAMS
        $I->seeNumRecords(2, 'fs_key_account_manager', ['chain_id' => $id]);
        $I->seeInDatabase(
            'fs_key_account_manager',
            ['foodsaver_id' => $this->getUserByRole('chainKeyAccountManager')['id'],
            'chain_id' => $id]
        );
        $I->seeInDatabase(
            'fs_key_account_manager',
            ['foodsaver_id' => $this->getUserByRole('chainKeyAccountManagerOtherChain')['id'],
            'chain_id' => $id]
        );
    }

    public function testMinimalValidCreationOfChain(ApiTester $I)
    {
        $I->login($this->getUserByRole('chainManager')['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            self::API_BASE,
            [
               'name' => 'New Chain minimal',
               'status' => 1,
               'headquartersZip' => '4312',
               'headquartersCity' => 'Ried in der Riedmark',
               'headquartersCountry' => 'Germany',
               'forumThread' => $this->chainForum['id'],
               'allowPress' => true,
            ]
        );
        $I->seeResponseCodeIs(Http::CREATED);
        $I->seeResponseIsJson();

        $id = $I->grabFromDatabase('fs_chain', 'id', [
            'name' => 'New Chain minimal',
            'status' => 1,
            'headquarters_zip' => '4312',
            'headquarters_city' => 'Ried in der Riedmark',
            'headquarters_country' => 'Germany',
            'allow_press' => true,
            'forum_thread' => $this->chainForum['id'],
            'notes' => null,
            'common_store_information' => null]);

        $modificationDate = $I->grabFromDatabase('fs_chain', 'modification_date', [
                'id' => $id]);
        $modificationDate = DateTime::createFromFormat('Y-m-d H:i:s', $modificationDate . ' 00:00:00', new DateTimeZone('Europe/Berlin'));

        $I->seeResponseContainsJson([
                'chain' => [
                  'id' => $id,
                  'name' => 'New Chain minimal',
                  'status' => 1,
                  'headquartersZip' => '4312',
                  'headquartersCity' => 'Ried in der Riedmark',
                  'headquartersCountry' => 'Germany',
                  'allowPress' => true,
                  'forumThread' => $this->chainForum['id'],
                  'notes' => null,
                  'commonStoreInformation' => null,
                  'kams' => [],
                  'modificationDate' => $modificationDate->format('c')
                ],
                'storeCount' => 0
        ]);

        // Test KAMS
        $I->seeNumRecords(0, 'fs_key_account_manager', ['chain_id' => $id]);
    }

    public function testRejectCreationOfChain(ApiTester $I)
    {
        $requestBodies = [];

        // Missing name
        $requestBodies[] = [
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 2,
            'allowPress' => true
        ];
        $requestBodies[] = [
            'name' => "<a href='test'>invalid name</a>",
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 2,
            'allowPress' => true
        ];
        $requestBodies[] = [
            'name' => '',
            'headquartersZip' => '4312',
            'headquartersCity' => 'empty name',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 2,
            'allowPress' => true
        ];

        // Invalid headquarter zip
        $requestBodies[] = [
            'name' => 'ToLongZip',
            'headquartersZip' => '4312123',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 2,
            'allowPress' => true
        ];
        $requestBodies[] = [
            'name' => 'empty',
            'headquartersZip' => '',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 2,
            'allowPress' => true
        ];
        // Missing headquarter zip
        $requestBodies[] = [
            'name' => 'missingZip',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 2,
            'allowPress' => true
        ];

        // missing headquarter city name
        $requestBodies[] = [
            'name' => 'missingCityName',
            'headquartersZip' => '4312',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 2,
            'allowPress' => true
        ];

        // missing headquarter country name
        $requestBodies[] = [
            'name' => 'missingCountryName',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'forumThread' => $this->chainForum['id'],
            'status' => 2,
            'allowPress' => true
        ];

        // to long headquarter city name
        $requestBodies[] = [
            'name' => 'toLongCityName',
            'headquartersZip' => '4312',
            'headquartersCity' => $this->getRandomString(51),
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 2,
            'allowPress' => true
        ];
        $requestBodies[] = [
            'name' => 'emptCityName',
            'headquartersZip' => '4312',
            'headquartersCity' => $this->getRandomString(51),
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 2,
            'allowPress' => true
        ];

        // invalid forum
        $requestBodies[] = [
            'name' => 'InvalidForumId',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'] + 1,
            'status' => 2,
            'allowPress' => true
        ];
        $requestBodies[] = [
            'name' => 'missingForum',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'status' => 2,
            'allowPress' => true
        ];
        $requestBodies[] = [
            'name' => 'InvalidForum',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => 'a',
            'status' => 2,
            'allowPress' => true
        ];

        // Invalid status
        $requestBodies[] = [
            'name' => 'Status out of range',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 3,
            'allowPress' => true
        ];
        $requestBodies[] = [
            'name' => 'Status with string',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 'a',
            'allowPress' => true
        ];
        $requestBodies[] = [
            'name' => 'Status empty',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => '',
            'allowPress' => true
        ];
        $requestBodies[] = [
            'name' => 'missing status',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'allowPress' => true
        ];

        // Invalid allowPress
        $requestBodies[] = [
            'name' => 'allowPress with true as string',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 1,
            'allowPress' => 'true'
        ];
        $requestBodies[] = [
            'name' => 'allowPress with number not boolean',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 1,
            'allowPress' => 1
        ];
        $requestBodies[] = [
            'name' => 'allowPress with string',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 1,
            'allowPress' => 'Hallo'
        ];

        $requestBodies[] = [
            'name' => 'missing allowPress',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 1,
        ];

        $I->login($this->getUserByRole('chainManager')['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        foreach ($requestBodies as &$body) {
            $I->sendPOST(self::API_BASE, $body);
            $I->seeResponseCodeIs(Http::BAD_REQUEST);
            $I->seeResponseIsJson();

            $testPattern = [];
            $testPattern['name'] = $body['name'] ?? null;
            $testPattern['headquarters_zip'] = $body['headquartersZip'] ?? null;
            $testPattern['headquarters_city'] = $body['headquartersCity'] ?? null;
            $testPattern['headquarters_country'] = $body['headquartersCountry'] ?? null;
            $testPattern['forum_thread'] = $body['forumThread'] ?? null;
            $testPattern['allow_press'] = $body['allowPress'] ?? null;
            $testPattern['status'] = $body['status'] ?? null;
            array_filter($testPattern, static function ($var) {return $var !== null; });
            $I->dontSeeInDatabase('fs_chain', $testPattern);
        }
    }

    public function testRejectOptionalPropertiesCreationOfChain(ApiTester $I)
    {
        $requestBodies = [];

        // Invalid notes
        $requestBodies[] = [
            'name' => 'To long notes text',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 1,
            'allowPress' => false,
            'notes' => bin2hex(random_bytes(201)),
            'commonStoreInformation' => 'Common Store information',
            'kams' => [$this->getUserByRole('chainKeyAccountManager')['id'], $this->getUserByRole('chainKeyAccountManagerOtherChain')['id']]
        ];
        $requestBodies[] = [
            'name' => 'With HTML break in notes',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 1,
            'allowPress' => false,
            'notes' => '<b>Hallo</b>',
            'commonStoreInformation' => 'Common Store information',
            'kams' => [$this->getUserByRole('chainKeyAccountManager')['id'], $this->getUserByRole('chainKeyAccountManagerOtherChain')['id']]
        ];
        $requestBodies[] = [
            'name' => 'With markdown markup in notes',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 1,
            'allowPress' => false,
            'notes' => '**Hallo**',
            'commonStoreInformation' => 'Common Store information',
            'kams' => [$this->getUserByRole('chainKeyAccountManager')['id'], $this->getUserByRole('chainKeyAccountManagerOtherChain')['id']]
        ];
        $requestBodies[] = [
            'name' => 'With Windows line break in notes',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 1,
            'allowPress' => false,
            'notes' => "Hallo\r\nhhh",
            'commonStoreInformation' => 'Common Store information',
            'kams' => [$this->getUserByRole('chainKeyAccountManager')['id'], $this->getUserByRole('chainKeyAccountManagerOtherChain')['id']]
        ];
        $requestBodies[] = [
            'name' => 'With UNIX line break in notes',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 1,
            'allowPress' => false,
            'notes' => "Hallo\nhhh",
            'commonStoreInformation' => 'Common Store information',
            'kams' => [$this->getUserByRole('chainKeyAccountManager')['id'], $this->getUserByRole('chainKeyAccountManagerOtherChain')['id']]
        ];

        // Test kams
        $requestBodies[] = [
            'name' => 'String a value',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 1,
            'allowPress' => true,
            'notes' => 'Notizen',
            'commonStoreInformation' => 'Common Store information',
            'kams' => 'any'
        ];
        $requestBodies[] = [
            'name' => 'Array of string as value',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 1,
            'allowPress' => true,
            'notes' => 'Notizen',
            'commonStoreInformation' => 'Common Store information',
            'kams' => ['any']
        ];
        $requestBodies[] = [
            'name' => 'Array with negativ number as value',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 1,
            'allowPress' => true,
            'notes' => 'Notizen',
            'commonStoreInformation' => 'Common Store information',
            'kams' => [-1]
        ];
        $requestBodies[] = [
            'name' => 'Array with invalid key account manager Id',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 1,
            'allowPress' => true,
            'notes' => 'Notizen',
            'commonStoreInformation' => 'Common Store information',
            'kams' => [$this->getUserByRole('chainKeyAccountManager')['id'] + 5]
        ];
        $requestBodies[] = [
            'name' => 'Array with foodsaver Id without relation to AG store chain',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'status' => 1,
            'allowPress' => true,
            'notes' => 'Notizen',
            'commonStoreInformation' => 'Common Store information',
            'kams' => [$this->getUserByRole('storeManager')['id']]
        ];

        // invalid estimated_store_count
        $requestBodies[] = [
            'name' => 'To low EstimatedStoreCount',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'estimatedStoreCount' => -1,
            'status' => 2,
            'allowPress' => true
        ];
        $requestBodies[] = [
            'name' => 'InvalidEstimatedStoreCount',
            'headquartersZip' => '4312',
            'headquartersCity' => 'Ried in der Riedmark',
            'headquartersCountry' => 'Germany',
            'forumThread' => $this->chainForum['id'],
            'estimatedStoreCount' => 'a',
            'status' => 2,
            'allowPress' => true
        ];

        // Test
        $I->login($this->getUserByRole('chainManager')['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        foreach ($requestBodies as &$body) {
            $I->sendPOST(self::API_BASE, $body);
            $I->seeResponseCodeIs(Http::BAD_REQUEST);
            $I->seeResponseIsJson();

            $testPattern = [];
            $testPattern['name'] = $body['name'] ?? null;
            $testPattern['headquarters_zip'] = $body['headquartersZip'] ?? null;
            $testPattern['headquarters_city'] = $body['headquartersCity'] ?? null;
            $testPattern['headquarters_country'] = $body['headquartersCountry'] ?? null;
            $testPattern['forum_thread'] = $body['forumThread'] ?? null;
            array_filter($testPattern, static function ($var) {return $var !== null; });
            $I->dontSeeInDatabase('fs_chain', $testPattern);
        }
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

    public function testChangeAllOfChain(ApiTester $I)
    {
        $newForum = $I->addForumThread(RegionIDs::STORE_CHAIN_GROUP, $this->chainManager['id']);

        $I->login($this->getUserByRole('chainManager')['email']);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID, [
            'name' => 'MyChain GmbH',
                'status' => 0,
                'headquartersZip' => '48150',
                'headquartersCity' => 'Münster 1',
                'headquartersCountry' => 'Germany',
                'allowPress' => false,
                'forumThread' => $newForum['id'],
                'notes' => 'Cooperating since 2021',
                'commonStoreInformation' => 'Pickup times between 11:00 and 12:15',
                'estimatedStoreCount' => 22,
                'kams' => [
                    $this->getUserByRole('chainKeyAccountManager')['id']
                ]
        ]
        );
        $I->seeResponseCodeIs(Http::OK);

        $modificationDate = $I->grabFromDatabase('fs_chain', 'modification_date', ['id' => StoreChainApiCest::CHAIN_ID]);
        $modificationDate = DateTime::createFromFormat('Y-m-d H:i:s', $modificationDate . ' 00:00:00', new DateTimeZone('Europe/Berlin'));

        $I->seeResponseContainsJson([
                'chain' => [
                  'id' => StoreChainApiCest::CHAIN_ID,
                  'name' => 'MyChain GmbH',
                  'status' => 0,
                  'headquartersZip' => '48150',
                  'headquartersCity' => 'Münster 1',
                  'headquartersCountry' => 'Germany',
                  'allowPress' => false,
                  'forumThread' => $newForum['id'],
                  'notes' => 'Cooperating since 2021',
                  'commonStoreInformation' => 'Pickup times between 11:00 and 12:15',
                  'estimatedStoreCount' => 22,
                  'kams' => [
                    [
                      'id' => $this->getUserByRole('chainKeyAccountManager')['id'],
                      'name' => $this->getUserByRole('chainKeyAccountManager')['name'],
                      'avatar' => null
                    ]
                    ],
                    'modificationDate' => $modificationDate->format('c')
                ],
                'storeCount' => 1
        ]);

        // Test KAMS
        $I->seeNumRecords(1, 'fs_key_account_manager', ['chain_id' => StoreChainApiCest::CHAIN_ID]);
        $I->seeInDatabase(
            'fs_key_account_manager',
            ['foodsaver_id' => $this->getUserByRole('chainKeyAccountManager')['id'],
            'chain_id' => StoreChainApiCest::CHAIN_ID]
        );
    }

    public function testChangeSinglePropertyOfChain(ApiTester $I)
    {
        $newForum = $I->addForumThread(RegionIDs::STORE_CHAIN_GROUP, $this->chainManager['id']);

        $I->login($this->getUserByRole('chainManager')['email']);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID, ['name' => 'MyChain GmbH 1', 'forumThread' => $this->chainForum['id'], 'headquartersZip' => '48151', 'headquartersCity' => 'Münster 2', 'headquartersCountry' => 'Österreich']);
        $I->seeResponseCodeIs(Http::OK);
        $I->sendPATCH(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID, ['name' => 'MyChain GmbH']);
        $I->seeResponseCodeIs(Http::OK);
        $I->sendPATCH(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID, ['status' => 1]);
        $I->seeResponseCodeIs(Http::OK);
        $I->sendPATCH(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID, ['headquartersZip' => '48150']);
        $I->seeResponseCodeIs(Http::OK);
        $I->sendPATCH(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID, ['headquartersCity' => 'Münster 1']);
        $I->seeResponseCodeIs(Http::OK);
        $I->sendPATCH(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID, ['headquartersCountry' => 'Schweiz']);
        $I->seeResponseCodeIs(Http::OK);
        $I->sendPATCH(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID, ['forumThread' => $newForum['id']]);
        $I->seeResponseCodeIs(Http::OK);
        $I->sendPATCH(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID, ['notes' => 'Cooperating since 2021']);
        $I->seeResponseCodeIs(Http::OK);
        $I->sendPATCH(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID, ['commonStoreInformation' => 'Pickup times between 11:00 and 12:15']);
        $I->seeResponseCodeIs(Http::OK);
        $I->sendPATCH(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID, ['estimatedStoreCount' => 12]);
        $I->seeResponseCodeIs(Http::OK);

        $I->sendPATCH(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID, ['kams' => [
            $this->getUserByRole('chainKeyAccountManager')['id']
        ]]);
        $I->seeResponseCodeIs(Http::OK);

        $modificationDate = $I->grabFromDatabase('fs_chain', 'modification_date', ['id' => StoreChainApiCest::CHAIN_ID]);
        $modificationDate = DateTime::createFromFormat('Y-m-d H:i:s', $modificationDate . ' 00:00:00', new DateTimeZone('Europe/Berlin'));

        $I->seeResponseContainsJson([
                'chain' => [
                  'regionId' => RegionIDs::STORE_CHAIN_GROUP,
                  'id' => StoreChainApiCest::CHAIN_ID,
                  'name' => 'MyChain GmbH',
                  'status' => 1,
                  'headquartersZip' => '48150',
                  'headquartersCity' => 'Münster 1',
                  'headquartersCountry' => 'Schweiz',
                  'allowPress' => false,
                  'forumThread' => $newForum['id'],
                  'notes' => 'Cooperating since 2021',
                  'commonStoreInformation' => 'Pickup times between 11:00 and 12:15',
                  'estimatedStoreCount' => 12,
                  'kams' => [
                    [
                      'id' => $this->getUserByRole('chainKeyAccountManager')['id'],
                      'name' => $this->getUserByRole('chainKeyAccountManager')['name'],
                      'avatar' => null
                    ]
                    ],
                    'modificationDate' => $modificationDate->format('c')
                ],
                'storeCount' => 1
        ]);

        // Test KAMS
        $I->seeNumRecords(1, 'fs_key_account_manager', ['chain_id' => StoreChainApiCest::CHAIN_ID]);
        $I->seeInDatabase(
            'fs_key_account_manager',
            ['foodsaver_id' => $this->getUserByRole('chainKeyAccountManager')['id'],
            'chain_id' => StoreChainApiCest::CHAIN_ID]
        );
    }

    public function testRejectUpdateOfChain(ApiTester $I)
    {
        $requestBodies = [];

        // Invalid name
        $requestBodies[] = [
            'name' => ''
        ];
        $requestBodies[] = [
            'name' => '<span>HTML Name</span>'
        ];
        $requestBodies[] = [
            'name' => '**Markdown Name**'
        ];
        $requestBodies[] = [
            'name' => '**Multiline
                 Name**'
        ];

        // Invalid status
        $requestBodies[] = [
            'name' => 'Status out of range',
            'status' => 3
        ];
        $requestBodies[] = [
            'name' => 'Status with string',
            'status' => 'a'
        ];
        $requestBodies[] = [
            'name' => 'Status empty string',
            'status' => ''
        ];

        // Invalid allowPress
        $requestBodies[] = [
            'name' => 'allowPress with true as string',
            'allowPress' => 'true'
        ];
        $requestBodies[] = [
            'name' => 'allowPress with number not boolean',
            'allowPress' => 1
        ];
        $requestBodies[] = [
            'name' => 'allowPress with string',
            'allowPress' => 'Hallo'
        ];

        // Invalid notes
        $requestBodies[] = [
            'name' => 'To long notes text',
            'notes' => bin2hex(random_bytes(201))
        ];
        $requestBodies[] = [
            'name' => 'With HTML break in notes',
            'notes' => '<b>Hallo</b>'
        ];
        $requestBodies[] = [
            'name' => 'With markdown markup in notes',
            'notes' => '**Hallo**'
        ];
        $requestBodies[] = [
            'name' => 'With Multi line break in notes',

            'notes' => 'Hallo
                hhh'
        ];
        $requestBodies[] = [
            'name' => 'With Windows line break in notes',

            'notes' => "Hallo\r\nhhh"
        ];
        $requestBodies[] = [
            'name' => 'With UNIX line break in notes',
            'notes' => "Hallo\nhhh"
        ];

        // Test kams
        $requestBodies[] = [
            'name' => 'String a value',
            'kams' => 'any'
        ];
        $requestBodies[] = [
            'name' => 'Array of string as value',
            'kams' => ['any']
        ];
        $requestBodies[] = [
            'name' => 'Array with negativ number as value',
            'kams' => [-1]
        ];
        $requestBodies[] = [
            'name' => 'Array with invalid key account manager Id',
            'kams' => [$this->getUserByRole('chainKeyAccountManager')['id'] + 5]
        ];
        $requestBodies[] = [
            'name' => 'Array with foodsaver Id without relation to AG store chain',
            'kams' => [$this->getUserByRole('storeManager')['id']]
        ];

        // Invalid headquarter zip
        $requestBodies[] = [
            'name' => 'ToLongZip',
            'headquartersZip' => '4312123'
        ];
        $requestBodies[] = [
            'name' => 'empty zip',
            'headquartersZip' => ''
        ];

        // missing city name
        $requestBodies[] = [
            'name' => 'missingCityName',
            'headquartersCity' => '',
            'forumThread' => $this->chainForum['id'],
        ];
        $requestBodies[] = [
            'name' => 'To long city',
            'headquartersCity' => bin2hex(random_bytes(51))
        ];
        $requestBodies[] = [
            'name' => 'Empty city',
            'headquartersCity' => ''
        ];
        $requestBodies[] = [
            'name' => 'With HTML break in city',
            'headquartersCity' => '<b>Hallo</b>'
        ];
        $requestBodies[] = [
            'name' => 'With markdown markup in city',
            'headquartersCity' => '**Hallo**'
        ];
        $requestBodies[] = [
            'name' => 'With Windows line break in city',
            'headquartersCity' => "Hallo\r\nhhh"
        ];
        $requestBodies[] = [
            'name' => 'With UNIX line break in city',
            'headquartersCity' => "Hallo\nhhh"
        ];

        // missing city name
        $requestBodies[] = [
            'name' => 'missingcountryName',
            'headquartersCountry' => '',
            'forumThread' => $this->chainForum['id'],
        ];
        $requestBodies[] = [
            'name' => 'To long country',
            'headquartersCountry' => bin2hex(random_bytes(51))
        ];
        $requestBodies[] = [
            'name' => 'Empty country',
            'headquartersCountry' => ''
        ];
        $requestBodies[] = [
            'name' => 'With HTML break in country',
            'headquartersCountry' => '<b>Hallo</b>'
        ];
        $requestBodies[] = [
            'name' => 'With markdown markup in country',
            'headquartersCountry' => '**Hallo**'
        ];
        $requestBodies[] = [
            'name' => 'With Windows line break in country',
            'headquartersCountry' => "Hallo\r\nhhh"
        ];
        $requestBodies[] = [
            'name' => 'With UNIX line break in country',
            'headquartersCountry' => "Hallo\nhhh"
        ];

        // invalid forum
        $requestBodies[] = [
            'name' => 'InvalidForumId',
            'forumThread' => $this->chainForum['id'] + 1
        ];
        $requestBodies[] = [
            'name' => 'missingForum',
            'forumThread' => ''
        ];
        $requestBodies[] = [
            'name' => 'InvalidForum',
            'forumThread' => 'a'
        ];

        // invalid estimated_store_count
        $requestBodies[] = [
            'name' => 'To low EstimatedStoreCount',
            'estimatedStoreCount' => -1
        ];
        $requestBodies[] = [
            'name' => 'InvalidEstimatedStoreCount',
            'estimatedStoreCount' => 'a',
        ];

        // Test
        $I->login($this->getUserByRole('chainManager')['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(self::API_BASE . '/' . 142, ['name' => 'Invalid chain id']);
        $I->seeResponseCodeIs(Http::NOT_FOUND);
        $I->seeResponseIsJson();

        foreach ($requestBodies as &$body) {
            $I->sendPATCH(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID, $body);
            $I->seeResponseCodeIs(Http::BAD_REQUEST);
            $I->seeResponseIsJson();

            $testPattern = [];
            $testPattern['name'] = $body['name'] ?? null;
            $testPattern['headquarters_zip'] = $body['headquartersZip'] ?? null;
            $testPattern['headquarters_city'] = $body['headquartersCity'] ?? null;
            $testPattern['allow_press'] = $body['allowPress'] ?? null;
            $testPattern['forum_thread'] = $body['forumThread'] ?? null;
            $testPattern['common_store_information'] = $body['commonStoreInformation'] ?? null;
            array_filter($testPattern, static function ($var) {return $var !== null; });
            $I->dontSeeInDatabase('fs_chain', $testPattern);
        }
    }

    /**
     * Test access of user role to store chain details (no content check).
     *
     * Expect that user get an error 403 on HTTP level
     *
     *
     * Forbidden roles:
     * anonym
     *
     * @example { "role": "foodsharer", "access": false}
     * @example { "role": "verifiedFoodsaver", "access": false}
     * @example { "role": "unverifiedFoodsaver", "access": false}
     * @example { "role": "storeTeamMemberWithoutChain", "access": false}
     *
     * Allowed roles:
     * @example { "role": "storeTeamMember", "access": true}
     * @example { "role": "storeManagerWithoutChain", "access": true}
     * @example { "role": "orga", "access": true}
     * @example { "role": "chainManager", "access": true}
     * @example { "role": "storeManagerAgChainMember", "access": true}
     * @example { "role": "chainMember", "access": true}
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

    /**
     * @example { "role": "storeTeamMember", "details": false}
     * @example { "role": "storeManagerWithoutChain", "details": false}
     * @example { "role": "storeManager", "details": false}
     * @example { "role": "storeManagerAgChainMember", "details": true}
     * @example { "role": "chainManager", "details": true}
     * @example { "role": "chainMember", "details": true}
     * @example { "role": "chainKeyAccountManager", "details": true}
     * @example { "role": "chainKeyAccountManagerOtherChain", "details": true}
     * @example { "role": "orga", "details": true}
     */
    public function testSeeInformationPermissionDependentForGetSingleStoreChainInformationEndpoint(ApiTester $I, Example $example)
    {
        $role = $example['role'];
        $details = $example['details'];
        $modificationDate = new DateTime('now', new DateTimeZone('Europe/Berlin'));
        $newChain = $I->addStoreChain([
            'name' => 'New Chain',
            'status' => 2,
            'headquarters_zip' => '4312',
            'headquarters_city' => 'Ried in der Riedmark',
            'headquarters_country' => 'Germany',
            'allow_press' => true,
            'forum_thread' => $this->chainForum['id'],
            'notes' => 'Notizen',
            'common_store_information' => 'Common Store information',
            'modification_date' => $modificationDate->format('Y-m-d')
        ]);
        $I->haveInDatabase('fs_key_account_manager', ['foodsaver_id' => $this->chainKeyAccountManager['id'], 'chain_id' => $newChain['id']]);
        $I->createStore($this->region['id'], null, null, ['kette_id' => $newChain['id']]);
        $I->createStore($this->region['id'], null, null, ['kette_id' => $newChain['id']]);
        $I->createStore($this->region['id'], null, null, ['kette_id' => $newChain['id']]);

        $I->login($this->getUserByRole($role)['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::API_BASE . '/' . $newChain['id']);
        $I->seeResponseCodeIs(Http::OK);

        $expectation = [
            'chain' => [
                'id' => $newChain['id'],
                'name' => $newChain['name'],
                'headquartersZip' => $newChain['headquarters_zip'],
                'headquartersCity' => $newChain['headquarters_city'],
                'headquartersCountry' => $newChain['headquarters_country'],
                'status' => $newChain['status'],
                'allowPress' => $newChain['allow_press'],
                'commonStoreInformation' => $newChain['common_store_information'],
                'modificationDate' => $modificationDate->format('Y-m-d\T00:00:00+02:00'),
                'forumThread' => $details ? $newChain['forum_thread'] : null,
                'notes' => $details ? $newChain['notes'] : null,
                'kams' => [
                    [
                    'id' => $this->chainKeyAccountManager['id'],
                    'name' => $this->chainKeyAccountManager['name'],
                    'avatar' => null
                    ]
                ],
                'regionId' => $details ? RegionIDs::STORE_CHAIN_GROUP : null
            ],
            'storeCount' => $details ? 3 : null
        ];

        $I->seeResponseContainsJson($expectation);
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
     * @example { "role": "verifiedFoodsaver", "access": false}
     * @example { "role": "unverifiedFoodsaver", "access": false}
     * @example { "role": "storeTeamMemberWithoutChain", "access": false}
     *
     * Allowed roles:
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

    private function createStoreChain($I, DateTime $modificationDate, int $countStores, array $keyAccountManagerIds = [])
    {
        $seed = rand();
        $newChain = $I->addStoreChain([
            'name' => 'New Chain ' . $seed,
            'status' => 2,
            'headquarters_zip' => '4312',
            'headquarters_city' => 'Ried in der Riedmark',
            'headquarters_country' => 'Germany',
            'allow_press' => true,
            'forum_thread' => $this->chainForum['id'],
            'estimated_store_count' => 1,
            'notes' => 'Notizen',
            'common_store_information' => 'Common Store information',
            'modification_date' => $modificationDate->format('Y-m-d')
        ]);
        foreach ($keyAccountManagerIds as $keyAccountManagerId) {
            $I->haveInDatabase('fs_key_account_manager', ['foodsaver_id' => $keyAccountManagerId, 'chain_id' => $newChain['id']]);
        }
        for ($i = 0; $i < $countStores; ++$i) {
            $I->createStore($this->region['id'], null, null, ['kette_id' => $newChain['id']]);
        }

        return $newChain;
    }

    /**
     * Tests the list all storechain endpoint that the user groups only is the information
     * they are allowed to see.
     *
     * Only see
     *
     * @example { "role": "storeTeamMember", "details": false}
     * @example { "role": "storeManagerWithoutChain", "details": false}
     * @example { "role": "storeManager", "details": false}
     * @example { "role": "storeManagerAgChainMember", "details": true}
     * @example { "role": "chainManager", "details": true}
     * @example { "role": "chainMember", "details": true}
     * @example { "role": "chainKeyAccountManager", "details": true}
     * @example { "role": "chainKeyAccountManagerOtherChain", "details": true}
     * @example { "role": "orga", "details": true}
     */
    public function testInformationForGetAllStoreChainInformationEndpoint(ApiTester $I, Example $example)
    {
        $role = $example['role'];
        $details = $example['details'];

        $modificationDate = new DateTime('now', new DateTimeZone('Europe/Berlin'));

        $newChain = $this->createStoreChain($I, $modificationDate, 3, [$this->chainKeyAccountManager['id']]);
        $newChain1 = $this->createStoreChain($I, $modificationDate, 2, [$this->chainKeyAccountManager['id']]);

        $I->login($this->getUserByRole($role)['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::API_BASE);
        $I->seeResponseCodeIs(Http::OK);
        $expectation = [[
            'chain' => [
                'id' => $newChain['id'],
                'name' => $newChain['name'],
                'headquartersZip' => $newChain['headquarters_zip'],
                'headquartersCity' => $newChain['headquarters_city'],
                'status' => $newChain['status'],
                'allowPress' => $newChain['allow_press'],
                'commonStoreInformation' => $newChain['common_store_information'],
                'modificationDate' => $modificationDate->format('Y-m-d\T00:00:00+02:00'),
                'forumThread' => $details ? $newChain['forum_thread'] : null,
                'notes' => $details ? $newChain['notes'] : null,
                'kams' => [
                    [
                    'id' => $this->chainKeyAccountManager['id'],
                    'name' => $this->chainKeyAccountManager['name'],
                    'avatar' => null
                    ]
                ],
                'regionId' => $details ? RegionIDs::STORE_CHAIN_GROUP : null
            ],
            'storeCount' => $details ? 3 : null
        ],
        [
            'chain' => [
                'id' => $newChain1['id'],
                'name' => $newChain1['name'],
                'headquartersZip' => $newChain1['headquarters_zip'],
                'headquartersCity' => $newChain1['headquarters_city'],
                'status' => $newChain1['status'],
                'allowPress' => $newChain1['allow_press'],
                'commonStoreInformation' => $newChain1['common_store_information'],
                'modificationDate' => $modificationDate->format('Y-m-d\T00:00:00+02:00'),
                'forumThread' => $details ? $newChain1['forum_thread'] : null,
                'notes' => $details ? $newChain1['notes'] : null,
                'kams' => [
                    [
                    'id' => $this->chainKeyAccountManager['id'],
                    'name' => $this->chainKeyAccountManager['name'],
                    'avatar' => null
                    ]
                ],
                'regionId' => $details ? RegionIDs::STORE_CHAIN_GROUP : null
            ],
            'storeCount' => $details ? 2 : null
        ]
        ];

        $I->seeResponseContainsJson($expectation);
    }

    /*
     * Test pagination for list of store chain
     */
   public function testInformationForGetAllStoreChainPaginationEndpoint(ApiTester $I)
   {
       $role = 'orga';

       $modificationDate = new DateTime('now', new DateTimeZone('Europe/Berlin'));

       // Already existing 1
       // Already existing 2
       $this->createStoreChain($I, $modificationDate, 3, [$this->chainKeyAccountManager['id']]);
       $this->createStoreChain($I, $modificationDate, 2, [$this->chainKeyAccountManager['id']]);
       $this->createStoreChain($I, $modificationDate, 1, [$this->chainKeyAccountManager['id']]);
       $this->createStoreChain($I, $modificationDate, 0, [$this->chainKeyAccountManager['id']]);

       $I->login($this->getUserByRole($role)['email']);
       $I->haveHttpHeader('Content-Type', 'application/json');

       // Test unlimited
       $I->sendGet(self::API_BASE, ['pageSize' => 0, 'offset' => 0]);
       $I->seeResponseCodeIs(Http::OK);
       $I->seeResponseIsJson();
       $storeCounts = $I->grabDataFromResponseByJsonPath('$..storeCount');
       $I->assertEquals(1, $storeCounts[0]);
       $I->assertEquals(0, $storeCounts[1]);
       $I->assertEquals(3, $storeCounts[2]);
       $I->assertEquals(2, $storeCounts[3]);
       $I->assertEquals(1, $storeCounts[4]);
       $I->assertEquals(0, $storeCounts[5]);

       // Test size limit with offset
       $I->sendGet(self::API_BASE, ['pageSize' => 2, 'offset' => 2]);
       $I->seeResponseCodeIs(Http::OK);
       $I->seeResponseIsJson();
       $storeCounts = $I->grabDataFromResponseByJsonPath('$..storeCount');
       $I->assertEquals(3, $storeCounts[0]);
       $I->assertEquals(2, $storeCounts[1]);

       // Test size limit
       $I->sendGet(self::API_BASE, ['pageSize' => 1]);
       $I->seeResponseCodeIs(Http::OK);
       $I->seeResponseIsJson();
       $storeCounts = $I->grabDataFromResponseByJsonPath('$..storeCount');
       $I->assertEquals(1, $storeCounts[0]);

       $I->sendGet(self::API_BASE, ['pageSize' => 2]);
       $I->seeResponseCodeIs(Http::OK);
       $I->seeResponseIsJson();
       $storeCounts = $I->grabDataFromResponseByJsonPath('$..storeCount');
       $I->assertEquals(1, $storeCounts[0]);
       $I->assertEquals(0, $storeCounts[1]);

       // Test size limit with offset
       $I->sendGet(self::API_BASE, ['pageSize' => 2, 'offset' => 2]);
       $I->seeResponseCodeIs(Http::OK);
       $I->seeResponseIsJson();
       $storeCounts = $I->grabDataFromResponseByJsonPath('$..storeCount');
       $I->assertEquals(3, $storeCounts[0]);
       $I->assertEquals(2, $storeCounts[1]);

       // Test partial output on end of storechain table
       $I->sendGet(self::API_BASE, ['pageSize' => 2, 'offset' => 5]);
       $I->seeResponseCodeIs(Http::OK);
       $I->seeResponseIsJson();
       $storeCounts = $I->grabDataFromResponseByJsonPath('$..storeCount');
       $I->assertEquals(0, $storeCounts[0]);
   }

   /*
     * Test pagination for list of store chain
     */
   public function testBadRequestInformationForGetAllStoreChainPaginationEndpoint(ApiTester $I)
   {
       $role = 'orga';

       $I->login($this->getUserByRole($role)['email']);
       $I->haveHttpHeader('Content-Type', 'application/json');

       $I->sendGet(self::API_BASE, ['pageSize' => 'a', 'offset' => 0]);
       $I->seeResponseCodeIs(Http::BAD_REQUEST);

       $I->sendGet(self::API_BASE, ['pageSize' => 0, 'offset' => 'a']);
       $I->seeResponseCodeIs(Http::BAD_REQUEST);

       $I->sendGet(self::API_BASE, ['pageSize' => -1, 'offset' => 0]);
       $I->seeResponseCodeIs(Http::BAD_REQUEST);

       $I->sendGet(self::API_BASE, ['pageSize' => 0, 'offset' => -1]);
       $I->seeResponseCodeIs(Http::BAD_REQUEST);
   }

    /*
     * Test list stores of store chain
     */
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
     * @example { "role": "storeTeamMember", "access": false}
     * @example { "role": "storeManagerWithoutChain", "access": false}
     * @example { "role": "storeManager", "access": false}
     *
     * Allowed roles:
     * @example { "role": "chainMember", "access": true}
     * @example { "role": "chainManager", "access": true}
     * @example { "role": "orga", "access": true}
     * @example { "role": "storeManagerAgChainMember", "access": true}
     * @example { "role": "chainKeyAccountManager", "access": true}
     * @example { "role": "chainKeyAccountManagerOtherChain", "access": true}
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

    public function testContentOfGetStoresOfStoreChainEndpoint(ApiTester $I)
    {
        $modificationDate = new DateTime('now', new DateTimeZone('Europe/Berlin'));
        $newChain = $I->addStoreChain([
            'name' => 'New Chain',
            'status' => 2,
            'headquarters_zip' => '4312',
            'headquarters_city' => 'Ried in der Riedmark',
            'estimated_store_count' => 12,
            'allow_press' => true,
            'forum_thread' => $this->chainForum['id'],
            'notes' => 'Notizen',
            'common_store_information' => 'Common Store information',
            'modification_date' => $modificationDate->format('Y-m-d')
        ]);

        $stores = [];
        $expectedResponse = [];
        for ($i = 0; $i < 5; ++$i) {
            $store = $I->createStore($this->region['id'], null, null, ['kette_id' => $newChain['id']]);
            $expectedResponse[] = ['id' => $store['id'], 'name' => $store['name']];
            $stores[] = $store;
        }
        $url = self::API_BASE . '/' . $newChain['id'] . '/stores';

        $I->login($this->getUserByRole('chainManager')['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet($url);
        $I->seeResponseCodeIs(HTTP::OK);
        $I->seeResponseContainsJson($expectedResponse);

        // Test size limit
        $I->sendGet($url, ['pageSize' => 1]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $storeIds = $I->grabDataFromResponseByJsonPath('$..id');
        $I->assertCount(1, $storeIds);
        $I->assertEquals($stores[0]['id'], $storeIds[0]);

        $I->sendGet($url, ['pageSize' => 2]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $storeIds = $I->grabDataFromResponseByJsonPath('$..id');
        $I->assertCount(2, $storeIds);
        $I->assertEquals($stores[0]['id'], $storeIds[0]);
        $I->assertEquals($stores[1]['id'], $storeIds[1]);

        // Test size limit with offset
        $I->sendGet($url, ['pageSize' => 2, 'offset' => 2]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $storeIds = $I->grabDataFromResponseByJsonPath('$..id');
        $I->assertCount(2, $storeIds);
        $I->assertEquals($stores[2]['id'], $storeIds[0]);
        $I->assertEquals($stores[3]['id'], $storeIds[1]);

        // Test partial output on end of storechain table
        $I->sendGet($url, ['pageSize' => 2, 'offset' => 4]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $storeIds = $I->grabDataFromResponseByJsonPath('$..id');
        $I->assertCount(1, $storeIds);
        $I->assertEquals($stores[4]['id'], $storeIds[0]);
    }

   public function testBadRequestInformationForGetStoresOfStoreChainEndpoint(ApiTester $I)
   {
       $I->login($this->getUserByRole('chainManager')['email']);
       $I->haveHttpHeader('Content-Type', 'application/json');

       // Test bad id
       $I->sendGet(self::API_BASE . '/42/stores');
       $I->seeResponseCodeIs(Http::NOT_FOUND);

       $I->sendGet(self::API_BASE . '/a/stores');
       $I->seeResponseCodeIs(Http::NOT_FOUND);

       // Test bad pagination for stores of store chain
       $I->sendGet(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID . '/stores', ['pageSize' => 'a', 'offset' => 0]);
       $I->seeResponseCodeIs(Http::BAD_REQUEST);

       $I->sendGet(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID . '/stores', ['pageSize' => 0, 'offset' => 'a']);
       $I->seeResponseCodeIs(Http::BAD_REQUEST);

       $I->sendGet(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID . '/stores', ['pageSize' => -1, 'offset' => 0]);
       $I->seeResponseCodeIs(Http::BAD_REQUEST);

       $I->sendGet(self::API_BASE . '/' . StoreChainApiCest::CHAIN_ID . '/stores', ['pageSize' => 0, 'offset' => -1]);
       $I->seeResponseCodeIs(Http::BAD_REQUEST);
   }
}
