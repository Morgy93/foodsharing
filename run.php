<?php

use Foodsharing\Kernel;

require __DIR__ . '/vendor/autoload.php';

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

require_once 'config.inc.php';

$env = $_SERVER['FS_ENV'] ?? getenv('FS_ENV') ?? 'dev';
$debug = (bool)($_SERVER['APP_DEBUG'] ?? ('prod' !== $env));
$kernel = new Kernel($env, $debug);
$kernel->boot();

global $container;
$container = $kernel->getContainer();

$app = 'Console';
$method = 'index';

if (isset($argv[3]) && $argv[3] == 'quiet') {
    define('QUIET', true);
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
