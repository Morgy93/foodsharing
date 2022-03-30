<?php

namespace Foodsharing\Modules\Stats;

use Foodsharing\Modules\Console\ConsoleControl;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;

class StatsControl extends ConsoleControl
{
	private StatsModel $model;
	private StoreGateway $storeGateway;
	private RegionGateway $regionGateway;
	private StatsGateway $statsGateway;

	public function __construct(
		StatsModel $model,
		StoreGateway $storeGateway,
		RegionGateway $regionGateway,
		StatsGateway $statsGateway
	) {
		$this->model = $model;
		$this->statsGateway = $statsGateway;
		$this->storeGateway = $storeGateway;
		$this->regionGateway = $regionGateway;
		parent::__construct();
	}

	public function foodsaver()
	{
		self::info('Statistik Auswertung für Foodsaver');

		$fetchCount = $this->statsGateway->getFoodsaverPickups();
		$bananaCount = $this->statsGateway->getBananaCount();
		$buddyCount = $this->statsGateway->getBuddyCount();
		$forumPosts = $this->statsGateway->getForumPostCount();
		$wallPosts = $this->statsGateway->getWallPostCount();
		$storeNoteCount = $this->statsGateway->getStoreNoteCount();
		$notFetchedCount = $this->statsGateway->getNotFetchedReportCount();

		$allFsIds = $this->model->getAllFoodsaverIds();
		foreach ($allFsIds as $fsid) {
			$totalKilosFetchedByFoodsaver = $this->model->getTotalKilosFetchedByFoodsaver($fsid);
			$stat_fetchrate = 100;

			if ($notFetchedCount[$fsid] > 0 && $fetchCount[$fsid] >= $notFetchedCount[$fsid]) {
				$stat_fetchrate = round(100 - ($notFetchedCount[$fsid] / ($fetchCount[$fsid] / 100)), 2);
			}

			$postCount = $forumPosts[$fsid] + $wallPosts[$fsid] + $storeNoteCount[$fsid];
			$this->model->update(
				'
						UPDATE fs_foodsaver

						SET 	stat_fetchweight = ' . (float)$totalKilosFetchedByFoodsaver . ',
						stat_fetchcount = ' . $fetchCount[$fsid] . ',
						stat_postcount = ' . $postCount . ',
						stat_buddycount = ' . $buddyCount[$fsid] . ',
						stat_bananacount = ' . $bananaCount[$fsid] . ',
						stat_fetchrate = ' . (float)$stat_fetchrate . '

						WHERE 	id = ' . (int)$fsid . '
				'
			);
		}

		self::success('foodsaver ready :o)');
	}

	public function betriebe()
	{
		self::info('Statistik Auswertung für Betriebe');

		$allStores = $this->statsGateway->fetchAllStores();

		foreach ($allStores as $store) {
			if ($store['id'] > 0) {
				$this->statsGateway->updateStoreUsersData($store['id']);
			}
		}

		self::success('stores ready :o)');
	}

	/**
	 * public accessible method to calculate all statistics for each region
	 * for the moment I have no other idea to calculate live because the hierarchical child region query takes to long.
	 */
	public function bezirke()
	{
		self::info('Statistik Auswertung für Bezirke');

		// get all regions non memcached
		$allRegions = $this->model->getAllRegions();
		foreach ($allRegions as $region) {
			$this->calcRegion($region);
		}

		self::success('region ready :o)');
	}

	private function calcRegion($region)
	{
		$region_id = $region['id'];
		$last_update = $region['stat_last_update'];

		$child_ids = $this->regionGateway->listIdsForDescendantsAndSelf($region_id);

		/* abholmenge & anzahl abholungen */
		$stat_fetchweight = $this->model->getFetchWeight($region_id, $last_update, $child_ids);
		$stat_fetchcount = $stat_fetchweight['count'];
		$stat_fetchweight = $stat_fetchweight['weight'];

		/* anzahl foodsaver */
		$stat_fscount = $this->model->getFsCount($region_id, $child_ids);

		/*anzahl botschafter*/
		$stat_botcount = $this->model->getBotCount($region_id, $child_ids);

		/* anzahl posts */
		$stat_postcount = $this->model->getPostCount($region_id, $child_ids);

		/* fairteiler_count */
		$stat_fairteilercount = $this->model->getFairteilerCount($region_id, $child_ids);

		/* count betriebe */
		$stat_betriebecount = $this->model->getStoreCount($region_id, $child_ids);

		/* count koorp betriebe */
		$stat_betriebCoorpCount = $this->model->getCooperatingStoresCount($region_id, $child_ids);

		$this->model->updateStats(
			$region_id,
			$stat_fetchweight,
			$stat_fetchcount,
			$stat_postcount,
			$stat_betriebecount,
			$stat_betriebCoorpCount,
			$stat_botcount,
			$stat_fscount,
			$stat_fairteilercount
		);

		return $stat_fetchweight;
	}
}
