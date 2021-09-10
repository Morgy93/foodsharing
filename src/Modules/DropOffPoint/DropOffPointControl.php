<?php

namespace Foodsharing\Modules\DropOffPoint;

use Foodsharing\Modules\Core\Control;

// TODO-810: Not yet working.
class DropOffPointControl extends Control
{
	private DropOffPointGateway $dropOffPointGateway;

	public function __construct(DropOffPointView $view, DropOffPointGateway $basketGateway)
	{
		$this->view = $view;
		$this->dropOffPointGateway = $basketGateway;

		parent::__construct();
	}

	public function index(): void
	{
		if ($id = $this->uriInt(2)) {
			if ($dropOffPoint = $this->dropOffPointGateway->getDropOffPoint($id)) {
				$this->dropOffPoint($dropOffPoint);
			}
		}
	}

	private function dropOffPoint(array $dropOffPoint): void
	{
		$this->view->dropOffPoint($dropOffPoint);
	}
}
