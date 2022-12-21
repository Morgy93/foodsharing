<?php

namespace Foodsharing\Modules\Basket;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Basket\Status;
use Foodsharing\Modules\Core\DBConstants\Map\MapConstants;

class BasketControl extends Control
{
    private BasketGateway $basketGateway;

    public function __construct(BasketView $view, BasketGateway $basketGateway)
    {
        $this->view = $view;
        $this->basketGateway = $basketGateway;

        parent::__construct();

        $this->pageHelper->addBread($this->translator->trans('terminology.baskets'));
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
        $loc = $this->session->getLocation();
        if (!$loc || $loc['lat'] === 0 && $loc['lon'] === 0) {
            $loc = ['lat' => MapConstants::CENTER_GERMANY_LAT, 'lon' => MapConstants::CENTER_GERMANY_LON];
        }
        $baskets = $this->basketGateway->listNearbyBasketsByDistance($this->session->id(), $loc);
        $this->view->find($baskets, $loc);
    }

    private function basket(array $basket): void
    {
        $requests = false;

        if ($this->session->mayRole()) {
            if ($basket['fs_id'] == $this->session->id()) {
                $requests = $this->basketGateway->listRequests($basket['id'], $this->session->id());
            } else {
                $requests = $this->basketGateway->getRequest($basket['id'], $this->session->id(), $basket['foodsaver_id']);
            }
        }
        if ($basket['status'] === Status::REQUESTED_MESSAGE_READ && $basket['until_ts'] >= time()) {
            $this->view->basket($basket, $requests);
        } elseif ($basket['status'] === Status::DELETED_OTHER_REASON || $basket['status'] === Status::DENIED || $basket['until_ts'] <= time()) {
            $this->view->basketTaken($basket);
        }
    }
}
