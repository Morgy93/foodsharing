<?php

use Foodsharing\Debug\DebugBar;
use Foodsharing\Lib\ContentSecurityPolicy;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Routing;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\DBConstants\Content\ContentId;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require __DIR__ . '/includes/setup.php';
require_once 'config.inc.php';

/* @var Request $request */
$request = Request::createFromGlobals();
$response = new Response('--');

/* @var Container $container */
global $container;
$container = initializeContainer();

/* @var ContentSecurityPolicy $csp */
$csp = $container->get(ContentSecurityPolicy::class);

// Security headers :)

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

header($csp->generate($request->getSchemeAndHttpHost(), CSP_REPORT_URI, CSP_REPORT_ONLY));

require_once 'lib/inc.php';

/* @var Mem $mem */
$mem = $container->get(Mem::class);

/* @var Utils $view_utils */
$view_utils = $container->get(Utils::class);

/* @var RouteHelper $routeHelper */
$routeHelper = $container->get(RouteHelper::class);

/* @var PageHelper $pageHelper */
$pageHelper = $container->get(PageHelper::class);

/* @var Session $session */
$session = $container->get(Session::class);

/* @var ContentGateway $contentGateway */
$contentGateway = $container->get(ContentGateway::class);
$g_broadcast_message = $contentGateway->get(ContentId::BROADCAST_MESSAGE)['body'];

/* @var DebugBar $debug */
$debug = $container->get(DebugBar::class);

if ($debug->isEnabled()) {
	$pageHelper->addHead($debug->renderHead());
}

if ($session->may()) {
	$uc = $request->query->get('uc');
	if ($uc !== null) {
		if ($session->id() != $uc) {
			$mem->logout($session->id());
			$routeHelper->goLogin();
		}
	}
}

$app = $routeHelper->getPage();

$controller = $routeHelper->getLegalControlIfNecessary() ?? Routing::getClassName($app, 'Control');
try {
	$obj = $container->get(ltrim($controller, '\\'));
} catch (ServiceNotFoundException $e) {
}

if (isset($obj)) {
	$action = $request->query->get('a');
	if ($action !== null && is_callable([$obj, $action])) {
		$obj->$action($request, $response);
	} else {
		$obj->index($request, $response);
	}
	$sub = $sub = $obj->getSubFunc();
	if ($sub !== false && is_callable([$obj, $sub])) {
		$obj->$sub($request, $response);
	}
} else {
	$response->setStatusCode(Response::HTTP_NOT_FOUND);
	$response->setContent('');
}

$page = $response->getContent();
$controllerUsedResponse = $page !== '--';
if ($controllerUsedResponse) {
	if ($debug->isEnabled()) {
		$response->setContent(str_replace(
			'</body>',
			$debug->renderContent() . '</body>',
			$response->getContent()
		));
	}
	$response->send();
} else {
	if ($debug->isEnabled()) {
		$pageHelper->addContent($debug->renderContent(), CNT_BOTTOM);
	}
	/* @var \Twig\Environment $twig */
	$twig = $container->get('twig');
	$page = $twig->render('layouts/' . $g_template . '.twig', $pageHelper->generateAndGetGlobalViewData());
}

if (isset($cache) && $cache->shouldCache()) {
	$cache->cache($page);
}

if (!$controllerUsedResponse) {
	$response->setContent($page);
	$response->send();
}