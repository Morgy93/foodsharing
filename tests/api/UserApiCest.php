<?php

namespace api;

use Codeception\Example;
use Codeception\Util\HttpCode as Http;
use Faker;

/**
 * Tests for the user api.
 */
class UserApiCest
{
	private $user;
	private $userOrga;
	private $faker;

	private const EMAIL = 'email';
	private const API_USER = 'api/user';
	private const ID = 'id';

	public function _before(\ApiTester $I)
	{
		$this->user = $I->createFoodsaver();
		$this->userOrga = $I->createOrga();

		$this->faker = Faker\Factory::create('de_DE');
	}

	public function getUser(\ApiTester $I)
	{
		$testUser = $I->createFoodsaver();
		$I->login($this->user[self::EMAIL]);

		// see your own data
		$I->sendGET(self::API_USER . '/' . $this->user[self::ID]);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		$I->sendGET(self::API_USER . '/current');
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		// see someone else's data
		$I->sendGET(self::API_USER . '/' . $testUser[self::ID]);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		// do not see data of a non-existing user
		$I->sendGET(self::API_USER . '/999999999');
		$I->seeResponseCodeIs(Http::NOT_FOUND);
		$I->seeResponseIsJson();
	}

	public function getUserDetails(\ApiTester $I)
	{
		$testUser = $I->createFoodsaver();
		$I->login($this->user[self::EMAIL]);

		// see your own details
		$I->sendGET(self::API_USER . '/' . $this->user[self::ID] . '/details');
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		$I->sendGET(self::API_USER . '/current/details');
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		// do not see someone else's details unless you are orga
		$I->sendGET(self::API_USER . '/' . $testUser[self::ID] . '/details');
		$I->seeResponseCodeIs(Http::FORBIDDEN);
		$I->seeResponseIsJson();

		$I->login($this->userOrga[self::EMAIL]);
		$I->sendGET(self::API_USER . '/' . $testUser[self::ID] . '/details');
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		// do not see details of non-existing user
		$I->sendGET(self::API_USER . '/999999999/details');
		codecept_debug($I->grabResponse());
		$I->seeResponseCodeIs(Http::NOT_FOUND);
		$I->seeResponseIsJson();
	}

	/**
	 * @example["abcd@efgh.com"]
	 * @example["test123@somedomain.de"]
	 */
	public function canUseEmailForRegistration(\ApiTester $I, Example $example): void
	{
		$I->sendPOST(self::API_USER . '/isvalidemail', ['email' => $example[0]]);
		$I->seeResponseCodeIs(Http::OK);
	}

	/**
	 * @example["abcd"]
	 * @example["abcd@efgh"]
	 * @example["abcd@-efgh"]
	 */
	public function canNotUseInvalidMailForRegistration(\ApiTester $I, Example $example): void
	{
		$I->sendPOST(self::API_USER . '/isvalidemail', ['email' => $example[0]]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson([
			'message' => 'email is not valid'
		]);
	}

	/**
	 * @example["abcd@foodsharing.de"]
	 * @example["abcd@foodsharing.network"]
	 */
	public function canNotUseFoodsharingEmailForRegistration(\ApiTester $I, Example $example): void
	{
		$I->sendPOST(self::API_USER . '/isvalidemail', ['email' => $example[0]]);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson([
			'valid' => false
		]);
	}

	public function canNotUseExistingEmailForRegistration(\ApiTester $I): void
	{
		// already existing email
		$I->sendPOST(self::API_USER . '/isvalidemail', ['email' => $this->user['email']]);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson([
			'valid' => false
		]);

		// not yet existing email
		$email = 'test123@somedomain.de';
		$I->sendPOST(self::API_USER . '/isvalidemail', ['email' => $email]);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson([
			'valid' => true
		]);

		$I->createFoodsharer(null, ['email' => $email]);
		$I->sendPOST(self::API_USER . '/isvalidemail', ['email' => $email]);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson([
			'valid' => false
		]);
	}

	public function canGiveBanana(\ApiTester $I): void
	{
		// create text of at least 150 characters
		$text = $this->faker->text(150);
		while (strlen($text) < 150) {
			$text .= ' ' . $this->faker->text(20);
		}

		$testUser = $I->createFoodsaver();
		$I->login($this->user[self::EMAIL]);
		$I->sendPUT(self::API_USER . '/' . $testUser['id'] . '/banana', ['message' => $text]);
		$I->seeResponseCodeIs(Http::OK);
	}

	public function canNotGiveBananaWithShortMessage(\ApiTester $I): void
	{
		$testUser = $I->createFoodsaver();
		$I->login($this->user[self::EMAIL]);
		$I->sendPUT(self::API_USER . '/' . $testUser['id'] . '/banana', ['message' => $this->faker->text(50)]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
	}

	public function canNotGiveBananaTwice(\ApiTester $I): void
	{
		$testUser = $I->createFoodsaver();
		$I->login($this->user[self::EMAIL]);
		$I->sendPUT(self::API_USER . '/' . $testUser['id'] . '/banana', ['message' => $this->faker->text(120)]);
		$I->seeResponseCodeIs(Http::OK);
		$I->sendPUT(self::API_USER . '/' . $testUser['id'] . '/banana', ['message' => $this->faker->text(120)]);
		$I->seeResponseCodeIs(Http::FORBIDDEN);
	}

	public function canNotGiveBananaToMyself(\ApiTester $I): void
	{
		$I->login($this->user[self::EMAIL]);
		$I->sendPUT(self::API_USER . '/' . $this->user['id'] . '/banana', ['message' => $this->faker->text(120)]);
		$I->seeResponseCodeIs(Http::FORBIDDEN);
	}
}
