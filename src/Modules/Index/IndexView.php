<?php

namespace Foodsharing\Modules\Index;

use Foodsharing\Modules\Core\View;

class IndexView extends View
{
	public function index($first_content)
	{
		$params = [
			'first_content' => $first_content,
		];

		//return $this->twig->render('pages/Index/page.twig', $params);
		return $this->vueComponent('index', 'Index', ['firstContent' => $first_content]);
	}
}
