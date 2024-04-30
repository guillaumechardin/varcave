<?php

require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveAuth.class.php');
require_once ('lib/varcave/varcaveUsers.class.php');


$auth = new varcaveAuth();
$users = new varcaveUsers();
$logger = $auth->logger;

if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
{
	$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $redirect);
	//echo 'vous allez être redirigé vers une connexion sécurisée :<br>'. $redirect; 
	exit();
}

if ( !$auth->isSessionValid() || $_SESSION['username'] == 'anonymous')
{
	header('HTTP/1.0 403 Forbidden ');
	$htmlstr = '';
	$html = new VarcaveHtml(L::pagename_myaccount);
	$htmlstr .= '<h2 style="margin:0.2em;">' . L::errors_ERROR . '</h2>';
	$htmlstr .= '<div style="margin: 0.5em 0 0 1em;">' . L::myaccount_authfirst . ' <a href="login.php">login.php</a></div>';
	
	
	$html->insert($htmlstr,true);
	echo $html->save();
	exit('403 FORBIDDEN : please authenticate first.');
	//header('Location: index.php');
}

/*
 * Change user password ONLY
 */
if ( !empty($_POST) && isset($_POST['passwd']) && $_POST['passwd'] != '' )
{
	$authState = true;
	
	//check is sha256 as been sent
	//sha256 is 64char long
	if ( strlen($_POST['passwd']) != 64  )
	{
		$authState = false;
	}
	
	if ($authState === false)
	{
		$return = array (
			'title' => L::myaccount_updatePwdTitle,
			'stateStr' => L::myaccount_updateFailBadData,
			'state' => 0,
 		);
		$httpError = 400;
		$httpErrorStr = ' Bad Request';
	}
	else
	{
		$users = new varcaveUsers();
		
		if ($users->changeUserPwd($_POST['passwd'], $_SESSION['uid']) )
		{
			$return = array (	
				'title' => L::myaccount_updatePwdTitle,
				'stateStr' => L::myaccount_successPwdChg,
				'state' => 1,
			);
			$httpError = 200;
			$httpErrorStr = 'OK';
		}
		else
		{
			$return = array (
				'title' => L::myaccount_updatePwdTitle,
				'stateStr' => L::myaccount_failedPwdChg,
				'state' => 0,
			);
			$httpError = 400;
			$httpErrorStr = ' Bad Request';
		}
	}
	
	//send back to browser
	header('HTTP/1.1 ' . $httpError . $httpErrorStr);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($return);
	exit();
	
}
elseif ( !empty($_POST) && isset($_POST['update'])   && isset($_POST['value']) )
{
	$logger->debug('request preference update' . $_POST['update']);
	
    //check email address validity
    $validMail = true;
    if($_POST['update'] == 'emailaddr' && !filter_var($_POST['value'], FILTER_VALIDATE_EMAIL) ){
        $logger->debug('email invalid');
        $validMail = false;
    }
    
    //$result = $users->changeUserPref($_POST['update'], $_POST['value'], $_SESSION['uid']);
    
	
	if($validMail && $result = $users->changeUserPref($_POST['update'], $_POST['value'], $_SESSION['uid']) )
	{
		$return = array (	
			'title' => L::myaccount_updatePrefTitle,
			'stateStr' => L::myaccount_successPrefChg,
			'state' => 1,
		);
		$httpCode = 200;
		$httpCodeStr = 'OK';
	}
	else
	{
		$return = array (
			'title' => L::myaccount_updatePrefTitle,
			'stateStr' => L::myaccount_failedPrefChg . ' : ' . $_POST['update'] ,
			'state' => 0,
		);
		$httpCode = 500;
		$httpCodeStr = ' Internal Server Error';
	}
	$json = json_encode($return);
	jsonWrite($json, $httpCode, $httpCodeStr);
	exit();

}
elseif( isset($_POST['action']) )
{
	$html = new VarcaveHtml(L::pagename_myaccount);
	switch($_POST['action'])
	{
		case 'toggleCaveToFav':
			$users->logger->debug(__FILE__ . ' : toggle cave to fav');
			$users->favoritesCaveToggle($_POST['guid'], $_SESSION['uid']);
			$state = 'unsaved';
			
			//check if cave is currently saved
			$savedState = $users->isCaveFavorite( $_POST['guid'] );
			if ( $savedState) 
			{
				$state = 'saved';
			}
			$html->writeJson( array('title'=> 'ok','msg'=> 'success', 'state'=> $state) );
			break;
		
		case 'acceptEULA':
			$users->logger->info(__FILE__ . ' : user uid[ ' . $_SESSION['uid'] . '] accepted EULA');
			$users->changeUserPref('EULA_read_on', time(), $_SESSION['uid']);
			$result = $users->changeUserPref('EULA_accepted', '1', $_SESSION['uid']);
			if ( $result ) 
			{
				$html->writeJson( array('title'=> 'ok','msg'=> 'EULA accept success', 'state'=> 'read') );
				break;
			}
			$users->logger->error('Fail to save eula accepted');
			$html->writeJson( array('title'=> 'fail','msg'=> 'EULA accept failed', 'state'=> 'not_ok'), 500, 'FAILED');
			break;

		default:
			$html->writeJson( array('title'=> L::errors_ERROR,'msg'=> L::errors_badArgs),  400, 'Bad Request');
			//end of script
			break;

	}
}
else
{
	/*
	 * TABS definitions
	 */
	$html = new VarcaveHtml(L::pagename_myaccount);
	$htmlstr = '<div id="myaccount_tabs">';
	$htmlstr .= '  <div id="jqUiDialog" ><div id="jqUiDialogContent">  </div></div>';	
	$htmlstr .= '  <ul>';
    $htmlstr .= '    <li><a href="#myaccount_account_info">'. L::myaccount_myaccount . '</a></li>';
    $htmlstr .= '    <li><a href="#myaccount_preferences">' . L::myaccount_preferences . '</a></li>';
    $htmlstr .= '    <li><a href="#myaccount_favorites">' . L::myaccount_favorites_caves . '</a></li>';
	$htmlstr .= '  </ul>';
	//end tabs def

	$htmlstr .= '<div id="myaccount_account_info">';
	$htmlstr .= '  <h1 class="userWelcome">';
	$htmlstr .=      L::myaccount_hello . ' ' . $_SESSION['firstname'] . ' ' . strtoupper($_SESSION['lastname']);
	$htmlstr .= '  </h1> ';
	$htmlstr .= '  <h2>  <i class="fas fa-info-circle"></i> ' . L::myaccount_userSessionInfo .' </h2>';
	$htmlstr .= '  <p><ul>';
	$htmlstr .= '    <li>' . L::myaccount_yourSessionExpire . ' : ' . date("d-m-Y H:i:s", $_SESSION['sessionend']) . '. </li>';
	$htmlstr .= '    <li>' . L::myaccount_yourAccountExpire . ' : ' . date("d-m-Y H:i:s", $_SESSION['expire']) .'. </li>';
	$htmlstr .= '    <li>' . L::myaccount_eulaReadOn . ' : ' . date("d-m-Y H:i:s", $_SESSION['EULA_read_on']) .'. </li>';
	$htmlstr .= '    <li>' . L::myaccount_groupList . ' : ' . $_SESSION['groups'] . '</li>';
	$htmlstr .= '  </ul></p>';     
	
    /*
	 * CHANGE PERSONAL INFO
	 */
    $htmlstr .=  '<h2>' . '<i class="fas fa-user-check"></i> ' . L::myaccount_change_personal_data . '</h2>';
	$htmlstr .= '  <form id="myaccount_personal_data">';
	$htmlstr .= '    <fieldset>';
    $htmlstr .= '      <legend>' . L::usermgmt_identification  . '</legend>';
	$htmlstr .= '      <label for="firstname">' . L::table_users_field_firstname  . '</label>';
    $htmlstr .= '      <input type="text"  name="firstname" id="firstname" value="' . $_SESSION['firstname'] . '"></input>';
	$htmlstr .= '      <label for="lastname">' .  L::table_users_field_lastname  .   '</label>';
    $htmlstr .= '      <input type="text" name="lastname" id="lastname" value="' . $_SESSION['lastname'] . '"></input>';
	$htmlstr .= '      <label for="emailaddr">' .  L::table_users_field_emailaddr  .  '</label>';
    $htmlstr .= '      <input type="text" name="emailaddr" id="emailaddr" value="' . $_SESSION['emailaddr'] . '"></input>';
    $htmlstr .= '    </fieldset>';
    $htmlstr .= '  </form>';
    
    
	/*
	 * CHANGE PASSWORD
	 */
	$htmlstr .= '  <h2>' . '<i class="fas fa-key"></i> ' . L::myaccount_changePwd . '</h2>';
	$htmlstr .= '  <p>';
	$htmlstr .= '  <form id="chgtPasswd">';
    $htmlstr .= '    <fieldset>';
    $htmlstr .= '      <legend>' . L::usermgmt_identification  . '</legend>';
	$htmlstr .=	'        <input type="password"   placeholder="' . L::myaccount_enterPwdHint .'" id="pass1" size="30" maxlength="25" autocomplete="off" value="" />';
	$htmlstr .= ' ';
	$htmlstr .= '        <input type="password" placeholder="' . L::myaccount_confirmPwdHint .'" id="pass2" size="30" maxlength="25" autoc5omplete="off" value=""/>';
	$htmlstr .= '        <p><input type="submit" value="OK"></p>';
    $htmlstr .= '    </fieldset>';
	$htmlstr .= '  </form>';
	$htmlstr .= '  </p>';     
	$htmlstr .= '</div>'; // end account_info
 	
    
	/*
	 * PREFERENCES TABS
	 * CHANGE THEME
	 */
	$htmlstr .= '<div id="myaccount_preferences">';
	$htmlstr .= '  <h2><i class="fas fa-palette"></i> ' . L::myaccount_customInterfaces .' </h2>';
	
	/*
	 * get any folder list from "css/custom" dir
	 * and dysplay a list a selectable entry to user
	 */
	$customCssFolders = __DIR__ . '/css/custom/';
	$availableCustomCss = array();
	if ($handle = opendir($customCssFolders)) 
	{
    
		/*
		 * parsing dir to find settings
		 */
		while (false !== ($entry = readdir($handle) ) )
		{
			if ( $entry != '.' || $entry != '..' || $entry != 'README.TXT' )
			{
				if (file_exists($customCssFolders . '/' . $entry . '/custom.css') )
				{
					$availableCustomCss[] = $entry;
				}
			}
		}
		closedir($handle);
	}
	else
	{
		$htmlstr .= '<b>' . L::myaccount_unableToOpenCssFolder . '</b>';
	}
	
	
	$currentTheme = $users->getUserTheme($_SESSION['uid']);
	//add an empty value to reset to default
	$availableCustomCss[] = '';
	if ( !isNullOrEmptyArray($availableCustomCss) )
	{
		$htmlstr .= '<select id="themeChange">';
		
		
		foreach($availableCustomCss as $cssFolder)
		{
			$selected ='';
			if ($cssFolder == $currentTheme)
			{
				$selected = 'selected';
			}
			$htmlstr .='<option value="' . $cssFolder . '"' . $selected . '>'. $cssFolder . '</option>';
		}
		$htmlstr .= '</select>';

	}
	else
	{
		$htmlstr .= L::myaccount_noThemeAvail;
	}
	
	/*
	 * CHANGE GEOAPI
	 * Fetch db to get a list of available geoAPI to offer to users
	 */
	/*$qGeoApi = 'SELECT name FROM ' . $users->getTablePrefix() . 'list_geo_api WHERE 1';
	$qGeoStmt = $users->PDO->query($qGeoApi);
	$geoAPIs = $qGeoStmt->fetchall(PDO::FETCH_ASSOC);*/
    $geoAPIs = $users->getListElements('default_geo_api');
	
	$htmlstr .=  '<h2><i class="fas fa-globe"></i> ' . L::myaccount_userGeoApi .' </h2>';
	$htmlstr .= '<select id="geo_api">';
		
	//print_r($geoAPIs);
	foreach($geoAPIs as $key => $API)
	{
		
		$selected ='';
		if ($_SESSION['geo_api'] == $API['list_item'])
		{
			$selected = 'selected';
		}
		$htmlstr .='<option value="' . $API['list_item'] . '"' . $selected . '>'. $API['list_item'] . '</option>';
	}
	$htmlstr .= '</select>';
    
    /*
     * CHANGE Max items for datatables items (ie : search table)
     */
    $htmlstr .=  '<h2><i class="fas fa-table"></i> ' . L::myaccount_maxItemsTables .' </h2>';
    $htmlstr .= '<select id="datatablesMaxItems">';
    $itemMaxList = array(5,10,20,40,50,100,150,200,500);
    foreach($itemMaxList as $key => $value)
    {
        $selected ='';
        if ( $_SESSION['datatablesMaxItems'] == $value ) 
        {
            $selected = 'selected';
        }
        $htmlstr .= '  <option value="' . $value . '" ' . $selected . '>' .  $value .'</option>';
    }
    $htmlstr .= '</select>';
	$htmlstr .= '</div>'; // end favorites

	/*
	 * FAVORITES CAVE TAB
	 */
	$htmlstr .= '<div id="myaccount_favorites">';
	$htmlstr .= '<h2>' . L::myaccount_my_fav_caves . '</h2>';
	if( isset($_SESSION['favorites_caves']) && !empty($_SESSION['favorites_caves']) )
	{
		$caveObj = new varcaveCave();
		$results = array();
		foreach($_SESSION['favorites_caves'] as $key => $cave )
		{
			$thiscave = $caveObj->selectByGuid($cave);
			$link = '<a href="display.php?guid='. $cave . '">' . $thiscave['name'] . '</a>';
			$results[]= array($key,$link,'<span data-guid="' . $cave . '" class="myaccount-del-fav"><i class="fas fa-trash delete-favorite"></i></span>');  //<i  class="fas fa-edit">
			
		}
		$htmlstr .= '<table id="myaccount_table_fav_caves" class="display" width="100%"></table>';
		$htmlstr .= '<script> var favCaveData = ' . json_encode($results) . '</script>';
	}
	else
	{
		$htmlstr .= '<p>' . L::myaccount_no_fav_caves . '</p>';
		$htmlstr .= '<script> var favCaveData = [[0]];  </script>';
	}

	$htmlstr .= '</div>'; //end favorites caves

	$htmlstr .= '</div>'; // myaccount_tabs
	$htmlstr .= '</div>'; // end account_info

	// load jquery/JS libs
	//$htmlstr .= '<script src="lib/varcave/datatables-i18n.php"></script>';
	//$htmlstr .= '<link rel="stylesheet" href="lib/jqueryui/jquery-ui-themes-1.12.1/themes/base/jquery-ui.css" />';
	$htmlstr .= '<link rel="stylesheet" type="text/css" href="lib/Datatables/DataTables-1.10.18/css/dataTables.jqueryui.min.css"/>';
	$htmlstr .= '<script type="text/javascript" src="lib/Datatables/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>';
	$htmlstr .= '<script type="text/javascript" src="lib/Datatables/DataTables-1.10.18/js/dataTables.jqueryui.min.js"></script>';
	
	//sha256 for passwords
	$htmlstr .= '<script src="lib/js-sha256/js-sha256.js"></script>';
	//jquery and jquery ui
	$htmlstr .= '<script src="lib/jqueryui/jquery-ui-1.12.1/jquery-ui.js"></script>';
	$htmlstr .= '<link rel="stylesheet" href="lib/jqueryui/jquery-ui-themes-1.12.1/themes/base/jquery-ui.css" />';
	//varcave
	$htmlstr .= '<script src="lib/varcave/myaccount.js"></script>';
}

$html->insert($htmlstr,true);
echo $html->save();



?>
