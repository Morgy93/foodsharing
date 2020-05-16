<?php

namespace Foodsharing\Modules\StoreOverview;

use Foodsharing\Modules\Core\View;

class StoreOverviewView extends View
{
	public function StoreOverview()
	{
		return $this->vueComponent('store-overview', 'storeOverview');
	}

	public function u_storeList($storeData, $title)
	{
		if (empty($storeData)) {
			return '';
		}

		$isRegion = false;
		$storeRows = [];
		foreach ($storeData as $i => $store) {
			$status = $this->v_utils->v_getStatusAmpel($store['betrieb_status_id']);

			$storeRows[$i] = [
				['cnt' => '<a class="linkrow ui-corner-all" href="/?page=fsbetrieb&id=' . $store['id'] . '">' . $store['name'] . '</a>']
			];
		}

		$countDates = 14;
		$date = date('d.m.');

		$head = [
			['name' => 'Betrieb', 'width' => 180],
			['name' => $date, 'width' => 90]];

		for ($i = 1; $i <= $countDates; $i++) {
			$head[] = ['name' =>  date("d.m." , strtotime("+" . $i . "day")), 'width' => 90];
		}

		$table = $this->v_utils->v_tablesorter($head, $storeRows);

		return $this->v_utils->v_field($table, $title);
	}
}
