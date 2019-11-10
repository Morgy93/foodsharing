<?php

namespace Foodsharing\Dev;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Symfony\Component\Console\Command\Command;
use Codeception\CustomCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Carbon\Carbon;

class SeedCommand extends Command implements CustomCommandInterface
{
	use \Codeception\Command\Shared\Config;

	protected $helper;

	/**
	 * @var \Symfony\Component\Console\Output\OutputInterface
	 */
	protected $output;

	protected $foodsavers = [];
	protected $stores = [];
	protected $ambassadors = [];
	protected $parentambassadors = [];

	/**
	 * returns the name of the command.
	 *
	 * @return string
	 */
	public static function getCommandName()
	{
		return 'foodsharing:seed';
	}

	public function getDescription()
	{
		return 'seed the dev db';
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->output = $output;

		$config = $this->getGlobalConfig();
		$di = new Di();
		$module = new ModuleContainer($di, $config);
		$this->helper = $module->create('\Helper\Foodsharing');
		$this->helper->_initialize();

		// Clear existing data to prevent collisions
		$this->output->writeln('Clearing existing ' . FS_ENV . ' seed data');
		$this->helper->clear();

		$this->output->writeln('Seeding ' . FS_ENV . ' database');
		$this->seed();

		$this->output->writeln('All done!');
	}

	protected function getRandomIDOfArray(array $value, $number = 1)
	{
		$rand = array_rand($value, $number);
		if ($number === 1) {
			return $value[$rand];
		}
		if (count($rand) > 0) {
			return array_intersect_key($value, $rand);
		}

		return [];
	}

	protected function CreateMorePickups()
	{
		for ($m = 0; $m <= 10; ++$m) {
			$store_id = $this->getRandomIDOfArray($this->stores);
			for ($i = 0; $i <= 10; ++$i) {
				$pickupDate = Carbon::create(2019, 4, random_int(1, 30), random_int(1, 24), random_int(1, 59));
				for ($k = 0; $k <= 2; ++$k) {
					$foodSaver_id = $this->getRandomIDOfArray($this->foodsavers);
					$this->helper->addCollector($foodSaver_id, $store_id, ['date' => $pickupDate->toDateTimeString()]);
				}
			}
		}
	}

	private function writeUser($user, $password, $name = 'user')
	{
		$this->output->writeln('Created ' . $name . ' ' . $user['email'] . ' with password "' . $password . '"');
	}

	protected function seed()
	{
		$I = $this->helper;
		$bezirk1 = '241'; // this is called 'Göttingen'
		$bezirk1parent = '57'; // this is called Niedersachsen
		$bezirk_vorstand = '1373';
		$ag_aktive = '1565';
		$ag_testimonials = '1564';
		$ag_quiz = '341';
		$password = 'user';

		$user1 = $I->createFoodsharer($password, ['email' => 'user1@example.com', 'name' => 'One', 'bezirk_id' => $bezirk1]);
		$this->writeUser($user1, $password, 'foodsharer');

		$user2 = $I->createFoodsaver($password, ['email' => 'user2@example.com', 'name' => 'Two', 'bezirk_id' => $bezirk1]);
		$this->writeUser($user2, $password, 'foodsaver');

		$userbot = $I->createAmbassador($password, [
			'email' => 'userbot@example.com',
			'name' => 'Bot',
			'bezirk_id' => $bezirk1,
			'about_me_public' => 'hello!'
		]);
		$this->writeUser($userbot, $password, 'ambassador');
		// create some more ambassadors
		$this->ambassadors = [$userbot['id']];
		foreach (range(0, 5) as $number) {
			$user = $I->createAmbassador($password, [
			'email' => 'userbot' . $number . '@example.com',
			'name' => 'Bot' . $number,
			'bezirk_id' => $bezirk1,
			]);
			$this->ambassadors[] = $user['id'];
			$I->addBezirkAdmin($bezirk1, $user['id']);
			$I->addBezirkMember($bezirk1parent, $user['id']);
		}

		// create some parent ambassadors
		foreach (range(6, 10) as $number) {
			$user = $I->createAmbassador($password, [
				'email' => 'userbot' . $number . '@example.com',
				'name' => 'Bot' . $number,
				'bezirk_id' => $bezirk1,
			]);
			$this->parentambassadors[] = $user['id'];
			$I->addBezirkAdmin($bezirk1parent, $user['id']);
		}

		$userorga = $I->createOrga($password, false, ['email' => 'userorga@example.com', 'name' => 'Orga', 'bezirk_id' => $bezirk1]);
		$this->writeUser($userorga, $password, 'orga');

		$I->addBezirkAdmin($bezirk1, $userbot['id']);
		$I->addBezirkMember($ag_quiz, $userbot['id']);
		$I->addBezirkAdmin($ag_quiz, $userbot['id']);
		$I->addBezirkMember($bezirk1parent, $userbot['id']);

		$I->addBezirkMember($bezirk_vorstand, $userbot['id']);
		$I->addBezirkMember($ag_aktive, $userbot['id']);

		$I->addBezirkMember($ag_testimonials, $user2['id']);

		$conv1 = $I->createConversation([$userbot['id'], $user2['id']], ['name' => 'betrieb_bla']);
		$conv2 = $I->createConversation([$userbot['id']], ['name' => 'springer_bla']);
		$I->addConversationMessage($userbot['id'], $conv1['id']);
		$I->addConversationMessage($userbot['id'], $conv2['id']);

		$store = $I->createStore($bezirk1, $conv1['id'], $conv2['id'], ['betrieb_status_id' => 5]);
		$I->addStoreTeam($store['id'], $user2['id']);
		$I->addStoreTeam($store['id'], $userbot['id'], true);
		$I->addRecurringPickup($store['id']);

		$theme = $I->addForumTheme($bezirk1, $userbot['id']);
		$I->addForumThemePost($theme['id'], $user2['id']);

		$foodSharePoint = $I->createFoodSharePoint($userbot['id'], $bezirk1);
		$I->addFoodSharePointFollower($user2['id'], $foodSharePoint['id']);
		$I->addFoodSharePointPost($userbot['id'], $foodSharePoint['id']);

		// create users and collect their ids in a list
		$this->foodsavers = [$user2['id'], $userbot['id'], $userorga['id']];
		foreach (range(0, 100) as $_) {
			$user = $I->createFoodsaver($password, ['bezirk_id' => $bezirk1]);
			$this->foodsavers[] = $user['id'];
			$I->addStoreTeam($store['id'], $user['id']);
			$I->addCollector($user['id'], $store['id']);
			$I->addStoreNotiz($user['id'], $store['id']);
			$I->addForumThemePost($theme['id'], $user['id']);
			$I->addBezirkMember($bezirk1parent, $user['id']);
		}
		$this->output->writeln('Created some other users');

		// create conversations between users
		foreach ($this->foodsavers as $user) {
			foreach ($this->getRandomIDOfArray($this->foodsavers, 10) as $chatpartner) {
				if ($user !== $chatpartner) {
					$conv = $I->createConversation([$user, $chatpartner]);
					$I->addConversationMessage($user, $conv['id']);
					$I->addConversationMessage($chatpartner, $conv['id']);
				}
			}
		}
		$this->output->writeln('Created conversations');

		// create more stores and collect their ids in a list
		$this->stores = [$store['id']];
		foreach (range(0, 40) as $_) {
			// TODO conversations are missing the other store members
			$conv1 = $I->createConversation([$userbot['id']], ['name' => 'team']);
			$conv2 = $I->createConversation([$userbot['id']], ['name' => 'springer']);

			$store = $I->createStore($bezirk1, $conv1['id'], $conv2['id']);
			foreach (range(0, 5) as $_) {
				$I->addRecurringPickup($store['id']);
			}
			$this->stores[] = $store['id'];
		}
		$this->output->writeln('Created stores');

		$this->CreateMorePickups();
		$this->output->writeln('Created more pickups');

		// create foodbaskets
		foreach (range(0, 500) as $_) {
			$user = $this->getRandomIDOfArray($this->foodsavers);
			$foodbasket = $I->createFoodbasket($user);
			$commenter = $this->getRandomIDOfArray($this->foodsavers);
			$I->addFoodbasketWallpost($commenter, $foodbasket['id']);
		}
		$this->output->writeln('Created foodbaskets');

		// create food share point
		foreach ($this->getRandomIDOfArray($this->foodsavers, 50) as $user) {
			$foodSharePoint = $I->createFoodSharePoint($user, $bezirk1);
			foreach ($this->getRandomIDOfArray($this->foodsavers, 10) as $follower) {
				if ($user !== $follower) {
					$I->addFoodSharePointFollower($follower, $foodSharePoint['id']);
				}
				$I->addFoodSharePointPost($follower, $foodSharePoint['id']);
			}
		}
		$this->output->writeln('Created food share points');

		foreach (range(0, 20) as $_) {
			$I->addBlogPost($userbot['id'], $bezirk1);
		}
		$this->output->writeln('Created blog posts');

		// Report foodsaver against foodsaver, not confirmed
		foreach (range(0, 4) as $_) {
			$I->addReport($this->getRandomIDOfArray($this->foodsavers), $this->getRandomIDOfArray($this->foodsavers), 0, 0);
		}

		// Report foodsaver against foodsaver confirmed
		foreach (range(0, 3) as $_) {
			$I->addReport($this->getRandomIDOfArray($this->foodsavers), $this->getRandomIDOfArray($this->foodsavers), 0, 1);
		}

		// Report foodsaver against ambassador
		foreach (range(0, 1) as $_) {
			$I->addReport($this->getRandomIDOfArray($this->foodsavers), $this->getRandomIDOfArray($this->ambassadors), 0, 1);
		}

		// Report ambassador against ambassador
		foreach (range(0, 2) as $_) {
			$I->addReport($this->getRandomIDOfArray($this->ambassadors), $this->getRandomIDOfArray($this->ambassadors), 0, 1);
		}

		// Report Ambassador against foodsaver
		foreach (range(0, 3) as $_) {
			$I->addReport($this->getRandomIDOfArray($this->ambassadors), $this->getRandomIDOfArray($this->foodsavers), 0, 1);
		}

		// Report foodsaver against parentambassador
		foreach (range(0, 2) as $_) {
			$I->addReport($this->getRandomIDOfArray($this->foodsavers), $this->getRandomIDOfArray($this->parentambassadors), 0, 1);
		}

	}
}
