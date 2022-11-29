<?php

namespace Foodsharing\Modules\StoreUser;

use Carbon\Carbon;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\RegionOptionType;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\PickupGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\WeightHelper;

class StoreUserControl extends Control
{
	private $regionGateway;
	private $pickupGateway;
	private $storeGateway;
	private $storePermissions;
	private $dataHelper;
	private $weightHelper;
	private $groupFunctionGateway;

	public function __construct(
		StoreUserView $view,
		RegionGateway $regionGateway,
		PickupGateway $pickupGateway,
		StoreGateway $storeGateway,
		StorePermissions $storePermissions,
		DataHelper $dataHelper,
		WeightHelper $weightHelper,
		GroupFunctionGateway $groupFunctionGateway
	) {
		$this->view = $view;
		$this->regionGateway = $regionGateway;
		$this->pickupGateway = $pickupGateway;
		$this->storeGateway = $storeGateway;
		$this->storePermissions = $storePermissions;
		$this->dataHelper = $dataHelper;
		$this->weightHelper = $weightHelper;
		$this->groupFunctionGateway = $groupFunctionGateway;

		parent::__construct();

		if (!$this->session->mayRole()) {
			$this->routeHelper->goLogin();
		}
	}

	public function index()
	{
		if (isset($_GET['id'])) {
			$storeId = intval($_GET['id']);
			$this->pageHelper->addBread($this->translator->trans('store.bread'), '/?page=fsbetrieb');
			global $g_data;

			$store = $this->storeGateway->getMyStore($this->session->id(), $storeId);

			if (!$store) {
				$this->routeHelper->goPage();
			}

			$this->pageHelper->jsData['store'] = [
				'id' => $storeId,
				'name' => $store['name'],
				'bezirk_id' => (int)$store['bezirk_id'],
				'verantwortlich' => $store['verantwortlich'],
				'prefetchtime' => $store['prefetchtime'],
				'isJumper' => $store['jumper']
			];

			$this->pageHelper->addTitle($store['name']);

			if ($this->storePermissions->mayAccessStore($storeId)) {
				if (!$store['verantwortlich']) {
					$store['verantwortlich'] = $this->isResponsibleForThisStoreAnyways($storeId);
				}

				$this->dataHelper->setEditData($store);

				$this->pageHelper->addBread($store['name']);

				/* find yourself in the pickup list and show your last pickup date in store info */
				$lastFetchDate = null;
				$userIsInStore = false;
				foreach ($store['foodsaver'] as $fs) {
					if ($fs['id'] === $this->session->id()) {
						$userIsInStore = true;
						if ($fs['last_fetch'] != null) {
							$lastFetchDate = Carbon::createFromTimestamp($fs['last_fetch']);
							break;
						}
					}
				}

				/* Infos */
				$this->pageHelper->addContent($this->view->vueComponent('vue-storeinfos', 'store-infos', [
					'particularitiesDescription' => $store['besonderheiten'] ?? '',
					'lastFetchDate' => $lastFetchDate,
					'street' => $store['str'],
					'postcode' => $store['plz'],
					'city' => $store['stadt'],
					'storeTitle' => $store['name'],
					'collectionQuantity' => $this->weightHelper->getFetchWeightName($store['abholmenge']),
					'press' => $store['presse'],
					'regionPickupRules' => (bool)$store['use_region_pickup_rule'],
					'regionPickupRuleActive' => (bool)$this->regionGateway->getRegionOption((int)$store['bezirk_id'], RegionOptionType::REGION_PICKUP_RULE_ACTIVE),
					'regionPickupRuleTimespan' => $this->regionGateway->getRegionOption((int)$store['bezirk_id'], RegionOptionType::REGION_PICKUP_RULE_TIMESPAN_DAYS),
					'regionPickupRuleLimit' => $this->regionGateway->getRegionOption((int)$store['bezirk_id'], RegionOptionType::REGION_PICKUP_RULE_LIMIT_NUMBER),
					'regionPickupRuleLimitDay' => $this->regionGateway->getRegionOption((int)$store['bezirk_id'], RegionOptionType::REGION_PICKUP_RULE_LIMIT_DAY_NUMBER),
					'regionPickupRuleInactive' => $this->regionGateway->getRegionOption((int)$store['bezirk_id'], RegionOptionType::REGION_PICKUP_RULE_INACTIVE_HOURS),
				]), CNT_RIGHT);

				/* options menu */
				$menu = [];

				/* store options */
				$teamConversionId = null;
				if ($this->storePermissions->mayChatWithRegularTeam($store)) {
					$teamConversionId = $store['team_conversation_id'];
				}

				$springerConversationId = null;
				if ($this->storePermissions->mayChatWithJumperWaitingTeam($store)) {
					$springerConversationId = $store['springer_conversation_id'];
				}

				$this->pageHelper->addContent(
					$this->view->vueComponent('vue-storeoptions', 'storeOptions', [
						'teamConversionId' => $teamConversionId,
						'springerConversationId' => $springerConversationId,
						'mayEditStore' => $this->storePermissions->mayEditStore($storeId),
						'userIsInStore' => $userIsInStore,
						'mayLeaveStoreTeam' => $this->storePermissions->mayLeaveStoreTeam($storeId, $this->session->id()),
						'storeId' => $storeId,
						'isJumper' => $store['jumper'],
						'fsId' => $this->session->id()
					]),
					CNT_LEFT
				);

				/* team list */
				$this->pageHelper->addContent(
					$this->view->vueComponent('vue-storeteam', 'store-team', [
						'fsId' => $this->session->id(),
						'mayEditStore' => $this->storePermissions->mayEditStore($storeId),
						'team' => $this->getDisplayedStoreTeam($store),
						'storeId' => $storeId,
						'storeTitle' => $store['name'],
						'regionId' => $store['bezirk_id'],
					]),
					CNT_LEFT
				);

				/* team status */
				if ($this->storePermissions->mayEditStore($storeId)) {
					$this->pageHelper->addContent(
						$this->view->vueComponent('vue-store-teamstatus', 'StoreTeamStatus', [
							'storeId' => $storeId,
						]),
						CNT_LEFT
					);
				}

				if ($store['verantwortlich']) {
					$this->pageHelper->addContent(
						$this->view->vueComponent('vue-store-applications', 'StoreApplications', [
							'storeId' => $storeId,
							'storeTitle' => $store['name'] ?? '',
							'storeRequests' => $store['requests'] ?? [],
							'requestCount' => count($store['requests'] ?? []),
						])
					);
				}

				if ($this->storePermissions->maySeePickupHistory($storeId)) {
					$this->pageHelper->addContent(
						$this->view->vueComponent('vue-pickup-history', 'PickupHistory', [
							'storeId' => $storeId,
							'coopStart' => $store['begin'],
						])
					);
				}

				if ($this->storePermissions->mayReadStoreWall($storeId)) {
					$this->pageHelper->addContent(
						$this->view->vueComponent('vue-storeview', 'Store', [
							'storeId' => $storeId,
							'storeManagers' => $this->storeGateway->getStoreManagers($storeId),
							'mayWritePost' => $this->storePermissions->mayWriteStoreWall($storeId),
							'mayDeleteEverything' => $this->storePermissions->mayDeleteStoreWall($storeId),
						])
					);
				} else {
					$this->pageHelper->addContent($this->v_utils->v_info($this->translator->trans('store.willgetcontacted')));
				}
				/* end of pinboard */

				/* fetchdates */
				if ($this->storePermissions->maySeePickups($storeId) && ($store['betrieb_status_id'] === CooperationStatus::COOPERATION_STARTING->value || $store['betrieb_status_id'] === CooperationStatus::COOPERATION_ESTABLISHED->value)) {
					$this->pageHelper->addContent(
						$this->view->vueComponent('vue-pickuplist', 'pickup-list', [
							'storeId' => $storeId,
							'storeTitle' => $store['name'],
							'isCoordinator' => $store['verantwortlich'],
							'teamConversationId' => $store['team_conversation_id'],
						]),
						CNT_RIGHT
					);
				}

				/* change regular fetchdates */
				if ($this->storePermissions->mayEditPickups($storeId)) {
					$width = $this->session->isMob() ? '$(window).width() * 0.96' : '$(window).width() / 2';
					$pickup_dates = $this->pickupGateway->getAbholzeiten($storeId);

					$this->pageHelper->hiddenDialog(
						'editpickups',
						[
							$this->view->u_editPickups($pickup_dates),
							$this->v_utils->v_form_hidden('bid', 0)
						],
						$this->translator->trans('pickup.edit.add'),
						true,
						$width
					);
				}

				if (!$store['jumper']) {
					if (!in_array($store['betrieb_status_id'], [
						CooperationStatus::COOPERATION_STARTING->value,
						CooperationStatus::COOPERATION_ESTABLISHED->value,
					])) {
						$icon = $this->v_utils->v_getStatusAmpel($store['betrieb_status_id']);
						$this->pageHelper->addContent($this->v_utils->v_field(
							'<p>' . $icon . $this->translator->trans('storestatus.' . $store['betrieb_status_id']) . '</p>',
							$this->translator->trans('storeview.status'),
							['class' => 'ui-padding']
						), CNT_RIGHT);
					}
				}
			} else {
				if ($store = $this->storeGateway->getBetrieb($storeId)) {
					$this->pageHelper->addBread($store['name']);
					$this->flashMessageHelper->info($this->translator->trans('store.not-in-team'));
					$this->routeHelper->go('/?page=map&bid=' . $storeId);
				} else {
					$this->routeHelper->go('/karte');
				}
			}
		} else {
			$this->pageHelper->addBread($this->translator->trans('menu.entry.your_stores'));

			if ($this->storePermissions->mayCreateStore()) {
				$this->pageHelper->addContent($this->v_utils->v_menu(
					[
						['href' => '/?page=betrieb&a=new', 'name' => $this->translator->trans('storeedit.add-new')]
					],
					$this->translator->trans('storeedit.actions')
				), CNT_RIGHT);
			}

			$region = $this->regionGateway->getRegion($this->session->getCurrentRegionId());
			$stores = $this->storeGateway->getMyStores($this->session->id(), $this->session->getCurrentRegionId());
			$this->pageHelper->addContent($this->view->u_storeList(
				$stores['verantwortlich'],
				$this->translator->trans('storelist.managing')
			));
			$this->pageHelper->addContent($this->view->u_storeList(
				$stores['team'],
				$this->translator->trans('storelist.fetching')
			));

			if (!is_null($region)) {
				$regionName = $region['name'];
				$this->pageHelper->addContent($this->view->u_storeList(
					$stores['sonstige'],
					$this->translator->trans('storelist.others', ['{region}' => $regionName])
				));
			}
		}
	}

	private function getDisplayedStoreTeam(array $store): array
	{
		$allowedFields = [
			// personal info
			'id', 'name', 'photo', 'quiz_rolle', 'sleep_status', 'verified', 'hygiene_level',
			// team-related info
			'verantwortlich', 'team_active', 'stat_fetchcount', 'add_date',
		];
		if ($this->storePermissions->maySeePhoneNumbers($store['id'])) {
			array_push($allowedFields, 'handy', 'telefon', 'last_fetch');
		}

		return array_map(
			function ($a) use ($allowedFields) {
				return array_filter($a, function ($key) use ($allowedFields) {
					return in_array($key, $allowedFields);
				}, ARRAY_FILTER_USE_KEY);
			},
			array_merge($store['foodsaver'], $store['springer']),
		);
	}

	/**
	 * Certain users will be able to manage a store even if not explicitly listed as manager:
	 * - all members of the 'store coordination' workgroup of the store's region
	 * - all (direct) ambassadors of the region attached to the store
	 * - members of the global orga team.
	 */
	private function isResponsibleForThisStoreAnyways($storeId): bool
	{
		if ($this->session->mayRole(Role::ORGA)) {
			$extraResponsibility = true;
			$extraMessageKey = 'storeedit.team.orga';
		} elseif ($this->storePermissions->mayEditStore($storeId)) {
			$extraResponsibility = true;
			$extraMessageKey = '';

			// this is duplicated from mayEditStore.
			// mayEditStore does not tell us why we can edit the store,
			// so to display the correct message, we have to do it all over again. Not ideal.
			$storeRegion = $this->storeGateway->getStoreRegionId($storeId);
			$storeGroup = $this->groupFunctionGateway->getRegionFunctionGroupId($storeRegion, WorkgroupFunction::STORES_COORDINATION);
			if (empty($storeGroup)) {
				if ($this->session->isAdminFor($storeRegion)) {
					$extraMessageKey = 'storeedit.team.amb';
				}
			} elseif ($this->session->isAdminFor($storeGroup)) {
				$extraMessageKey = 'storeedit.team.coordinator';
			}
		} else {
			$extraResponsibility = false;
			$extraMessageKey = '';
		}

		if ($extraResponsibility) {
			$store['verantwortlich'] = true;
			$this->flashMessageHelper->info(
				'<strong>' . $this->translator->trans('storeedit.team.note') . '</strong> '
					. $this->translator->trans($extraMessageKey)
			);
		}

		return $extraResponsibility;
	}
}
