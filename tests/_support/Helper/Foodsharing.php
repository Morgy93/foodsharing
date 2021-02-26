<?php

namespace Helper;

use Carbon\Carbon;
use DateTime;
use Faker;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\FoodSharePoint\FollowerType;
use Foodsharing\Modules\Core\DBConstants\Info\InfoType;
use Foodsharing\Modules\Core\DBConstants\Mailbox\MailboxFolder;
use Foodsharing\Modules\Core\DBConstants\Quiz\SessionStatus;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingScope;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingType;

class Foodsharing extends \Codeception\Module\Db
{
	public $faker;
	private $email_counter = 1;

	public function __construct($moduleContainer, $config = null)
	{
		parent::__construct($moduleContainer, $config);
		$this->faker = Faker\Factory::create('de_DE');
	}

	public function clear()
	{
		/* This method should clear the database back to a state that seed data can be inserted again without fucking something up.
		It is okay to destroy user generated data and it is okay to fail when the user changed things. Actually, the suggested way is
		to reseed the database in case any modification to static data could have happened */
		$regionsToKeep = implode(',', [
			RegionIDs::ROOT,
			258, // Orgateam Archiv, apparently was a parent of some stuff
			RegionIDs::QUIZ_AND_REGISTRATION_WORK_GROUP,
			RegionIDs::GLOBAL_WORKING_GROUPS,
			RegionIDs::EUROPE,
			RegionIDs::NEWSLETTER_WORK_GROUP,
			RegionIDs::EUROPE_REPORT_TEAM,
			RegionIDs::TEAM_BOARD_MEMBER,
			RegionIDs::TEAM_ALUMNI_MEMBER,
			RegionIDs::TEAM_ADMINISTRATION_MEMBER,
			RegionIDs::WORKGROUP_ADMIN_CREATION_GROUP,
			RegionIDs::PR_START_PAGE,
			RegionIDs::PR_PARTNER_AND_TEAM_WORK_GROUP,
		]);
		$this->_getDriver()->executeQuery('
			DELETE FROM fs_buddy;
			DELETE FROM fs_question_has_quiz;
			DELETE FROM fs_question;
			DELETE FROM fs_quiz;
			DELETE FROM fs_foodsaver_has_bezirk;
			DELETE FROM fs_foodsaver_has_conversation;
			DELETE FROM fs_msg;
			DELETE FROM fs_betrieb_team;
			DELETE FROM fs_betrieb_has_lebensmittel;
			DELETE FROM fs_kette;
			DELETE FROM fs_betrieb;
			DELETE FROM fs_abholer;
			DELETE FROM fs_abholzeiten;
			DELETE FROM fs_botschafter;
			DELETE FROM fs_theme_post;
			DELETE FROM fs_bezirk_has_theme;
			DELETE FROM fs_theme;
			DELETE FROM fs_betrieb_notiz;
			DELETE FROM fs_fairteiler;
			DELETE FROM fs_fairteiler_follower;
			DELETE FROM fs_fairteiler_has_wallpost;
			DELETE FROM fs_report;
			DELETE FROM fs_basket;
			DELETE FROM fs_foodsaver;
			DELETE FROM fs_conversation;
			DELETE FROM fs_wallpost;
			DELETE FROM fs_mailbox;
			DELETE FROM fs_mailbox_message;
			DELETE FROM fs_region_function;
			DELETE FROM fs_bezirk WHERE id NOT IN(' . $regionsToKeep . ') and type = 7;
			DELETE FROM fs_bezirk WHERE id NOT IN(' . $regionsToKeep . ') and id in (SELECT bez.id as id FROM `fs_bezirk` bez
						left outer join fs_bezirk par on  bez.id = par.parent_id
						where par.parent_id is null
						order by bez.id);
			DELETE FROM fs_bezirk WHERE id NOT IN(' . $regionsToKeep . ');
			DELETE FROM fs_lebensmittel;
		', []);
	}

	public function clearTable($table)
	{
		$this->_getDriver()->deleteQueryByCriteria($table, []);
	}

	/**
	 * Insert a new foodsharer into the database.
	 *
	 * @param string pass to set as foodsharer password
	 * @param array extra_params override params
	 *
	 * @return array with all the foodsaver fields
	 */
	public function createFoodsharer($pass = null, $extra_params = [])
	{
		if (!isset($pass)) {
			$pass = 'password';
		}
		$params = array_merge([
			'email' => ($this->email_counter++) . '.' . $this->faker->email,
			'bezirk_id' => 0,
			'name' => $this->faker->firstName,
			'nachname' => $this->faker->lastName,
			'verified' => 0,
			'rolle' => 0,
			'plz' => $this->faker->postcode,
			'stadt' => $this->faker->city,
			'lat' => $this->faker->latitude,
			'lon' => $this->faker->longitude,
			'anmeldedatum' => $this->faker->dateTimeBetween('-5 years', '-5 days'),
			'geb_datum' => $this->faker->dateTimeBetween('-80 years', '-18 years'),
			'last_login' => $this->faker->dateTimeBetween('-1 years', '-1 hours'),
			'anschrift' => $this->faker->streetName,
			'handy' => $this->faker->phoneNumber,
			'active' => 1,
			'privacy_policy_accepted_date' => '2020-05-16 00:09:33',
			'privacy_notice_accepted_date' => '2018-05-24 18:25:28',
			'token' => uniqid('', true)
		], $extra_params);
		$params['password'] = password_hash($pass, PASSWORD_ARGON2I, [
			'time_cost' => 1
		]);
		$params['geb_datum'] = $this->toDateTime($params['geb_datum']);
		$params['last_login'] = $this->toDateTime($params['last_login']);
		$params['anmeldedatum'] = $this->toDateTime($params['anmeldedatum']);
		$id = $this->haveInDatabase('fs_foodsaver', $params);
		if ($params['bezirk_id']) {
			$this->addRegionMember($params['bezirk_id'], $id);
		}
		$params['id'] = $id;

		return $params;
	}

	public function createQuiz(int $quizId, int $questionCount = 1): array
	{
		$roles = [
			Role::FOODSAVER => 'Foodsaver/in',
			Role::STORE_MANAGER => 'Betriebsverantwortliche/r',
			Role::AMBASSADOR => 'Botschafter/in'
		];
		$params = [
			'id' => $quizId,
			'name' => 'Quiz #' . $quizId,
			'desc' => 'Werde ' . $roles[$quizId] . ' mit diesem Quiz.',
			'maxfp' => 0,
			'questcount' => $questionCount,
		];
		$params['id'] = $this->haveInDatabase('fs_quiz', $params);

		$params['questions'] = [];
		for ($i = 1; $i <= $questionCount; ++$i) {
			$questionText = 'Frage #' . $i . ' für Quiz #' . $params['id'];
			$params['questions'][] = $this->createQuestion($params['id'], $questionText);
		}

		return $params;
	}

	private function createQuestion(int $quizId, string $text = 'Question', int $failurePoints = 1): array
	{
		$params = [
			'text' => $text,
			'duration' => 60,
			'wikilink' => 'wiki.foodsharing.de'
		];
		$questionId = $this->haveInDatabase('fs_question', $params);
		$params['id'] = $questionId;

		$this->haveInDatabase('fs_question_has_quiz', [
			'question_id' => $questionId,
			'quiz_id' => $quizId,
			'fp' => $failurePoints
		]);

		$params['answers'] = [];
		$params['answers'][] = $this->createAnswer($questionId, true);
		$params['answers'][] = $this->createAnswer($questionId, false);

		return $params;
	}

	private function createAnswer(int $questionId, bool $right = true): array
	{
		$params = [
			'question_id' => $questionId,
			'text' => ($right ? 'Richtige' : 'Falsche') . ' Antwort',
			'explanation' => 'Diese Antwort ist ' . ($right ? 'richtig' : 'falsch') . '.',
			'right' => $right ? 1 : 0
		];
		$params['id'] = $this->haveInDatabase('fs_answer', $params);

		return $params;
	}

	public function letUserFailQuiz(array $user, int $daysAgo, int $times)
	{
		$level = $user['rolle'] + 1;
		foreach (range(1, $times) as $i) {
			$this->createQuizTry($user['id'], $level, SessionStatus::FAILED, $daysAgo);
		}
	}

	public function createQuizTry(int $fsId, int $level, int $status, int $daysAgo = 0)
	{
		$startTime = Carbon::now()->subDays($daysAgo);
		$v = [
			'quiz_id' => $level,
			'status' => $status,
			'foodsaver_id' => $fsId,
			'time_start' => $this->toDateTime($startTime)
		];
		$this->haveInDatabase('fs_quiz_session', $v);
	}

	public function createFoodsaver($pass = null, $extra_params = [])
	{
		$params = array_merge([
			'verified' => 1,
			'rolle' => 1,
			'quiz_rolle' => 1,
			'geschlecht' => random_int(0, 3)
		], $extra_params);
		$params = $this->createFoodsharer($pass, $params);
		$this->createQuizTry($params['id'], 1, 1);

		return $params;
	}

	public function createStoreCoordinator($pass = null, $extra_params = [])
	{
		if (!array_key_exists('bezirk_id', $extra_params)) {
			$region = $this->createRegion();
			$extra_params['bezirk_id'] = $region['id'];
		}
		$params = array_merge([
			'rolle' => 2,
			'quiz_rolle' => 2,
		], $extra_params);
		$params = $this->createFoodsaver($pass, $params);
		$this->createQuizTry($params['id'], 2, 1);

		// create a mailbox and assign this user to it
		$mailbox = $this->createMailbox(strtolower(substr($params['name'], 0, 1) . '.' . $params['nachname']));
		$this->updateInDatabase('fs_foodsaver', ['mailbox_id' => $mailbox['id']], ['id' => $params['id']]);

		return $params;
	}

	public function createAmbassador($pass = null, $extra_params = [])
	{
		$params = array_merge([
			'rolle' => 3,
			'quiz_rolle' => 3,
		], $extra_params);
		$params = $this->createStoreCoordinator($pass, $params);
		$this->createQuizTry($params['id'], 3, 1);

		return $params;
	}

	public function createOrga($pass = null, $is_admin = false, $extra_params = [])
	{
		$params = array_merge([
			'rolle' => ($is_admin ? 5 : 4),
			'orgateam' => 1,
			'admin' => ($is_admin ? 1 : 0),
		], $extra_params);

		$params = $this->createAmbassador($pass, $params);

		return $params;
	}

	public function createStore($bezirk_id, $team_conversation = null, $springer_conversation = null, $extra_params = [])
	{
		$params = array_merge([
			'betrieb_status_id' => $this->faker->numberBetween(0, 6),
			'status' => 1,
			'added' => $this->faker->dateTime(),
			'plz' => $this->faker->postcode(),
			'stadt' => $this->faker->city(),
			'str' => $this->faker->streetAddress(),
			'hsnr' => $this->faker->numberBetween(0, 1000),
			'lat' => $this->faker->latitude(),
			'lon' => $this->faker->longitude(),
			'name' => 'betrieb_' . $this->faker->company(),
			'status_date' => $this->faker->dateTime(),
			'ansprechpartner' => $this->faker->name(),
			'telefon' => $this->faker->phoneNumber(),
			'fax' => $this->faker->phoneNumber(),
			'email' => $this->faker->email(),
			'begin' => $this->faker->date('Y-m-d'),
			'besonderheiten' => '',
			'public_info' => '',
			'public_time' => 0,
			'ueberzeugungsarbeit' => 0,
			'presse' => 0,
			'sticker' => 0,
			'abholmenge' => $this->faker->numberBetween(0, 7),
			'team_status' => 1,
			'prefetchtime' => 1209600,

			// relations
			'bezirk_id' => $bezirk_id,
			'team_conversation_id' => $team_conversation,
			'springer_conversation_id' => $springer_conversation,
			'kette_id' => 0,
			'betrieb_kategorie_id' => 0,
		], $extra_params);
		$params['status_date'] = $this->toDateTime($params['status_date']);
		$params['added'] = $this->toDateTime($params['added']);

		$params['id'] = $this->haveInDatabase('fs_betrieb', $params);

		return $params;
	}

	/** Adds a user or an array of users to the store team.
	 * If the user is not confirmed yet, the waiter status is ignored.
	 * Care: This method does not care about store conversations!
	 * Care: This method does not care about adding the user to the matching bezirk!
	 */
	public function addStoreTeam($store_id, $fs_id, $is_coordinator = false, $is_waiting = false, $is_confirmed = true)
	{
		if (is_array($fs_id)) {
			foreach ($fs_id as $fs) {
				$this->addStoreTeam($store_id, $fs, $is_coordinator, $is_waiting, $is_confirmed);
			}
		} else {
			$v = [
				'betrieb_id' => $store_id,
				'foodsaver_id' => $fs_id,
				'active' => $is_confirmed ? ($is_waiting ? 2 : 1) : 0,
				'verantwortlich' => $is_coordinator ? 1 : 0,
			];
			$this->haveInDatabase('fs_betrieb_team', $v);
		}
	}

	public function addCollector($user, $store, $extra_params = [])
	{
		$params = array_merge([
			'foodsaver_id' => $user,
			'betrieb_id' => $store,
			'date' => $this->faker->dateTime(),
			'confirmed' => 1
		], $extra_params);
		$params['date'] = $this->toDateTime($params['date']);
		$res = $this->countInDatabase('fs_abholer', $params);
		if ($res < 1) {
			$id = $this->haveInDatabase('fs_abholer', $params);
			$params['id'] = $id;
		}

		return $params;
	}

	public function addStoreNotiz($user, $store, $extra_params = [])
	{
		$params = array_merge([
			'foodsaver_id' => $user,
			'betrieb_id' => $store,
			'milestone' => 0,
			'text' => $this->faker->realText(100),
			'zeit' => $this->faker->dateTime(),
			'last' => 0, // should be 1 for newest entry, can't do that here though
		], $extra_params);
		$params['zeit'] = $this->toDateTime($params['zeit']);

		$id = $this->haveInDatabase('fs_betrieb_notiz', $params);
		$params['id'] = $id;

		return $params;
	}

	public function addPickup($store, $extra_params = [])
	{
		$date = $this->faker->date('Y-m-d H:i:s');
		$params = array_merge([
			'betrieb_id' => $store,
			'time' => $date,
			'fetchercount' => $this->faker->numberBetween(1, 8)
		], $extra_params);

		$params['time'] = $this->toDateTime($params['time']);

		$id = $this->haveInDatabase('fs_fetchdate', $params);
		$params['id'] = $id;

		return $params;
	}

	public function addRecurringPickup($store, $extra_params = [])
	{
		$hours = $this->faker->numberBetween(0, 23);
		$minutes = $this->faker->randomElement($array = ['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55']);

		$params = array_merge([
			'betrieb_id' => $store,
			'dow' => $this->faker->numberBetween(0, 6),
			'time' => sprintf('%02d:%s:00', $hours, $minutes),
			'fetcher' => $this->faker->numberBetween(1, 8),
		], $extra_params);

		try {
			/* ToDo: Easy to generate a collision with the chosen randoms on big number of stores */
			$id = $this->haveInDatabase('fs_abholzeiten', $params);
			$params['id'] = $id;
		} catch (\Exception $e) {
			if (!$extra_params) {
				return $this->addRecurringPickup($store, $extra_params);
			} else {
				throw $e;
			}
		}

		return $params;
	}

	public function addPicker($store, $foodsaverId, $extra_params = [])
	{
		$date = $this->faker->date('Y-m-d H:i:s');
		$params = array_merge([
			'foodsaver_id' => $foodsaverId,
			'betrieb_id' => $store,
			'date' => $date,
			'confirmed' => '1'
		], $extra_params);

		$params['date'] = $this->toDateTime($params['date']);

		$id = $this->haveInDatabase('fs_abholer', $params);
		$params['id'] = $id;

		return $params;
	}

	public function addStoreFoodType($extra_params = [])
	{
		$params = array_merge([
			'name' => 'food_' . $this->faker->word()
		], $extra_params);

		$id = $this->haveInDatabase('fs_lebensmittel', $params);
		$params['id'] = $id;

		return $params;
	}

	public function addStoreChain($extra_params = [])
	{
		$params = array_merge([
			'name' => 'chain_' . $this->faker->company()
		], $extra_params);

		$id = $this->haveInDatabase('fs_kette', $params);
		$params['id'] = $id;

		return $params;
	}

	public function createWorkingGroup($name, $extra_params = [])
	{
		$extra_params = array_merge([
			'parent_id' => RegionIDs::GLOBAL_WORKING_GROUPS,
			'type' => Type::WORKING_GROUP,
			'teaser' => 'an autogenerated working group without a description',
		], $extra_params);

		return $this->createRegion($name, $extra_params);
	}

	public function createMailbox($name = null)
	{
		if ($name == null) {
			$name = $this->faker->userName();
		}
		$mb['name'] = $name;
		$mb['id'] = $this->haveInDatabase('fs_mailbox', $mb);

		// add up to 10 emails to each folder
		foreach ([MailboxFolder::FOLDER_INBOX, MailboxFolder::FOLDER_SENT, MailboxFolder::FOLDER_TRASH] as $folder) {
			$numMails = $this->faker->numberBetween(0, 10);
			for ($i = 0; $i < $numMails; ++$i) {
				$this->createEmail($mb, $folder);
			}
		}

		return $mb;
	}

	public function createEmail(array $mailbox, int $folder, array $extra_params = []): int
	{
		$body = $this->faker->realText(200);
		$extra_params = array_merge([
			'mailbox_id' => $mailbox['id'],
			'folder' => $folder,
			'subject' => $this->faker->text(30),
			'body' => $body,
			'body_html' => $body,
			'time' => $this->faker->dateTimeThisDecade->format('Y-m-d H:i:s'),
			'attach' => null,
			'read' => ($folder == MailboxFolder::FOLDER_INBOX) ? $this->faker->boolean : true,
			'answer' => false
		], $extra_params);

		// add sender and receiver based on the mailbox folder
		if ($folder == MailboxFolder::FOLDER_INBOX) {
			$extra_params['sender'] = $this->createRandomEmailAddress($this->faker->boolean);
			$extra_params['to'] = [$this->createFoodsharingEmailAddress($mailbox)];
		} else {
			$extra_params['to'] = [];
			for ($i = 0; $i < $this->faker->numberBetween(1, 5); ++$i) {
				$extra_params['to'][] = $this->createRandomEmailAddress(false);
			}
			$extra_params['sender'] = $this->createFoodsharingEmailAddress($mailbox);
		}

		$extra_params['sender'] = json_encode($extra_params['sender']);
		$extra_params['to'] = json_encode($extra_params['to']);

		return $this->haveInDatabase('fs_mailbox_message', $extra_params);
	}

	private function createFoodsharingEmailAddress(array $mailbox): array
	{
		return [
			'host' => 'foodsharing.network',
			'mailbox' => $mailbox['name'],
			'personal' => $mailbox['name'] . '@foodsharing.network'
		];
	}

	private function createRandomEmailAddress(bool $includePersonal = true): array
	{
		return [
			'host' => $this->faker->safeEmailDomain,
			'mailbox' => $this->faker->userName,
			'personal' => $includePersonal ? $this->faker->name : null
		];
	}

	public function addBuddy(int $user1, int $user2, bool $confirmed = true): void
	{
		$confirmed = $confirmed ? 1 : 0;
		$this->haveInDatabase('fs_buddy', ['foodsaver_id' => $user1, 'buddy_id' => $user2, 'confirmed' => $confirmed]);
	}

	public function createRegion($name = null, $extra_params = [])
	{
		if ($name == null) {
			$name = $this->faker->lastName() . '-region';
		}

		$v = array_merge([
			'parent_id' => RegionIDs::EUROPE,
			'name' => $name,
			'type' => Type::PART_OF_TOWN],
			$extra_params);
		$v['id'] = $this->haveInDatabase('fs_bezirk', $v);
		if (empty($v['email'])) {
			$mailbox = $this->createMailbox('region-' . $v['id']);
		} else {
			$mailbox = $this->createMailbox($v['email']);
		}

		$this->updateInDatabase('fs_bezirk', ['mailbox_id' => $mailbox['id']], ['id' => $v['id']]);
		/* Add to closure table for hierarchies */
		$this->_getDriver()->executeQuery('INSERT INTO `fs_bezirk_closure`
		(ancestor_id, bezirk_id, depth)
		SELECT t.ancestor_id, ?, t.depth+1 FROM `fs_bezirk_closure` AS t WHERE t.bezirk_id = ?
		UNION ALL SELECT ?, ?, 0', [$v['id'], $v['parent_id'], $v['id'], $v['id']]);

		return $v;
	}

	public function addRegionAdmin($region_id, $fs_id)
	{
		$v = [
			'bezirk_id' => $region_id,
			'foodsaver_id' => $fs_id,
		];
		$result = $this->countInDatabase('fs_botschafter', $v);
		if ($result > 0) {
			return;
		} else {
			$this->haveInDatabase('fs_botschafter', $v);
		}
	}

	public function addRegionMember($region_id, $fs_id, $is_active = true)
	{
		if (is_array($fs_id)) {
			array_map(function ($x) use ($region_id, $is_active) {
				$this->addRegionMember($region_id, $x, $is_active);
			}, $fs_id);
		} else {
			$v = [
				'bezirk_id' => $region_id,
				'foodsaver_id' => $fs_id,
				'active' => $is_active ? 1 : 0,
			];
			$result = $this->countInDatabase('fs_foodsaver_has_bezirk', $v);
			if ($result > 0) {
				return;
			} else {
				$this->haveInDatabase('fs_foodsaver_has_bezirk', $v);
			}
		}
	}

	public function addForumThread($region_id, $fs_id, $isAmbassadorThread = false, $extra_params = [])
	{
		$params = array_merge([
			'foodsaver_id' => $fs_id,
			'name' => $this->faker->sentence(),
			'time' => $this->faker->dateTime(),
			'active' => '1',
		], $extra_params);
		$params['time'] = $this->toDateTime($params['time']);

		$threadId = $this->haveInDatabase('fs_theme', $params);

		$this->haveInDatabase('fs_bezirk_has_theme', [
			'theme_id' => $threadId,
			'bezirk_id' => $region_id,
			'bot_theme' => ($isAmbassadorThread ? 1 : 0),
		]);

		$post_params = [
			'body' => $this->faker->realText(500),
			'time' => $params['time'],
		];
		$params['post'] = $this->addForumThreadPost($threadId, $fs_id, $post_params);
		$params['id'] = $threadId;

		return $params;
	}

	public function addForumThreadPost($threadId, $fs_id, $extra_params = [])
	{
		$params = array_merge([
			'theme_id' => $threadId,
			'foodsaver_id' => $fs_id,
			'body' => $this->faker->realText(200),
			'time' => $this->faker->dateTime(),
		], $extra_params);
		$params['time'] = $this->toDateTime($params['time']);

		$params['id'] = $this->haveInDatabase('fs_theme_post', $params);

		$this->updateForumThreadWithPost($threadId, $params);

		return $params;
	}

	public function createConversation($users, $extra_params = [])
	{
		$params = array_merge([
			'locked' => 0,
			'name' => null,
			'last' => $this->faker->dateTime(),
			'last_foodsaver_id' => $users ? $users[0] : null,
			'last_message_id' => null,
			'last_message' => null,
		], $extra_params);
		$params['last'] = $this->toDateTime($params['last']);
		$id = $this->haveInDatabase('fs_conversation', $params);

		foreach ($users as $user) {
			$this->addUserToConversation($user, $id);
		}

		$params['id'] = $id;

		return $params;
	}

	public function addUserToConversation($user, $conversation, $extra_params = [])
	{
		$params = array_merge([
			'foodsaver_id' => $user,
			'conversation_id' => $conversation,
			'unread' => 0,
		], $extra_params);

		$id = $this->haveInDatabase('fs_foodsaver_has_conversation', $params);

		$params['id'] = $id;

		return $params;
	}

	public function addConversationMessage($user, $conversation, $extra_params = [])
	{
		$params = array_merge([
			'foodsaver_id' => $user,
			'conversation_id' => $conversation,
			'body' => $this->faker->realText(100),
			'time' => $this->faker->dateTime()
		], $extra_params);
		$params['time'] = $this->toDateTime($params['time']);

		$id = $this->haveInDatabase('fs_msg', $params);

		$this->updateInDatabase('fs_conversation', [
			'last_message' => $params['body'],
			'last_message_id' => $id,
			'last_foodsaver_id' => $user,
			'last' => $params['time']
		], ['id' => $conversation]);

		$params['id'] = $id;

		return $params;
	}

	public function createFoodSharePoint($user, $bezirk = null, $extra_params = [])
	{
		if ($bezirk === null) {
			$bezirk = ($this->createRegion())['id'];
		}
		$params = array_merge([
			'bezirk_id' => $bezirk,
			'name' => $this->faker->city(),
			'desc' => $this->faker->text(200),
			'status' => 1,
			'anschrift' => $this->faker->address(),
			'plz' => $this->faker->postcode(),
			'ort' => $this->faker->city(),
			'lat' => $this->faker->latitude(),
			'lon' => $this->faker->longitude(),
			'add_date' => $this->faker->dateTime(),
			'add_foodsaver' => $user,
		], $extra_params);
		$params['add_date'] = $this->toDateTime($params['add_date']);

		$id = $this->haveInDatabase('fs_fairteiler', $params);

		$this->addFoodSharePointAdmin($user, $id);

		$params['id'] = $id;

		return $params;
	}

	public function addFoodSharePointFollower($user, $foodSharePoint, $extra_params = [])
	{
		$params = array_merge([
			'fairteiler_id' => $foodSharePoint,
			'foodsaver_id' => $user,
			'type' => FollowerType::FOLLOWER,
			'infotype' => InfoType::EMAIL,
		], $extra_params);
		$this->haveInDatabase('fs_fairteiler_follower', $params);

		return $params;
	}

	public function addFoodSharePointAdmin($user, $foodSharePoint, $extra_params = [])
	{
		return $this->addFoodSharePointFollower($user, $foodSharePoint, array_merge($extra_params, ['type' => FollowerType::FOOD_SHARE_POINT_MANAGER]));
	}

	public function addFoodSharePointPost($user, $foodSharePoint, $extra_params = [])
	{
		$post = $this->createWallpost($user, $extra_params);
		$this->haveInDatabase('fs_fairteiler_has_wallpost', [
			'fairteiler_id' => $foodSharePoint,
			'wallpost_id' => $post['id'],
		]);

		return $post;
	}

	public function createWallpost($user, $extra_params = [])
	{
		$params = array_merge([
			'foodsaver_id' => $user,
			'body' => $this->faker->realText(200),
			'time' => $this->faker->dateTime(),
		], $extra_params);
		$params['time'] = $this->toDateTime($params['time']);

		$id = $this->haveInDatabase('fs_wallpost', $params);

		$params['id'] = $id;

		return $params;
	}

	public function createFoodbasket($user, $extra_params = [])
	{
		$params = array_merge([
			'foodsaver_id' => $user,
			'status' => 1,
			'time' => $this->faker->dateTime($max = 'now'),
			'until' => $this->faker->dateTimeBetween('+5m', '+14 days'),
			'fetchtime' => null,
			'description' => $this->faker->realText(200),
			'picture' => null,
			'tel' => $this->faker->phoneNumber(),
			'handy' => $this->faker->phoneNumber(),
			'contact_type' => 1,
			'location_type' => 0,
			'weight' => $this->faker->numberBetween(1, 100),
			'lat' => $this->faker->latitude,
			'lon' => $this->faker->longitude,
			'bezirk_id' => 0,
		], $extra_params);
		$params['time'] = $this->toDateTime($params['time']);
		$params['until'] = $this->toDateTime($params['until']);

		$id = $this->haveInDatabase('fs_basket', $params);

		$params['id'] = $id;

		return $params;
	}

	public function addBells($users, $extra_params = [])
	{
		$params = array_merge([
			'name' => 'title',
			'body' => $this->faker->text(50),
			'vars' => '',
			'attr' => serialize(['href' => '/']),
			'icon' => 'icon',
			'identifier' => '',
			'time' => $this->faker->dateTime($max = 'now'),
			'closeable' => 1
		], $extra_params);
		$params['time'] = $this->toDateTime($params['time']);

		$bell_id = $this->haveInDatabase('fs_bell', $params);

		foreach ($users as $user) {
			$this->haveInDatabase('fs_foodsaver_has_bell', [
				'foodsaver_id' => $user['id'],
				'bell_id' => $bell_id,
				'seen' => 0
			]);
		}

		return $bell_id;
	}

	public function addBlogPost($authorId, $regionId, $extra_params = [])
	{
		$params = array_merge([
			'bezirk_id' => $regionId,
			'foodsaver_id' => $authorId,
			'name' => $this->faker->text(40),
			'body' => $this->faker->text(),
			'teaser' => $this->faker->text(50),
			'time' => $this->faker->dateTime($max = 'now'),
			'active' => 1,
			'picture' => ''
		], $extra_params);
		$params['time'] = $this->toDateTime($params['time']);
		$params['id'] = $this->haveInDatabase('fs_blog_entry', $params);

		return $params;
	}

	public function addReport($reporterId, $reporteeId, $storeId = 0, $confirmed = 0, $reason = null, $msg = null)
	{
		$params = [
			'reporter_id' => $reporterId,
			'foodsaver_id' => $reporteeId,
			'betrieb_id' => $storeId,
			'reporttype' => 1,
			'time' => $this->toDateTime($this->faker->dateTimeBetween('first day of january this year', $max = 'now')),
			'msg' => $msg ?? $this->faker->text(500),
			'tvalue' => $reason ?? $this->faker->text(50),
			'committed' => $confirmed
		];
		$params['id'] = $this->haveInDatabase('fs_report', $params);

		return $params;
	}

	public function updateThePrivacyNoticeDate()
	{
		$lastModified = $this->grabFromDatabase('fs_content', 'last_mod', ['name' => 'datenschutzbelehrung']);
		$beforeLastModified = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($lastModified)));
		$this->updateInDatabase('fs_content', ['last_mod' => $beforeLastModified], ['name' => 'datenschutzbelehrung']);

		return $lastModified;
	}

	public function resetThePrivacyNoticeDate($lastModified)
	{
		$this->updateInDatabase('fs_content', ['last_mod' => $lastModified], ['name' => 'datenschutzbelehrung']);
	}

	public function updateThePrivacyPolicyDate()
	{
		$lastModified = $this->grabFromDatabase('fs_content', 'last_mod', ['name' => 'datenschutz']);
		$beforeLastModified = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($lastModified)));
		$this->updateInDatabase('fs_content', ['last_mod' => $beforeLastModified], ['name' => 'datenschutz']);

		return $lastModified;
	}

	public function resetThePrivacyPolicyDate($lastModified)
	{
		$this->updateInDatabase('fs_content', ['last_mod' => $lastModified], ['name' => 'datenschutz']);
	}

	public function createPoll(int $regionId, int $authorId, array $extraParams = [])
	{
		$params = array_merge([
			'name' => $this->faker->text(30),
			'description' => $this->faker->realText(500),
			'scope' => VotingScope::FOODSAVERS,
			'type' => $this->faker->randomElement(range(VotingType::SELECT_ONE_CHOICE, VotingType::THUMB_VOTING)),
			'start' => $this->faker->dateTimeBetween('-7 days', 'now')->format('Y-m-d H:i:s'),
			'end' => $this->faker->dateTimeBetween('now', '+7 days')->format('Y-m-d H:i:s'),
			'votes' => $this->faker->numberBetween(0, 1000),
			'eligible_votes_count' => 0
		], $extraParams);
		$params['author'] = $authorId;
		$params['region_id'] = $regionId;
		$params['id'] = $this->haveInDatabase('fs_poll', $params);

		return $params;
	}

	public function createPollOption(int $pollId, array $values, array $extraParams = [])
	{
		$params = array_merge([
			'option_text' => $this->faker->text(30)
		], $extraParams);
		$params['poll_id'] = $pollId;
		$params['option'] = $this->countInDatabase('fs_poll_has_options', ['poll_id' => $pollId]);

		$this->haveInDatabase('fs_poll_has_options', $params);

		foreach ($values as $value) {
			$this->haveInDatabase('fs_poll_option_has_value', [
				'poll_id' => $pollId,
				'option' => $params['option'],
				'value' => (int)$value,
				'votes' => $this->faker->numberBetween(0, 100)
			]);
		}

		return $params;
	}

	public function addVoters(int $pollId, array $userIds)
	{
		foreach ($userIds as $id) {
			$this->haveInDatabase('fs_foodsaver_has_poll', [
				'poll_id' => $pollId,
				'foodsaver_id' => $id,
				'time' => null
			]);
		}

		$previousValue = $this->grabFromDatabase('fs_poll', 'eligible_votes_count', ['id' => $pollId]);
		$this->updateInDatabase('fs_poll', [
			'eligible_votes_count' => $previousValue + count($userIds)
		]);
	}

	// =================================================================================================================
	// private methods
	// =================================================================================================================

	private function updateForumThreadWithPost($threadId, $post)
	{
		$last_post_id = $this->grabFromDatabase('fs_theme', 'last_post_id', ['id' => $threadId]);
		$last_post_date = new DateTime($this->grabFromDatabase('fs_theme_post', 'time', ['id' => $last_post_id]));
		$this_post_date = new DateTime($post['time']);
		if ($last_post_date >= $this_post_date) {
			$this->_getDriver()->executeQuery('UPDATE fs_theme SET last_post_id = ? WHERE id = ?', [$post['id'], $threadId]);
		}
	}

	// copied from elsewhere....
	private function encryptMd5($email, $pass)
	{
		$email = strtolower($email);

		return md5($email . '-lz%&lk4-' . $pass);
	}

	private function toDateTime($date = null)
	{
		if ($date === null) {
			return null;
		}
		if ($date instanceof DateTime) {
			$dt = $date;
		} else {
			$dt = new DateTime($date);
		}

		return $dt->format('Y-m-d H:i:s');
	}
}
