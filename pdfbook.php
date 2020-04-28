<?php

require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveCave.class.php');

$htmlstr = '';
$html = new varcaveHtml(L::pagename_pdfbook);

$htmlstr .= '<h1>' . L::pdfbook_title . '</h1>';
$htmlstr .=  '<div>';
$htmlstr .= '<i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong> ' . L::errors_pageNotAvailable . '</strong></i>';
$htmlstr .=  '</div>';


$html->insert($htmlstr,true);
echo $html->save();


?>
