<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\WallPost\StoreWallEntryType;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class StoreControl extends Control
{
	private $bellGateway;
	private $storeModel;
	private $storeGateway;
	private $storePermissions;
	private $storeTransactions;
	private $regionGateway;
	private $foodsaverGateway;
	private $translator;
	private $identificationHelper;
	private $dataHelper;

	public function __construct(
		StoreModel $model,
		StorePermissions $storePermissions,
		StoreTransactions $storeTransactions,
		StoreView $view,
		BellGateway $bellGateway,
		StoreGateway $storeGateway,
		FoodsaverGateway $foodsaverGateway,
		RegionGateway $regionGateway,
		TranslatorInterface $translator,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper
	) {
		$this->storeModel = $model;
		$this->view = $view;
		$this->bellGateway = $bellGateway;
		$this->storeGateway = $storeGateway;
		$this->storePermissions = $storePermissions;
		$this->storeTransactions = $storeTransactions;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->regionGateway = $regionGateway;
		$this->translator = $translator;
		$this->identificationHelper = $identificationHelper;
		$this->dataHelper = $dataHelper;

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

		if (!$this->session->isOrgaTeam() && $regionId == 0) {
			$regionId = $this->session->getCurrentRegionId();
		}
		if ($regionId > 0) {
			$region = $this->regionGateway->getRegion($regionId);
		} else {
			$region = ['name' => 'kompletter Datenbank'];
		}
		if ($this->identificationHelper->getAction('new')) {
			if (!$this->storePermissions->mayCreateStore()) {
				$this->flashMessageHelper->info(
					$this->translator->trans('storeedit.needsStoreManager')
				);
				$this->routeHelper->go('?page=settings&sub=upgrade/up_bip');
			}

			$this->handle_add($this->session->id());

			$this->pageHelper->addBread($this->translator->trans('bread.stores'), '/?page=betrieb');
			$this->pageHelper->addBread($this->translator->trans('bread.store.new'));

			if (isset($_GET['id'])) {
				$g_data['foodsaver'] = $this->storeGateway->getStoreManagers($_GET['id']);
			}

			$chosenRegion = ($regionId > 0 && Type::isAccessibleRegion($this->regionGateway->getType($regionId))) ? $region : null;
			$this->pageHelper->addContent($this->view->betrieb_form($chosenRegion));

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
				['name' => $this->translator->trans('bread.backToOverview'), 'href' => '/?page=fsbetrieb&bid=' . $regionId]
			]), $this->translationHelper->s('actions')), CNT_RIGHT);
		} elseif ($storeId = $this->identificationHelper->getActionId('delete')) {
			// see #485
		} elseif ($storeId = $this->identificationHelper->getActionId('edit')) {
			if (!$this->storePermissions->mayEditStore($storeId)) {
				$this->flashMessageHelper->error(
					$this->translator->trans('storeedit.notAllowed')
				);
				$this->routeHelper->go('/?page=fsbetrieb&id=' . $storeId);
			}

			$store = $this->storeModel->getOne_betrieb($storeId);

			$this->pageHelper->addBread($this->translator->trans('bread.stores'), '/?page=fsbetrieb');
			$this->pageHelper->addBread($store['name'], '/?page=fsbetrieb&id=' . $storeId);
			$this->pageHelper->addBread($this->translator->trans('bread.store.edit'));

			$this->pageHelper->addTitle($store['name']);
			$this->pageHelper->addTitle($this->translator->trans('bread.store.edit'));

			$this->handle_edit($store['name']);

			$this->dataHelper->setEditData($store);

			$region = $this->storeModel->getValues(['id', 'name'], 'bezirk', $store['bezirk_id']);
			$g_data['foodsaver'] = $this->storeGateway->getStoreManagers($storeId);

			$this->pageHelper->addContent(
				$this->view->vueComponent('vue-storeedit', 'store-edit', [
					'storeData' => $store,
					'mayManageStoreChains' => $this->storePermissions->mayManageStoreChains(),
					'foodTypeOptions' => $this->storeGateway->getBasics_groceries(),
					'chainOptions' => $this->storeGateway->getBasics_chain(),
					'categoryOptions' => $this->storeGateway->getStoreCategories(),
				])
			);
			$this->pageHelper->addContent(
				$this->view->betrieb_form($region, 'store-legacydata d-none')
			);

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
				$this->routeHelper->pageLink('fsbetrieb&id=' . $storeId, 'back_to_betrieb')
			]), $this->translationHelper->s('actions')), CNT_TOP);
		} elseif (isset($_GET['id'])) {
			$this->routeHelper->go('/?page=fsbetrieb&id=' . (int)$_GET['id']);
		} else {
			$this->pageHelper->addBread($this->translator->trans('bread.stores'), '/?page=betrieb');

			$stores = $this->storeModel->listBetriebReq($regionId);

			$storesMapped = array_map(function ($store) {
				$storeStatus = (int)$store['betrieb_status_id'];
				// status COOPERATION_STARTING and COOPERATION_ESTABLISHED are the same
				// => always return COOPERATION_STARTING
				if ($storeStatus == CooperationStatus::COOPERATION_ESTABLISHED) {
					$storeStatus = CooperationStatus::COOPERATION_STARTING;
				}

				return [
					'id' => (int)$store['id'],
					'name' => $store['name'],
					'status' => $storeStatus,
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
				'stores' => $storesMapped
			]));
		}
	}

	private function handle_edit(string $storeName)
	{
		global $g_data;
		if ($this->submitted()) {
			$storeId = (int)$_GET['id'];
			$g_data['stadt'] = $g_data['ort'];
			$g_data['hsnr'] = '';
			$g_data['str'] = $g_data['anschrift'];

			if ($this->storeModel->update_legacyStoreInfo($storeId, $g_data, $storeName)) {
				$this->storeTransactions->setStoreNameInConversations($storeId, $storeName);
				$this->flashMessageHelper->info(
					$this->translator->trans('storeedit.savedChanges')
				);
				$this->routeHelper->go('/?page=fsbetrieb&id=' . $storeId);
			} else {
				$this->flashMessageHelper->error(
					$this->translator->trans('error_unexpected')
				);
			}
		}
	}

	private function handle_add($coordinator)
	{
		global $g_data;
		if ($this->submitted()) {
			if (!isset($g_data['bezirk_id'])) {
				$g_data['bezirk_id'] = $this->session->getCurrentRegionId();
			}
			if (!in_array($g_data['bezirk_id'], $this->session->listRegionIDs())) {
				$this->flashMessageHelper->error(
					$this->translator->trans('storeedit.needsRegionMembership')
				);
				$this->routeHelper->goPage();
			}

			if (isset($g_data['ort'])) {
				$g_data['stadt'] = $g_data['ort'];
			}
			$g_data['foodsaver'] = [$coordinator];
			if (isset($g_data['anschrift'])) {
				$g_data['str'] = $g_data['anschrift'];
			}
			$g_data['hsnr'] = '';

			if ($id = $this->storeModel->add_betrieb($g_data)) {
				$this->storeTransactions->setStoreNameInConversations($id, $g_data['name']);
				$this->storeGateway->add_betrieb_notiz([
					'foodsaver_id' => $this->session->id(),
					'betrieb_id' => $id,
					'text' => '{BETRIEB_ADDED}',
					'zeit' => date('Y-m-d H:i:s', (time() - 10)),
					'milestone' => StoreWallEntryType::STORE_CREATED,
				]);

				if (isset($g_data['first_post']) && !empty($g_data['first_post'])) {
					$this->storeGateway->add_betrieb_notiz([
						'foodsaver_id' => $this->session->id(),
						'betrieb_id' => $id,
						'text' => $g_data['first_post'],
						'zeit' => date('Y-m-d H:i:s'),
						'milestone' => StoreWallEntryType::TEXT_POSTED,
					]);
				}

				$foodsaver = $this->foodsaverGateway->getFoodsaversByRegion($g_data['bezirk_id']);

				$bellData = Bell::create('store_new_title', 'store_new', 'fas fa-store-alt', [
					'href' => '/?page=fsbetrieb&id=' . (int)$id
				], [
					'user' => $this->session->user('name'),
					'name' => $g_data['name']
				], 'store-new-' . (int)$id);
				$this->bellGateway->addBell($foodsaver, $bellData);

				$this->flashMessageHelper->info(
					$this->translator->trans('storeedit.newSuccess')
				);

				$this->routeHelper->go('/?page=fsbetrieb&id=' . (int)$id);
			} else {
				$this->flashMessageHelper->error(
					$this->translator->trans('error_unexpected')
				);
			}
		}
	}
}
