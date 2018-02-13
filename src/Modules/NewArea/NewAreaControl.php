<?php

namespace Foodsharing\Modules\NewArea;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;

class NewareaControl extends Control
{
	public function __construct(NewareaModel $model, NewareaView $view)
	{
		$this->model = $model;
		$this->view = $view;

		parent::__construct();

		if (!S::may('orga')) {
			$this->func->go('/?page=dashboard');
		}
	}

	public function index()
	{
		$this->func->addBread('Anfragen für neue Bezirke');
		if ($foodsaver = $this->model->getWantNews()) {
			$this->func->addContent($this->view->listWantNews($foodsaver));

			$this->func->addContent($this->view->orderToBezirk(), CNT_RIGHT);

			$this->func->addContent($this->view->options(), CNT_RIGHT);
		}
	}
}
