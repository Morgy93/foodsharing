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
		$this->pageHelper->addBread($this->translationHelper->s('registration'));
		$this->pageHelper->addTitle($this->translationHelper->s('registration'));

		$this->pageHelper->addContent($this->view->registerForm());
	}
}
