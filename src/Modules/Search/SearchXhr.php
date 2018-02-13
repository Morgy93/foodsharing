<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;

class SearchXhr extends Control
{
	public function __construct(SearchModel $model, SearchView $view)
	{
		$this->model = $model;
		$this->view = $view;

		parent::__construct();
	}

	public function search()
	{
		if (S::may('fs')) {
			if ($res = $this->model->search($_GET['s'])) {
				$out = array();
				foreach ($res as $key => $value) {
					if (count($value) > 0) {
						$out[] = array(
							'title' => $this->func->s($key),
							'result' => $value
						);
					}
				}

				return array(
					'data' => $out,
					'status' => 1
				);
			}
		}

		return array(
			'status' => 0
		);
	}
}
