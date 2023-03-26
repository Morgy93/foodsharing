<?php

namespace api;

use ApiTester;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Codeception\Example;
use Codeception\Util\HttpCode as Http;
use DateTime;

use function PHPUnit\Framework\assertNotNull;

/**
 * Tests for the voting api.
 */
class VerificationApiCest
{
    private array $region;
    private array $foodsaver;
    private array $foodsaver2;
    private array $ambassador;
    private array $orga;

    public function _before(ApiTester $I)
    {
        $this->region = $I->createRegion();
        $this->ambassador = $I->createAmbassador(null, ['bezirk_id' => $this->region['id']]);
        $I->addRegionAdmin($this->region['id'], $this->ambassador['id']);
        $this->orga = $I->createOrga();

        $this->foodsaver = $I->createFoodsaver(null, ['bezirk_id' => $this->region['id']]);
        $this->foodsaver2 = $I->createFoodsaver(null, ['bezirk_id' => $this->region['id']]);
        foreach ([$this->foodsaver, $this->foodsaver2] as $fs) {
            foreach (range(0, 4) as $i) {
                $I->addVerificationHistory($fs['id'], $this->ambassador['id'], $i % 2 == 0, Carbon::now()->subWeeks($i + 1));
                $I->addPassHistory($fs['id'], $this->ambassador['id'], Carbon::now()->subWeeks($i + 1));
            }
        }
    }

    /**
     * @example["passhistory"]
     * @example["verificationhistory"]
     */
    public function canOnlySeeHistoryWhenLoggedIn(ApiTester $I, Example $example)
    {
        $I->sendGET('api/user/' . $this->foodsaver['id'] . '/' . $example[0]);
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);
    }

    /**
     * @example["passhistory"]
     * @example["verificationhistory"]
     */
    public function canNotSeeHistoryAsFoodsaver(ApiTester $I, Example $example)
    {
        $I->login($this->foodsaver['email']);
        $I->sendGET('api/user/' . $this->foodsaver['id'] . '/' . $example[0]);
        $I->seeResponseCodeIs(Http::FORBIDDEN);
    }

    /**
     * @example["passhistory"]
     * @example["verificationhistory"]
     */
    public function canSeeHistoryAsAmbassador(ApiTester $I, Example $example)
    {
        $I->login($this->ambassador['email']);
        $I->sendGET('api/user/' . $this->foodsaver['id'] . '/' . $example[0]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->canSeeResponseContainsJson([[
            'foodsaverId' => $this->foodsaver['id'],
            'actor' => [
                'id' => $this->ambassador['id']
            ]
        ]]);

        $dates = $I->grabDataFromResponseByJsonPath('$[*].date');
        foreach ($dates as $date) {
            assertNotNull($this->parseDate($I, $date));
        }
    }

    /**
     * @example["passhistory"]
     * @example["verificationhistory"]
     */
    public function canSeeHistoryAsOrga(ApiTester $I, Example $example)
    {
        $I->login($this->orga['email']);
        $I->sendGET('api/user/' . $this->foodsaver['id'] . '/' . $example[0]);
        $I->seeResponseCodeIs(Http::OK);
        $I->seeResponseIsJson();
        $I->canSeeResponseContainsJson([[
            'foodsaverId' => $this->foodsaver['id'],
            'actor' => [
                'id' => $this->ambassador['id']
            ]
        ]]);

        $dates = $I->grabDataFromResponseByJsonPath('$[*].date');
        foreach ($dates as $date) {
            assertNotNull($this->parseDate($I, $date));
        }
    }

    /**
     * Tries to parse a date string. Returns null if the string can not be parsed.
     */
    private function parseDate(ApiTester $I, string $date): ?DateTime
    {
        try {
            return Carbon::parse($date);
        } catch (InvalidFormatException $t) {
            return null;
        }
    }
}
