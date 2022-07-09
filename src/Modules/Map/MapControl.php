<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Modules\Core\Control;

class MapControl extends Control
{
	private MapGateway $mapGateway;

	public function __construct(MapGateway $mapGateway, MapView $view)
	{
		$this->view = $view;
		$this->mapGateway = $mapGateway;

		parent::__construct();
	}

	public function index()
	{
		$this->setTemplate('map');
		$this->pageHelper->addContent($this->view->index(), CNT_MAIN);
	}
}
