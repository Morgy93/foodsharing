<?php

namespace Foodsharing\Modules\StoreOverview;

use Foodsharing\Modules\Core\Control;

class StoreOverviewControl extends Control
{
	protected $view;

	public function __construct(
		StoreOverviewView $view
	) {
		$this->view = $view;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->goLogin();
		}
	}

	public function index()
	{
		$this->pageHelper->addContent('<h1>Test</h1>');
		$this->pageHelper->addContent($this->view->vueComponent('vue-storeoverview', 'store-overview-list', []));
	}
}
