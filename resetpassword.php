<?php
require_once ('lib/PHPMailer/6.1.1/src/Exception.php');
require_once ('lib/PHPMailer/6.1.1/src/PHPMailer.php');
require_once ('lib/PHPMailer/6.1.1/src/SMTP.php'); 
require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveAuth.class.php');
require_once ('lib/varcave/varcaveCave.class.php');
require_once ('lib/varcave/functions.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$htmlstr = '';
$html = new VarcaveHtml(L::pagename_resetpwd);
$auth = new VarcaveAuth();
$users = new VarcaveUsers();

//redirect to HTTPS if non secure connection
if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
{
	$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $redirect);
	exit();
}

//load main page
if ( (isset($_POST['resetpwd']) && $_POST['resetpwd'] == true) && $_POST['username'] != '' )
{
    $html->logger->debug('Get user request for password reset');
    $userID = $users->getUidByUsername($_POST['username']);
    
    if($userID)
    {
        $linkid = $users->updPwdResetLink($userID['uid']);
    }

    $useremail = '';
    if( $userID && $linkid )
    {
        //generate a unique linkid and update database
        $userinfo = $users->getUserDetails($userID['uid']);
        $useremail = '(' . hideEmail($userinfo['emailaddr']) . ')';    
    }
    //on if userid and linkid generation succeed emailaddr is populated
    // on failure, this var is not populated and prevent data leak 
    $return = array(
                'title' => L::general_operation_complete,
                'stringmsg' => '',
                'data' => array(
                        'message' => L::resetpassword_reset_notice,
                        'emailaddr' => $useremail,
                        ),
    );

    //no user found or email address empty
    if(empty($useremail))
    {
        $html->writeJson($return);
    }
    /* ******************************
       ****    START SENDMAIL    ****
       ******************************/
    //send the Email
    $mail = new PHPMailer;
    
    //Tell PHPMailer to use SMTP1
    $mail->isSMTP();
    
    //set subject UTF8
    $mail->CharSet  = 'UTF-8';
    
    //Enable SMTP debugging
    $mail->SMTPDebug = $html->getConfigElement('smtp_server_debuglbvl');
    $mail->Debugoutput = function($str, $level)
    {
        file_put_contents('logs/smtp.log', gmdate('Y-m-d H:i:s') . "\t$level\t$str\n", FILE_APPEND | LOCK_EX);
    };
    
    
    

    //Set the hostname of the mail server
    $mail->Host = $html->getConfigElement('smtp_server');
    
    //Set the SMTP port number - likely to be 25, 465 or 587
    $mail->Port = $html->getConfigElement('smtp_port');
    //Whether to use SMTP authentication
    if( $html->getConfigElement('smtp_useauth') )
    {
        $mail->SMTPAuth = true;
        //Username to use for SMTP authentication
        $mail->Username = $html->getConfigElement('smtp_user');
        //Password to use for SMTP authentication
        $mail->Password = $html->getConfigElement('smtp_userpwd');
    }
    
    //Set who the message is to be sent from
    $mail->setFrom($html->getConfigElement('smtp_sender'), 'Varcave');
    //Set an alternative reply-to address
    $mail->addReplyTo($html->getConfigElement('smtp_sender'), 'Varcave');
    //Set who the message is to be sent to
    $mail->addAddress( strtolower($userinfo['emailaddr']), strtolower($userinfo['emailaddr']) );
   
    
    //Set the subject line
    $mail->Subject = L::resetpassword_mail_subject;
    
    /*
     * Only plaintext email for now
     * 
     * smtp_max_attach_size
     * smtp_max_attach_global_size
     * 
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $mail->msgHTML(file_get_contents('contents.html'), __DIR__);
    */
    
    //Replace the plain text body with one created manually
    $mail->isHTML(true);
    $mail->Body = L::general_hello . ',<br/> ' . 
                  '' . L::resetpassword_mail_body . '<br/>' . $html->getConfigElement('httpdomain') .
                  '/resetpassword.php?resetlink=' . $linkid['linkid'] . '<br>' . 
                  '['. L::resetpassword_mail_signature . ']';
     
    //send the message, check for errors
    if (!$mail->send()) 
    {
        $html->logger->error('  Unable to send email:' . $mail->ErrorInfo);
    }
    else
    {
        $html->logger->debug('  Email sent successfully');
    }
    /* ******************************
       ****     END  SENDMAIL    ****
       ******************************/
    $html->writeJson($return);
}
/*
 * check user provided link and display password reset fields.
 * All operations done by GET, form to reset pwd  sent by POST
 */
elseif(isset($_GET['resetlink']) && $_GET['resetlink'] != '')
{
    //check if id exists
    if ($users->isResetLinkValid($_GET['resetlink']) == false )
    {
        $htmlstr .= '<strong>' . L::resetpassword_inexistant_link . '</strong>';
    }
    else
    {
        $htmlstr .= '<h2>' . '<i class="fas fa-key"></i> ' . L::myaccount_changePwd . '</h2>';
        $htmlstr .= '<p>';
        $htmlstr .= '<form id="rstPasswd" method="post">';
        $htmlstr .=	'  <input type="password"   placeholder="' . L::myaccount_enterPwdHint .'" id="pass1" size="30" maxlength="25" autocomplete="off" value="" />';
        $htmlstr .= ' ';
        $htmlstr .= '  <input type="password" placeholder="' . L::myaccount_confirmPwdHint .'" id="pass2" size="30" maxlength="25" autocomplete="off" value=""/>';
        $htmlstr .= '  <input id="reset-link" type="hidden" value="' . $_GET['resetlink'] . '"></p>';
        $htmlstr .= '  <p><input type="submit" value="OK"></p>';
        $htmlstr .= '</form>';
        $htmlstr .= '</p>';     
        $htmlstr .= '<script src="lib/js-sha256/js-sha256.js"></script>';
        $htmlstr .= '<script src="lib/jqueryui/jquery-ui-1.12.1/jquery-ui.js"></script>';
        $htmlstr .= '<link rel="stylesheet" href="lib/jqueryui/jquery-ui-themes-1.12.1/themes/base/jquery-ui.css" />';
    } 
}
/*
 * user send a new password change DB and delete reset link all operations are done by POST
 */
elseif( isset($_POST['resetlink']) && $_POST['resetlink'] != '' && 
        isset($_POST['passwd']) && $_POST['passwd'] != ''){  
    if ($users->isResetLinkValid($_POST['resetlink']) == false )
    {
        $return = array(
                'title' => L::errors_ERROR,
                'stringmsg' => L::general_operation_fail,
        );
        $html->writeJson(L::errors_ERROR, 500, 'INTERNAL SERVER ERROR');
    }
    else
    {
        if( $users->resetPwdFromLink($_POST['resetlink'], $_POST['passwd']) ){
            $return = array(
                'title' => L::general_info,
                'stateStr' => L::myaccount_successPwdChg . "\n" . L::general_redirect_pending,
                'data' => array('option' => array('pwdreset' => 1),),
            );
            $html->writeJson($return);
        }
        $return = array(
                'title' => L::errors_ERROR,
                'stateStr' => L::myaccount_failedPwdChg,
        );
        $html->writeJson(L::errors_ERROR, 500, 'INTERNAL SERVER ERROR');        
    }
}
else{
    //show form to ask client varcave username for reseting password
    $htmlstr .= '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
    $htmlstr .= '<div id="resetpassword-wrapform">';
    $htmlstr .= '  <form action="?dummy=yes" method="POST" id="resetpassword-form">';
    $htmlstr .= '    <div class="loadingSpiner"><i class="fas fa-spinner fa-pulse fa-3x"></i></div>';
    $htmlstr .= '    <div id="resetpassword-notice">';
    $htmlstr .=      L::resetpassword_user_notice;
    $htmlstr .= '    </div>';
    $username = '';
    if( isset($_GET['username']) )
    {
        $username = $_GET['username'];
    }
    $htmlstr .= '    <input type="text" id="resetpassword-username" value="' . $username . '">';
    $htmlstr .= '    <div id="resetpassword-wrapcaptcha">';
    $htmlstr .= '      <div class="g-recaptcha" data-sitekey="' . $auth->getConfigElement('captcha_public_key') .'"></div>';
    $htmlstr .= '      <br/>';
    $htmlstr .= '    </div>';
    $htmlstr .= '    <input type="submit" id ="resetpassword-doreset" value="' . L::contact_send . '">';
    $htmlstr .= '  </form>';
    $htmlstr .= '  <script src="lib/varcave/resetpassword.js"></script>';
    $htmlstr .= '</div>';
}


$html->insert($htmlstr,true);
echo $html->save();
?>