<?php

require_once (__DIR__ . '/lib/varcave/varcaveCave.class.php');
require_once (__DIR__ . '/lib/varcave/functions.php');
require_once (__DIR__ . '/lib/varcave/varcaveAuth.class.php');
require_once (__DIR__ . '/lib/varcave/varcaveHtml.class.php');

$auth = new VarcaveAuth();

$acl = $auth->getacl('52f41225-92c9-4926-a24f-d62f1e824f8d');
if ( !$auth->isSessionValid() ||  !$auth->isMember( $acl[0]) )
{
    $auth->logger->error('getpdf.php : user try to access unauthentified' . 'IP : '. $_SERVER['REMOTE_ADDR'] );
    $html = new VarcaveHtml(L::errors_ERROR);
    $html->stopWithMessage(L::errors_ERROR, L::errors_pageAccessDenied, 401, 'Unauthorized ');
}


$cave = new varcaveCave();

if(isset($_GET['guid']) && !empty($_GET['guid']) && isset($_GET['gpx']) )
{
	//fetch and start download of gpx file
	$GPXdata = $cave->createGPX($_GET['guid']);
	$caveData = $cave->selectByGUID($_GET['guid']);
	if ($caveData == false)
	{
		exit();
	}

	header('Content-Type: application/gpx+xml');
	//clean cav name susbtr is here to remove the 6last letter of dummy file extension and makes cleanStringFilename works
	$filename = substr(cleanStringFilename($caveData['name'].'.dummy'), 0,-6);
	//$filename = substr($filename,0,10);
	header('Content-Disposition: attachment; filename=' . $filename . '.gpx');
	echo $GPXdata;
}

?>