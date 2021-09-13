<?php

namespace Foodsharing\Modules\DropOffPoint;

use Foodsharing\Modules\Core\Control;
use Symfony\Component\HttpFoundation\Request;

class DropOffPointControl extends Control
{
	private DropOffPointGateway $dropOffPointGateway;

	public function __construct(DropOffPointView $view, DropOffPointGateway $dropOffPointGateway)
	{
		$this->view = $view;
		$this->dropOffPointGateway = $dropOffPointGateway;

		parent::__construct();
	}

	public function index(Request $request): void
	{
		if ($dropOffPointId = intval($request->query->get('id'))) {
			// TODO-810 Prepare dropOffPoint data array object and path it into view.
			$this->dropOffPoint([]);
		}
	}

	private function dropOffPoint(array $dropOffPoint): void
	{
		$this->view->dropOffPoint($dropOffPoint);
	}
}
