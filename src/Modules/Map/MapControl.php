<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MapControl extends Control
{
    public function __construct(private readonly MapGateway $mapGateway, MapView $view)
    {
        $this->view = $view;

        parent::__construct();
    }

    public function index(Request $request, Response $response)
    {
        $this->pageHelper->addTitle($this->translator->trans('map.title'));

        if ($this->session->mayRole()) {
            $center = $this->mapGateway->getFoodsaverLocation($this->session->id());
        }
        $this->pageHelper->addContent($this->view->mapControl(), CNT_TOP);

        $jsarr = '';
        if (isset($_GET['load']) && $_GET['load'] == 'baskets') {
            $jsarr = '["baskets"]';
        } elseif (isset($_GET['load']) && $_GET['load'] == 'fairteiler') {
            $jsarr = '["fairteiler"]';
        }

        $this->pageHelper->addContent(
            $this->view->lMap()
        );

        if ($this->session->mayRole(Role::FOODSAVER) && isset($_GET['bid'])) {
            $storeId = intval($_GET['bid']);
            $center = $this->mapGateway->getStoreLocation($storeId);
            $this->pageHelper->addJs('ajreq(\'bubble\', { app: \'store\', id: ' . $storeId . ' });');
        }

        $this->pageHelper->addJs('u_init_map();');

        if (!empty($center)) {
            if ($center['lat'] == 0 && $center['lon'] == 0) {
                $this->pageHelper->addJs('u_map.fitBounds([[46.0, 4.0],[55.0, 17.0]]);');
            } else {
                $this->pageHelper->addJs('u_map.setView([' . $center['lat'] . ',' . $center['lon'] . '],15);');
            }
        }

        $this->pageHelper->addJs('map.initMarker(' . $jsarr . ');');

        $response->setContent($this->render('layouts/map.twig'));
    }
}
