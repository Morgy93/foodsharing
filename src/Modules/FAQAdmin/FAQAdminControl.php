<?php

namespace Foodsharing\Modules\FAQAdmin;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\Control;

class FAQAdminControl extends Control
{
	private $faqGateway;
	private $identificationHelper;
	private $dataHelper;

	public function __construct(
		Db $model,
		FAQAdminView $view,
		FAQGateway $faqGateway,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper
	) {
		$this->model = $model;
		$this->view = $view;
		$this->faqGateway = $faqGateway;
		$this->identificationHelper = $identificationHelper;
		$this->dataHelper = $dataHelper;

		parent::__construct();

		if (!$this->session->may('orga')) {
			$this->routeHelper->goLogin();
		}
	}

	public function index()
	{
		if ($this->identificationHelper->getAction('neu')) {
			$this->handle_add();

			$this->pageHelper->addBread($this->func->s('bread_faq'), '/?page=faq');
			$this->pageHelper->addBread($this->func->s('bread_new_faq'));

			$this->pageHelper->addContent($this->view->faq_form($this->faqGateway->getBasics_faq_category()));

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->routeHelper->pageLink('faq', 'back_to_overview')
			)), $this->func->s('actions')), CNT_RIGHT);
		} elseif ($id = $this->identificationHelper->getActionId('delete')) {
			if ($this->faqGateway->del_faq($id)) {
				$this->func->info($this->func->s('faq_deleted'));
				$this->routeHelper->goPage();
			}
		} elseif ($id = $this->identificationHelper->getActionId('edit')) {
			$this->handle_edit();
			$this->pageHelper->addBread($this->func->s('bread_faq'), '/?page=faq');
			$this->pageHelper->addBread($this->func->s('bread_edit_faq'));

			$data = $this->faqGateway->getOne_faq($id);
			$this->dataHelper->setEditData($data);

			$this->pageHelper->addContent($this->view->faq_form($this->faqGateway->getBasics_faq_category()));

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->routeHelper->pageLink('faq', 'back_to_overview')
			)), $this->func->s('actions')), CNT_RIGHT);
		} elseif (isset($_GET['id'])) {
			$data = $this->faqGateway->getOne_faq($_GET['id']);
			print_r($data);
		} else {
			$this->pageHelper->addBread($this->func->s('faq_bread'), '/?page=faq');

			if ($data = $this->faqGateway->get_faq()) {
				$sort = array();
				foreach ($data as $d) {
					if (!isset($sort[$d['faq_kategorie_id']])) {
						$sort[$d['faq_kategorie_id']] = array();
					}
					$sort[$d['faq_kategorie_id']][] = $d;
				}

				foreach ($sort as $key => $data) {
					$rows = array();
					foreach ($data as $d) {
						$rows[] = array(
							array('cnt' => '<a class="linkrow ui-corner-all" href="/?page=faq&a=edit&id=' . $d['id'] . '">' . $d['name'] . '</a>'),
							array('cnt' => $this->v_utils->v_toolbar(array('id' => $d['id'], 'types' => array('edit', 'delete'), 'confirmMsg' => $this->func->sv('delete_sure', $d['name'])))
							));
					}

					$table = $this->v_utils->v_tablesorter(array(
						array('name' => $this->func->s('name')),
						array('name' => $this->func->s('actions'), 'sort' => false, 'width' => 50)
					), $rows);

					$this->pageHelper->addContent($this->v_utils->v_field($table, $this->model->getVal('name', 'faq_category', $key)));
				}
			} else {
				$this->func->info($this->func->s('faq_empty'));
			}

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				array('href' => '/?page=faq&a=neu', 'name' => $this->func->s('neu_faq'))
			)), 'Aktionen'), CNT_RIGHT);
		}
	}

	private function handle_edit()
	{
		global $g_data;

		if ($this->func->submitted()) {
			$g_data['foodsaver_id'] = $this->session->id();
			if ($this->faqGateway->update_faq($_GET['id'], $g_data)) {
				$this->func->info($this->func->s('faq_edit_success'));
				$this->routeHelper->goPage();
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}

	private function handle_add()
	{
		global $g_data;

		if ($this->func->submitted()) {
			$g_data['foodsaver_id'] = $this->session->id();
			if ($this->model->add_faq($g_data)) {
				$this->func->info($this->func->s('faq_add_success'));
				$this->routeHelper->goPage();
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}
}
