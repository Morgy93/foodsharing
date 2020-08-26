<?php

namespace api;

use ApiTester;
use Codeception\Util\HttpCode as Http;
use DateTime;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingScope;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingType;

/**
 * Tests for the voting api.
 */
class VotingApiCest
{
	private $region;
	private $userFoodsaverUnverified;
	private $userFoodsaver;
	private $userStoreManager;
	private $userAmbassador;
	private $poll;

	private const POLLS_API = 'api/polls';
	private const GROUPS_API = 'api/groups';

	public function _before(ApiTester $I)
	{
		$this->region = $I->createRegion();
		$this->userFoodsaverUnverified = $I->createFoodsaver(null, ['bezirk_id' => $this->region['id'], 'verified' => 0]);
		$this->userFoodsaver = $I->createFoodsaver(null, ['bezirk_id' => $this->region['id']]);

		$this->userAmbassador = $I->createAmbassador(null, ['bezirk_id' => $this->region['id']]);
		$I->addRegionAdmin($this->region['id'], $this->userAmbassador['id']);

		$store = $I->createStore($this->region['id']);
		$this->userStoreManager = $I->createStoreCoordinator(null, ['bezirk_id' => $this->region['id']]);
		$I->addStoreTeam($store['id'], $this->userStoreManager['id'], true);

		$this->poll = $this->createPoll($I, [1], ['type' => VotingType::SELECT_ONE_CHOICE]);
	}

	public function canSeePolls(ApiTester $I)
	{
		$I->sendGET(self::POLLS_API . '/' . $this->poll['id']);
		$I->seeResponseCodeIs(Http::FORBIDDEN);

		$I->sendGET(self::GROUPS_API . '/' . $this->region['id'] . '/polls');
		$I->seeResponseCodeIs(Http::FORBIDDEN);

		$I->login($this->userFoodsaver['email']);
		$I->sendGET(self::POLLS_API . '/' . $this->poll['id']);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson([
			'id' => $this->poll['id']
		]);

		$I->sendGET(self::GROUPS_API . '/' . $this->region['id'] . '/polls');
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson([
			'id' => $this->poll['id']
		]);
	}

	public function canNotGetNonExistingPoll(ApiTester $I)
	{
		$I->login($this->userFoodsaver['email']);
		$I->sendGET(self::POLLS_API . '/' . ($this->poll['id'] + 1));
		$I->seeResponseCodeIs(Http::NOT_FOUND);
	}

	public function canOnlyVoteOnce(ApiTester $I)
	{
		$choice = 0;
		$votes = $I->grabFromDatabase('fs_poll_option_has_value', 'votes', [
			'poll_id' => $this->poll['id'],
			'option' => $choice,
			'value' => 1
		]);

		$I->login($this->userFoodsaver['email']);
		$I->sendPUT(self::POLLS_API . '/' . $this->poll['id'] . '/vote', ['options' => [$choice => 1]]);
		$I->seeResponseCodeIs(Http::OK);
		$I->sendPUT(self::POLLS_API . '/' . $this->poll['id'] . '/vote', ['options' => [$choice => 1]]);
		$I->seeResponseCodeIs(Http::FORBIDDEN);

		$I->seeInDatabase('fs_poll_option_has_value', [
			'poll_id' => $this->poll['id'],
			'option' => $choice,
			'value' => 1,
			'votes' => $votes + 1
		]);
		$I->seeInDatabase('fs_foodsaver_has_voted', [
			'poll_id' => $this->poll['id'],
			'foodsaver_id' => $this->userFoodsaver['id']
		]);
	}

	public function canNotVoteInDifferentRegion(ApiTester $I)
	{
		$choice = 0;
		$votes = $I->grabFromDatabase('fs_poll_option_has_value', 'votes', [
			'poll_id' => $this->poll['id'],
			'option' => $choice,
			'value' => 1
		]);

		$region = $I->createRegion();
		$user = $I->createFoodsaver(null, ['bezirk_id' => $region['id']]);

		$I->login($user['email']);
		$I->sendPUT(self::POLLS_API . '/' . $this->poll['id'] . '/vote', ['options' => [0 => 1]]);
		$I->seeResponseCodeIs(Http::FORBIDDEN);

		$I->seeInDatabase('fs_poll_option_has_value', [
			'poll_id' => $this->poll['id'],
			'option' => $choice,
			'value' => 1,
			'votes' => $votes
		]);
	}

	public function canOnlyVoteInOngoingPoll(ApiTester $I)
	{
		$I->login($this->userFoodsaver['email']);

		// create outdated poll
		$poll = $this->createPoll($I, [1], [
			'type' => VotingType::SELECT_ONE_CHOICE,
			'end' => (new DateTime('now - 1 day'))->format('Y-m-d H:i:s')
		]);

		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 1]
		]);
		$I->seeResponseCodeIs(Http::FORBIDDEN);

		// create future poll
		$poll2 = $this->createPoll($I, [1], [
			'type' => VotingType::SELECT_ONE_CHOICE,
			'start' => (new DateTime('now + 1 day'))->format('Y-m-d H:i:s')
		]);

		$I->sendPUT(self::POLLS_API . '/' . $poll2['id'] . '/vote', [
			'options' => [0 => 1]
		]);
		$I->seeResponseCodeIs(Http::FORBIDDEN);
	}

	public function canUseSingleSelection(ApiTester $I)
	{
		// create poll with single selection
		$poll = $this->createPoll($I, [1], ['type' => VotingType::SELECT_ONE_CHOICE]);

		// vote with different numbers of options
		$I->login($this->userFoodsaver['email']);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 1, 1 => 0, 2 => 1]
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => []
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [1 => 0]
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [1 => 1]
		]);
		$I->seeResponseCodeIs(Http::OK);
	}

	public function canUseMultipleSelection(ApiTester $I)
	{
		// create poll with multi-selection
		$poll = $this->createPoll($I, [1], ['type' => VotingType::SELECT_MULTIPLE]);

		// vote with different numbers of options
		$I->login($this->userFoodsaver['email']);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 1, 1 => 0]
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 1, 1 => 0, 2 => 1, 3 => 0, 4 => 1, 5 => 0]
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => []
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [1 => 1, 2 => 1]
		]);
		$I->seeResponseCodeIs(Http::OK);
	}

	public function canUseThumbVoting(ApiTester $I)
	{
		// create poll with score voting
		$poll = $this->createPoll($I, [-1, 0, 1], ['type' => VotingType::THUMB_VOTING]);

		// vote with different numbers of options
		$I->login($this->userFoodsaver['email']);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 1, 1 => 0]
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 1, 1 => 0, 2 => 1, 3 => 0, 4 => 1, 5 => 0]
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => []
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 0, 1 => 1, 2 => -1, 3 => 0]
		]);
		$I->seeResponseCodeIs(Http::OK);
	}

	public function canUseScoreVoting(ApiTester $I)
	{
		// create poll with score voting
		$poll = $this->createPoll($I, range(-3, 3), ['type' => VotingType::SCORE_VOTING]);

		// vote with different numbers of options
		$I->login($this->userFoodsaver['email']);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 1, 1 => 0]
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 1, 1 => 0, 2 => 1, 3 => 0, 4 => 1, 5 => 0]
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => []
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 0, 1 => -5, 2 => -1, 3 => 0]
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 0, 1 => 1, 2 => -1, 3 => 0]
		]);
		$I->seeResponseCodeIs(Http::OK);
	}

	public function orgaCanCancelPoll(ApiTester $I)
	{
		$poll = $this->createPoll($I, [-1, 0, 1], ['type' => VotingType::THUMB_VOTING]);
		$I->login($this->userFoodsaver['email']);
		$I->sendDELETE(self::POLLS_API . '/' . $poll['id']);
		$I->seeResponseCodeIs(Http::FORBIDDEN);
		$I->seeInDatabase('fs_poll', [
			'id' => $poll['id'],
			'cancelled_by' => null
		]);

		$userOrga = $I->createOrga();
		$I->login($userOrga['email']);
		$I->sendDELETE(self::POLLS_API . '/' . $poll['id']);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeInDatabase('fs_poll', [
			'id' => $poll['id'],
			'cancelled_by' => $userOrga['id']
		]);
	}

	public function canVoteInScopeFoodsavers(ApiTester $I)
	{
		$poll = $this->createPoll($I, [-1, 0, 1], [
			'type' => VotingType::THUMB_VOTING,
			'scope' => VotingScope::FOODSAVERS
		]);
		$this->testCanVote($I, $poll, $this->userFoodsaverUnverified);
		$this->testCanVote($I, $poll, $this->userFoodsaver);
		$this->testCanVote($I, $poll, $this->userStoreManager);
		$this->testCanVote($I, $poll, $this->userAmbassador);
	}

	public function canVoteInScopeVerifiedFoodsavers(ApiTester $I)
	{
		$poll = $this->createPoll($I, [-1, 0, 1], [
			'type' => VotingType::THUMB_VOTING,
			'scope' => VotingScope::VERIFIED_FOODSAVERS
		]);
		$this->testCanVote($I, $poll, $this->userFoodsaverUnverified, false);
		$this->testCanVote($I, $poll, $this->userFoodsaver);
		$this->testCanVote($I, $poll, $this->userStoreManager);
		$this->testCanVote($I, $poll, $this->userAmbassador);
	}

	public function canVoteInScopeStoreManagers(ApiTester $I)
	{
		$poll = $this->createPoll($I, [-1, 0, 1], [
			'type' => VotingType::THUMB_VOTING,
			'scope' => VotingScope::STORE_MANAGERS
		]);
		$this->testCanVote($I, $poll, $this->userFoodsaverUnverified, false);
		$this->testCanVote($I, $poll, $this->userFoodsaver, false);
		$this->testCanVote($I, $poll, $this->userStoreManager);
		$this->testCanVote($I, $poll, $this->userAmbassador, false);
	}

	public function canVoteInScopeAmbassadors(ApiTester $I)
	{
		$poll = $this->createPoll($I, [-1, 0, 1], [
			'type' => VotingType::THUMB_VOTING,
			'scope' => VotingScope::AMBASSADORS
		]);
		$this->testCanVote($I, $poll, $this->userFoodsaverUnverified, false);
		$this->testCanVote($I, $poll, $this->userFoodsaver, false);
		$this->testCanVote($I, $poll, $this->userStoreManager, false);
		$this->testCanVote($I, $poll, $this->userAmbassador);
	}

	private function createPoll(ApiTester $I, array $values, array $params = []): array
	{
		$poll = $I->createPoll($this->region['id'], $this->userFoodsaver['id'], $params);
		foreach (range(0, 3) as $_) {
			$I->createPollOption($poll['id'], $values);
		}

		return $poll;
	}

	private function testCanVote(ApiTester $I, array $poll, array $user, bool $canVote = true)
	{
		$I->login($user['email']);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 0, 1 => 0, 2 => 0, 3 => 0]
		]);
		$I->seeResponseCodeIs($canVote ? Http::OK : Http::FORBIDDEN);
	}
}
