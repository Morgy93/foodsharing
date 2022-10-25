<?php

use Symfony\Component\DependencyInjection\Container;

require __DIR__ . '/includes/setup.php';
require_once 'config.inc.php';

/* @var Container $container */
global $container;
$container = initializeContainer();

/*
 * force only executing on commandline
*/
if (!isset($argv)) {
	header('Location: ' . BASE_URL);
	exit;
}

$app = 'Console';
$method = 'index';

if (isset($argv[3]) && $argv[3] == 'quiet') {
	define('QUIET', true);
} else {
}

if (isset($argv) && is_array($argv)) {
	if (count($argv) > 1) {
		$app = $argv[1];
	}
	if (count($argv) > 2) {
		$method = $argv[2];
	}
}

$app = '\\Foodsharing\\Modules\\' . $app . '\\' . $app . 'Control';
echo "Starting $app::$method...\n";

$appInstance = $container->get(ltrim($app, '\\'));

if (is_callable([$appInstance, $method])) {
	$appInstance->$method();
} else {
	echo 'Modul ' . $app . ' konnte nicht geladen werden';
}
