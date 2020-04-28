<?php

require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveAuth.class.php');
require_once ('lib/varcave/varcaveUsers.class.php');


$auth = new varcaveAuth();
$varcave = new varcave();
$logger = $auth->logger;

$htmlstr = '';
$html = new VarcaveHtml(L::pagename_editsiteconfig);


if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
{
	$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $redirect);
	//echo 'vous allez être redirigé vers une connexion sécurisée :<br>'. $redirect; 
	exit();
}

$acl = $auth->getacl('a45f34efc-536f-4a31-a5e6-e2a8b24cdda');
if ( !$auth->isSessionValid() || !$auth->isMember($acl[0]))
{
    $logger->error('editcave.php : user try to access unauthentified');
    $logger->error('IP : '. $_SERVER['REMOTE_ADDR']);
    $html = new VarcaveHtml(L::errors_ERROR );
    $htmlstr .= '<h2>' . L::errors_ERROR . '</h2>';
    $htmlstr .= L::errors_pageAccessDenied . '.';
    $html->insert($htmlstr,true);
    echo $html->save();
    exit();
}

if( ($_SERVER['REQUEST_METHOD']) == 'GET')
{  //dsiplay default edit site configuration page
	$html->logger->info('User access edit site configuration page');
	

	//If advanced mode set, all configuration settings are available (use with caution :) )
	if( isset($_GET['advanced']) )
	{
		$logger->info('Advanced mode is set');
		//link to toggle simple mode
		$htmlstr .= '<div id="siteconfig-modeswitch"><a href="/siteconfig.php?simple=true">' . L::siteconfig_changetosimple . '</a></div>';
		$varcave->fetchConfigSettings(true, true);
		$configItems = $varcave->getAllConfigElements();
	}
	else	
	{
		//link to toggle to advanced mode
		$htmlstr .= '<div id="siteconfig-modeswitch"><a href="/siteconfig.php?advanced=true">' . L::siteconfig_changetoadvanced . '</a></div>';
		$varcave->fetchConfigSettings(false, true);
		$configItems = $varcave->getAllConfigElements();
	}
	
	$htmlstr .= '<h2>' . L::siteconfig_title . '</h2>';
	$htmlstr .= '<div class="loadingSpiner"><i class="fas fa-spinner fa-pulse fa-3x"></i></div>';
    
    //reordering configItems by itemGroup
    $newconfigItems = array();
    foreach($configItems as $key => $value)
    {
        $itemGroup = $value['configItemGroup'];
        //check if array index exists
        if (! isset($newconfigItems[ $itemGroup ]) )
        {
            // create news entry for configItemGroup
            // remove index configItemGroup
            unset($value['configItemGroup']);
            
            // copy array data
            $newconfigItems[ $itemGroup ][] = $value;
        }
        else
        {
            unset($value['configItemGroup']);
            // copy array data
            $newconfigItems[ $itemGroup ][] = $value;
        }
    }
    
    foreach($newconfigItems as $itemGroup => $data)
	{
        
        // Create a new title for current itemgroup 
         $htmlstr .= '<h3>' . $itemGroup . '</h3>';
        foreach($data as $key => $value)
        {
           
            //set a personalized input box size depending string lenght
            $cssInputClass = 'siteconfig-input-short';
            if(strlen($value['configItemValue']) >= 10 )
            {
                $cssInputClass='siteconfig-input-mid';
            }
            
            if(strlen($value['configItemValue']) >= 50 )
            {
                $cssInputClass='siteconfig-input-long';
            }
            $L='L';
            $dspName = 'siteconfighelp_' . $value['configItem'] . '_dsp';
            $hlp = 'siteconfighelp_' . $value['configItem'] . '_hlp';
            
            $htmlstr .= '<div class="siteconfig-row" >';
            $htmlstr .= '  <span class="col-1 siteconfig-csstooltip">';
            $htmlstr .= '    <span class="siteconfig-csstooltiptxt"> ' . constant($L . '::' . $hlp) . '</span>' ; //item help
            $htmlstr .= '    <span>' . constant($L . '::' . $dspName) . ': </span>' ; //item name
            $htmlstr .= '  </span>';
            $htmlstr .= '  <span class="col-2">'; //item value
            $htmlstr .= '    <input type="text" name="' . $value['configItem'] . '" data="name" class="' . $cssInputClass .'" value="' . $value['configItemValue'] . '"></input>';
            $htmlstr .= '    <input type="hidden" name="' . $value['configIndexid'] . '" data="id" value="' . $value['configIndexid'] . '"></input>';
            $htmlstr .= '  </span>';
            $htmlstr .= '  <span class="col-3">'; //save buton
            $htmlstr .= '  </div>';
            $htmlstr .= '</span>';
        }

	}
	$htmlstr .= '<script src="lib/varcave/siteconfig.js"></script>';
	$htmlstr .= '<script src="lib/jqueryui/jquery-ui-1.12.1/jquery-ui.js"></script>';
	$htmlstr .= '<link rel="stylesheet" href="lib/jqueryui/jquery-ui-themes-1.12.1/themes/base/jquery-ui.css" />';
	$htmlstr .= '</div>';

	$html->insert($htmlstr,true);
	echo $html->save();
}
else
{ //Handling ajax with post request
	
	try
	{
		$logger->info('User request update of website configuration : [' . $_POST['itemname'] . '],id[' . $_POST['itemid'] . ']');
		
		//check minimal args requirement
		if(!isset($_POST['itemname']) || !isset($_POST['itemid']) || !isset($_POST['itemvalue']))
		{
			throw new exception(L::errors_badArgs);
		}
		
		$logger->debug('Update value is:[' . $_POST['itemvalue'] .']');
		
		//update DB
		//$id = $varcave->PDO->quote($_POST['itemid']);
		//$value = $varcave->PDO->quote($_POST['itemvalue']);
		$varcave->setConfigSettings($_POST['itemid'], $_POST['itemvalue']);
		
		//preparing info back to user
		$return = array(
                    'title' => L::edit,
                    'stateStr'=> L::editcave_success,
                    'newVal' => htmlentities($_POST['value']),
                    );
		$httpError = 200;
		$httpErrorStr = ' OK';
		
	}
	catch(Exception $e)
	{
		$logger->error('Update fail : '. $e->getmessage() );
		$return = array(
			'title' => L::errors_ERROR,
			'stateStr'=> L::editcave_fail,
			'state' => 0,
			);
		$httpError = 500;
		$httpErrorStr = ' Internal server error';
	}
	
	jsonWrite(json_encode($return, JSON_UNESCAPED_SLASHES), $httpError, $httpErrorStr);
	exit();
	
	
}


?>
