<?php

namespace Foodsharing\Modules\Content;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Helpers\StatusChecksHelper;
use Foodsharing\Modules\Core\Control;
use Parsedown;

class ContentControl extends Control
{
	private $contentGateway;
	private $identificationHelper;
	private $statusChecksHelper;
	private $dataHelper;

	public function __construct(
		ContentView $view,
		ContentGateway $contentGateway,
		IdentificationHelper $identificationHelper,
		StatusChecksHelper $statusChecksHelper,
		DataHelper $dataHelper
	) {
		$this->view = $view;
		$this->contentGateway = $contentGateway;
		$this->identificationHelper = $identificationHelper;
		$this->statusChecksHelper = $statusChecksHelper;
		$this->dataHelper = $dataHelper;

		parent::__construct();
	}

	public function index()
	{
		if (!isset($_GET['sub'])) {
			if (!$this->session->may('orga')) {
				$this->routeHelper->go('/');
			}
			$this->model;

			if ($this->identificationHelper->getAction('neu')) {
				$this->handle_add();

				$this->pageHelper->addBread($this->translationHelper->s('bread_content'), '/?page=content');
				$this->pageHelper->addBread($this->translationHelper->s('bread_new_content'));

				$this->pageHelper->addContent($this->content_form());

				$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
					$this->routeHelper->pageLink('content', 'back_to_overview')
				)), $this->translationHelper->s('actions')), CNT_RIGHT);
			} elseif ($id = $this->identificationHelper->getActionId('delete')) {
				if ($this->contentGateway->delete($id)) {
					$this->loggingHelper->info($this->translationHelper->s('content_deleted'));
					$this->routeHelper->goPage();
				}
			} elseif ($id = $this->identificationHelper->getActionId('edit')) {
				$this->handle_edit();

				$this->pageHelper->addBread($this->translationHelper->s('bread_content'), '/?page=content');
				$this->pageHelper->addBread($this->translationHelper->s('bread_edit_content'));

				$data = $this->contentGateway->getDetail($id);
				$this->dataHelper->setEditData($data);

				$this->pageHelper->addContent($this->content_form());

				$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
					$this->routeHelper->pageLink('content', 'back_to_overview')
				)), $this->translationHelper->s('actions')), CNT_RIGHT);
			} elseif ($id = $this->identificationHelper->getActionId('view')) {
				if ($cnt = $this->contentGateway->get($id)) {
					$this->pageHelper->addBread($cnt['title']);
					$this->pageHelper->addTitle($cnt['title']);

					$this->pageHelper->addContent($this->view->simple($cnt));
				}
			} elseif (isset($_GET['id'])) {
				$this->routeHelper->go('/?page=content&a=edit&id=' . (int)$_GET['id']);
			} else {
				$this->pageHelper->addBread($this->translationHelper->s('content_bread'), '/?page=content');

				if ($data = $this->contentGateway->list()) {
					$rows = array();
					foreach ($data as $d) {
						$rows[] = array(
							array('cnt' => $d['id']),
							array('cnt' => '<a class="linkrow ui-corner-all" href="/?page=content&id=' . $d['id'] . '">' . $d['name'] . '</a>'),
							array('cnt' => $this->v_utils->v_toolbar(array('id' => $d['id'], 'types' => array('edit', 'delete'), 'confirmMsg' => $this->translationHelper->sv('delete_sure', $d['name'])))
							));
					}

					$table = $this->v_utils->v_tablesorter(array(
						array('name' => 'ID', 'width' => 30),
						array('name' => $this->translationHelper->s('name')),
						array('name' => $this->translationHelper->s('actions'), 'sort' => false, 'width' => 50)
					), $rows);

					$this->pageHelper->addContent($this->v_utils->v_field($table, 'Öffentliche Webseiten bearbeiten'));
				} else {
					$this->loggingHelper->info($this->translationHelper->s('content_empty'));
				}

				$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
					array('href' => '/?page=content&a=neu', 'name' => $this->translationHelper->s('neu_content'))
				)), 'Aktionen'), CNT_RIGHT);
			}
		}
	}

	public function partner()
	{
		if ($cnt = $this->contentGateway->get(10)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->partner($cnt));
		}
	}

	public function unterstuetzung()
	{
		if ($cnt = $this->contentGateway->get(42)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function presse()
	{
		if ($cnt = $this->contentGateway->get(58)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function communitiesGermany()
	{
		if ($cnt = $this->contentGateway->get(52)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function communitiesAustria()
	{
		if ($cnt = $this->contentGateway->get(61)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function communitiesSwitzerland()
	{
		if ($cnt = $this->contentGateway->get(62)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function forderungen()
	{
		if ($cnt = $this->contentGateway->get(60)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function leeretonne()
	{
		if ($cnt = $this->contentGateway->get(46)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function fairteilerrettung()
	{
		if ($cnt = $this->contentGateway->get(49)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function faq(): void
	{
		$this->pageHelper->addBread('F.A.Q');
		$this->pageHelper->addTitle('F.A.Q.');

		$cat_ids = array(1, 6, 7);
		if ($this->session->may('fs')) {
			$cat_ids[] = 2;
			$cat_ids[] = 4;
		}
		if ($this->session->may('bot')) {
			$cat_ids[] = 5;
		}

		if ($faq = $this->contentGateway->listFaq($cat_ids)) {
			$this->pageHelper->addContent($this->view->faq($faq));
		}
	}

	public function impressum()
	{
		if ($cnt = $this->contentGateway->get(8)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->impressum($cnt));
		}
	}

	public function about()
	{
		if ($cnt = $this->contentGateway->get(9)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->about($cnt));
		}
	}

	public function ratgeber()
	{
		$this->pageHelper->addBread('Ratgeber');
		$this->pageHelper->addTitle('Ratgeber Lebensmittelsicherheit');
		$this->pageHelper->addContent($this->view->ratgeber());
	}

	public function joininfo()
	{
		$this->pageHelper->addBread('Mitmachen');
		$this->pageHelper->addTitle('Mitmachen - Unsere Regeln');
		$this->pageHelper->addContent($this->view->joininfo());
	}

	public function fuer_unternehmen()
	{
		if ($cnt = $this->contentGateway->get(4)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->partner($cnt));
		}
	}

	public function infohub()
	{
		if ($cnt = $this->contentGateway->get(59)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function changelog()
	{
		$this->pageHelper->addBread('Changelog');
		$this->pageHelper->addTitle('Changelog');
		$markdown = file_get_contents('CHANGELOG.md');
		$markdown = preg_replace('/\@(\S+)/', '[@\1](https://gitlab.com/\1)', $markdown);
		$markdown = preg_replace('/!([0-9]+)/', '[!\1](https://gitlab.com/foodsharing-dev/foodsharing/merge_requests/\1)', $markdown);
		$markdown = preg_replace('/#([0-9]+)/', '[#\1](https://gitlab.com/foodsharing-dev/foodsharing/issues/\1)', $markdown);
		$Parsedown = new Parsedown();
		$cl['body'] = $Parsedown->parse($markdown);
		$cl['title'] = 'Changelog';
		$this->pageHelper->addContent($this->view->simple($cl));
	}

	private function content_form($title = 'Content Management')
	{
		return $this->v_utils->v_form('faq', array(
			$this->v_utils->v_field(
				$this->v_utils->v_form_text('name', array('required' => true)) .
				$this->v_utils->v_form_text('title', array('required' => true)),

				$title,
				array('class' => 'ui-padding')
			),
			$this->v_utils->v_field($this->v_utils->v_form_tinymce('body', array('public_content' => true, 'nowrapper' => true)), 'Inhalt')
		), array('submit' => $this->translationHelper->s('save')));
	}

	private function handle_edit()
	{
		global $g_data;
		if ($this->statusChecksHelper->submitted()) {
			$g_data['last_mod'] = date('Y-m-d H:i:s');
			if ($this->contentGateway->update($_GET['id'], $g_data)) {
				$this->loggingHelper->info($this->translationHelper->s('content_edit_success'));
				$this->routeHelper->go('/?page=content&a=edit&id=' . (int)$_GET['id']);
			} else {
				$this->loggingHelper->error($this->translationHelper->s('error'));
			}
		}
	}

	private function handle_add()
	{
		global $g_data;
		if ($this->statusChecksHelper->submitted()) {
			$g_data['last_mod'] = date('Y-m-d H:i:s');
			if ($this->contentGateway->create($g_data)) {
				$this->loggingHelper->info($this->translationHelper->s('content_add_success'));
				$this->routeHelper->goPage();
			} else {
				$this->loggingHelper->error($this->translationHelper->s('error'));
			}
		}
	}
}
