<?php

namespace Foodsharing\Modules\GeoClean;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Lib\Session\S;

class GeoCleanControl extends Control
{
	public function __construct()
	{
		$this->model = new GeoCleanModel();
		$this->view = new GeoCleanView();

		parent::__construct();

		if (!S::may('orga')) {
			$this->func->goLogin();
		}
	}

	public function lostRegion()
	{
		$this->func->addBread($this->func->s('lost_regions'));
		if ($regions = $this->model->q('
			SELECT 
				
				DISTINCT b.id,
				b.`name`

			FROM 
				fs_bezirk b
				
			LEFT JOIN 
				fs_botschafter bot
				
			ON 
				b.id = bot.bezirk_id 
				
			WHERE
				bot.foodsaver_id IS NULL
				
			AND 
				b.id > 0
				
			ORDER BY 
				b.name
		')
		) {
			$tmp = array();
			foreach ($regions as $r) {
				if ($count = $this->model->qRow('SELECT COUNT(foodsaver_id) AS count, bezirk_id FROM fs_foodsaver_has_bezirk WHERE bezirk_id = ' . (int)$r['id'])) {
					if ($count['count'] > 0) {
						$r['fscount'] = $count['count'];
						$tmp[] = $r;
					}
				}
			}
			$this->func->addContent($this->view->regionList($tmp));
		}
	}

	public function index()
	{
		if (!isset($_GET['sub'])) {
			$this->func->addBread('Geo Location Cleaner');

			if ($foodsaver = $this->model->getFsWithoutGeo()) {
				$this->func->addContent($this->view->listFs($foodsaver));
			}

			$this->func->addContent($this->view->rightmenu(), CNT_RIGHT);
		}
	}
}
