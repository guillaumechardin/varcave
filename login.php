<?php

require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveAuth.class.php');
require_once ('lib/varcave/varcaveCave.class.php');
require_once ('lib/varcave/functions.php');



$htmlstr = '';
$html = new VarcaveHtml(L::pagename_login);
$auth = new VarcaveAuth();


//redirect to HTTPS if non secure connection
if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
{
	$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $redirect);
	exit();
}


if ( isset($_GET['logout']) && $_GET['logout']!='' )
{
	$auth->logout();
	header('Location: index.php');
	exit();
	
}

if ( isNullOrEmptyArray($_POST) )
{
    
    $htmlstr .= '<form id="login-login-form" action="/login.php" >';
    $htmlstr .= '<p>' . L::login_loginPrompt . '</p>';
    $htmlstr .= '  <label for="username">' . L::login_userName . '</label><br>';
    $htmlstr .= '  <input type="text" id="login-username" name="username"><br>';
    $htmlstr .= '  <label for="password">' . L::login_userPassword . '</label><br>';
    $htmlstr .= '  <input type="password" id="login-password" name="password"><br>';
    $htmlstr .= '  <input type="submit" id="login-submit" name="submit" value="' . L::login_connect . '"><br>';
    $htmlstr .= '</form>';
    
    
    
    
	/*$htmlstr .= '<div id="userpwdform" >';
	$htmlstr .= '  <p>' . L::login_loginPrompt . '</p>';;
	$htmlstr .=    L::login_userName . '<br />';
	$htmlstr .= '  <input type="text" name="username" id="username" />';
	$htmlstr .= '  <br />';
	$htmlstr .=    L::login_userPassword . '<br />';
	$htmlstr .= '  <input type="password" name="password" id="password"/>';
	$htmlstr .= '  <br />';
	$htmlstr .= '  <button id="login-doLogin"> OK </button>';
	$htmlstr .= '</div>';*/
	$htmlstr .= '<script src="lib/js-sha256/js-sha256.js"></script>';
	$htmlstr .= '<script src="lib/jqueryui/jquery-ui-1.12.1/jquery-ui.js"></script>';
	$htmlstr .= '<link rel="stylesheet" href="lib/jqueryui/jquery-ui-themes-1.12.1/themes/base/jquery-ui.css" />';
	
} 
elseif ( isset($_POST['username']) && isset($_POST['password']) ) 
{
	$authState = $auth->login($_POST['username'], $_POST['password'] );
	
	$debugMsg = '';
	if ( $auth->getConfigElement('loglevel') )
	{
		$debugMsg = $auth->getErrorMsg(true);
	}
	
	if ($authState === false)
	{
		$reason = $auth->getErrorMsg();
		
		$return = array (
			'title' => 'login',
			'stateStr' => $reason[0]['msg'],
			'state' => 0,
 		);
		$httpError = 400;
		$httpErrorStr = ' Bad Request';
	}
	else
	{
		$return = array (
			'title' => 'login',
			'stateStr' => L::authSuccess,
			'state' => 1,
		);
		$httpError = 200;
		$httpErrorStr = 'OK';
	}
	
	//send back to browser
	header('HTTP/1.1 ' . $httpError . $httpErrorStr);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($return);
	exit();

}





//echo $htmlstr;

$html->insert($htmlstr,true);
echo $html->save();
?>
