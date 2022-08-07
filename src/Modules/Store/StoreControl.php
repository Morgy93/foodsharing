<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Bell\BellType;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\Milestone;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\WeightHelper;

class StoreControl extends Control
{
	private $bellGateway;
	private $storeGateway;
	private $storePermissions;
	private $storeTransactions;
	private $regionGateway;
	private $foodsaverGateway;
	private $identificationHelper;
	private $dataHelper;
	private $weightHelper;

	public function __construct(
		StorePermissions $storePermissions,
		StoreTransactions $storeTransactions,
		StoreView $view,
		BellGateway $bellGateway,
		StoreGateway $storeGateway,
		FoodsaverGateway $foodsaverGateway,
		RegionGateway $regionGateway,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper,
		WeightHelper $weightHelper
	) {
		$this->view = $view;
		$this->bellGateway = $bellGateway;
		$this->storeGateway = $storeGateway;
		$this->storePermissions = $storePermissions;
		$this->storeTransactions = $storeTransactions;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->regionGateway = $regionGateway;
		$this->identificationHelper = $identificationHelper;
		$this->dataHelper = $dataHelper;
		$this->weightHelper = $weightHelper;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->goLogin();
		}
	}

	public function index()
	{
		/* form methods below work with $g_data */
		global $g_data;

		if (isset($_GET['bid'])) {
			$regionId = (int)$_GET['bid'];
		} else {
			$regionId = $this->session->getCurrentRegionId();
		}

		if (!$this->session->may('orga') && $regionId == 0) {
			$regionId = $this->session->getCurrentRegionId();
		}
		if ($regionId > 0) {
			$region = $this->regionGateway->getRegion($regionId);
		} else {
			$region = ['name' => $this->translator->trans('store.complete')];
		}
		if ($this->identificationHelper->getAction('new')) {
			if ($this->storePermissions->mayCreateStore()) {
				$this->handle_add($this->session->id());

				$this->pageHelper->addBread($this->translator->trans('store.bread'), '/?page=fsbetrieb');
				$this->pageHelper->addBread($this->translator->trans('storeedit.add-new'));

				$chosenRegion = ($regionId > 0 && UnitType::isAccessibleRegion($this->regionGateway->getType($regionId))) ? $region : null;
				$this->pageHelper->addContent($this->view->betrieb_form(
					$chosenRegion,
					'betrieb',
					$this->storeGateway->getBasics_groceries(),
					$this->storeGateway->getBasics_chain(),
					$this->storeGateway->getStoreCategories(),
					$this->getStoreStateList(),
					$this->weightHelper->getWeightListEntries()
				));

				$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
					['name' => $this->translator->trans('bread.backToOverview'), 'href' => '/?page=fsbetrieb&bid=' . $regionId]
				]), $this->translator->trans('storeedit.actions')), CNT_RIGHT);
			} else {
				$this->flashMessageHelper->info($this->translator->trans('store.smneeded'));
				$this->routeHelper->go('/?page=settings&sub=up_bip');
			}
		} elseif ($id = $this->identificationHelper->getActionId('delete')) {
		} elseif ($id = $this->identificationHelper->getActionId('edit')) {
			$this->pageHelper->addBread($this->translator->trans('store.bread'), '/?page=fsbetrieb');
			$this->pageHelper->addBread($this->translator->trans('storeedit.bread'));
			$data = $this->storeGateway->getEditStoreData($id);

			$this->pageHelper->addTitle($data['name']);
			$this->pageHelper->addTitle($this->translator->trans('storeedit.bread'));

			if ($this->storePermissions->mayEditStore($id)) {
				$this->handle_edit();

				$this->dataHelper->setEditData($data);

				$regionId = $data['bezirk_id'];
				$regionName = $this->regionGateway->getRegionName($regionId);

				$this->pageHelper->addContent($this->view->betrieb_form(
					['id' => $regionId, 'name' => $regionName],
					'',
					$this->storeGateway->getBasics_groceries(),
					$this->storeGateway->getBasics_chain(),
					$this->storeGateway->getStoreCategories(),
					$this->getStoreStateList(),
					$this->weightHelper->getWeightListEntries()
				));
			} else {
				$this->flashMessageHelper->info($this->translator->trans('store.locked'));
			}

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
				$this->routeHelper->pageLink('betrieb')
			]), $this->translator->trans('storeedit.actions')), CNT_RIGHT);
		} elseif (isset($_GET['id'])) {
			$this->routeHelper->go('/?page=fsbetrieb&id=' . (int)$_GET['id']);
		} else {
			$this->pageHelper->addBread($this->translator->trans('store.bread'), '/?page=fsbetrieb');

			$stores = $this->storeGateway->listStoresInRegion($regionId, true);

			$storesMapped = array_map(function ($store) {
				return [
					'id' => (int)$store['id'],
					'name' => $store['name'],
					// status COOPERATION_STARTING and COOPERATION_ESTABLISHED are the same (in cooperation), always return COOPERATION_STARTING
					'status' => $store['betrieb_status_id'] == CooperationStatus::COOPERATION_ESTABLISHED ? CooperationStatus::COOPERATION_STARTING : (int)$store['betrieb_status_id'],
					'added' => $store['added'],
					'region' => $store['bezirk_name'],
					'address' => $store['anschrift'],
					'city' => $store['stadt'],
					'zipcode' => $store['plz'],
					'geo' => $store['geo'],
				];
			}, $stores);

			$this->pageHelper->addContent($this->view->vueComponent('vue-storelist', 'store-list', [
				'regionName' => $region['name'],
				'regionId' => $regionId,
				'showCreateStore' => $this->storePermissions->mayCreateStore(),
				'stores' => array_values($storesMapped),
			]));
		}
	}

	public function getStoreStateList(): array
	{
		return [
			['id' => '1', 'name' => $this->translator->trans('storestatus.1')],
			['id' => '2', 'name' => $this->translator->trans('storestatus.2')],
			['id' => '3', 'name' => $this->translator->trans('storestatus.3a')],
			['id' => '4', 'name' => $this->translator->trans('storestatus.4')],
			['id' => '5', 'name' => $this->translator->trans('storestatus.5')],
			['id' => '6', 'name' => $this->translator->trans('storestatus.6')],
			['id' => '7', 'name' => $this->translator->trans('storestatus.7')],
		];
	}

	private function handle_edit()
	{
		global $g_data;
		if ($this->submitted()) {
			$id = (int)$_GET['id'];
			$g_data['stadt'] = $g_data['ort'];
			$g_data['str'] = $g_data['anschrift'];

			$this->storeTransactions->updateAllStoreData($id, $g_data);

			$this->storeTransactions->setStoreNameInConversations($id, $g_data['name']);

			$this->flashMessageHelper->success($this->translator->trans('storeedit.edit_success'));
			$this->routeHelper->go('/?page=fsbetrieb&id=' . $id);
		}
	}

	private function handle_add($coordinator)
	{
		global $g_data;
		if (!$this->submitted()) {
			return;
		}

		$g_data['bezirk_id'] ??= $this->session->getCurrentRegionId();

		if (!in_array($g_data['bezirk_id'], $this->session->listRegionIDs())) {
			$this->flashMessageHelper->error($this->translator->trans('storeedit.not-in-region'));
			$this->routeHelper->goPage();

			return;
		}

		if (isset($g_data['ort'])) {
			$g_data['stadt'] = $g_data['ort'];
		}
		if (isset($g_data['anschrift'])) {
			$g_data['str'] = $g_data['anschrift'];
		}

		$storeId = $this->storeTransactions->createStore($g_data);

		if (!$storeId) {
			$this->flashMessageHelper->error($this->translator->trans('error_unexpected'));

			return;
		}

		$this->storeTransactions->setStoreNameInConversations($storeId, $g_data['name']);
		$this->storeGateway->add_betrieb_notiz([
			'foodsaver_id' => $this->session->id(),
			'betrieb_id' => $storeId,
			'text' => '{BETRIEB_ADDED}', // TODO Do we want to keep this?
			'zeit' => date('Y-m-d H:i:s', (time() - 10)),
			'milestone' => Milestone::CREATED,
		]);

		if (isset($g_data['first_post']) && !empty($g_data['first_post'])) {
			$this->storeGateway->add_betrieb_notiz([
				'foodsaver_id' => $this->session->id(),
				'betrieb_id' => $storeId,
				'text' => $g_data['first_post'],
				'zeit' => date('Y-m-d H:i:s'),
				'milestone' => Milestone::NONE,
			]);
		}

		$foodsaver = $this->foodsaverGateway->getFoodsaversByRegion($g_data['bezirk_id']);

		$bellData = Bell::create('store_new_title', 'store_new', 'fas fa-store-alt', [
			'href' => '/?page=fsbetrieb&id=' . (int)$storeId
		], [
			'user' => $this->session->user('name'),
			'name' => $g_data['name']
		], BellType::createIdentifier(BellType::NEW_STORE, (int)$storeId));
		$this->bellGateway->addBell($foodsaver, $bellData);

		$this->flashMessageHelper->success($this->translator->trans('storeedit.add_success'));

		$this->routeHelper->go('/?page=fsbetrieb&id=' . (int)$storeId);
	}
}
