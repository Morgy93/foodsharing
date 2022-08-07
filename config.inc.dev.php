<?php

if (php_sapi_name() != 'cli') {
	/* Whoops catches all error messages in CLI mode as well :( */
	Foodsharing\Debug\Whoops::register();
}

$protocol = 'http';
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
	$protocol = 'https';
}

$host = 'localhost:18080';

define('PROTOCOL', $protocol);
define('DB_HOST', 'db');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_DB', 'foodsharing');
define('ERROR_REPORT', E_ALL);
define('BASE_URL', $protocol . '://' . $host);
define('WEBCAL_URL', 'webcal://' . $host);

define('INFLUX_DSN', 'udp+influxdb://influxdb:8089/foodsharing');

define('DEFAULT_EMAIL', 'noreply@foodsharing.de');
define('SUPPORT_EMAIL', 'it@foodsharing.network');
define('DEFAULT_EMAIL_NAME', 'foodsharing-Freiwillige');
define('VERSION', '0.8.3');
define('EMAIL_PUBLIC', 'info@foodsharing.de');
define('EMAIL_PUBLIC_NAME', 'foodsharing-Freiwillige');
define('NOREPLY_EMAIL_HOST', 'foodsharing.de');
define('PLATFORM_MAILBOX_HOST', 'foodsharing.network');

define('MAILBOX_OWN_DOMAINS', ['foodsharing.network', 'lebensmittelretten.de', 'foodsharing.de']);

define('MAILER_HOST', 'smtp://maildev:25');

define('MEM_ENABLED', true);

define('SOCK_URL', 'http://chat:1338/');
define('REDIS_HOST', 'redis');
define('REDIS_PORT', 6379);

define('IMAP', [
	['host' => 'imap', 'user' => 'user', 'password' => 'pass']
]);

define('BOUNCE_IMAP_HOST', null);
define('BOUNCE_IMAP_USER', null);
define('BOUNCE_IMAP_PASS', null);
define('BOUNCE_IMAP_PORT', null);
define('BOUNCE_IMAP_SERVICE_OPTION', null);

if (!defined('ROOT_DIR')) {
	define('ROOT_DIR', './');
}

define('WEBPUSH_PUBLIC_KEY', 'BGBBW8RtRe4LpGT+6Q7BJGGSbgcULM/w9BrxBLva2AVf85Pj7t4xrViT3lsxn8Dp0fpJ1SPoDbwP1n6gt3/R7ps='); // test public key
define('WEBPUSH_PRIVATE_KEY', 'z5g0ssYryhDhQnwVAZ2Q2oOiqF3ZngJzkLXMrww8gDU='); // test private key

// Test key for firebase cloud messaging
define('FCM_KEY', '');
