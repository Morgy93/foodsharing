<?php

namespace Foodsharing\Modules\Stats;

use Foodsharing\Modules\Console\ConsoleControl;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;

class StatsControl extends ConsoleControl
{
	private $storeGateway;
	private $regionGateway;

	public function __construct(StatsModel $model, StoreGateway $storeGateway, RegionGateway $regionGateway)
	{
		$this->model = $model;
		$this->storeGateway = $storeGateway;
		$this->regionGateway = $regionGateway;
		parent::__construct();
	}

	public function foodsaver()
	{
		self::info('Statistik Auswertung für Foodsaver');

		if ($fsids = $this->model->getFoodsaverIds()) {
			foreach ($fsids as $fsid) {
				$stat_gerettet = $this->model->getGerettet($fsid);
				$stat_fetchcount = (int)$this->model->qOne('SELECT COUNT(foodsaver_id) FROM fs_abholer WHERE foodsaver_id = ' . (int)$fsid . ' AND `date` < NOW()');
				$stat_post = (int)$this->model->qOne('SELECT COUNT(id) FROM fs_theme_post WHERE foodsaver_id = ' . (int)$fsid);
				$stat_post += (int)$this->model->qOne('SELECT COUNT(id) FROM fs_wallpost WHERE foodsaver_id = ' . (int)$fsid);
				$stat_post += (int)$this->model->qOne('SELECT COUNT(id) FROM fs_betrieb_notiz WHERE milestone = 0 AND foodsaver_id = ' . (int)$fsid);

				$stat_bananacount = (int)$this->model->qOne('SELECT COUNT(foodsaver_id) FROM fs_rating WHERE `ratingtype` = 2 AND foodsaver_id = ' . (int)$fsid);

				$stat_buddycount = (int)$this->model->qone('SELECT COUNT(foodsaver_id) FROM fs_buddy WHERE foodsaver_id = ' . (int)$fsid . ' AND confirmed = 1');

				$stat_fetchrate = 100;

				$count_not_fetch = (int)$this->model->qOne('SELECT COUNT(foodsaver_id) FROM fs_report WHERE `reporttype` = 1 AND committed = 1 AND tvalue like \'%Ist gar nicht zum Abholen gekommen%\' AND foodsaver_id = ' . (int)$fsid);

				if ($count_not_fetch > 0 && $stat_fetchcount >= $count_not_fetch) {
					$stat_fetchrate = round(100 - ($count_not_fetch / ($stat_fetchcount / 100)), 2);
				}

				$this->model->update('
						UPDATE fs_foodsaver

						SET 	stat_fetchweight = ' . (float)$stat_gerettet . ',
						stat_fetchcount = ' . (int)$stat_fetchcount . ',
						stat_postcount = ' . (int)$stat_post . ',
						stat_buddycount = ' . (int)$stat_buddycount . ',
						stat_bananacount = ' . (int)$stat_bananacount . ',
						stat_fetchrate = ' . (float)$stat_fetchrate . '

						WHERE 	id = ' . (int)$fsid . '
				');
			}
		}

		self::success('OK');
	}

	public function betriebe()
	{
		self::info('Statistik Auswertung für Betriebe');

		$betriebe = $this->model->getBetriebe();

		foreach ($betriebe as $i => $b) {
			$this->calcBetrieb($b);
		}

		self::success('ready :o)');
	}

	private function calcBetrieb($betrieb)
	{
		$bid = $betrieb['id'];

		if ($bid > 0) {
			$added = $betrieb['added'];

			if ($team = $this->storeGateway->getBetriebTeam($bid)) {
				foreach ($team as $fs) {
					$newdata = array(
						'stat_first_fetch' => $fs['stat_first_fetch'],
						'foodsaver_id' => $fs['id'],
						'betrieb_id' => $bid,
						'verantwortlich' => $fs['verantwortlich'],
						'stat_fetchcount' => $fs['stat_fetchcount'],
						'stat_last_fetch' => null
					);

					/* first_fetch */
					if ($first_fetch = $this->model->getFirstFetchInBetrieb($bid, $fs['id'])) {
						$newdata['stat_first_fetch'] = $first_fetch;
					}

					/*last fetch*/
					if ($last_fetch = $this->model->getLastFetchInBetrieb($bid, $fs['id'])) {
						$newdata['stat_last_fetch'] = $last_fetch;
					}

					/*fetchcount*/
					$fetchcount = $this->model->getBetriebFetchCount($bid, $fs['id'], $fs['stat_last_update'], $fs['stat_fetchcount']);

					$this->model->updateBetriebStat(
						$bid, // Betrieb id
						$fs['id'], // foodsaver_id
						$fs['stat_add_date'], // add date
						$newdata['stat_first_fetch'], // erste mal abholen
						$fetchcount, // anzahl abholungen
						$newdata['stat_last_fetch']
					);
				}
			}
		}
	}

	/**
	 * public accacable method to calculate all statictic for each bezirk
	 * for the moment i have no other idea to calculate live because the hierarchical child bezirk query neeed so long time.
	 */
	public function bezirke()
	{
		self::info('Statistik Auswertung für Bezirke');

		// get all Bezirke non memcached
		$bezirke = $this->model->getAllBezirke();
		foreach ($bezirke as $i => $b) {
			$kilo = $this->calcBezirk($b);
		}

		self::success('ready :o)');
	}

	private function calcBezirk($bezirk)
	{
		$bezirk_id = $bezirk['id'];
		$last_update = $bezirk['stat_last_update'];

		$child_ids = $this->regionGateway->listIdsForDescendantsAndSelf($bezirk_id);

		/* abholmenge & anzahl abholungen */
		$stat_fetchweight = $this->model->getFetchWeight($bezirk_id, $last_update, $child_ids);
		$stat_fetchcount = $stat_fetchweight['count'];
		$stat_fetchweight = $stat_fetchweight['weight'];

		/* anzahl foodsaver */
		$stat_fscount = $this->model->getFsCount($bezirk_id, $child_ids);

		/*anzahl botschafter*/
		$stat_botcount = $this->model->getBotCount($bezirk_id, $child_ids);

		/* anzahl posts */
		$stat_postcount = $this->model->getPostCount($bezirk_id, $child_ids);

		/* fairteiler_count */
		$stat_fairteilercount = $this->model->getFairteilerCount($bezirk_id, $child_ids);

		/* count betriebe */
		$stat_betriebecount = $this->model->getBetriebCount($bezirk_id, $child_ids);

		/* count koorp betriebe */
		$stat_betriebCoorpCount = $this->model->getBetriebKoorpCount($bezirk_id, $child_ids);

		$this->model->updateStats($bezirk_id, $stat_fetchweight, $stat_fetchcount, $stat_postcount, $stat_betriebecount, $stat_betriebCoorpCount, $stat_botcount, $stat_fscount, $stat_fairteilercount);

		return $stat_fetchweight;
	}
}
