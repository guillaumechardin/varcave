<?php
require_once(__DIR__ . '/lib/varcave/varcaveCave.class.php');
require_once(__DIR__ . '/lib/varcave/varcavepdf.class.php');
require_once(__DIR__ . '/lib/varcave/varcaveAuth.class.php');
$auth = new VarcaveAuth();

$acl = $auth->getacl('bda76758-f447-4f6a-90da-2c595a4adfb5');
if ( !$auth->isSessionValid() ||  !$auth->isMember( $acl[0]) )
{
    $logger->error('getpdf.php : user try to access unauthentified' . 'IP : '. $_SERVER['REMOTE_ADDR'] );
    $html = new VarcaveHtml(L::errors_ERROR);
    $html->stopWithMessage(L::errors_ERROR, L::errors_pageAccessDenied, 401, 'Unauthorized ');
}


$cave = new varcaveCave();

$_cave = $cave->selectByguid($_GET['guid']);

$vPdf = new Varcavepdf($_cave) ;

$vPdf->setY(15);
$vPdf->caveinfo();
//$vPdf->setY(70);
$vPdf->caveaccess();
$vPdf->addcavemaps();
$vPdf->output('file.pdf','I');

?>
