<?php

namespace Foodsharing\Modules\StoreOverview;

use Foodsharing\Modules\Core\Control;

class StoreOverviewControl extends Control
{
	public function __construct(
		StoreOverviewView $view
	) {
		$this->view = $view;

		parent::__construct();
	}

	public function index()
	{
		$this->pageHelper->addContent($this->view->StoreOverview());
	}
}
