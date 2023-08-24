<?php

namespace Foodsharing\Modules\StoreUser;

use Foodsharing\Lib\FoodsharingController;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Permissions\StorePermissions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StoreUserController extends FoodsharingController
{
    #[Route(path: '/store/{storeId}', name: 'store.show', requirements: ['storeId' => '\d+'], methods: ['GET'])]
    public function index(
        int $storeId,
        StoreGateway $storeGateway,
        StorePermissions $storePermissions,
    ): Response {
        if (!$this->session->mayRole()) {
            $this->routeHelper->goLoginAndExit();
        }

        if (!$storeGateway->storeExists($storeId)) {
            $this->routeHelper->goAndExit('/?page=dashboard');
        }

        if (!$storePermissions->mayAccessStore($storeId)) {
            $this->flashMessageHelper->info($this->translator->trans('store.not-in-team'));
            $this->routeHelper->goAndExit('/?page=map&bid=' . $storeId);
        }

        $params['storeId'] = $storeId;
        $params['storeManagers'] = $storeGateway->getStoreManagers($storeId);

        $this->pageHelper->addTitle($storeGateway->getStoreName($storeId));
        $vue = $this->prepareVueComponent('vue-store-page', 'StorePage', $params);
        $this->pageHelper->addContent($vue);

        return $this->renderGlobal();
    }
}
