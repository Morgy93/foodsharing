<?php

namespace Foodsharing\Modules\DropOffPoint;

use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Core\Control;

/**
 * Can be instantiated via reflection by the @link Routing.php
 * TODO-810: Fill with data from not yet existing database.
 */
class DropOffPointXhr extends Control
{
	public function bubble(): array
	{
		$xhrDialog = new XhrDialog();

		$xhrDialog->setTitle("Abgabenstellenname");

		return $xhrDialog->xhrout();
	}
}
