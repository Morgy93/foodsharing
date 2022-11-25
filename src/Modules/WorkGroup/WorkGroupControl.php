<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Permissions\WorkGroupPermissions;
use Foodsharing\Utility\ImageHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkGroupControl extends Control
{
	private WorkGroupGateway $workGroupGateway;
	private WorkGroupPermissions $workGroupPermissions;
	private ImageHelper $imageService;

	public function __construct(
		WorkGroupView $view,
		WorkGroupGateway $workGroupGateway,
		WorkGroupPermissions $workGroupPermissions,
		ImageHelper $imageService
	) {
		$this->view = $view;
		$this->workGroupGateway = $workGroupGateway;
		$this->workGroupPermissions = $workGroupPermissions;
		$this->imageService = $imageService;

		parent::__construct();
	}

	public function index(Request $request, Response $response): void
	{
		if (!$this->session->mayRole()) {
			$this->routeHelper->goLogin();
		}

		$this->pageHelper->addBread($this->translator->trans('terminology.groups'), '/?page=groups');

		if (!$request->query->has('sub')) {
			$this->list($request, $response);
		} elseif ($request->query->get('sub') == 'edit') {
			$this->edit($request, $response);
		}
	}

	private function getSideMenuData(?string $activeUrlPartial = null): array
	{
		$countries = $this->workGroupGateway->getCountryGroups();
		$bezirke = $this->session->getRegions();

		$localRegions = array_filter($bezirke, function ($region) {
			return !in_array($region['type'], [UnitType::COUNTRY, UnitType::WORKING_GROUP]);
		});

		$regionToMenuItem = function ($region) {
			return [
				'name' => $region['name'],
				'href' => '/?page=groups&p=' . $region['id']
			];
		};

		$menuGlobal = [['name' => $this->translator->trans('group.show-all'), 'href' => '/?page=groups']];
		$menuLocalRegions = array_map($regionToMenuItem, $localRegions);
		$menuCountries = array_map($regionToMenuItem, $countries);

		$myRegions = $_SESSION['client']['bezirke'] ?? [];
		$myGroups = array_filter($myRegions, function ($group) {
			return UnitType::isGroup($group['type']);
		});
		$menuMyGroups = array_map(
			function ($group) {
				return [
					'name' => $group['name'],
					'href' => '/?page=bezirk&bid=' . $group['id'] . '&sub=forum'
				];
			}, $myGroups
		);

		return [
			'global' => $menuGlobal,
			'local' => $menuLocalRegions,
			'countries' => $menuCountries,
			'groups' => $menuMyGroups,
			'active' => $activeUrlPartial,
		];
	}

	private function list(Request $request, Response $response): void
	{
		$this->pageHelper->addTitle($this->translator->trans('terminology.groups'));

		$sessionId = $this->session->id();
		$parent = $request->query->getInt('p', RegionIDs::GLOBAL_WORKING_GROUPS);
		$myApplications = $this->workGroupGateway->getApplications($sessionId);
		$myStats = $this->workGroupGateway->getStats($sessionId);
		$groups = $this->getGroups($parent, $myApplications, $myStats);

		foreach ($groups as &$group) {
			$group['function_tooltip_key'] = $this->getTooltipKey($group);
		}

		$list = $this->render('pages/WorkGroup/list.twig', [
			'nav' => $this->getSideMenuData('=' . $parent),
			'groups' => $groups,
		]);

		$response->setContent($list);
	}

	/**
	 * Returns the translation key of the tooltip text that is shown for working groups with special
	 * functions. Returns null if the group does not have any function.
	 */
	private function getTooltipKey(array $group): ?string
	{
		// working group function that can be present in any region
		// TODO: remove the exception when the FS-management group is implemented
		if (!empty($group['function']) && $group['function'] !== WorkgroupFunction::FSMANAGEMENT) {
			return 'group.function.tooltip_function_' . $group['function'];
		}

		// special permissions for unique super-regional groups
		if (RegionIDs::hasSpecialPermission($group['id'])) {
			return 'group.unique_function.tooltip_function_region' . $group['id'];
		}

		return null;
	}

	private function getGroups(int $parent, array $applications, array $stats): array
	{
		$insertLeaderImage = function (array $leader): array {
			return array_merge($leader, ['image' => $this->imageService->img($leader['photo'])]);
		};
		$enrichGroupData = function (array $group) use ($insertLeaderImage, $applications, $stats): array {
			$leaders = array_map($insertLeaderImage, $group['leaders']);
			$satisfied = $this->workGroupPermissions->fulfillApplicationRequirements($group, $stats);

			return array_merge($group, [
				'leaders' => $leaders,
				'image' => $this->fixPhotoPath($group['photo']),
				'appliedFor' => in_array($group['id'], $applications),
				'applyMinBananaCount' => $group['banana_count'],
				'applyMinFetchCount' => $group['fetch_count'],
				'applyMinFoodsaverWeeks' => $group['week_num'],
				'applicationRequirementsNotFulfilled' => !$satisfied,
				'mayEdit' => $this->workGroupPermissions->mayEdit($group),
				'mayAccess' => $this->workGroupPermissions->mayAccess($group),
				'mayApply' => $this->workGroupPermissions->mayApply($group, $applications, $stats),
				'mayJoin' => $this->workGroupPermissions->mayJoin($group),
			]);
		};

		return array_map($enrichGroupData, $this->workGroupGateway->listGroups($parent));
	}

	private function edit(Request $request, Response $response): void
	{
		$groupId = $request->query->getInt('id');
		$group = $this->workGroupGateway->getGroup($groupId);
		if (!$group) {
			$this->routeHelper->go('/?page=groups');
		} elseif ($group['type'] != UnitType::WORKING_GROUP || !$this->workGroupPermissions->mayEdit($group)) {
			$this->routeHelper->go('/?page=dashboard');
		}

		$bread = $this->translator->trans('group.edit.title', ['{group}' => $group['name']]);
		$this->pageHelper->addBread($bread, '/?page=groups&sub=edit&id=' . (int)$group['id']);

		$group['photo'] = $this->fixPhotoPath($group['photo']);

		$response->setContent($this->render('pages/WorkGroup/edit.twig',
			['nav' => $this->getSideMenuData(), 'group' => $group]
		));
	}

	/**
	 * Old photos that were uploaded by Xhr are named "workgroup/[uuid].jpg" and are in the /images/workgroup
	 * directory. New ones that were uploaded with the REST API already contain the full path when stored in the
	 * database. This function returns a valid path for all photos.
	 *
	 * @param string $photo the group's photo file from the database
	 *
	 * @return string the valid path that can be used in the frontend
	 */
	private function fixPhotoPath(string $photo): string
	{
		return (!empty($photo) && str_starts_with($photo, 'workgroup'))
			? '/images/' . $photo
			: $photo;
	}
}
