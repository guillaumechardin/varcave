<?php
require_once(__DIR__ . '/lib/varcave/varcaveCave.class.php');
require_once(__DIR__ . '/lib/varcave/varcavepdf.class.php');
require_once(__DIR__ . '/lib/varcave/varcaveAuth.class.php');
require_once(__DIR__ . '/lib/varcave/varcaveHtml.class.php');
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
//$vPdf->setY(70);
$vPdf->caveaccess();
$vPdf->addcavemaps();
$vPdf->output('file.pdf','I');

?>
