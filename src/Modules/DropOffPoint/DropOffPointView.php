<?php

namespace Foodsharing\Modules\DropOffPoint;

use Foodsharing\Lib\View\vPage;
use Foodsharing\Modules\Core\View;

// TODO-810: Not yet working.
class DropOffPointView extends View
{
	public function dropOffPoint(array $dropOffPoints): void
	{
		$page = new vPage('Test Abgabeseitelabel', '<p>Hello World</p>');
		$page->render();
	}
}
