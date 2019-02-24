<?php

namespace Foodsharing\Modules\Blog;

use Foodsharing\Helpers\TimeHelper;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Permissions\BlogPermissions;

class BlogControl extends Control
{
	private $blogGateway;
	private $timeHelper;
	private $blogPermissions;

	public function __construct(BlogView $view, BlogGateway $blogGateway, BlogPermissions $blogPermissions, TimeHelper $timeHelper)
	{
		$this->view = $view;
		$this->blogGateway = $blogGateway;
		$this->timeHelper = $timeHelper;
		$this->blogPermissions = $blogPermissions;

		parent::__construct();
		if ($id = $this->func->getActionId('delete')) {
			if ($this->blogPermissions->mayEdit($this->blogGateway->getAuthorOfPost($id))) {
				if ($this->blogGateway->del_blog_entry($id)) {
					$this->func->info($this->func->s('blog_entry_deleted'));
				}
			} else {
				$this->func->info('Diesen Artikel kannst Du nicht löschen');
			}
			$this->routeHelper->goPage();
		}
		$this->pageHelper->addBread($this->func->s('blog_bread'), '/?page=blog');
		$this->pageHelper->addTitle($this->func->s('blog_bread'));
	}

	public function index()
	{
		if (!isset($_GET['sub'])) {
			$this->listNews();
		}
	}

	public function listNews()
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

			$this->pageHelper->addContent($this->v_utils->v_field($out, $this->func->s('news')));
			$this->pageHelper->addContent($this->view->pager($page));
		} elseif ($page > 1) {
			$this->routeHelper->go('/?page=blog');
		}
	}

	public function read()
	{
		if ($news = $this->blogGateway->getPost($_GET['id'])) {
			$this->pageHelper->addBread($news['name']);
			$this->pageHelper->addContent($this->view->newsPost($news));
		}
	}

	public function manage()
	{
		if ($this->session->mayEditBlog()) {
			$this->pageHelper->addBread($this->func->s('manage_blog'));
			$title = 'Blog Artikel';

			$this->pageHelper->addContent($this->view->headline($title));

			if ($data = $this->blogGateway->listArticle()) {
				$this->pageHelper->addContent($this->view->listArticle($data));
			} else {
				$this->func->info($this->func->s('blog_entry_empty'));
			}

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				array(
					'href' => '/?page=blog&sub=add',
					'name' => $this->func->s('new_article')
				)
			)), $this->func->s('actions')), CNT_LEFT);
		}
	}

	public function post()
	{
		if ($this->session->mayEditBlog()) {
			if (isset($_GET['id'])) {
				if ($post = $this->blogGateway->getOne_blog_entry($_GET['id'])) {
					if ($post['active'] == 1) {
						$this->pageHelper->addTitle($post['name']);
						$this->pageHelper->addBread($post['name'], '/?page=blog&post=' . (int)$post['id']);
						$this->pageHelper->addContent($this->view->topbar($post['name'], $this->timeHelper->niceDate($post['time_ts'])));
						$this->pageHelper->addContent($this->v_utils->v_field($post['body'], $post['name'], array('class' => 'ui-padding')));
					}
				}
			}
		}
	}

	public function add()
	{
		if ($this->session->mayEditBlog()) {
			$this->handle_add();

			$this->pageHelper->addBread($this->func->s('bread_new_blog_entry'));

			$bezirke = $this->session->getRegions();
			if (!$this->session->may('orga')) {
				$bot_ids = $this->session->getBotBezirkIds();
				foreach ($bezirke as $k => $v) {
					if ($v['type'] != 7 || !in_array($v['id'], $bot_ids)) {
						unset($bezirke[$k]);
					}
				}
			}

			$this->pageHelper->addContent($this->view->blog_entry_form($bezirke, true));

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->func->pageLink('blog', 'back_to_overview')
			)), $this->func->s('actions')), CNT_LEFT);
		} else {
			$this->func->info('Du darfst keine Artikel erstellen!');
			$this->routeHelper->goPage();
		}
	}

	private function handle_add()
	{
		global $g_data;

		if ($this->session->mayEditBlog() && $this->func->submitted()) {
			$g_data['foodsaver_id'] = $this->session->id();
			$g_data['time'] = date('Y-m-d H:i:s');

			if ($this->blogPermissions->mayAdd($g_data['bezirk_id']) && $this->blogGateway->add_blog_entry($g_data)) {
				$this->func->info($this->func->s('blog_entry_add_success'));
				$this->routeHelper->goPage();
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}

	public function edit()
	{
		if ($this->session->mayEditBlog() && $this->blogPermissions->mayEdit($this->blogGateway->getAuthorOfPost($_GET['id'])) && ($data = $this->blogGateway->getOne_blog_entry($_GET['id']))) {
			$this->handle_edit();

			$this->pageHelper->addBread($this->func->s('bread_blog_entry'), '/?page=blog&sub=manage');
			$this->pageHelper->addBread($this->func->s('bread_edit_blog_entry'));

			$this->func->setEditData($data);
			$bezirke = $this->session->getRegions();

			$this->pageHelper->addContent($this->view->blog_entry_form($bezirke));

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->func->pageLink('blog', 'back_to_overview')
			)), $this->func->s('actions')), CNT_LEFT);
		} else {
			$this->func->info('Diesen Artikel kannst Du nicht bearbeiten');
			$this->routeHelper->goPage();
		}
	}

	private function handle_edit()
	{
		global $g_data;
		if ($this->session->mayEditBlog() && $this->func->submitted()) {
			$data = $this->model->getValues(array('time', 'foodsaver_id'), 'blog_entry', $_GET['id']);

			$g_data['foodsaver_id'] = $data['foodsaver_id'];
			$g_data['time'] = $data['time'];

			if ($this->blogGateway->update_blog_entry($_GET['id'], $g_data)) {
				$this->func->info($this->func->s('blog_entry_edit_success'));
				$this->routeHelper->goPage();
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}
}
