<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Modules\Core\Control;

class MapControl extends Control
{
	public function __construct(MapView $view)
	{
		$this->view = $view;
		parent::__construct();
	}

	public function index()
	{
		$this->setTemplate('map');
		$this->pageHelper->addContent($this->view->index(), CNT_MAIN);
	}
}
