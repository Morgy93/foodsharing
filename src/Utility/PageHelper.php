<?php

namespace Foodsharing\Utility;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Permissions\BlogPermissions;
use Foodsharing\Permissions\ContentPermissions;
use Foodsharing\Permissions\MailboxPermissions;
use Foodsharing\Permissions\NewsletterEmailPermissions;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Permissions\QuizPermissions;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\Permissions\ReportPermissions;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Permissions\WorkGroupPermissions;
use Twig\Environment;

final class PageHelper
{
	private string $add_css = '';
	private string $content_main = '';
	private string $content_right = '';
	private string $content_left = '';
	private string $content_bottom = '';
	private string $content_top = '';
	private string $content_overtop = '';
	private string $head = '';
	private string $hidden = '';
	private string $js_func = '';
	private string $js = '';
	private array $bread = [];
	private array $title = ['foodsharing'];
	private array $webpackScripts = [];
	private array $webpackStylesheets = [];

	private int $content_left_width = 6;
	private int $content_right_width = 6;

	public array $jsData = [];

	private ImageHelper $imageService;
	private RouteHelper $routeHelper;
	private Sanitizer $sanitizerService;
	private Session $session;
	private BlogPermissions $blogPermissions;
	private ContentPermissions $contentPermissions;
	private MailboxPermissions $mailboxPermissions;
	private NewsletterEmailPermissions $newsletterEmailPermissions;
	private QuizPermissions $quizPermissions;
	private RegionPermissions $regionPermissions;
	private ReportPermissions $reportPermissions;
	private StorePermissions $storePermissions;
	private WorkGroupPermissions $workGroupPermissions;
	private ProfilePermissions $profilePermissions;
	private Environment $twig;

	public function __construct(
		Session $session,
		Sanitizer $sanitizerService,
		ImageHelper $imageService,
		Environment $twig,
		RouteHelper $routeHelper,
		MailboxPermissions $mailboxPermissions,
		QuizPermissions $quizPermissions,
		ReportPermissions $reportPermissions,
		StorePermissions $storePermissions,
		ContentPermissions $contentPermissions,
		BlogPermissions $blogPermissions,
		RegionPermissions $regionPermissions,
		NewsletterEmailPermissions $newsletterEmailPermissions,
		WorkGroupPermissions $workGroupPermissions,
		ProfilePermissions $profilePermissions
	) {
		$this->twig = $twig;
		$this->imageService = $imageService;
		$this->routeHelper = $routeHelper;
		$this->sanitizerService = $sanitizerService;
		$this->session = $session;
		$this->blogPermissions = $blogPermissions;
		$this->contentPermissions = $contentPermissions;
		$this->mailboxPermissions = $mailboxPermissions;
		$this->newsletterEmailPermissions = $newsletterEmailPermissions;
		$this->quizPermissions = $quizPermissions;
		$this->regionPermissions = $regionPermissions;
		$this->reportPermissions = $reportPermissions;
		$this->storePermissions = $storePermissions;
		$this->workGroupPermissions = $workGroupPermissions;
		$this->profilePermissions = $profilePermissions;
	}

	public function generateAndGetGlobalViewData(): array
	{
		$this->getMessages();
		$mainWidth = 24;

		$contentLeft = $this->getContent(CNT_LEFT);
		$contentRight = $this->getContent(CNT_RIGHT);

		if (!empty($contentLeft)) {
			$mainWidth -= $this->content_left_width;
		}

		if (!empty($contentRight)) {
			$mainWidth -= $this->content_right_width;
		}

		$bodyClasses = [];

		if ($this->session->may()) {
			$bodyClasses[] = 'loggedin';
		}

		if ($this->session->may('fs')) {
			$bodyClasses[] = 'fs';
		}

		$bodyClasses[] = 'page-' . $this->routeHelper->getPage();

		return [
			'head' => $this->getHeadData(),
			'bread' => $this->bread,
			'bodyClasses' => $bodyClasses,
			'serverDataJSON' => json_encode($this->getServerData()),
			'menu' => $this->getMenu(),
			'route' => $this->routeHelper->getPage(),
			'dev' => FS_ENV == 'dev',
			'hidden' => $this->hidden,
			'isMob' => $this->session->isMob(),
			'footer' => $this->getFooter(),
			'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? BASE_URL,
			'content' => [
				'main' => [
					'html' => $this->getContent(CNT_MAIN),
					'width' => $mainWidth
				],
				'left' => [
					'html' => $contentLeft,
					'width' => $this->content_left_width,
					'id' => 'left'
				],
				'right' => [
					'html' => $contentRight,
					'width' => $this->content_right_width,
					'id' => 'right'
				],
				'top' => [
					'html' => $this->getContent(CNT_TOP),
					'id' => 'content_top'
				],
				'bottom' => [
					'html' => $this->getContent(CNT_BOTTOM),
					'id' => 'content_bottom'
				],
				'overtop' => [
					'html' => $this->getContent(CNT_OVERTOP)
				]
			]
		];
	}

	/**
	 * This is used to set window.serverData on in the frontend.
	 */
	public function getServerData(): array
	{
		$user = $this->session->get('user');

		$userData = [
			'id' => $this->session->id(),
			'firstname' => $user['name'] ?? '',
			'lastname' => $user['nachname'] ?? '',
			'may' => $this->session->may(),
			'homeRegionId' => $user['bezirk_id'] ?? null,
			'mailBoxId' => $user['mailbox_id'] ?? null,
			'isFoodsaver' => $this->session->may('fs') ? true : false,
			'verified' => $this->session->isVerified(),
			'avatar' => $user['photo'] ?? null,
		];

		$permissions = null;
		if ($this->session->may()) {
			$userData['token'] = $this->session->user('token');
			$permissions = $this->getPermissions();
		}

		$locations = null;
		if ($pos = $this->session->getLocation()) {
			$locations = [
				'lat' => (float)$pos['lat'],
				'lon' => (float)$pos['lon'],
			];
		}

		$sentryConfig = null;

		if (defined('RAVEN_JAVASCRIPT_CONFIG')) {
			$sentryConfig = RAVEN_JAVASCRIPT_CONFIG;
		}

		return array_merge($this->jsData, [
			'user' => $userData,
			'permissions' => $permissions,
			'page' => $this->routeHelper->getPage(),
			'subPage' => $this->routeHelper->getSubPage(),
			'locations' => $locations,
			'ravenConfig' => $sentryConfig,
			'isDev' => getenv('FS_ENV') === 'dev',
			'locale' => $this->session->getLocale()
		]);
	}

	private function getPermissions(): array
	{
		$data = $this->session->get('user');

		return [
			'mayEditUserProfile' => $this->profilePermissions->mayEditUserProfile($this->session->id()),
			'mayAdministrateUserProfile' => $this->profilePermissions->mayAdministrateUserProfile($this->session->id(), $data['bezirk_id']),
			'administrateBlog' => $this->blogPermissions->mayAdministrateBlog(),
			'editQuiz' => $this->quizPermissions->mayEditQuiz(),
			'handleReports' => $this->reportPermissions->mayHandleReports(),
			'addStore' => $this->storePermissions->mayCreateStore(),
			'manageMailboxes' => $this->mailboxPermissions->mayManageMailboxes(),
			'editContent' => $this->contentPermissions->mayEditContent(),
			'administrateNewsletterEmail' => $this->newsletterEmailPermissions->mayAdministrateNewsletterEmail(),
			'administrateRegions' => $this->regionPermissions->mayAdministrateRegions(),
		];
	}

	private function getMenu(): string
	{
		$groups = $_SESSION['client']['bezirke'] ?? [];

		$regions = [];
		$workingGroups = [];

		foreach ($groups as $group) {
			$groupId = $group['id'];
			$groupType = $group['type'];
			$group = array_merge($group, [
				'mayHandleFoodsaverRegionMenu' => $this->regionPermissions->mayHandleFoodsaverRegionMenu($groupId),
				'hasConference' => $this->regionPermissions->hasConference($groupType)
			]);
			if (Type::isRegion($groupType)) {
				$group['isAdmin'] = $this->session->isAdminFor($groupId);
				$group['mayAccessReportGroupReports'] = $this->reportPermissions->mayAccessReportGroupReports($groupId);
				$group['mayAccessArbitrationGroupReports'] = $this->reportPermissions->mayAccessArbitrationReports($groupId);
				$group['maySetRegionOptions'] = $this->regionPermissions->maySetRegionOptions($groupId);
				$group['maySetRegionPin'] = $this->regionPermissions->maySetRegionPin($groupId);
				$regions[] = $group;
			} else {
				$group['isAdmin'] = $this->workGroupPermissions->mayEdit($group);
				$workingGroups[] = $group;
			}
		}

		$params = array_merge(
			[
				'regions' => $regions,
				'groups' => $workingGroups,
			]
		);

		return $this->twig->render(
			'partials/vue-wrapper.twig',
			[
				'id' => 'vue-topbar',
				'component' => 'topbar',
				'props' => $params,
			]
		);
	}

	private function getFooter(): string
	{
		$params = [
			'version' => defined('SRC_REVISION') ? SRC_REVISION : null,
		];

		return $this->twig->render(
			'partials/vue-wrapper.twig',
			[
				'id' => 'vue-footer',
				'component' => 'Footer',
				'props' => $params,
			]
		);
	}

	private function getHeadData(): array
	{
		return [
			'title' => implode(' | ', $this->title),
			'extra' => $this->head,
			'css' => str_replace(["\r", "\n"], '', $this->add_css),
			'jsFunc' => $this->js_func,
			'js' => $this->js,
			'ravenConfig' => null,
			'stylesheets' => $this->webpackStylesheets,
			'scripts' => $this->webpackScripts
		];
	}

	private function getMessages(): void
	{
		if (!isset($_SESSION['msg'])) {
			$_SESSION['msg'] = [];
		}
		if (isset($_SESSION['msg']['error']) && !empty($_SESSION['msg']['error'])) {
			$msg = '';
			foreach ($_SESSION['msg']['error'] as $e) {
				$msg .= '<div class="item">' . $e . '</div>';
			}
			$this->addJs('pulseError("' . $this->sanitizerService->jsSafe($msg, '"') . '");');
		}
		if (isset($_SESSION['msg']['success']) && !empty($_SESSION['msg']['success'])) {
			$msg = '';
			foreach ($_SESSION['msg']['success'] as $i) {
				$msg .= '<p>' . $i . '</p>';
			}
			$this->addJs('pulseSuccess("' . $this->sanitizerService->jsSafe($msg, '"') . '");');
		}
		if (isset($_SESSION['msg']['info']) && !empty($_SESSION['msg']['info'])) {
			$msg = '';
			foreach ($_SESSION['msg']['info'] as $i) {
				$msg .= '<p>' . $i . '</p>';
			}
			$this->addJs('pulseInfo("' . $this->sanitizerService->jsSafe($msg, '"') . '");');
		}
		$_SESSION['msg']['info'] = [];
		$_SESSION['msg']['success'] = [];
		$_SESSION['msg']['error'] = [];
	}

	private function getContent(int $positionCode = CNT_MAIN): string
	{
		switch ($positionCode) {
			case CNT_MAIN:
				$content = $this->content_main;
				break;
			case CNT_RIGHT:
				$content = $this->content_right;
				break;
			case CNT_TOP:
				$content = $this->content_top;
				break;
			case CNT_BOTTOM:
				$content = $this->content_bottom;
				break;
			case CNT_LEFT:
				$content = $this->content_left;
				break;
			case CNT_OVERTOP:
				$content = $this->content_overtop;
				break;
			default:
				$content = '';
				break;
		}

		return $content;
	}

	public function addContent(string $newContent, int $positionCode = CNT_MAIN): void
	{
		switch ($positionCode) {
			case CNT_MAIN:
				$this->content_main .= $newContent;
				break;
			case CNT_RIGHT:
				$this->content_right .= $newContent;
				break;
			case CNT_TOP:
				$this->content_top .= $newContent;
				break;
			case CNT_BOTTOM:
				$this->content_bottom .= $newContent;
				break;
			case CNT_LEFT:
				$this->content_left .= $newContent;
				break;
			case CNT_OVERTOP:
				$this->content_overtop .= $newContent;
				break;
			default:
				break;
		}
	}

	public function addBread(string $name, string $href = ''): void
	{
		$this->bread[] = ['name' => $name, 'href' => $href];
	}

	public function addWebpackScript(string $src): void
	{
		$this->webpackScripts[] = $src;
	}

	public function addWebpackStylesheet(string $src): void
	{
		$this->webpackStylesheets[] = $src;
	}

	public function addStyle(string $css): void
	{
		$this->add_css .= trim($css);
	}

	public function addJs(string $njs): void
	{
		$this->js .= $njs;
	}

	public function addJsFunc(string $nfunc): void
	{
		$this->js_func .= $nfunc;
	}

	public function addHead(string $str): void
	{
		$this->head .= "\n" . $str;
	}

	public function addTitle(string $name): void
	{
		$this->title[] = $name;
	}

	public function addHidden(string $html): void
	{
		$this->hidden .= $html;
	}

	/**
	 * @deprecated - use modern frontend code instead
	 */
	public function hiddenDialog(string $id, array $fields, string $title = '', bool $reload = false, string $width = ''): void
	{
		$form = '';
		foreach ($fields as $f) {
			$form .= $f;
		}

		$get = '';
		if (isset($_GET['id'])) {
			$get = '<input type="hidden" name="id" value="' . (int)$_GET['id'] . '" />';
		}

		$this->addHidden('<div id="' . $id . '"><form>' . $form . $get . '</form></div>');

		$width = $width ? "width: {$width}," : '';
		$success = $reload ? 'reload();' : '';

		$this->addJs('
		$("#' . $id . '").dialog({
		' . $width . '
		autoOpen: false,
		modal: true,
		title: "' . $title . '",
		buttons: {
			"Speichern": function () {
				showLoader();
				$.ajax({
					dataType: "json",
					url: "/xhr.php?f=' . $id . '&" + $("#' . $id . ' form").serialize(),
					success: function (data) {
						$("#' . $id . '").dialog(\'close\');
						' . $success . '
						if (data.script != undefined) {
							$.globalEval(data.script);
						}
					},
					complete: function () {
						hideLoader();
					}
				});
			}
		}
	});
	');
	}

	public function setContentWidth(int $left, int $right): void
	{
		$this->content_left_width = $left;
		$this->content_right_width = $right;
	}
}
