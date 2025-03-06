<?php

require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveCave.class.php');


$html = new varcaveHtml(L::pagename_about);
$auth = new varcaveAuth();
$logger = $auth->logger;

$htmlstr = '';

$aboutContent = $html->getAboutPage();
if($aboutContent === false){
    $html = new VarcaveHtml(L::errors_ERROR);
    $html->stopWithMessage(L::errors_ERROR, L::errors_pageNotAvailable, 500, 'INTERNAL SERVER ERROR');
}


if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'edit' )
{   
    $logger->info('about.php : start edit page');
    $acl = $auth->getacl('bcf1886e-e1a8-4096-a02f-a44dd17d440e');
    if ( !$auth->isSessionValid() ||  !$auth->isMember( $acl[0]) )
    {
        $logger->error('about.php : user try to access unauthentified' . 'IP : '. $_SERVER['REMOTE_ADDR'] );
        $html = new VarcaveHtml(L::errors_ERROR);
        $html->stopWithMessage(L::errors_ERROR, L::errors_pageAccessDenied, 401, 'Unauthorized ');
    }

    $htmlstr .= '<h1>' . L::about_edit_title . '</h1>';

    $htmlstr .= <<<EOL
    <script src="/lib/trumbowyg/2.20/trumbowyg.min.js"></script>
    <script src="/lib/varcave/about.js"></script>
	<link rel="stylesheet" href="lib/trumbowyg/2.20/trumbowyg.min.css">
EOL;

    $htmlstr .= '<div id="about_edit_content">';
    $htmlstr .= '  <div id="about_contenthtml" value="">';
    $htmlstr .= $aboutContent['html_content'];
    $htmlstr .= '  </div>';
    
    $htmlstr .= '<button>' . L::general_save . '</button>';

    $htmlstr .= '</div>';
}
elseif($_SERVER['REQUEST_METHOD'] == 'GET'){
    $htmlstr .= '<div id=about_page_content>';
    
    $acl = $auth->getacl('bcf1886e-e1a8-4096-a02f-a44dd17d440e');
    if ( $auth->isSessionValid() && $auth->isMember( $acl[0]) )
    {
        $htmlstr .=   '<a  title="' . L::general_edit . '" class="fa-3x" href="about.php?action=edit">';
		$htmlstr .=     '<i  class="fas fa-edit"></i>';
		$htmlstr .=   '</a>';
        $htmlstr .= '<hr>';
    }

    $htmlstr .= $aboutContent['html_content'];
    $htmlstr .= '</div>';

}
else{
    $htmlstr .= L::errors_badAction;
    
}



$html->insert($htmlstr,true);
echo $html->save();


?>
