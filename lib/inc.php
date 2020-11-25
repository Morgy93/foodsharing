<?php

use Foodsharing\Lib\Cache\Caching;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\PageHelper;
use Symfony\Component\DependencyInjection\Container;

/* @var Container $container */
global $container;

/* @var Session $session */
$session = $container->get(Session::class);
$session->initIfCookieExists();

/* @var Mem $mem */
$mem = $container->get(Mem::class);

/* @var \Foodsharing\Modules\Core\InfluxMetrics $influxdb */
$influxdb = $container->get(\Foodsharing\Modules\Core\InfluxMetrics::class);

if (isset($g_page_cache) && strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
	$cache = new Caching($g_page_cache, $session, $mem, $influxdb);
	$cache->lookup();
}

$translator = $container->get('translator');
$translator->setLocale($session->getLocale());

error_reporting(E_ALL);

if (isset($_GET['logout'])) {
	$_SESSION['client'] = [];
	unset($_SESSION['client']);
}

/* @var DataHelper $dataHelper */
$dataHelper = $container->get(DataHelper::class);

/* @var PageHelper $pageHelper */
$pageHelper = $container->get(PageHelper::class);

/* @var IdentificationHelper $identificationHelper */
$identificationHelper = $container->get(IdentificationHelper::class);

/* @var Utils $viewUtils */
$viewUtils = $container->get(Utils::class);

$g_template = 'default';
$g_data = $dataHelper->getPostData();

$pageHelper->addHidden('<div id="u-profile"></div>');
$pageHelper->addHidden('<ul id="hidden-info"></ul>');
$pageHelper->addHidden('<ul id="hidden-error"></ul>');
$pageHelper->addHidden('<div id="dialog-confirm" title="Wirklich l&ouml;schen?"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><span id="dialog-confirm-msg"></span><input type="hidden" value="" id="dialog-confirm-url" /></p></div>');
$pageHelper->addHidden('<div id="uploadPhoto"><form method="post" enctype="multipart/form-data" target="upload" action="/xhr.php?f=addPhoto"><input type="file" name="photo" onchange="uploadPhoto();" /></form><div id="uploadPhoto-preview"></div><iframe name="upload" width="1" height="1" src=""></iframe></div>');
