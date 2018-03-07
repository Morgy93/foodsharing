<?php

if (php_sapi_name() != 'cli') {
	/* Whoops catches all error messages in CLI mode as well :( */
	Foodsharing\Debug\Whoops::register();
}

$protocol = 'http';
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
	$protocol = 'https';
}

define('PROTOCOL', $protocol);
define('DB_HOST', 'db');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_DB', 'foodsharing');
define('PREFIX', 'fs_');
define('ERROR_REPORT', E_ALL);
define('BASE_URL', $protocol . '://localhost:18080');
define('URL_INTERN', $protocol . '://localhost:18080');

define('DEFAULT_EMAIL', 'noreply@lebensmittelretten.de');
define('DEFAULT_EMAIL_NAME', 'Foodsharing Freiwillige');
define('VERSION', '0.8.2');
define('EMAIL_PUBLIC', 'info@lebensmittelretten.de');
define('EMAIL_PUBLIC_NAME', 'Foodsharing Freiwillige');
define('DEFAULT_HOST', 'lebensmittelretten.de');
define('MAPZEN_API_KEY', 'mapzen-RaXru7A');
define('GOOGLE_API_KEY', 'AIzaSyCkFfCoOnj8ZjGGcApHS1rX6Rt6OxrW6hQ');

define('MAILBOX_OWN_DOMAINS', array('lebensmittelretten.de'));

define('SMTP_HOST', 'maildev');
define('SMTP_PORT', 25);

define('MEM_ENABLED', true);

define('SOCK_URL', 'http://chat:1338/');
define('REDIS_HOST', 'redis');
define('REDIS_PORT', 6379);

if (!defined('ROOT_DIR')) {
	define('ROOT_DIR', './');
}
