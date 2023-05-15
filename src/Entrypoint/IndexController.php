<?php

namespace Foodsharing\Entrypoint;

use Foodsharing\Annotation\DisableCsrfProtection;
use Foodsharing\Lib\Routing;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends AbstractController
{
    /**
     * @DisableCsrfProtection CSRF Protection (originally done for the REST API)
     * breaks POST on these entrypoints right now,
     * so this annotation disables it.
     */
    public function __invoke(
        Request $request,
        RouteHelper $routeHelper,
        PageHelper $pageHelper
    ): Response {
        $response = new Response('--');

        $app = $routeHelper->getPage();

        $controllerName = $routeHelper->getLegalControlIfNecessary() ?? Routing::getClassName($app, 'Control');

        try {
            global $container;
            if ($controllerName !== null) {
                /** @var Control $controller */
                $controller = $container->get(ltrim($controllerName, '\\'));
                $controller->setRequest($request);
            }
        } catch (ServiceNotFoundException) {
            throw $this->createNotFoundException();
        }

        if (isset($controller)) {
            $action = $request->query->get('a');
            if ($action !== null && is_callable([$controller, $action])) {
                $controller->$action($request, $response);
            } else {
                // In practice, every class inheriting from Control has an index method,
                // but it does not exist in Control itself, which is why PHPStan complains here.
                /* @phpstan-ignore-next-line */
                $controller->index($request, $response);
            }
            $sub = $controller->getSub();
            if ($sub !== false && is_callable([$controller, $sub])) {
                $controller->$sub($request, $response);
            }
        } else {
            throw $this->createNotFoundException();
        }

        $controllerUsedResponse = $response->getContent() !== '--';
        if (!$controllerUsedResponse) {
            $page = $this->renderView('layouts/default.twig', $pageHelper->generateAndGetGlobalViewData());

            $response->setContent($page);
        }

        return $response;
    }
}
