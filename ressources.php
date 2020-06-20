<?php

require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveAuth.class.php');
require_once ('lib/varcave/varcaveCave.class.php');
require_once ('lib/varcave/functions.php');
require_once ('lib/php-i18n/i18n.class.php');



$htmlstr = '';
$html = new VarcaveHtml(L::pagename_ressources);
$auth = new VarcaveAuth();

$logger = $auth->logger;

$acl = $auth->getacl('8e9d7d52-f061-4021-8109-cc48fbfe0e61');
if ( !$auth->isSessionValid() ||  !$auth->isMember( $acl[0]) )
{
    $logger->error('editcave.php : user try to access unauthentified' . 'IP : '. $_SERVER['REMOTE_ADDR'] );
    $html = new VarcaveHtml(L::errors_ERROR);
    $html->stopWithMessage(L::errors_ERROR, L::errors_pageAccessDenied, 401, 'Unauthorized ');
}


//computing ressources files for later utilization
$fileGroups = $html->getFilesRessources();


// if `get`  method without arg show fles to user.
// if `post` method, we handle file saving and db update
if( strtolower($_SERVER['REQUEST_METHOD']) == 'get' )
{	
	$logger->debug('User start files ressources display');
	//show a small upload form for Admin users
    $acl = $auth->getacl('ade8fdde-1e7c-4abd-9ead-99787a13f099');
	if ( $auth->isSessionValid() && $auth->isMember($acl[0]) )
	{
		
		$logger->debug('adding upload form ');
		$htmlstr .= '<div id="ressources-upload">';
		$htmlstr .= '  <div class="loadingSpiner"><i class="fas fa-spinner fa-pulse fa-3x"></i></div>';
		$htmlstr .= '  <h2 class="inline-block" id="ressources-form-title">' . L::ressources_add_title .'</h2>';
		$htmlstr .= '  <span class="fas fa-caret-right fa-lg ressources-toggleform"></span>';
		$htmlstr .= '  <div class="ressources-toggleform-hint italic">' . L::ressources_clicktoshowform . '</div>';
		$htmlstr .= '  <div id="ressources-uploadform">';
		$htmlstr .= '    <label for="display_group">' . L::table_files_ressources_field_display_group . '</label><input type=text maxlength="75" value="" id="display_group"></input>';
		$htmlstr .= '    <label for="display_name">' . L::table_files_ressources_field_display_name . '</label><input type=text maxlength="30" value="" id="display_name"></input>';
		$htmlstr .= '    <label for="description">' . L::table_files_ressources_field_description . '</label><textarea type=text value="" id="description"></textarea>';
		$htmlstr .= '    <input type="file" id="file">';
		$htmlstr .= '    <button class="pure-button" disabled id="ressources-savefile">' . L::general_save . '</button>';
		$htmlstr .= '  </div>';
		$htmlstr .= '</div>';
        
        $logger->debug('adding generate all gpx option');
        $htmlstr .= '<div id="ressources-genGpx-container">';
        $htmlstr .= '  <h2 id="ressources-title-genGPX">' . L::ressources_titleGenGPX .'</h2>';
        $htmlstr .= '  <button id="ressources-genGPX">' . L::ressources_genGPX . '</button>';
        $htmlstr .= '</div>';
	}
	
	$htmlstr .= '<div id="available-ressources">';
	$htmlstr .= '<h2>' . L::ressources_user_title .'</h2>';
	
	//now for each ressources theme we process data for display :
	foreach($fileGroups as $key=>$value)
	{
		$htmlstr .= '<div id="ressources-displayGroup-' . strtolower( $value['display_group'] ) . '">';
		$htmlstr .= '<h4 class="ressources-displayGroup-title">' . strtoupper( $value['display_group'] ) . '</h4>';
		$htmlstr .= '<div class="ressources-displayGroup">';
		
		
		
		$logger->debug('get data from db:' . print_r($value,true) );
		
		$array_display_name = str_getcsv( $value['display_name'] );
		$array_filepath = str_getcsv( $value['filepath'] );
		$array_description = str_getcsv( $value['description'] );
		$array_id = str_getcsv( $value['indexid'] );
		$array_creation = str_getcsv( $value['creation_date'] );
		$array_accessrights = str_getcsv( $value['access_rights'] );
		
		$i = 0;
		while ($i < count($array_display_name) )
		{
			$id = $array_id[$i];
			$fileinfo = pathinfo($array_filepath[$i]);
			$icon = getFaIcon( $fileinfo['extension'] );
			$filename = $fileinfo['basename'];
			
			//Build a button to show a delete icon if user have necessary access rights
			$deleteFile = '';
			$acl = $auth->getacl('ade8fdde-1e7c-4abd-9ead-99787a13f099');
			if ( $auth->isSessionValid() || $auth->isMember($acl[0]) )
			{
				$logger->debug('adding delete file icon');
				$deleteFile  = '<div class="ressources-item center-txt">';
				$deleteFile .= '  <span class="fas fa-trash-alt fa-lg ressources-deletefile" data-id="' . $id . '"></span>';
				$deleteFile .= '</div>';
			}
			
			$htmlstr .= '<div class="ressources-fileitem">';
			$htmlstr .= '  <div id="ressources-filelink-' . $id . '">';
			$htmlstr .= '   <span class="' . $icon . ' fa-4x" ></span>';
			$htmlstr .= '  </div>';
			$htmlstr .= '  <div class="ressources-item"><a href=" '.$array_filepath[$i] .'">' . $array_display_name[$i]. ' </a></div>';
			//$htmlstr .= '  <div class="ressources-item">' . . '  </div>';
			$htmlstr .= '  <div class="ressources-item italic">' . $array_description[$i]. '  </div>';
			$htmlstr .= '  <div class="ressources-item italic"><small>' . L::ressources_added . ':' . date('d/m/Y', $array_creation[$i]). '</small></div>';
			
			//show a button to delete file 
			$htmlstr .= $deleteFile;
			
			$htmlstr .= '</div>'; //ressources fileitem
		$i++;
		}
		$htmlstr .= '</div>'; // class = ressources-displayGroup
		$htmlstr .= '</div>'; // id=ressources-displayGroup-xxxxx
	}
	$htmlstr .= '</div>';//end available-ressources">';
	
	$htmlstr .= '<script src="lib/varcave/ressources.js"></script>';
	$htmlstr .= '<script src="lib/varcave/common.js"></script>';
	$htmlstr .= '<script src="lib/jqueryui/jquery-ui-1.12.1/jquery-ui.js"></script>';
	$htmlstr .= '<link rel="stylesheet" href="lib/jqueryui/jquery-ui-themes-1.12.1/themes/base/jquery-ui.css" />';
}
elseif( strtolower($_SERVER['REQUEST_METHOD']) == 'post' && isset($_POST['action'] ) )
{
	try
	{
		$logger->info('User start files ressources update');
		
		//initialyze some var that are returned to user
		$lastinsertid = '';
		$faIcon = '';
		$httpFile ='';
		
		 $acl = $auth->getacl('ade8fdde-1e7c-4abd-9ead-99787a13f099');
		if ( !$auth->isSessionValid() && !$auth->isMember($acl[0]) )
		{
			throw new exception('Action denied, user is not member of [' . $acl[0] . ']');
		}
		
		if($_POST['action'] == 'add')
		{
			$logger->info('Adding file');		
			
			if ($_FILES['file']['error'] == UPLOAD_ERR_OK)
			{
				$displayName = $html->PDO->quote($_POST['display_name']);
				$displayGroup = $html->PDO->quote($_POST['display_group']);
				$description = $html->PDO->quote($_POST['description']);
				$accessRights = $html->PDO->quote($_POST['access_rights']);
				$creatorID = $html->PDO->quote($_SESSION['indexid']);
				
				$filename = cleanStringFilename( basename($_FILES['file']['name']) );
				$localStoredir = __DIR__ . '/' . $html->getConfigElement('ressources_stor_dir') ;
				$localFile = $localStoredir . '/' . $filename;
				$httpFile = $html->getConfigElement('ressources_stor_dir') . '/' . $filename;
				
				//generate a new filename if already exists. just add some random nbr
				if( file_exists($localFile) )
				{
					$id = rand (1000, 9999);
					$newFilename = $id . '_' .  $filename;
					$localFile = $localStoredir . '/' . $newFilename;
					$httpFile = $html->getConfigElement('ressources_stor_dir') . '/' . $newFilename;
				}

				
				$q = 'INSERT INTO ' . $html->getTablePrefix() . 'files_ressources
							(display_name,display_group,filepath,description,creator,access_rights) 
							VALUES(' . $displayName . ',' . $displayGroup . ',' . $html->PDO->quote($httpFile) .  ',' . $description . ',' . $creatorID . ',' . $accessRights .')';
				
				
				if( !is_writable($localStoredir) )
				{
					throw new Exception(L::errors_readonlydir . ':' . $localStoredir);
				}
				
				//finaly move files
				$logger->debug('move file to [' . $localFile . ']' );
				move_uploaded_file($_FILES['file']['tmp_name'], $localFile);
				$html->PDO->query($q);
				$lastinsertid = $html->PDO->lastinsertid();
				
				//get pathinfo extension to fetch correponding fa icon
				$fInfo = pathinfo($filename); 
				$faIcon = getFaIcon($fInfo['extension']);
				
			}
            
            //prepare data feedback
            $return = array(
                'title' =>L::general_edit,
                'stateStr'=> L::ressources_fileaddedsuccess,
                'newid' => $lastinsertid,
                'newfile' => $httpFile, 
                'actionType' => $_POST['action'],
                'faIcon' => $faIcon,
                'deleted' => 'true',
                'display_name' => $_POST['display_name'],
                'display_group' => $_POST['display_group'],
                'description' => $_POST['description'],
            );
            $httpError = 200;
            $httpErrorStr = ' OK';
            jsonWrite(json_encode($return), $httpError, $httpErrorStr);
			
		}
		elseif($_POST['action'] == 'delete')
		{
			$logger->info('delete file :' . $_POST['id']);
			
			$q = 'SELECT filepath from '  . $html->getTablePrefix() . 'files_ressources WHERE indexid=' . $html->PDO->quote($_POST['id']);
			$selPdoStmt = $html->PDO->query($q);
			
			//check if file is writable before delete
			$f = $selPdoStmt->fetch(PDO::FETCH_ASSOC);
			$logger->debug('try to delete [' . __DIR__ . '/' . $f['filepath'] . ']');
			if(!is_writable ( __DIR__ . '/' . $f['filepath'] ) )
			{
				$logger->error('file not writable[' . __DIR__ . '/' . $f['filepath'] . ']');
				throw new exception(L::errors_readonlyfile . '[' . __DIR__ . '/' . $f['filepath'] . ']' );
			}
			//delete from disk
			unlink(__DIR__ . '/' . $f['filepath']);
			
			//delete from db
			$q = 'DELETE FROM ' . $html->getTablePrefix() . 'files_ressources WHERE indexid=' . $html->PDO->quote($_POST['id']);
			$delPdoStmt = $html->PDO->query($q);
		}
		elseif($_POST['action'] == 'buildgpx')
		{
            ini_set('max_execution_time', 60);
            try{
                $cave = new varcaveCave();
                $gpxdata = $cave->createAllGPXKML('gpx');
                $date = date_create();
                $prefix = date_format($date, 'Y-m-d_His');
                $filepath = './ressources/'. $prefix . '_coords.gpx';
                if( !file_put_contents ( $filepath, $gpxdata) ){
                    $logger->debug('Unable to create file in ressources dir');
                    throw new exception('file upload error');
                }
                
                //update Database
                $lastInsert = $cave->addFilesRessources(L::ressources_gpxFile, 'SIG', $filepath, L::ressources_gpxCavesFile, $_SESSION['uid'], '');
                
                //send back to browser OK state
                $return = array(
                    'title' =>L::general_edit,
                    'stateStr'=> "build complete",
                    'newid' => $lastInsert,
                    'faIcon' => 'fas fa-map-marked-alt',
                    'actionType' => 'add',
                    'display_name' => L::ressources_gpxFile,
                    'description' => L::ressources_gpxCavesFile,
                    'display_group' => 'SIG',
                    'newfile' => $filepath,
                    
                );
                $httpError = 200;
                $httpErrorStr = ' OK';
                jsonWrite(json_encode($return), $httpError, $httpErrorStr);
            }
            catch( exception $e){
                $return = array(
                    'title' => L::errors_ERROR,
                    'stateStr'=> $e->getmessage() ,
                );
                $httpError = 500;
                $httpErrorStr = 'Internal error';
            }
            
            
		}
		else
		{
			
		}
		//prepare data feedback
		$return = array(
            'title' =>L::general_edit,
            'stateStr'=> L::ressources_fileaddedsuccess,
            'newid' => $lastinsertid,
            'newfile' => $httpFile, 
            'actionType' => $_POST['action'],
            'faIcon' => $faIcon,
            'deleted' => 'true',
		);
		$httpError = 200;
		$httpErrorStr = ' OK';
		
	}
	catch(exception $e)
	{
		$logger->error('edit ressource files failed.');
		$logger->debug($e->getmessage() );
		$logger->debug('Original query : ' . $q);
		$return = array(
			'title' => L::errors_ERROR,
			'stateStr'=> L::editcave_fail,
			'state' => 1,
		);
		$httpError = 400;
		$httpErrorStr = ' Bad Request';
		
	}
	
	

	jsonWrite(json_encode($return), $httpError, $httpErrorStr);
	exit();
}
else
{
	$htmlstr .= '<h2>' . L::errors_ERROR . '</h2>' . L::errors_methodNotSupported;
}

$html->insert($htmlstr,true);
echo $html->save();
?>
