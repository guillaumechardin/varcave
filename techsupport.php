<?php

require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveAuth.class.php');
require_once ('lib/varcave/varcaveUsers.class.php');


$auth = new varcaveAuth();
$logger = $auth->logger;

if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
{
	$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $redirect);
	//echo 'vous allez être redirigé vers une connexion sécurisée :<br>'. $redirect; 
	exit();
}


$acl = $auth->getacl('150edca1-1783-45ae-a433-0a1b2ff332bc');
if ( !$auth->isSessionValid() || !$auth->isMember($acl[0]))
{
    $logger->error(__FILE__ . ' : user try to access unauthentified');
    $logger->error('IP : '. $_SERVER['REMOTE_ADDR']);
    $html = new VarcaveHtml(L::errors_ERROR );
    $htmlstr .= '<h2>' . L::errors_ERROR . '</h2>';
    $htmlstr .= L::errors_pageAccessDenied . '.';
    $html->insert($htmlstr,true);
    echo $html->save();
    exit();
}

$htmlstr = '';
$html = new VarcaveHtml(L::pagename_techsupport);


ob_start();
phpinfo();
$phpinfo = ob_get_contents();
ob_end_clean();
$htmlstr .= '<h2>' . L::techsupport_title . '</h2>';
$htmlstr .= '<div id="phpinfo">';
    
$htmlstr .='
<style type="text/css">
    #phpinfo {}
    #phpinfo pre {margin: 0; font-family: monospace;}
    #phpinfo a:link {color: #009; text-decoration: none; background-color: #fff;}
    #phpinfo a:hover {text-decoration: underline;}
    #phpinfo table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}
    #phpinfo .center {text-align: center;}
    #phpinfo .center table {margin: 1em auto; text-align: left;}
    #phpinfo .center th {text-align: center !important;}
    #phpinfo td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
    #phpinfo h1 {font-size: 150%;}
    #phpinfo h2 {font-size: 125%;}
    #phpinfo .p {text-align: left;}
    #phpinfo .e {background-color: #ccf; width: 300px; font-weight: bold;}
    #phpinfo .h {background-color: #99c; font-weight: bold;}
    #phpinfo .v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
    #phpinfo .v i {color: #999;}
    #phpinfo img {float: right; border: 0;}
    #phpinfo hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}
</style>';

$htmlstr .= preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
$htmlstr .= '</div>';






$html->insert($htmlstr,true);
echo $html->save();
