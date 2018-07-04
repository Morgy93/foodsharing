<?php

use Foodsharing\DI;
use Foodsharing\Lib\Routing;
use Foodsharing\Lib\Session\S;
use Foodsharing\Lib\Xhr\XhrResponses;

require __DIR__ . '/includes/setup.php';

if (isset($_GET['app']) && isset($_GET['m'])) {
	$app = str_replace('/', '', $_GET['app']);
	$meth = str_replace('/', '', $_GET['m']);

	require_once 'config.inc.php';
	require_once 'lang/DE/de.php';

	/* @var $session S */
	$session = DI::$shared->get(S::class);
	$session->init();

	$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

	$class = Routing::getClassName($app, 'Xhr');
	$obj = DI::$shared->get(ltrim($class, '\\'));

	if (method_exists($obj, $meth)) {
		$out = $obj->$meth($request);

		if ($out === XhrResponses::PERMISSION_DENIED) {
			header('HTTP/1.1 403 Forbidden');
			exit();
		}

		if (isset($_GET['format']) && $_GET['format'] == 'jsonp') {
			echo $_GET['callback'] . '(' . json_encode($out) . ');';
			exit();
		}

		if (!isset($out['script'])) {
			$out['script'] = '';
		}

		$out['script'] = '$(".tooltip").tooltip({show: false,hide:false,position: {	my: "center bottom-20",	at: "center top",using: function( position, feedback ) {	$( this ).css( position );	$("<div>").addClass( "arrow" ).addClass( feedback.vertical ).addClass( feedback.horizontal ).appendTo( this );}}});' . $out['script'];

		echo json_encode($out);
	}
}
