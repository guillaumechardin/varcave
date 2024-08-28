<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once ('lib/PHPMailer/6.1.1/src/Exception.php');
require_once ('lib/PHPMailer/6.1.1/src/PHPMailer.php');
require_once ('lib/PHPMailer/6.1.1/src/SMTP.php'); 
require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveAuth.class.php');
require_once ('lib/varcave/varcaveUsers.class.php');

$html = new varcaveHtml(L::pagename_contact);
$htmlstr = '';

if( !isset($_POST) || empty($_POST) )
{
    $htmlstr .= '<h2>' . L::contact_title . '</h2>';
    $htmlstr .= '<div id="contact-contactform">';
    $htmlstr .= '</div>';
    
    /*
     * set some javascript custom data
     */
    $htmlstr .= '<script>';
    $htmlstr .= '  var maxfilesize = "' . $html->getConfigElement('smtp_max_attach_size') * 1000 . '";'; // in bytes
    $htmlstr .= '  var maxtotalfilessize = "' . $html->getConfigElement('smtp_max_attach_global_size') * 1000 . '";';
    $htmlstr .= '  var infoRequired = "' . L::errors_inforequired . '";' ;
    $htmlstr .= '  var send = "' . L::email_send . '";' ;
    $htmlstr .= '  var newmessage = "' . L::email_newmessage . '";' ;
    $htmlstr .= '  var mailUseCaptcha = "' . $html->getConfigElement('mail_use_captcha') . '";' ;    
    $htmlstr .= '  var email_usermail = "' . L::email_usermail . '";' ;    
    $htmlstr .= '  var email_subject = "' . L::email_subject . '";' ;    
    $htmlstr .= '  var email_yourmessage = "' . L::email_yourmessage . '";' ;    
    $htmlstr .= '  var email_attachfiles = "' . L::email_attachfiles . '";' ;    
    $htmlstr .= '  var contact_fileSizeNotice = "' . L::contact_fileSizeNotice . ' ' . round($html->getConfigElement('smtp_max_attach_size')/1024,1) . ' ' . 'Mo.";';
    $htmlstr .= '  var contact_TotalFileSizeNotice = "' . L::contact_TotalFileSizeNotice . ' ' . round($html->getConfigElement('smtp_max_attach_global_size')/1024,1) . ' ' . 'Mo.";';
    $htmlstr .= '  var captchaPubKey = "' . $html->getConfigElement('captcha_public_key') . '";' ;    
    $htmlstr .= '</script>';
    $htmlstr .= '<script src="lib/varcave/contact.js"></script>';

    $html->insert($htmlstr);
    echo $html->save();
}
else
{
    // this part handle mail flow creation and communication/send to smtp server
    
    // to prevent spambot from contact form,
    // check the content of $_POST[webbot] as a honeypot
    $html->logger->debug( basename(__FILE__ ) . ' : Try to send email');
    if( $_POST['webot'] != '' )
    {
        $html->logger->error('Bot try to send email from : ['. $_SERVER['REMOTE_ADDR'] . ']');
        $return = L::errors_ERROR . 'You are not welcome here Bot !';
        $httpError = 400;
        $httpErrorStr = ' Bad Request';
        //send back to browser
        header('HTTP/1.1 ' . $httpError . $httpErrorStr);
        header('Content-Type: application/json; charset=UTF-8');
        echo $return;
        exit();
    }
    
    /*
     * Check if captcha is correct and user not a bot
     */
    if ( $html->getconfigelement('mail_use_captcha') ){
        $captcha_secret = $html->getconfigelement('captcha_secret_key') ;
        $response = $_POST['captcha'];
        $ret = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$captcha_secret}&response={$response}");
        $captcha_success = json_decode($ret);
        $html->logger->debug( 'g-captcha return : '.print_r($captcha_success,true) );
       
        if ($captcha_success->success == false) {
            $html->logger->debug('Captcha verification failed IP['. $_SERVER['REMOTE_ADDR'] .']' . $captcha_success->{'error-codes'}[0] );
            $return = array (
				'title' => L::errors_ERROR,
				'stateStr' => L::errors_captchaFail . '[' . $captcha_success->{'error-codes'}[0] . ']',
				'state' => 0,
			);
            $httpError = 400;
            $httpErrorStr = ' Bad Request';
            //send back to browser
            header('HTTP/1.1 ' . $httpError . $httpErrorStr);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($return);
            exit();
        }
    }
    
    
    
    /* 
     * Check if required args are there
     */
    if (!isset($_POST['subject']) || !isset($_POST['sender']) || !isset($_POST['body']) || !isset($_POST['origin']) )
    {
        $html->logger->debug('Bad or missing args supplied, stop sending email');
        $return = array (
				'title' => L::errors_ERROR,
				'stateStr' => L::errors_badArgs,
				'state' => 0,
			);
        $httpError = 400;
        $httpErrorStr = ' Bad Request';
        //send back to browser
        header('HTTP/1.1 ' . $httpError . $httpErrorStr);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($return);
        exit();
    }
    
    // check email validity
    if ( !filter_var($_POST['sender'], FILTER_VALIDATE_EMAIL) )
    {
        $html->logger->debug('Bad email address');
        $return = array (
				'title' => L::errors_ERROR,
				'stateStr' => L::errors_badEmail,
				'state' => 0,
			);
        $httpError = 400;
        $httpErrorStr = ' Bad Request';
        //send back to browser
        header('HTTP/1.1 ' . $httpError . $httpErrorStr);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($return);
        exit();
    }
    
    switch($_POST['origin'])
    {
        case 'cave':
            $bcc = explode( ',' , $html->getConfigElement('smtp_cave_edit_recipients') );
            break;
            
        default:
            $bcc = explode( ',' , $html->getConfigElement('smtp_general_inquiry_recipient') );
            break;
    }
    

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
    $mail->setFrom($html->getConfigElement('smtp_sender'), 'Varcave Contact');
    //Set an alternative reply-to address
    $mail->addReplyTo($html->getConfigElement('smtp_sender'), 'Varcave Contact');
    //Set who the message is to be sent to
    $mail->addAddress( strtolower($_POST['sender']), strtolower($_POST['sender']) );
    
    foreach ($bcc as $key => $contactInfo)
    {
        $mail->addBcc(strtolower($contactInfo), strtolower($contactInfo) );
    }
    
    //Set the subject line
    $mail->Subject = $_POST['subject'];
    
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
    $mail->isHTML(false);
    $mail->Body = $_POST['body'];
    
    //Attach files if any
    if( isset($_FILES) && !empty($_FILES) )
    {
        $html->logger->debug('Request file attachment to email : ' . print_r($_FILES, true) );
        
        //check is total item size not too large
        $totalsize = array_sum($_FILES['files']['size']);
        if( $totalsize > (int)$html->getConfigElement('smtp_max_attach_global_size') * 1000 )
        {
            $html->logger->error('Unable to send email : too large attachments');
            $return = array (
				'title' => L::errors_ERROR,
				'stateStr' => L::contact_tooLargeFiles,
				'state' => 0,
			);
            $httpError = 400;
            $httpErrorStr = ' Bad Request';
            jsonWrite(json_encode($return), $httpError, $httpErrorStr);
        }
        
        //check individual item for size
        $sizes = $_FILES["files"]['size'];
        foreach ($size as $key => $size)
        {
            if( $size > (int)$html->getConfigElement('smtp_max_attach_size') * 1000 )
            {
                $html->logger->error('Unable to send email : too large attachment');
                $return = array (
                    'title' => L::errors_ERROR,
                    'stateStr' => L::contact_tooLargeFile . ': [' . $_FILES["files"]['name'][$key] . ']',
                    'state' => 0,
                );
                $httpError = 400;
                $httpErrorStr = ' Bad Request';
                jsonWrite(json_encode($return), $httpError, $httpErrorStr);
            }
        }
        
         //then attach the file for sending
        foreach($_FILES['files']['error'] as $key => $error)
        {
            if ($error == UPLOAD_ERR_OK)
            {
                $newname = cleanStringFilename($_FILES["files"]['name'][$key]) ;
                
                $html->logger->debug('add file [ ' . $_FILES["files"]['tmp_name'][$key] . '] to mail as [' . $newname . ']');
                $mail->addAttachment( $_FILES["files"]['tmp_name'][$key], $newname );
            }
            else
            {
                 $html->logger->error('Unable to attach file to email ERROR : ' . $error . ']' );
            }
        }
    }
     
    //send the message, check for errors
    if (!$mail->send()) 
    {
        $html->logger->error('Unable to send email:' . $mail->ErrorInfo);
        $return = array (
				'title' => L::errors_ERROR,
				'stateStr' => L::contact_msgNotSent,
				'state' => 0,
			);
        $httpError = 500;
        $httpErrorStr = ' Internal Server Error';
        //send back to browser
        jsonWrite(json_encode($return), $httpError, $httpErrorStr);
    }
    else
    {
        $html->logger->debug('Email sent successfully');
        $return = array (
				'title' => '☻',
				'stateStr' => L::contact_msgSent,
				'state' => 1,
			);
        $httpError = 200;
        $httpErrorStr = ' OK';
        //send back to browser
        jsonWrite(json_encode($return), $httpError, $httpErrorStr);
    }
    

}


?>
