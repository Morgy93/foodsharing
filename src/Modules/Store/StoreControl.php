<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\DTO\CreateStoreData;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;

class StoreControl extends Control
{
    private $storeGateway;
    private $storePermissions;
    private $storeTransactions;
    private $regionGateway;
    private $identificationHelper;
    private $dataHelper;

    public function __construct(
        StorePermissions $storePermissions,
        StoreTransactions $storeTransactions,
        StoreView $view,
        StoreGateway $storeGateway,
        RegionGateway $regionGateway,
        IdentificationHelper $identificationHelper,
        DataHelper $dataHelper
    ) {
        $this->view = $view;
        $this->storeGateway = $storeGateway;
        $this->storePermissions = $storePermissions;
        $this->storeTransactions = $storeTransactions;
        $this->regionGateway = $regionGateway;
        $this->identificationHelper = $identificationHelper;
        $this->dataHelper = $dataHelper;

        parent::__construct();

        if (!$this->session->mayRole()) {
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

        if (!$this->session->mayRole(Role::ORGA) && $regionId == 0) {
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
                    $this->storeTransactions->getCommonStoreMetadata(false),
                    $chosenRegion,
                    'betrieb'
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
                    $this->storeTransactions->getCommonStoreMetadata(false),
                    ['id' => $regionId, 'name' => $regionName],
                    '',
                ));
            } else {
                $this->flashMessageHelper->info($this->translator->trans('store.locked'));
            }

            $this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
                ['name' => $this->translator->trans('bread.backToStore'), 'href' => '/?page=fsbetrieb&bid=' . $regionId]
            ]), $this->translator->trans('storeedit.actions')), CNT_RIGHT);
        } elseif (isset($_GET['id'])) {
            $this->routeHelper->go('/?page=fsbetrieb&id=' . (int)$_GET['id']);
        } else {
            if (!$this->session->mayRole() || !$this->storePermissions->mayListStores()) {
                $this->routeHelper->go('/');
            }

            if (empty($region) || $regionId <= 0) {
                $this->flashMessageHelper->info($this->translator->trans('store.error'));
                $this->routeHelper->go('/');
            } else {
                $this->pageHelper->addBread($this->translator->trans('store.bread'), '/?page=fsbetrieb');
                $storesMapped = $this->storeTransactions->listOverviewInformationsOfStoresInRegion($regionId, true);

                $this->pageHelper->addContent($this->view->vueComponent('vue-storelist', 'store-list', [
                    'regionName' => $region['name'],
                    'regionId' => $regionId,
                    'showCreateStore' => $this->storePermissions->mayCreateStore(),
                    'stores' => array_values($storesMapped),
                ]));
            }
        }
    }

    private function handle_edit()
    {
        global $g_data;
        if ($this->submitted()) {
            $id = (int)$_GET['id'];
            $g_data['stadt'] = $g_data['ort'];
            $g_data['str'] = $g_data['anschrift'];

            $this->storeTransactions->updateAllStoreData($id, $g_data);

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
        $firstStorePost = $g_data['first_post'] ?? null;
        $storeId = $this->storeTransactions->createStore(CreateStoreData::createFromArray($g_data), $this->session->id(), $firstStorePost);

        if (!$storeId) {
            $this->flashMessageHelper->error($this->translator->trans('error_unexpected'));

            return;
        }

        $this->flashMessageHelper->success($this->translator->trans('storeedit.add_success'));

        $this->routeHelper->go('/?page=fsbetrieb&id=' . (int)$storeId);
    }
}
