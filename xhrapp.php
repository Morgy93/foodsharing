<?php

use Foodsharing\Lib\Routing;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\Xhr\XhrResponses;
use Symfony\Component\DependencyInjection\Container;

require __DIR__ . '/includes/setup.php';
require_once 'config.inc.php';

/* @var $container Container */
global $container;
$container = initializeContainer();

/*
	methods wich are excluded from the CSRF Protection.
	We start with every method and remove one by another
	NEVER ADD SOMETING TO THIS LIST!
*/
$csrf_whitelist = [
	// 'Activity::loadMore',
	// 'Activity::load',
	// 'Application::accept',
	// 'Application::decline',
	// 'Basket::basketCoordinates',
	// 'Basket::newBasket',
	// 'Basket::publish',
	// 'Basket::resizePic',
	// 'Basket::nearbyBaskets',
	// 'Basket::bubble',
	// 'Basket::fsBubble',
	// 'Basket::infobar',
	// 'Basket::removeRequest',
	// 'Basket::removeBasket',
	// 'Basket::editBasket',
	// 'Basket::publishEdit',
	// 'Basket::finishRequest',
	// 'Bell::infobar',
	// 'Bell::delbell',
	// 'Bell::markBellsAsRead',
	// 'Buddy::request',
	// 'Buddy::removeRequest',
	// 'Buddy::makeCard',
	// 'Email::testmail' .
	// 'Event::accept',
	// 'Event::maybe',
	// 'Event::noaccept',
	// 'Event::ustat',
	// 'Event::ustatadd',
	// 'Foodsaver::loadFoodsaver',
	// 'Foodsaver::foodsaverrefresh',
	// 'Foodsaver::delFromRegion',
	// 'Login::loginsubmit',
	'Login::photoupload',
	'Login::joinsubmit',
	'Login::join',
	'Mailbox::attach',
	// 'Mailbox::loadmails',
	// 'Mailbox::move',
	'Mailbox::quickreply',
	// 'Mailbox::send_message',
	'Mailbox::fmail',
	// 'Mailbox::loadMail',
	// 'Mailbox::refresh',
	// 'Mailbox::attach_allow',
	'Main::picupload',
	// 'Map::savebpos',
	// 'Message::rename',
	// 'Message::leave',
	// 'Message::loadconversation',
	// 'Message::loadmore',
	// 'Message::sendmsg',
	// 'Message::loadconvlist',
	// 'Message::user2conv',
	// 'Message::newconversation',
	// 'Message::heartbeat($opt)',
	// 'Message::people',
	// 'Profile::rate',
	// 'Profile::history',
	// 'Profile::deleteFromSlot',
	// 'Profile::quickprofile',
	// 'Quiz::hideinfo',
	// 'Quiz::addquest',
	// 'Quiz::delquest',
	// 'Quiz::delanswer',
	// 'Quiz::addansw',
	// 'Quiz::updateansw',
	// 'Quiz::editanswer',
	// 'Quiz::addanswer',
	// 'Quiz::addquestion',
	// 'Quiz::editquest',
	// 'Quiz::abort',
	// 'Quiz::startquiz',
	// 'Quiz::endpopup',
	// 'Quiz::quizpopup',
	// 'Quiz::addcomment',
	// 'Quiz::next',
	// 'Quiz::pause',
	// 'Quiz::updatequest',
	// 'Region::morethemes',
	'Region::quickreply',
	// 'Region::signout',
	// 'Report::loadReport',
	// 'Report::comReport',
	// 'Report::delReport',
	// 'Report::reportDialog',
	// 'Report::betriebReport',
	// 'Search::search',
	// 'Settings::changemail',
	// 'Settings::changemail2',
	// 'Settings::changemail3',
	// 'Settings::abortchangemail',
	// 'Settings::changemail4',
	// 'Settings::sleepmode',
	// 'Store::savedate',
	// 'Store::deldate',
	// 'Store::getfetchhistory',
	// 'Store::fetchhistory',
	// 'Store::adddate',
	// 'Store::savebezirkids',
	// 'Store::setbezirkids',
	// 'Store::signout',
	'Team::contact',
	// 'WallPost::delpost',
	// 'WallPost::update',
	'WallPost::quickreply',
	// 'WallPost::post',
	'WallPost::attachimage',
	// 'WallPost::attach_allow',
	// 'WorkGroup::apply',
	// 'WorkGroup::addtogroup',
	// 'WorkGroup::applysend',
	// 'WorkGroup::sendtogroup',
	// 'WorkGroup::contactgroup',
];

if (isset($_GET['app'], $_GET['m'])) {
	$app = str_replace('/', '', $_GET['app']);
	$meth = str_replace('/', '', $_GET['m']);

	require_once 'config.inc.php';
	require_once 'lang/DE/de.php';

	/* @var $session \Foodsharing\Lib\Session */
	$session = $container->get(Session::class);
	$session->initIfCookieExists();

	$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

	$class = Routing::getClassName($app, 'Xhr');
	$obj = $container->get(ltrim($class, '\\'));

	if (method_exists($obj, $meth)) {
		// check CSRF Header
		$whitelist_key = explode('\\', $class)[3] . '::' . $meth;
		if (!in_array($whitelist_key, $csrf_whitelist)) {
			if (!$session->isValidCsrfHeader()) {
				header('HTTP/1.1 403 Forbidden');
				die('CSRF Failed: CSRF token missing or incorrect.');
			}
		}

		// execute method
		$out = $obj->$meth($request);

		if ($out === XhrResponses::PERMISSION_DENIED) {
			header('HTTP/1.1 403 Forbidden');
			exit();
		}

		if (!isset($out['script'])) {
			$out['script'] = '';
		}

		$out['script'] = '$(".tooltip").tooltip({show: false,hide:false,position: {	my: "center bottom-20",	at: "center top",using: function( position, feedback ) {	$( this ).css( position );	$("<div>").addClass( "arrow" ).addClass( feedback.vertical ).addClass( feedback.horizontal ).appendTo( this );}}});' . $out['script'];

		header('Content-Type: application/json');
		echo json_encode($out);
	}
}
