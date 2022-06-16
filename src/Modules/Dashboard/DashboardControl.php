<?php

namespace Foodsharing\Modules\Dashboard;

use Exception;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Content\ContentId;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Event\EventGateway;
use Foodsharing\Modules\Event\InvitationStatus;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Quiz\QuizSessionGateway;
use Foodsharing\Modules\Settings\SettingsGateway;

class DashboardControl extends Control
{
	private ?array $user;
	private array $params;
	private ContentGateway $contentGateway;
	private SettingsGateway $settingsGateway;
	private FoodsaverGateway $foodsaverGateway;
	private EventGateway $eventGateway;
	private QuizSessionGateway $quizSessionGateway;

	/**
	 * @throws Exception
	 */
	public function __construct(
		DashboardView $view,
		ContentGateway $contentGateway,
		SettingsGateway $settingsGateway,
		FoodsaverGateway $foodsaverGateway,
		EventGateway $eventGateway,
		QuizSessionGateway $quizSessionGateway
	) {
		$this->view = $view;
		$this->contentGateway = $contentGateway;
		$this->settingsGateway = $settingsGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->eventGateway = $eventGateway;
		$this->quizSessionGateway = $quizSessionGateway;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->go('/');
		}

		$this->user = $this->foodsaverGateway->getFoodsaverBasics($this->session->id());
		$this->params = [];
	}

	/**
	 * @throws Exception
	 */
	public function index(): void
	{
		$this->params['broadcast'] = $this->getBroadcast();
		$this->params['errors'] = $this->getErrors();
		$this->params['quiz'] = $this->getQuiz();

		if ($this->session->may('fs')) {
			$this->params['events'] = $this->getEvents();
		}

		// echo json_encode($_SESSION);
		$this->pageHelper->addContent($this->view->index($this->params), CNT_MAIN);
	}

	private function getBroadcast(): array
	{
		return $this->contentGateway->getDetail(ContentId::BROADCAST_MESSAGE);
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

	private function getEvents(): object
	{
		return (object)[
			'invites' => $this->eventGateway->getEventsByStatus($this->session->id(), [InvitationStatus::INVITED]),
			'accepted' => $this->eventGateway->getEventsByStatus($this->session->id(), [InvitationStatus::ACCEPTED, InvitationStatus::MAYBE]),
		];
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
				],
				(object)[
					'urlShortHand' => 'quizLearning',
					'text' => 'foodsaver.upgrade.learning',
				]
			];

			return $cnt;
		}

		return null;
	}
}
