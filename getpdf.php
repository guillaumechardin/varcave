<?php
require_once(__DIR__ . '/lib/varcave/varcaveCave.class.php');
require_once(__DIR__ . '/lib/varcave/varcavepdf.class.php');
require_once(__DIR__ . '/lib/varcave/varcaveAuth.class.php');
require_once(__DIR__ . '/lib/varcave/varcaveHtml.class.php');
require_once(__DIR__ . '/lib/varcave/functions.php');
$auth = new VarcaveAuth();

$acl = $auth->getacl('bda76758-f447-4f6a-90da-2c595a4adfb5');
if ( !$auth->isSessionValid() ||  !$auth->isMember( $acl[0]) )
{
    $html = new VarcaveHtml(L::errors_ERROR);
    $html->logger->error('getpdf.php : user try to access unauthentified' . 'IP : '. $_SERVER['REMOTE_ADDR'] );
    $html->stopWithMessage(L::errors_ERROR, L::errors_pageAccessDenied, 401, 'Unauthorized ');
}


$cave = new varcaveCave();

$_cave = $cave->selectByguid($_GET['guid'], false, false);
$vPdf = new Varcavepdf($_cave) ;

$vPdf->setY(15);
$vPdf->caveinfo();
//$vPdf->setY(80);
$acl = $auth->getacl('200f72ca-3c96-42e0-805c-2e133ce98ad0');
if ( $auth->isSessionValid() &&  $auth->isMember( $acl[0]) ){
    $vPdf->caveaccess();
}
$acl = $auth->getacl('da77ca5f-1e0a-4b02-a49b-a4ac428902d5');
if ( $auth->isSessionValid() &&  $auth->isMember( $acl[0]) ){
    $vPdf->addcavemaps();
}
$file_name = cleanStringFilename( $_cave['name'] . '.pdf' );
//echo $file_name;
$vPdf->output( $file_name, 'I');

?>
