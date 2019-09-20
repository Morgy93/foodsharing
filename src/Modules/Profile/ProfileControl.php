<?php

namespace Foodsharing\Modules\Profile;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Basket\BasketGateway;

final class ProfileControl extends Control
{
	private $foodsaver;
	private $regionGateway;
	private $profileGateway;
	private $basketGateway;

	public function __construct(
		ProfileView $view,
		RegionGateway $regionGateway,
		ProfileGateway $profileGateway,
		BasketGateway $basketGateway
	) {
		$this->view = $view;
		$this->profileGateway = $profileGateway;
		$this->regionGateway = $regionGateway;
		$this->basketGateway = $basketGateway;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->go('/');
		}

		if ($id = $this->uriInt(2)) {
			$this->profileGateway->setFsId((int)$id);
			if ($data = $this->profileGateway->getData($this->session->id())) {
				if ($data['deleted_at'] === null || $this->session->may('orga')) {
					$this->foodsaver = $data;
					$this->foodsaver['buddy'] = $this->profileGateway->buddyStatus($this->foodsaver['id']);
					$this->foodsaver['basketCount'] = $this->basketGateway->getAmountOfFoodBaskets($this->foodsaver['id']);

					$this->view->setData($this->foodsaver);

					if ($this->uriStr(3) === 'notes') {
						$this->organisationTeamNotes();
					} else {
						$this->profile();
					}
				} else {
					$this->routeHelper->goPage('dashboard');
				}
			} else {
				$this->routeHelper->goPage('dashboard');
			}
		} else {
			$this->routeHelper->goPage('dashboard');
		}
	}

	// this is required even if empty.
	public function index(): void
	{
	}

	private function organisationTeamNotes(): void
	{
		$this->pageHelper->addBread($this->foodsaver['name'], '/profile/' . $this->foodsaver['id']);
		if ($this->session->may('orga')) {
			$this->view->userNotes(
				$this->wallposts('usernotes', $this->foodsaver['id']),
				true,
				true,
				true,
				$this->profileGateway->getCompanies($this->foodsaver['id']),
				$this->profileGateway->getCompaniesCount($this->foodsaver['id'])
			);
		} else {
			$this->routeHelper->go('/profile/' . $this->foodsaver['id']);
		}
	}

	public function profile(): void
	{
		$regionIDs = $this->regionGateway->getFsRegionIds($this->foodsaver['id']);
		if ($this->session->isAmbassadorForRegion($regionIDs, false, true) || $this->session->isOrgaTeam()) {
			$this->view->profile(
				$this->wallposts('foodsaver', $this->foodsaver['id']),
				true,
				true,
				true,
				true,
				$this->profileGateway->getCompanies($this->foodsaver['id']),
				$this->profileGateway->getCompaniesCount($this->foodsaver['id']),
				$this->profileGateway->getNextDates($this->foodsaver['id'], 50)
			);
		} else {
			$this->view->profile(
				$this->wallposts('foodsaver', $this->foodsaver['id'])
			);
		}
	}
}
