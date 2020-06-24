<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Settings\SettingsGateway;
use Foodsharing\Modules\Store\StoreModel;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class FoodsaverControl extends Control
{
	private $foodsaverGateway;
	private $foodsaverTransactions;
	private $contentGateway;
	private $regionGateway;
	private $settingsGateway;
	private $storeModel;
	private $profilePermissions;
	private $regionPermissions;
	private $dataHelper;
	private $identificationHelper;
	private $translator;

	public function __construct(
		FoodsaverView $view,
		FoodsaverGateway $foodsaverGateway,
		FoodsaverTransactions $foodsaverTransactions,
		ContentGateway $contentGateway,
		RegionGateway $regionGateway,
		SettingsGateway $settingsGateway,
		StoreModel $storeModel,
		ProfilePermissions $profilePermissions,
		RegionPermissions $regionPermissions,
		DataHelper $dataHelper,
		IdentificationHelper $identificationHelper,
		TranslatorInterface $translator
	) {
		$this->view = $view;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->foodsaverTransactions = $foodsaverTransactions;
		$this->contentGateway = $contentGateway;
		$this->regionGateway = $regionGateway;
		$this->settingsGateway = $settingsGateway;
		$this->storeModel = $storeModel;
		$this->profilePermissions = $profilePermissions;
		$this->regionPermissions = $regionPermissions;
		$this->dataHelper = $dataHelper;
		$this->identificationHelper = $identificationHelper;
		$this->translator = $translator;

		parent::__construct();
	}

	/*
	 * Default Method for ?page=foodsaver
	 */
	public function index()
	{
		if ((isset($_GET['bid']) && $regionId = $_GET['bid']) && $this->regionPermissions->mayHandleFoodsaverRegionMenu($regionId)) {
			if ($region = $this->regionGateway->getRegion($regionId)) {
				if ($foodsavers = $this->foodsaverGateway->getFoodsaversByRegion($regionId)) {
					$this->pageHelper->addBread('Foodsaver', '/?page=foodsaver&bid=' . $regionId);
					$this->pageHelper->addBread($region['name'], '/?page=foodsaver&bid=' . $regionId);

					$this->pageHelper->addContent(
						$this->view->foodsaverList($foodsavers, $region),
						CNT_LEFT
					);

					$this->pageHelper->addContent($this->view->foodsaverForm());

					if ($inactiveFoodsavers = $this->foodsaverGateway->getFoodsaversByRegion($regionId, true)) {
						$this->pageHelper->addContent(
							$this->view->foodsaverList($inactiveFoodsavers, $region, true),
							CNT_RIGHT
						);
					}
				}
			}
		} elseif (($fsId = $this->identificationHelper->getActionId('edit')) && $this->profilePermissions->mayAdministrateUserProfile($fsId)) {
			if ($fs = $this->foodsaverGateway->getFoodsaver($fsId)) {
				$this->handle_edit();
				$fs = $this->foodsaverGateway->getFoodsaver($fsId); // refresh data as it may changed

				$this->pageHelper->addBread($this->translationHelper->s('bread_foodsaver'), '/?page=foodsaver');
				$this->pageHelper->addBread($this->translationHelper->s('bread_edit_foodsaver'));

				$this->dataHelper->setEditData($fs);

				$regionDetails = $fs['bezirk_id'] > 0 ? $this->regionGateway->getRegion($fs['bezirk_id']) : false;
				$this->pageHelper->addContent($this->view->foodsaver_form($fs['name'] . ' ' . $fs['nachname'] . ' bearbeiten', $regionDetails));

				if ($this->profilePermissions->mayDeleteUser()) {
					$this->pageHelper->addContent($this->v_utils->v_field(
						$this->v_utils->v_menu([
						['href' => '/profile/' . $fs['id'], 'name' => $this->translationHelper->s('back_to_profile')],
						['click' => 'fsapp.confirmDeleteUser(' . $fs['id'] . ')', 'name' => $this->translator->trans('foodsaver.delete_account')]
						]),
						$this->translationHelper->s('actions')),
						CNT_RIGHT
					);
				}
				$this->pageHelper->addContent($this->v_utils->v_field(
					$this->v_utils->v_menu([
						['href' => '/profile/' . $fs['id'], 'name' => $this->translationHelper->s('back_to_profile')],
					]),
					$this->translationHelper->s('actions')),
					CNT_RIGHT
				);
			}
		} else {
			$this->pageHelper->addContent($this->v_utils->v_info('Du hast leider keine Berechtigung für diesen Bezirk'));
		}
	}

	private function handle_edit()
	{
		global $g_data;

		if ($this->submitted()) {
			if ($this->session->may('orga')) {
				if (isset($g_data['orgateam']) && is_array($g_data['orgateam']) && $g_data['orgateam'][0] == 1) {
					$g_data['orgateam'] = 1;
				}
			} else {
				$g_data['orgateam'] = 0;
				unset($g_data['email'], $g_data['rolle']);
			}

			if (isset($_GET['id']) && $fsId = (int)$_GET['id']) {
				if ($oldFs = $this->foodsaverGateway->getFoodsaver($fsId)) {
					$changedFields = ['name', 'nachname', 'stadt', 'plz', 'anschrift', 'telefon', 'handy', 'geschlecht', 'geb_datum', 'rolle', 'orgateam'];
					$this->settingsGateway->logChangedSetting($fsId, $oldFs, $g_data, $changedFields, $this->session->id());
				}

				if (!isset($g_data['bezirk_id'])) {
					$g_data['bezirk_id'] = $this->session->getCurrentRegionId();
				}

				if ($this->updateFoodsaver($oldFs, $g_data)) {
					$this->flashMessageHelper->info($this->translationHelper->s('foodsaver_edit_success'));
				} else {
					$this->flashMessageHelper->error($this->translationHelper->s('foodsaver_edit_failure'));
				}
			}
		}
	}

	private function updateFoodsaver(array $fs, array $data): bool
	{
		if (!$this->session->may('orga')) {
			unset($data['rolle']);
		}

		if (isset($data['rolle']) && $data['rolle'] == Role::FOODSHARER && $data['rolle'] < $fs['rolle']) {
			$downgradedRows = $this->foodsaverTransactions->downgradeAndBlockForQuizPermanently($fs['id'], $this->storeModel);
		} else {
			$downgradedRows = 0;
		}

		$updatedRows = $this->foodsaverGateway->updateFoodsaver($fs['id'], $data);

		return $downgradedRows > 0 || $updatedRows > 0;
	}

	private function picture_box()
	{
		$photo = $this->foodsaverGateway->getPhotoFileName($_GET['id']);

		if (!(file_exists('images/thumb_crop_' . $photo))) {
			$p_cnt = $this->v_utils->v_photo_edit('img/portrait.png', (int)$_GET['id']);
		} else {
			$p_cnt = $this->v_utils->v_photo_edit('images/thumb_crop_' . $photo, (int)$_GET['id']);
			//$p_cnt = $this->v_utils->v_photo_edit('img/portrait.png');
		}

		return $this->v_utils->v_field($p_cnt, 'Dein Foto');
	}
}
