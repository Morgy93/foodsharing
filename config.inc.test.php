<?php

/* Adding Whoops during testing can be very useful as the screenshots in the tests/_output folder can show a nice
	 error message. It also catches warnings and the whole site runs in a way that is always throwing warnings out.
	 But hopefully we fix all those at some point :)
*/
Foodsharing\Debug\Whoops::register();

$protocol = 'http';
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
	$protocol = 'https';
}

$host = 'lmr.local/';

define('PROTOCOL', $protocol);
define('DB_HOST', 'db');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_DB', 'foodsharing');
define('ERROR_REPORT', E_ALL);
define('BASE_URL', $protocol . '://' . $host);
define('WEBCAL_URL', 'webcal://' . $host);

define('INFLUX_DSN', 'udp+influxdb://influxdb:8089/foodsharing');

define('VERSION', '0.8.3');

define('DEFAULT_EMAIL','no-reply@foodsharing.network');
define('SUPPORT_EMAIL', 'it@foodsharing.network');
define('DEFAULT_EMAIL_NAME','Foodsharing');
define('EMAIL_PUBLIC', 'info@foodsharing.de');
define('EMAIL_PUBLIC_NAME','Foodsharing');
define('PLATFORM_MAILBOX_HOST', 'foodsharing.network');

define('MAILBOX_OWN_DOMAINS', ['foodsharing.network', 'lebensmittelretten.de', 'foodsharing.de']);

define('MAILER_HOST', 'smtp://maildev:1025');

define('MEM_ENABLED', true);

define('SOCK_URL', 'http://websocket:1338/');
define('REDIS_HOST', 'redis');
define('REDIS_PORT', 6379);

define('DELAY_MICRO_SECONDS_BETWEEN_MAILS', 1330000);

define('IMAP', []);


define('BOUNCE_IMAP_HOST', null);
define('BOUNCE_IMAP_USER', null);
define('BOUNCE_IMAP_PASS', null);
define('BOUNCE_IMAP_PORT', null);
define('BOUNCE_IMAP_SERVICE_OPTION', null);

if (!defined('ROOT_DIR')) {
	define('ROOT_DIR', './');
}

define('CSRF_TEST_TOKEN', '__TESTTOKEN__');

define('WEBPUSH_PUBLIC_KEY', 'BGBBW8RtRe4LpGT+6Q7BJGGSbgcULM/w9BrxBLva2AVf85Pj7t4xrViT3lsxn8Dp0fpJ1SPoDbwP1n6gt3/R7ps='); // test public key
define('WEBPUSH_PRIVATE_KEY', 'z5g0ssYryhDhQnwVAZ2Q2oOiqF3ZngJzkLXMrww8gDU='); // test private key

// Test key for firebase cloud messaging
define('FCM_KEY', '');
