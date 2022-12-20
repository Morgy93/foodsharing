<?php

namespace Foodsharing\Entrypoint;

use Foodsharing\Annotation\DisableCsrfProtection;
use Foodsharing\Lib\Routing;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\Xhr\XhrResponses;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class XhrAppController extends AbstractController
{
	/**
	 * @var ContainerInterface Kernel container needed to access any service,
	 * instead of just the ones specified in AbstractController::getSubscribedServices
	 */
	private ContainerInterface $fullServiceContainer;

	public function __construct(ContainerInterface $container)
	{
		$this->fullServiceContainer = $container;
	}

	/*
		methods which are excluded from the CSRF Protection.
		We start with every method and remove one by another
		NEVER ADD SOMETING TO THIS LIST!
	*/
	private const csrf_whitelist = [
		// 'Activity::load',
		// 'Application::accept',
		// 'Application::decline',
		// 'Basket::basketCoordinates',
		// 'Basket::newBasket',
		// 'Basket::publish',
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
		// 'Message::user2conv',
		// 'Message::newconversation',
		// 'Message::people',
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

	/**
	 * @DisableCsrfProtection CSRF Protection (originally done for the REST API)
	 * breaks POST on these entrypoints right now,
	 * so this annotation disables it.
	 * Note that this entry point still performs CSRF checks on its own,
	 * except for what's specified in csrf_whitelist.
	 */
	public function __invoke(Request $request, Session $session): Response
	{
		if (!isset($_GET['app'], $_GET['m'])) {
			return new Response(null, Response::HTTP_BAD_REQUEST);
		}

		$app = str_replace('/', '', $_GET['app']);
		$meth = str_replace('/', '', $_GET['m']);

		$session->initIfCookieExists();

		$class = Routing::getClassName($app, 'Xhr');

		global $container;
		$container = $this->fullServiceContainer;
		$obj = $this->fullServiceContainer->get(ltrim($class, '\\'));

		if (!method_exists($obj, $meth)) {
			return new Response(null, Response::HTTP_BAD_REQUEST);
		}

		$response = new Response();

		// check CSRF Header
		$whitelist_key = explode('\\', $class)[3] . '::' . $meth;
		if (!in_array($whitelist_key, XhrAppController::csrf_whitelist) && !$session->isValidCsrfHeader()) {
			$response->setProtocolVersion('1.1');
			$response->setStatusCode(Response::HTTP_FORBIDDEN);
			$response->setContent('CSRF Failed: CSRF token missing or incorrect.');

			return $response;
		}

		// execute method
		$out = $obj->$meth($request);

		if ($out === XhrResponses::PERMISSION_DENIED) {
			$response->setProtocolVersion('1.1');
			$response->setStatusCode(Response::HTTP_FORBIDDEN);

			return $response;
		}

		if (!isset($out['script'])) {
			$out['script'] = '';
		}

		$out['script'] = '$(".tooltip").tooltip({show: false,hide:false,position: {	my: "center bottom-20",	at: "center top",using: function( position, feedback ) {	$( this ).css( position );	$("<div>").addClass( "arrow" ).addClass( feedback.vertical ).addClass( feedback.horizontal ).appendTo( this );}}});' . $out['script'];

		$response->headers->set('Content-Type', 'application/json');
		$response->setContent(json_encode($out));

		return $response;
	}
}
