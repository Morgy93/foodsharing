<?php

namespace Foodsharing\Modules\StoreOverview;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Store\StoreGateway;

class StoreOverviewControl extends Control
{
	private $storeGateway;

	public function __construct(
		StoreOverviewView $view,
		StoreGateway $storeGateway
	) {
		$this->view = $view;
		$this->storeGateway = $storeGateway;
		parent::__construct();
	}

	public function index()
	{
		$this->pageHelper->addBread($this->translationHelper->s('overview'));

		$this->pageHelper->addContent($this->view->StoreOverview());

		$stores = $this->storeGateway->getMyStores($this->session->id(), $this->session->getCurrentRegionId());
		$this->pageHelper->addContent($this->view->u_storeList($stores['sonstige'], $this->translationHelper->s('overview_stores')));

	}
}
