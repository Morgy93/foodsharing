<?php

use Foodsharing\Lib\Db\Db;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require __DIR__ . '/includes/setup.php';

require_once 'config.inc.php';

require_once 'lang/DE/de.php';

/** Checks the validity of an API token.
 * @param Foodsaver $fs ID
 * @param API $key token
 *
 * @return true or False depending on validity
 */
function check_api_token($fs, $key, Db $model)
{
	$res = $model->qOne('SELECT COUNT(foodsaver_id) FROM fs_apitoken WHERE foodsaver_id = ' . (int)$fs . ' AND token="' . $model->safe($key) . '"');

	return $res == 1;
}

function dateToCal($timestamp)
{
	return gmdate('Ymd\THis\Z', $timestamp);
}

function dateToLocalCal($timestamp)
{
	return date('Ymd\THis', $timestamp);
}

function escapeString($string)
{
	$string = str_replace("\r\n", '\\n', $string);
	$string = str_replace("\n", '\\n', $string);

	return preg_replace('/([\,;])/', '\\\$1', $string);
}

function generate_calendar_event($utc_begin, $utc_end, $utc_change, $uid, $location, $description, $summary, $uri)
{
	$out = "BEGIN:VEVENT\r\nDTEND:";
	$out .= dateToCal($utc_end) . "\r\nUID:";
	$out .= $uid . "\r\nDTSTAMP:";
	$out .= dateToCal($utc_change) . "\r\nLOCATION:";
	$out .= escapeString($location) . "\r\nDESCRIPTION:";
	$out .= escapeString($description) . "\r\nURL;VALUE=URI:";
	$out .= escapeString($uri) . "\r\nSUMMARY:";
	$out .= escapeString($summary) . "\r\nDTSTART:";
	$out .= dateToCal($utc_begin) . "\r\nEND:VEVENT\r\n";

	return $out;
}

function api_generate_calendar($fs, $options, Db $model): Response
{
	/* adapted from https://gist.github.com/jakebellacera/635416 */
	$response = new Response();

	$response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
	$response->headers->set('Content-Disposition', 'attachment; filename=calendar.ics');

	$content = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//foodsharing.de//NONSGML v1.0//EN\r\nCALSCALE:GREGORIAN\r\n";
	if (strpos($options, 's') !== false) {
		$fetches = $model->q('SELECT b.id, b.name, b.str, b.hsnr, b.plz, b.stadt, a.confirmed, UNIX_TIMESTAMP(a.`date`) AS date_ts FROM fs_abholer a INNER JOIN fs_betrieb b ON a.betrieb_id = b.id WHERE a.foodsaver_id = ' . (int)$fs . ' AND a.`date` > NOW() - INTERVAL 1 DAY');
		if (is_array($fetches)) {
			foreach ($fetches as $f) {
				$datestart = $f['date_ts'];
				$dateend = $f['date_ts'] + 30 * 60;
				$uid = $f['id'] . $f['date_ts'] . '@fetch.foodsharing.de';
				$address = $f['str'] . ' ' . $f['hsnr'] . ', ' . $f['plz'] . ' ' . $f['stadt'];
				$summary = $f['name'] . ' Abholung';
				if (!$f['confirmed']) {
					$summary .= ' (unbestätigt)';
				}
				$description = 'foodsharing Abholung bei ' . $f['name'];
				$uri = BASE_URL . '/?page=fsbetrieb&id=' . $f['id'];
				// 3. Append the ics file's contents
				$content .= generate_calendar_event($datestart, $dateend, time(), $uid, $address, $description, $summary, $uri);
			}
		}
	}

	if (strpos($options, 'e') !== false) {
		$calendar = $model->q('
				SELECT
					e.id,
					e.name,
					e.`description`,
					UNIX_TIMESTAMP(e.`start`) AS start_ts,
					UNIX_TIMESTAMP(e.`end`) AS end_ts,
					e.online,
					fe.`status`,
					loc.name AS loc_name,
					loc.street,
					loc.zip,
					loc.city
				FROM
					`fs_event` e
				INNER JOIN
					`fs_foodsaver_has_event` fe
				ON
					e.id = fe.event_id AND fe.foodsaver_id = ' . (int)$fs . '
				LEFT JOIN
					`fs_location` loc
				ON
					loc.id = e.location_id
				WHERE
					e.start  > NOW() - INTERVAL 1 DAY
				AND
					((e.public = 1 AND (fe.`status` IS NULL OR fe.`status` <> 3))
					OR
						fe.`status` IN(1,2)
					)');
		if (is_array($calendar)) {
			foreach ($calendar as $c) {
				$datestart = $c['start_ts'];
				$dateend = $c['end_ts'];
				$uid = $c['id'] . $c['start_ts'] . '@event.foodsharing.de';
				if ($c['online']) {
					$address = 'Online, mumble.foodsharing.de';
				} else {
					$address = $c['loc_name'] . ', ' . $c['street'] . ' ' . $c['zip'] . ', ' . $c['city'];
				}
				$summary = $c['name'] . ' Event';
				if (!$c['status'] == 1) {
					$summary = '(' . $summary . ')';
				}
				$description = 'foodsharing Event: ' . $c['description'];
				$uri = BASE_URL . '/?page=event&id=' . $c['id'];
				// 3. append the ics file's contents
				$content .= generate_calendar_event($datestart, $dateend, time(), $uid, $address, $description, $summary, $uri);
			}
		}
	}

	$content .= "END:VCALENDAR\r\n";

	$response->setContent($content);

	return $response;
}

$request = Request::createFromGlobals();

$action = $request->query->get('f');
$fs = $request->query->get('fs');
$key = $request->query->get('key');
$opts = $request->query->get('opts');

/* @var Container $container */
global $container;
$container = initializeContainer();

/* @var Db $model */
$model = $container->get(Db::class);

$response = new Response();

if (!check_api_token($fs, $key, $model)) {
	$response->setStatusCode(Response::HTTP_FORBIDDEN);
	$response->setContent('Invalid access token!');
	$response->send();
} else {
	switch ($action) {
		case 'cal':
			$response = api_generate_calendar($fs, $opts, $model);
			$response->send();
			break;
	}
}
