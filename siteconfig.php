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
{
    //display default edit site configuration page
	$logger->info('User access edit site configuration page');
	
	$htmlstr .= '<div id="siteconfig_tabs">' ; //start jquery tab
    $htmlstr .= '  <ul>';
    $htmlstr .= '    <li><a href="#tab-siteconfig">' . "Configuration du site" . '</a></li>';
    $htmlstr .= '    <li><a href="#tab-EULA">Edition EULA</a></li>';
    $htmlstr .= '  </ul>';
    
    $htmlstr .= '<div class="loadingSpiner"><i class="fas fa-spinner fa-pulse fa-3x"></i></div>';

    $htmlstr .= '<div id="tab-siteconfig">';
	$htmlstr .= '<h2>' . L::siteconfig_title . '</h2>';
    
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
        
        // Title for current itemgroup 
        $htmlstr .= '<h3>' . $itemGroup . '</h3>';
        
        // create the right input type depending ̀ configItemType`
        foreach($data as $key => $value)
        {
            $L='L';
            $dspName = 'siteconfighelp_' . $value['configItem'] . '_dsp';
            $hlp = 'siteconfighelp_' . $value['configItem'] . '_hlp';
            
            //start new row
            $htmlstr .= '<div class="siteconfig-row" >'; ///b
            if($value['configItemType'] === 'bool')
            {
                if( empty($value['configItemValue']) ){
                    $checked = '';
                }
                else{
                    $checked = 'checked';
                }

                $htmlstr .= '  <span class="col-1 siteconfig-csstooltip">';
                $htmlstr .= '    <span class="siteconfig-csstooltiptxt"> ' . constant($L . '::' . $hlp) . '</span>' ; //item help
                $htmlstr .= '    <span>' . constant($L . '::' . $dspName) . ': </span>' ; //item name
                $htmlstr .= '  </span>';
                $htmlstr .= '  <span class="col-2">'; //item value
                $htmlstr .= '    <input type="checkbox" ' . $checked . ' name="' . $value['configItem'] . '">';
                $htmlstr .= '    <input type="hidden" name="' . $value['configIndexid'] . '" data="id" value="' . $value['configIndexid'] . '">';
                $htmlstr .= '  </span>';
                
            }
            elseif($value['configItemType'] === 'list')
            {
                //get a list of current item. Target data are stored in `lists` table.
                $listData = $varcave->getListElements($value['configItem']);
                
                $htmlstr .= '  <span class="col-1 siteconfig-csstooltip">';
                $htmlstr .= '    <span class="siteconfig-csstooltiptxt"> ' . constant($L . '::' . $hlp) . '</span>' ; //item help
                $htmlstr .= '    <span>' . constant($L . '::' . $dspName) . ': </span>' ; //item name
                $htmlstr .= '  </span>';
                $htmlstr .= '  <span class="col-2">'; //item value
                $htmlstr .= '    <select  name="' . $value['configItem'] . '">';
                
                foreach($listData as $key => $listItem )
                {
                    $selected = '';
                    if ( $value['configItemValue'] == $listItem['list_item'] )
                    {
                        $selected = ' selected';
                    }

                    $lstItemHlp = 'L::siteconfighelp_' . $listItem['list_name'] . '_lst' . $listItem['list_item'];
                    $configStrHlp = '';
                    if( defined($lstItemHlp) )
                    {
                        $configStrHlp = ' - ' . constant($lstItemHlp);
                    }
 
                    $htmlstr .= '   <option value="' . $listItem['list_item'] . '"' .  $selected . '>' . $listItem['list_item'] . $configStrHlp . ' </option>';
                }
                $htmlstr .= '    </select> ';
                $htmlstr .= '    <input type="hidden" name="' . $value['configIndexid'] . '" data="id" value="' . $value['configIndexid'] . '">';
                
                $htmlstr .= '  </span>';
                
            }
            elseif($value['configItemType'] === 'dec')
            {
                $htmlstr .= '  <span class="col-1 siteconfig-csstooltip">';
                $htmlstr .= '    <span class="siteconfig-csstooltiptxt"> ' . constant($L . '::' . $hlp) . '</span>' ; //item help
                $htmlstr .= '    <span>' . constant($L . '::' . $dspName) . ': </span>' ; //item name
                $htmlstr .= '  </span>';
                $htmlstr .= '  <span class="col-2">'; //item value
                $htmlstr .= '    <input type="number" name="' . $value['configItem'] . '" data="name" class="siteconfig-input-short" value="' . $value['configItemValue'] . '">';
                $htmlstr .= '    <input type="hidden" name="' . $value['configIndexid'] . '" data="id" value="' . $value['configIndexid'] . '">';
                $htmlstr .= '  </span>';
            }
            elseif($value['configItemType'] === 'longtext'){ 
        
                $htmlstr .= '  <span class="col-1 siteconfig-csstooltip">';
                $htmlstr .= '    <span class="siteconfig-csstooltiptxt"> ' . constant($L . '::' . $hlp) . '</span>' ; //item help
                $htmlstr .= '    <span>' . constant($L . '::' . $dspName) . ': </span>' ; //item name
                $htmlstr .= '  </span>';
                $htmlstr .= '  <span class="col-2">'; //item value
                $htmlstr .= '    <textarea name="' . $value['configItem'] . '" data="name" class="siteconfig-textarea">' . $value['configItemValue'] . '</textarea>';
                $htmlstr .= '    <input type="hidden" name="' . $value['configIndexid'] . '" data="id" value="' . $value['configIndexid'] . '">';
                $htmlstr .= '  </span>';
            }
            else{ 
                /*
                 * it's standard text data
                 * set a personalized input box size depending string lenght
                 */
                $cssInputClass = 'siteconfig-input-short';
                if(strlen($value['configItemValue']) >= 10 )
                {
                    $cssInputClass='siteconfig-input-mid';
                }

                if(strlen($value['configItemValue']) >= 50 )
                {
                    $cssInputClass='siteconfig-input-long';
                }
                $htmlstr .= '  <span class="col-1 siteconfig-csstooltip">';
                $htmlstr .= '    <span class="siteconfig-csstooltiptxt"> ' . constant($L . '::' . $hlp) . '</span>' ; //item help
                $htmlstr .= '    <span>' . constant($L . '::' . $dspName) . ': </span>' ; //item name
                $htmlstr .= '  </span>';
                $htmlstr .= '  <span class="col-2">'; //item value
                $htmlstr .= '    <input type="text" name="' . $value['configItem'] . '" data="name" class="' . $cssInputClass .'" value="' . $value['configItemValue'] . '">';
                $htmlstr .= '    <input type="hidden" name="' . $value['configIndexid'] . '" data="id" value="' . $value['configIndexid'] . '">';
                $htmlstr .= '  </span>';
            }
            
            $htmlstr .= '  </div>'; //end siteconfig-row
        }
	}
	$htmlstr .= '<script src="lib/varcave/siteconfig.js"></script>';
    $htmlstr .= '</div>'; //end tab-siteconfig
    
    $htmlstr .= '<div id="tab-EULA">'; //start tab-EULA
    $htmlstr .= '<script src="/lib/trumbowyg/2.20/trumbowyg.min.js"></script>';

	$htmlstr .= '<link rel="stylesheet" href="lib/trumbowyg/2.20/trumbowyg.min.css">';
    
    $htmlstr .= '<div id="siteconfig-EULA-wrapper">'; //EULA edit wrapper
    $htmlstr .= '<h2>Edition EULA</h2>';
    $htmlstr .= '<p>';
    //$htmlstr .= '  <label for="siteconfig_editEULA">Edition EULA</label>';
    $htmlstr .= '  <textarea id="siteconfig_editEULA">';
    $htmlstr .=    $html->getEULA('fr');
    $htmlstr .= '  </textarea>';
    $htmlstr .= '</p>';
    $htmlstr .= '<button id="siteconfig-eula-save" class="button" >OK</button>';
    $htmlstr .= '</div>'; //end EULA edit wrapper
    $htmlstr .= '</div>'; //end tab-EULA

    $htmlstr .= '</div>' ; //end siteconfig_tabs

	$html->insert($htmlstr,true);
	echo $html->save();
}
else
{ 
    //Handling ajax post request
	
    //post request error
    if( ! isset($_POST['target']))
    {
        $return = array(
            'title' => L::errors_ERROR,
            'stateStr'=> L::general_operation_fail,
            'state' => 0,
            );
        $httpError = 400;
        $httpErrorStr = ' Bad Request ';
    
        jsonWrite(json_encode($return, JSON_UNESCAPED_SLASHES), $httpError, $httpErrorStr);
        exit();
    }

    switch ($_POST['target'])
    {
        case 'updateConfig':
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
                            'title' => L::general_edit,
                            'stateStr'=> L::editcave_success,
                            'newVal' => htmlentities($_POST['itemvalue']),
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
            break;
        
        case 'updateEULA':
            try
            {
                $varcave->setEula($_POST['eulaContent'], 'fr');
            }
            catch(Exception $e)
            {
                $return = array(
                    'title' => L::errors_ERROR,
                    'stateStr'=> L::siteconfig_eula_edit_fail,
                    'state' => 0,
                    );
                $httpError = 500;
                $httpErrorStr = ' Internal server error';

            }

            jsonWrite(json_encode($return, JSON_UNESCAPED_SLASHES), $httpError, $httpErrorStr);
            exit();

            break;
        
        //bad command exit with error
        default:
        $return = array(
            'title' => L::errors_ERROR,
            'stateStr'=> L::general_operation_fail,
            'state' => 0,
            );
        $httpError = 400;
        $httpErrorStr = ' Bad Request ';

        jsonWrite(json_encode($return, JSON_UNESCAPED_SLASHES), $httpError, $httpErrorStr);
        exit();
    }
}
?>
