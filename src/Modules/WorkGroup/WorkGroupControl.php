<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\ApplyType;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Utility\ImageHelper;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkGroupControl extends Control
{
	/**
	 * @var FormFactoryInterface
	 */
	private $formFactory;
	private $imageService;
	private $workGroupGateway;

	public function __construct(
		WorkGroupView $view,
		ImageHelper $imageService,
		WorkGroupGateway $workGroupGateway
	) {
		$this->view = $view;
		$this->imageService = $imageService;
		$this->workGroupGateway = $workGroupGateway;

		parent::__construct();
	}

	/**
	 * @required
	 */
	public function setFormFactory(FormFactoryInterface $formFactory)
	{
		$this->formFactory = $formFactory;
	}

	public function index(Request $request, Response $response)
	{
		if (!$this->session->may()) {
			$this->routeHelper->goLogin();
		}

		$this->pageHelper->addBread('Arbeitsgruppen', '/?page=groups');

		if (!$request->query->has('sub')) {
			$this->list($request, $response);
		} elseif ($request->query->get('sub') == 'edit') {
			$this->edit($request, $response);
		}
	}

	private function fulfillApplicationRequirements($group, $stats)
	{
		return
			$stats['bananacount'] >= $group['banana_count']
			&& $stats['fetchcount'] >= $group['fetch_count']
			&& $stats['weeks'] >= $group['week_num'];
	}

	private function mayEdit(array $group): bool
	{
		// this actually only implements access for bots for _direct parents_, not all hierarchical parents
		return $this->session->isOrgaTeam() || $this->session->isAdminFor($group['id']) || $this->session->isAdminFor($group['parent_id']);
	}

	private function mayAccess(array $group): bool
	{
		return $this->session->mayBezirk($group['id']) || $this->session->isAdminFor($group['parent_id']);
	}

	private function mayApply(array $group, $applications, $stats): bool
	{
		if ($this->session->mayBezirk($group['id'])) {
			return false; // may not apply if already a member
		}
		if (in_array($group['id'], $applications)) {
			return false; // may not apply if already applied
		}
		if ($group['apply_type'] == ApplyType::EVERYBODY) {
			return true;
		}
		if ($group['apply_type'] == ApplyType::REQUIRES_PROPERTIES) {
			return $this->fulfillApplicationRequirements($group, $stats);
		}

		return false;
	}

	private function mayJoin(array $group): bool
	{
		if ($this->session->mayBezirk($group['id'])) {
			return false; // may not join if already a member
		}

		return $group['apply_type'] == ApplyType::OPEN;
	}

	private function getSideMenuData($activeUrlPartial = null)
	{
		$countries = $this->workGroupGateway->getCountryGroups();
		$bezirke = $this->session->getRegions();

		$localRegions = array_filter($bezirke, function ($region) {
			return !in_array($region['type'], [Type::COUNTRY, Type::WORKING_GROUP]);
		});

		$regionToMenuItem = function ($region) {
			return [
				'name' => $region['name'],
				'href' => '/?page=groups&p=' . $region['id']
			];
		};

		$menuGlobal = [['name' => 'Alle anzeigen', 'href' => '/?page=groups']];
		$menuLocalRegions = array_map($regionToMenuItem, $localRegions);
		$menuCountries = array_map($regionToMenuItem, $countries);

		$myGroups = array_filter(isset($_SESSION['client']['bezirke']) ? $_SESSION['client']['bezirke'] : [], function ($group) {
			return $group['type'] == Type::WORKING_GROUP;
		});
		$menuMyGroups = array_map(
			function ($group) {
				return [
					'name' => $group['name'],
					'href' => '/?page=bezirk&bid=' . $group['id'] . '&sub=forum'
				];
			}, $myGroups
		);

		return ['global' => $menuGlobal,
			'local' => $menuLocalRegions,
			'countries' => $menuCountries,
			'groups' => $menuMyGroups,
			'active' => $activeUrlPartial];
	}

	private function list(Request $request, Response $response)
	{
		$this->pageHelper->addTitle($this->translationHelper->s('groups'));

		$parent = $request->query->getInt('p', RegionIDs::GLOBAL_WORKING_GROUPS);
		$myApplications = $this->workGroupGateway->getApplications($this->session->id());
		$myStats = $this->workGroupGateway->getStats($this->session->id());
		$groups = $this->getGroups($parent, $myApplications, $myStats);

		$response->setContent(
			$this->render(
				'pages/WorkGroup/list.twig',
				['nav' => $this->getSideMenuData('=' . $parent), 'groups' => $groups]
			)
		);
	}

	private function getGroups(int $parent, array $applications, array $stats): array
	{
		return array_map(
			function ($group) use ($applications, $stats) {
				return array_merge(
					$group,
					[
						'leaders' => array_map(
							function ($leader) {
								return array_merge($leader, ['image' => $this->imageService->img($leader['photo'])]);
							},
							$group['leaders']
						),
						'image' => $group['photo'] ? 'images/' . $group['photo'] : null,
						'appliedFor' => in_array($group['id'], $applications),
						'applyMinBananaCount' => $group['banana_count'],
						'applyMinFetchCount' => $group['fetch_count'],
						'applyMinFoodsaverWeeks' => $group['week_num'],
						'applicationRequirementsNotFulfilled' => ($group['apply_type'] == ApplyType::REQUIRES_PROPERTIES)
																	&& !$this->fulfillApplicationRequirements($group, $stats),
						'mayEdit' => $this->mayEdit($group),
						'mayAccess' => $this->mayAccess($group),
						'mayApply' => $this->mayApply($group, $applications, $stats),
						'mayJoin' => $this->mayJoin($group),
					]
				);
			},
			$this->workGroupGateway->listGroups($parent)
		);
	}

	private function edit(Request $request, Response $response)
	{
		$groupId = $request->query->getInt('id');
		$group = $this->workGroupGateway->getGroup($groupId);
		if (!$group) {
			$this->routeHelper->go('/?page=groups');
		} elseif ($group['type'] != Type::WORKING_GROUP || !$this->mayEdit($group)) {
			$this->routeHelper->go('/?page=dashboard');
		}

		$this->pageHelper->addBread($group['name'] . ' bearbeiten', '/?page=groups&sub=edit&id=' . (int)$group['id']);
		$editWorkGroupRequest = EditWorkGroupData::fromGroup($group);
		$form = $this->formFactory->create(WorkGroupForm::class, $editWorkGroupRequest);
		$form->handleRequest($request);
		if ($form->isSubmitted()) {
			if ($form->isValid()) {
				$data = $editWorkGroupRequest->toGroup();
				$this->workGroupGateway->updateGroup($group['id'], $data);
				$this->workGroupGateway->updateTeam($group['id'], $data['member'], $data['leader']);
				$this->flashMessageHelper->info('Änderungen gespeichert!');
				$this->routeHelper->goSelf();
			}
		}
		$response->setContent($this->render('pages/WorkGroup/edit.twig',
			['nav' => $this->getSideMenuData(), 'group' => $group, 'form' => $form->createView()]
		));
	}
}
