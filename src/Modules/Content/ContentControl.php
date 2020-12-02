<?php

namespace Foodsharing\Modules\Content;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Content\ContentId;
use Foodsharing\Permissions\ContentPermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Parsedown;

class ContentControl extends Control
{
	private ContentGateway $contentGateway;
	private IdentificationHelper $identificationHelper;
	private DataHelper $dataHelper;
	private ContentPermissions $contentPermissions;

	public function __construct(
		ContentView $view,
		ContentGateway $contentGateway,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper,
		ContentPermissions $contentPermissions
	) {
		$this->view = $view;
		$this->contentGateway = $contentGateway;
		$this->identificationHelper = $identificationHelper;
		$this->dataHelper = $dataHelper;
		$this->contentPermissions = $contentPermissions;

		parent::__construct();
	}

	public function index(): void
	{
		if (!isset($_GET['sub'])) {
			if (!$this->contentPermissions->mayEditContent()) {
				$this->routeHelper->go('/');
			}

			if ($this->identificationHelper->getAction('neu')) {
				$this->handle_add();

				$this->pageHelper->addBread($this->translator->trans('content.bread'), '/?page=content');
				$this->pageHelper->addBread($this->translator->trans('content.new'));

				$this->pageHelper->addContent($this->content_form());

				$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
					$this->routeHelper->pageLink('content')
				]), $this->translator->trans('content.actions')), CNT_RIGHT);
			} elseif ($id = $this->identificationHelper->getActionId('delete')) {
				if ($this->contentGateway->delete($id)) {
					$this->flashMessageHelper->success($this->translator->trans('content.delete_success'));
					$this->routeHelper->goPage();
				}
			} elseif ($id = $this->identificationHelper->getActionId('edit')) {
				if (!$this->contentPermissions->mayEditContentId((int)$_GET['id'])) {
					$this->routeHelper->go('/?page=content');
				}
				$this->handle_edit();

				$this->pageHelper->addBread($this->translator->trans('content.bread'), '/?page=content');
				$this->pageHelper->addBread($this->translator->trans('content.edit'));

				$data = $this->contentGateway->getDetail($id);
				$this->dataHelper->setEditData($data);

				$this->pageHelper->addContent($this->content_form());

				$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
					$this->routeHelper->pageLink('content')
				]), $this->translator->trans('content.actions')), CNT_RIGHT);
			} elseif ($id = $this->identificationHelper->getActionId('view')) {
				if ($cnt = $this->contentGateway->get($id)) {
					$this->pageHelper->addBread($cnt['title']);
					$this->pageHelper->addTitle($cnt['title']);

					$this->pageHelper->addContent($this->view->simple($cnt));
				}
			} elseif (isset($_GET['id'])) {
				$this->routeHelper->go('/?page=content&a=edit&id=' . (int)$_GET['id']);
			} else {
				$this->pageHelper->addBread($this->translator->trans('content.public'), '/?page=content');

				$contentIds = $this->contentPermissions->getEditableContentIds();
				if ($data = $this->contentGateway->list($contentIds)) {
					$rows = [];
					foreach ($data as $d) {
						$link = '<a class="linkrow ui-corner-all" href="/?page=content&id=' . $d['id'] . '">';
						$rows[] = [
							['cnt' => $d['id']],
							['cnt' => $link . $d['name'] . '</a>'],
							['cnt' => $this->v_utils->v_toolbar([
								'id' => $d['id'],
								'types' => ['edit', 'delete'],
								'confirmMsg' => $this->translator->trans('content.delete', ['{name}' => $d['name']]),
							])],
						];
					}

					$table = $this->v_utils->v_tablesorter([
						['name' => 'ID', 'width' => 30],
						['name' => $this->translator->trans('content.name')],
						['name' => $this->translator->trans('content.actions'), 'sort' => false, 'width' => 50]
					], $rows);

					$this->pageHelper->addContent($this->v_utils->v_field($table, 'Öffentliche Webseiten bearbeiten'));
				} else {
					$this->flashMessageHelper->info($this->translator->trans('content.empty'));
				}

				if ($this->contentPermissions->mayCreateContent()) {
					$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
						['href' => '/?page=content&a=neu', 'name' => $this->translator->trans('content.new')]
					]), $this->translator->trans('content.actions')), CNT_RIGHT);
				}
			}
		}
	}

	public function partner(): void
	{
		if ($cnt = $this->contentGateway->get(ContentId::PARTNER_PAGE_10)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->partner($cnt));
		}
	}

	public function presse(): void
	{
		if ($cnt = $this->contentGateway->get(58)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function communitiesGermany(): void
	{
		if ($cnt = $this->contentGateway->get(52)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function communitiesAustria(): void
	{
		if ($cnt = $this->contentGateway->get(61)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function communitiesSwitzerland(): void
	{
		if ($cnt = $this->contentGateway->get(62)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function forderungen(): void
	{
		if ($cnt = $this->contentGateway->get(60)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function contact(): void
	{
		if ($cnt = $this->contentGateway->get(73)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function academy(): void
	{
		if ($cnt = $this->contentGateway->get(69)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function festival(): void
	{
		if ($cnt = $this->contentGateway->get(72)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function international(): void
	{
		if ($cnt = $this->contentGateway->get(74)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function transparency(): void
	{
		if ($cnt = $this->contentGateway->get(68)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function leeretonne(): void
	{
		if ($cnt = $this->contentGateway->get(46)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function foodSharePointRescue(): void
	{
		if ($cnt = $this->contentGateway->get(49)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function impressum(): void
	{
		if ($cnt = $this->contentGateway->get(8)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->impressum($cnt));
		}
	}

	public function about(): void
	{
		if ($cnt = $this->contentGateway->get(9)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->about($cnt));
		}
	}

	public function ratgeber(): void
	{
		header('Location: ' . 'https://wiki.foodsharing.de/Hygiene-Ratgeber_f%C3%BCr_Lebensmittel', true, 301);
	}

	public function joininfo(): void
	{
		$this->pageHelper->addBread('Mitmachen');
		$this->pageHelper->addTitle('Mitmachen - Unsere Regeln');
		$this->pageHelper->addContent($this->view->joininfo());
	}

	public function fuer_unternehmen(): void
	{
		if ($cnt = $this->contentGateway->get(4)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->partner($cnt));
		}
	}

	public function infohub(): void
	{
		if ($cnt = $this->contentGateway->get(59)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function fsstaedte(): void
	{
		if ($cnt = $this->contentGateway->get(66)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function workshops(): void
	{
		if ($cnt = $this->contentGateway->get(71)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function releaseNotes(): void
	{
		$this->pageHelper->addBread($this->translator->trans('menu.entry.release-notes'));
		$this->pageHelper->addTitle($this->translator->trans('menu.entry.release-notes'));
		$markdown = $this->parseGitlabLinks(file_get_contents('release-notes.md'));
		$Parsedown = new Parsedown();
		$cl['changelog'] = '<a class="float-right" href="/?page=content&sub=changelog">'
			. $this->translator->trans('content.changelog') .
		'</a>';
		$cl['title'] = $this->translator->trans('menu.entry.release-notes');
		$cl['body'] = $Parsedown->parse($markdown);
		$this->pageHelper->addContent($this->view->releaseNotes($cl));
	}

	public function changelog(): void
	{
		$this->pageHelper->addBread($this->translator->trans('content.changelog'));
		$this->pageHelper->addTitle($this->translator->trans('content.changelog'));
		$markdown = $this->parseGitlabLinks(file_get_contents('CHANGELOG.md'));
		$Parsedown = new Parsedown();
		$cl['title'] = $this->translator->trans('content.changelog');
		$cl['body'] = $Parsedown->parse($markdown);
		$this->pageHelper->addContent($this->view->simple($cl));
	}

	private function content_form($title = 'Content Management')
	{
		return $this->v_utils->v_form('faq', [
			$this->v_utils->v_field(
				$this->v_utils->v_form_text('name', ['required' => true]) .
				$this->v_utils->v_form_text('title', ['required' => true]),
				$title,
				['class' => 'ui-padding']
			),
			$this->v_utils->v_field(
				$this->v_utils->v_form_tinymce('body', [
					'public_content' => true,
					'nowrapper' => true,
				]),
				$this->translator->trans('content.content')
			)
		], ['submit' => $this->translator->trans('button.save')]);
	}

	private function handle_edit(): void
	{
		global $g_data;
		if ($this->submitted()) {
			$g_data['last_mod'] = date('Y-m-d H:i:s');
			if ($this->contentGateway->update($_GET['id'], $g_data)) {
				$this->flashMessageHelper->success($this->translator->trans('content.edit_success'));
				$this->routeHelper->go('/?page=content&a=edit&id=' . (int)$_GET['id']);
			} else {
				$this->flashMessageHelper->error($this->translator->trans('error_unexpected'));
			}
		}
	}

	private function handle_add(): void
	{
		global $g_data;
		if ($this->submitted()) {
			$g_data['last_mod'] = date('Y-m-d H:i:s');
			if ($this->contentGateway->create($g_data)) {
				$this->flashMessageHelper->success($this->translator->trans('content.new_success'));
				$this->routeHelper->goPage();
			} else {
				$this->flashMessageHelper->error($this->translator->trans('error_unexpected'));
			}
		}
	}
}
