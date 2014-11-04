<?php 
error_reporting(E_ALL);
ini_set('display_errors','On');

$js = '';
if(isset($_GET['app']) && isset($_GET['m']))
{

	$app = str_replace('/','',$_GET['app']);
	$meth = str_replace('/','',$_GET['m']);
	
	if(!file_exists(ROOT_DIR . 'app/'.$app.'/'.$app.'.xhr.php'))
	{
		exit();
	}
	
	require_once 'config.inc.php';
	require_once 'lib/Session.php';
	require_once 'lang/DE/de.php';
	require_once 'lang/DE/'.$app.'.lang.php';
	
	require_once 'lib/db.class.php';
	require_once 'lib/Manual.class.php';
	require_once 'lib/func.inc.php';
	require_once 'lib/view.inc.php';
	require_once 'lib/Manual.class.php';
	
	require_once ROOT_DIR.'app/core/core.control.php';
	require_once ROOT_DIR.'app/core/core.model.php';
	require_once ROOT_DIR.'app/core/core.view.php';
	
	require_once 'app/'.$app.'/'.$app.'.xhr.php';
	require_once 'app/'.$app.'/'.$app.'.model.php';
	require_once 'app/'.$app.'/'.$app.'.view.php';
	
	S::init();
	
	
	$class = ucfirst($app).'Xhr';
	
	$obj = new $class();
	
	if(method_exists($obj, $meth))
	{
		$out = $obj->$meth();
		
		if(isset($_GET['format']) && $_GET['format'] == 'jsonp')
		{
			echo $_GET['callback'] . '(' . json_encode($out) . ');';
			exit();
		}
		
		if(!isset($out['script']))
		{
			$out['script'] = '';
		}
		
		$out['script'] = '$(".tooltip").tooltip({show: false,hide:false,position: {	my: "center bottom-20",	at: "center top",using: function( position, feedback ) {	$( this ).css( position );	$("<div>").addClass( "arrow" ).addClass( feedback.vertical ).addClass( feedback.horizontal ).appendTo( this );}}});'.$out['script'];
		
		echo json_encode($out);
	}
}