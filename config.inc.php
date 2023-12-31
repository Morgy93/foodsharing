<?php

/* If you make changes here check if changes at the production server are needed */

$FS_ENV = getenv('FS_ENV');
$env_filename = __DIR__ . '/config.inc.' . $FS_ENV . '.php';
$private_env_filename = __DIR__ . '/.config.inc.' . $FS_ENV . '.php';
if (defined('FS_ENV')) {
    if (FS_ENV !== $FS_ENV) {
        exit('different values of FS_ENV const (' . FS_ENV . ') and ENV var (' . $FS_ENV . ')');
    }
} else {
    define('FS_ENV', $FS_ENV);
}

if (file_exists($env_filename)) {
    require_once $env_filename;
} else {
    exit('no config found for env [' . $FS_ENV . ']');
}
if (file_exists($private_env_filename)) {
    require_once $private_env_filename;
}
if (!defined('SOCK_URL')) {
    define('SOCK_URL', 'http://127.0.0.1:1338/');
}

date_default_timezone_set('Europe/Berlin');
locale_set_default('de-DE');
/*
 * Read revision from revision file.
 * It is supposed to define SRC_REVISION.
 */
$revision_filename = __DIR__ . '/revision.inc.php';
if (file_exists($revision_filename)) {
    require_once $revision_filename;
}

if (!defined('FCM_KEY')) {
    define('FCM_KEY', '');
}

if (!defined('RAVEN_JAVASCRIPT_CONFIG') && getenv('RAVEN_JAVASCRIPT_CONFIG')) {
    define('RAVEN_JAVASCRIPT_CONFIG', getenv('RAVEN_JAVASCRIPT_CONFIG'));
}

if (!defined('SENTRY_TRACING_SAMPLE_RATE')) {
    define('SENTRY_TRACING_SAMPLE_RATE', 0); // disables tracing
}

if (!defined('CSP_REPORT_URI')) {
    define('CSP_REPORT_URI', null);
}

if (!defined('CSP_REPORT_ONLY')) {
    define('CSP_REPORT_ONLY', false);
}

define('FPDF_FONTPATH', __DIR__ . '/lib/font/');

/* global definitions for Foodsharing\\Helpers\\PageHelper*/
define('CNT_MAIN', 0);
define('CNT_RIGHT', 1);
define('CNT_TOP', 2);
define('CNT_BOTTOM', 3);
define('CNT_LEFT', 4);
define('CNT_OVERTOP', 5);

define('DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_DB . ';charset=utf8mb4');

// define('WEBPUSH_PUBLIC_KEY', 'TO CHANGE AT DEPLOYMENT');
// define('WEBPUSH_PRIVATE_KEY', 'TO CHANGE AT DEPLOYMENT');

if (!defined('BBB_DOMAIN')) {
    define('BBB_DOMAIN', \Foodsharing\Lib\BigBlueButton::DEFAULT_CLIENT);
    define('BBB_SECRET', 'CHANGEME');
    define('BBB_DIALIN', '+49xxxxx');
}

/*
 * How to put the webpush keys at the first deployment after webpush was introduced:
 *
 * 1. Generate the keys by executing the following in your UNIX shell:
 * 	openssl ecparam -genkey -name prime256v1 -out private_key.pem
 *  openssl ec -in private_key.pem -pubout -outform DER|tail -c 65|base64|tr -d '=' |tr '/+' '_-' >> public_key.txt
 *  openssl ec -in private_key.pem -outform DER|tail -c +8|head -c 32|base64|tr -d '=' |tr '/+' '_-' >> private_key.txt
 *
 * 2. Uncomment ll. 65-66 of this script and replace TO CHANGE AT DEPLOYMENT with the contents of public_key.txt and
 * 	private_key.txt
 */
