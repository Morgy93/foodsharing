<?php

namespace Foodsharing\Modules\Basket;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\BasketRequests\Status;

class BasketControl extends Control
{
	private $basketGateway;

	public function __construct(BasketView $view, BasketGateway $basketGateway)
	{
		$this->view = $view;
		$this->basketGateway = $basketGateway;

		parent::__construct();

		$this->pageHelper->addBread('Essenskörbe');
	}

	public function index(): void
	{
		if ($id = $this->uriInt(2)) {
			if ($basket = $this->basketGateway->getBasket($id)) {
				$this->basket($basket);
			}
		} else {
			if ($m = $this->uriStr(2)) {
				if (method_exists($this, $m)) {
					$this->$m();
				} else {
					$this->routeHelper->go('/essenskoerbe/find');
				}
			} else {
				$this->routeHelper->go('/essenskoerbe/find');
			}
		}
	}

	public function find(): void
	{
		$baskets = $this->basketGateway->listCloseBaskets($this->session->id(), $this->session->getLocation());
		$this->view->find($baskets, $this->session->getLocation());
	}

	private function basket($basket): void
	{
		$wallPosts = false;
		$requests = false;

		if ($this->session->may()) {
			if ($basket['fs_id'] == $this->session->id()) {
				$requests = $this->basketGateway->listRequests($basket['id'], $this->session->id());
				$wallPosts = $this->wallposts('basket', $basket['id'], true);
			} else {
				$wallPosts = $this->wallposts('basket', $basket['id'], false);
			}
		}
		if ($basket['until_ts'] >= time() && $basket['status'] == Status::REQUESTED_MESSAGE_READ) {
			$this->view->basket($basket, $wallPosts, $requests);
		} elseif ($basket['until_ts'] <= time() || $basket['status'] == Status::DENIED) {
			$this->view->basketTaken($basket);
		}
	}
}
