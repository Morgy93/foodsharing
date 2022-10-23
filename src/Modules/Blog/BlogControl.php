<?php

namespace Foodsharing\Modules\Blog;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Permissions\BlogPermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\TimeHelper;

class BlogControl extends Control
{
	private BlogGateway $blogGateway;
	private BlogPermissions $blogPermissions;
	private DataHelper $dataHelper;
	private IdentificationHelper $identificationHelper;
	private TimeHelper $timeHelper;

	public function __construct(
		BlogView $view,
		BlogGateway $blogGateway,
		BlogPermissions $blogPermissions,
		DataHelper $dataHelper,
		IdentificationHelper $identificationHelper,
		TimeHelper $timeHelper
	) {
		$this->view = $view;
		$this->blogGateway = $blogGateway;
		$this->blogPermissions = $blogPermissions;
		$this->dataHelper = $dataHelper;
		$this->identificationHelper = $identificationHelper;
		$this->timeHelper = $timeHelper;

		parent::__construct();
		if ($id = $this->identificationHelper->getActionId('delete')) {
			if ($this->blogPermissions->mayEdit($id)) {
				if ($this->blogGateway->del_blog_entry($id)) {
					$this->flashMessageHelper->success($this->translator->trans('blog.success.delete'));
				} else {
					$this->flashMessageHelper->error($this->translator->trans('blog.failure.delete'));
				}
			} else {
				$this->flashMessageHelper->info($this->translator->trans('blog.permissions.delete'));
			}
			$this->routeHelper->goPage();
		}
		$this->pageHelper->addBread($this->translator->trans('blog.bread'), '/?page=blog');
		$this->pageHelper->addTitle($this->translator->trans('blog.bread'));
	}

	public function index(): void
	{
		if (!isset($_GET['sub'])) {
			$this->listNews();
		}
	}

	public function listNews(): void
	{
		$page = 1;
		if (isset($_GET['p'])) {
			$page = (int)$_GET['p'];
		}

		if ($news = $this->blogGateway->listNews($page)) {
			$out = '';
			foreach ($news as $n) {
				$out .= $this->view->newsListItem($n);
			}

			$this->pageHelper->addContent($this->v_utils->v_field(
				$out, $this->translator->trans('blog.header')
			));
			$this->pageHelper->addContent($this->view->pager($page));
		} elseif ($page > 1) {
			$this->routeHelper->go('/?page=blog');
		}
	}

	public function read(): void
	{
		if (isset($_GET['id']) && is_numeric($_GET['id']) && $news = $this->blogGateway->getPost($_GET['id'])) {
			$this->pageHelper->addBread($news['name']);
			$this->pageHelper->addContent($this->view->newsPost($news));
		}
	}

	public function manage(): void
	{
		if ($this->blogPermissions->mayAdministrateBlog()) {
			$this->pageHelper->addBread($this->translator->trans('blog.manage'));

			if ($data = $this->blogGateway->getBlogpostList()) {
				$this->pageHelper->addContent($this->view->blogpostOverview($data));
			} else {
				$this->flashMessageHelper->info($this->translator->trans('blog.empty'));
			}
		}
	}

	public function post()
	{
		if (!$this->blogPermissions->mayAdministrateBlog() || !isset($_GET['id'])) {
			return;
		}
		$post = $this->blogGateway->getOne_blog_entry($_GET['id']);

		if (!$post || $post['active'] != 1) {
			return;
		}
		$this->pageHelper->addTitle($post['name']);
		$this->pageHelper->addBread($post['name'], '/?page=blog&post=' . (int)$post['id']);

		$when = $this->timeHelper->niceDate($post['time_ts']);
		$this->pageHelper->addContent($this->view->topbar($post['name'], $when));
		$this->pageHelper->addContent($this->v_utils->v_field($post['body'], $post['name'], [
			'class' => 'ui-padding',
		]));
	}

	public function add(): void
	{
		if ($this->blogPermissions->mayAdministrateBlog()) {
			$this->handle_add();

			$this->pageHelper->addBread($this->translator->trans('blog.new'));

			$regions = $this->session->getRegions();
			if (!$this->session->may('orga')) {
				$bot_ids = $this->session->getMyAmbassadorRegionIds();
				foreach ($regions as $k => $v) {
					if (!UnitType::isGroup($v['type']) || !in_array($v['id'], $bot_ids)) {
						unset($regions[$k]);
					}
				}
			}

			$this->pageHelper->addContent($this->view->blog_entry_form($regions));

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
				$this->routeHelper->pageLink('blog')
			]), $this->translator->trans('blog.actions')), CNT_LEFT);
		} else {
			$this->flashMessageHelper->info($this->translator->trans('blog.permissions.new'));
			$this->routeHelper->goPage();
		}
	}

	private function handle_add(): void
	{
		global $g_data;

		if ($this->blogPermissions->mayAdministrateBlog() && $this->submitted()) {
			$g_data['foodsaver_id'] = $this->session->id();
			$g_data['time'] = date('Y-m-d H:i:s');

			if ($this->blogGateway->add_blog_entry($g_data) && $this->blogPermissions->mayAdd()) {
				$this->flashMessageHelper->success($this->translator->trans('blog.success.new'));
				$this->routeHelper->goPage();
			} else {
				$this->flashMessageHelper->error($this->translator->trans('blog.failure.new'));
			}
		}
	}

	public function edit(): void
	{
		$blogId = $_GET['id'] ?? null;
		if ($this->blogPermissions->mayAdministrateBlog() && $this->blogPermissions->mayEdit($blogId) && ($data = $this->blogGateway->getOne_blog_entry($blogId))) {
			$this->handle_edit();

			$this->pageHelper->addBread($this->translator->trans('blog.all'), '/?page=blog&sub=manage');
			$this->pageHelper->addBread($this->translator->trans('blog.edit'));

			$regions = $this->session->getRegions();

			$this->pageHelper->addContent($this->view->blog_entry_form($regions, $data));
		} else {
			$this->flashMessageHelper->info($this->translator->trans('blog.permissions.edit'));
			$this->routeHelper->goPage();
		}
	}

	private function handle_edit(): void
	{
		global $g_data;
		if ($this->blogPermissions->mayAdministrateBlog() && $this->submitted()) {
			$data = $this->blogGateway->getOne_blog_entry($_GET['id']);

			$g_data['foodsaver_id'] = $data['foodsaver_id'];
			$g_data['time'] = $data['time'];

			if ($this->blogGateway->update_blog_entry($_GET['id'], $g_data)) {
				$this->flashMessageHelper->success($this->translator->trans('blog.success.edit'));
				$this->routeHelper->goPage('blog&sub=manage');
			} else {
				$this->flashMessageHelper->error($this->translator->trans('blog.failure.edit'));
			}
		}
	}
}
