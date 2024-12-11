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
    $htmlstr .= '  <input type="text" id="login-username" name="username" placeholder="' . $html->getConfigElement('user_login_tip') . '" ><br>';
    $htmlstr .= '  <label for="password">' . L::login_userPassword . '</label><br>';
    $htmlstr .= '  <input type="password" id="login-password" name="password"><br>';
    $htmlstr .= '  <input type="submit" id="login-submit" name="submit" value="' . L::login_connect . '"><br>';
    $htmlstr .= '  <div id="login-resetpwd" style="display:none"><a href="resetpassword.php">' . L::resetpassword_resetpassword . '</a></div>';
    $htmlstr .= '</form>';
    
	$htmlstr .= '<script src="lib/js-sha256/js-sha256.js"></script>';	
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
