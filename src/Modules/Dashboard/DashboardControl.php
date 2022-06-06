<?php

namespace Foodsharing\Modules\Dashboard;

use Exception;
use Foodsharing\Modules\Basket\BasketGateway;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Content\ContentId;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Map\MapConstants;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Event\EventGateway;
use Foodsharing\Modules\Event\InvitationStatus;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Quiz\QuizSessionGateway;
use Foodsharing\Modules\Store\PickupGateway;
use Foodsharing\Modules\Store\StoreTransactions;
use Mobile_Detect;

class DashboardControl extends Control
{
	private ?array $user;
	private array $params;
	private DashboardGateway $dashboardGateway;
	private ContentGateway $contentGateway;
	private BasketGateway $basketGateway;
	private StoreTransactions $storeTransactions;
	private FoodsaverGateway $foodsaverGateway;
	private EventGateway $eventGateway;
	private PickupGateway $pickupGateway;
	private QuizSessionGateway $quizSessionGateway;

	/**
	 * @throws Exception
	 */
	public function __construct(
		DashboardView $view,
		DashboardGateway $dashboardGateway,
		ContentGateway $contentGateway,
		BasketGateway $basketGateway,
		StoreTransactions $storeTransactions,
		FoodsaverGateway $foodsaverGateway,
		EventGateway $eventGateway,
		PickupGateway $pickupGateway,
		QuizSessionGateway $quizSessionGateway
	) {
		$this->view = $view;
		$this->dashboardGateway = $dashboardGateway;
		$this->contentGateway = $contentGateway;
		$this->basketGateway = $basketGateway;
		$this->storeTransactions = $storeTransactions;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->eventGateway = $eventGateway;
		$this->pickupGateway = $pickupGateway;
		$this->quizSessionGateway = $quizSessionGateway;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->go('/');
		}

		$this->user = $this->dashboardGateway->getUser($this->session->id());
		$this->params = [];
	}

	/**
	 * @throws Exception
	 */
	public function index(): void
	{
		$this->params['user'] = $this->user;
		$this->params['broadcast'] = $this->getBroadcast();
		$this->params['errors'] = $this->getErrors();
		$this->params['informations'] = $this->getInformations();
		$this->params['baskets'] = $this->getBaskets();
		$this->params['quiz'] = $this->getQuiz();

		if ($this->session->may('fs')) {
			$this->params['events'] = $this->getEvents();
			$this->params['stores'] = $this->getStores();
			$this->params['pickups'] = $this->getPickups();
			$this->params['groups'] = $this->getGroups();
			$this->params['regions'] = $this->getRegions();
		}

		// echo json_encode($_SESSION);
		$this->pageHelper->addContent($this->view->index($this->params), CNT_MAIN);
	}

	/**
	 * Gets the user location if missing or invalid, it shows the default location.
	 */
	private function getUserLocationOrDefault(): array
	{
		return $this->session->getLocation() ?? ['lat' => MapConstants::CENTER_GERMANY_LAT, 'lon' => MapConstants::CENTER_GERMANY_LON];
	}

	private function getBroadcast(): array
	{
		return $this->contentGateway->getDetail(ContentId::BROADCAST_MESSAGE);
	}

	private function getRelease(): array
	{
		$cnt = $this->contentGateway->getDetail(ContentId::BROADCAST_MESSAGE);
		$cnt['body'] = '2022-05';
		$cnt['links'] = [
			(object)[
				'urlShortHand' => 'releaseNotes',
				'text' => 'menu.entry.release-notes',
			]
		];

		return $cnt;
	}

	private function getInformations(): array
	{
		$arr = [];

		if (strpos($_SERVER['HTTP_HOST'] ?? BASE_URL, 'beta.foodsharing') === false) {
			$rel = $this->getRelease();
			$arr[] = (object)[
				'tag' => 'release',
				'title' => $this->translator->trans('releases.' . $rel['body']),
				'links' => $rel['links'],
				'isTimeBased' => true,
				'isCloseable' => true,
			];
		}

		// Calendar sync hint
		if ($this->session->may('fs')) {
			$arr[] = (object)[
				'tag' => 'information.calendar_sync',
				'icon' => 'fa-calendar-alt',
				'title' => $this->translator->trans('information.calendar_sync.title'),
				'description' => $this->translator->trans('information.calendar_sync.info'),
				'links' => [
					[
						'urlShortHand' => 'settingsCalendar',
						'text' => 'information.calendar_sync.link'
					]
				],
			];

			// Disabled for all iOS users (Until iOs supports Push Notifications)
			$mod = new Mobile_Detect();
			if (!$mod->isiOS()) {
				$arr[] = (object)[
					'tag' => 'information.push',
					'icon' => 'fa-info-circle',
					'title' => $this->translator->trans('information.push.title'),
					'description' => $this->translator->trans('information.push.info'),
					'links' => [
						[
							'urlShortHand' => 'settingsNotifications',
							'text' => 'information.push.link'
						]
					],
				];
			}
		}

		return $arr;
	}

	/**
	 * @throws Exception
	 */
	private function getErrors(): array
	{
		$errors = [];
		if ($this->session->may('fs')) {
			$address = $this->foodsaverGateway->getFoodsaverAddress($this->session->id());
			if (empty($address['lat']) || empty($address['lon'])) {
				$errors[] = (object)[
					'type' => 'danger',
					'tag' => 'error.adress',
					'icon' => 'fa-map-marker-alt',
					'title' => $this->translator->trans('error.adress.title'),
					'description' => $this->translator->trans('error.adress.info'),
					'isCloseable' => false,
					'links' => [
						[
							'urlShortHand' => 'settings',
							'text' => 'error.adress.link'
						]
					],
				];
			}

			if (!$this->session->getCurrentRegionId()) {
				$this->pageHelper->addJs('becomeBezirk();');

				$errors[] = (object)[
					'type' => 'danger',
					'tag' => 'error.choose_home_region',
					'icon' => 'fa-map-marker-alt',
					'title' => $this->translator->trans('error.choose_home_region.title'),
					'description' => $this->translator->trans('error.choose_home_region.info'),
					'isCloseable' => false,
					'links' => [
						[
							'href' => 'javascript:becomeBezirk();',
							'text' => 'error.choose_home_region.link'
						]
					],
				];
			}
		}

		if (!$this->session->get('email_is_activated')) {
			$errors[] = (object)[
				'type' => 'danger',
				'tag' => 'error.mail_activation',
				'icon' => 'fa-exclamation-triangle',
				'title' => $this->translator->trans('error.mail_activation.title'),
				'description' => $this->translator->trans('error.mail_activation.description'),
				'isCloseable' => false,
				'links' => [
					[
						'urlShortHand' => 'resendActivationMail',
						'text' => 'error.mail_activation.link_1',
					],
					[
						'urlShortHand' => 'settings',
						'text' => 'error.mail_activation.link_2',
					]
				],
			];
		}

		if ($this->session->get('email_is_bouncing')) {
			$errors[] = (object)[
				'type' => 'danger',
				'tag' => 'error.mail_bounce',
				'icon' => 'fa-exclamation-triangle',
				'title' => $this->translator->trans('error.mail_bounce.title'),
				'description' => $this->translator->trans('error.mail_bounce.description'),
				'isCloseable' => false,
				'links' => [
					[
						'urlShortHand' => 'settings',
						'text' => 'error.mail_bounce.link_1',
					],
					[
						'urlShortHand' => 'guideLockedEmail',
						'text' => 'error.mail_bounce.link_2',
					]
				],
			];
		}

		return $errors;
	}

	private function getStores(): array
	{
		return $this->storeTransactions->getFilteredStoresForUser($this->session->id());
	}

	private function getEvents(): object
	{
		return (object)[
			'invites' => $this->eventGateway->getEventsByStatus($this->session->id(), [InvitationStatus::INVITED]),
			'accepted' => $this->eventGateway->getEventsByStatus($this->session->id(), [InvitationStatus::ACCEPTED, InvitationStatus::MAYBE]),
		];
	}

	private function getBaskets(): object
	{
		return (object)[
			'recent' => $this->basketGateway->listNewestBaskets(),
			'nearby' => $this->basketGateway->listNearbyBasketsByDistance($this->session->id(), $this->getUserLocationOrDefault()),
		];
	}

	private function getRegions(): array
	{
		$arr = [];
		foreach ($_SESSION['client']['bezirke'] as $b) {
			if ($b['type'] !== Type::WORKING_GROUP) {
				$arr[] = (object)[
					'id' => $b['id'],
					'name' => $b['name'],
				];
			}
		}

		return $arr;
	}

	private function getGroups(): array
	{
		$arr = [];
		foreach ($_SESSION['client']['bezirke'] as $b) {
			if ($b['type'] == Type::WORKING_GROUP) {
				$arr[] = (object)[
					'id' => $b['id'],
					'name' => $b['name'],
				];
			}
		}

		return $arr;
	}

	/**
	 * @throws Exception
	 */
	private function getPickups(): array
	{
		return $this->pickupGateway->getNextPickups($this->session->id(), 10);
	}

	private function getQuiz(): ?array
	{
		$is_foodsharer = !$this->session->may('fs') && !$this->quizSessionGateway->hasPassedQuiz($this->session->id(), Role::FOODSAVER);

		if ($is_foodsharer) {
			$cnt = $this->contentGateway->get(ContentId::QUIZ_REMARK_PAGE_33);
			$cnt['body'] = str_replace([
				'{NAME}',
				'{ANREDE}'
			], [
				$this->session->user('name'),
				$this->translator->trans('salutation.' . $this->session->user('gender'))
			], $cnt['body']);
			$cnt['closeable'] = false;
			$cnt['links'] = [
				(object)[
					'urlShortHand' => 'quizFs',
					'text' => 'foodsaver.upgrade.to_fs',
				]
			];

			return $cnt;
		}

		return null;
	}
}
