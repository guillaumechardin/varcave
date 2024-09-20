<?php

require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveAuth.class.php');
require_once ('lib/varcave/varcaveCave.class.php');
require_once ('lib/varcave/varcaveUsers.class.php');
require_once ('lib/varcave/functions.php');
require_once ('lib/php-i18n/i18n.class.php');



$htmlstr = '';
$html = new VarcaveHtml(L::pagename_ressources);
$auth = new VarcaveAuth();
$users = new VarcaveUsers();

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
$groupList = $users->getGroupsList(false);

// if `get`  method without arg show fles to user.
// if `post` method, we handle file saving and db update
if( strtolower($_SERVER['REQUEST_METHOD']) == 'get' )
{	
	$logger->debug('User start files ressources display');
	//show a small upload form for Admin users
    $acl = $auth->getacl('ade8fdde-1e7c-4abd-9ead-99787a13f099');
	if ( $auth->isSessionValid() && $auth->isMember($acl[0]) ){
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
        
        //groups access mgmt
        $htmlstr .= '   <fieldset>';
        $htmlstr .= '     <legend>' . L::usermgmt_groups  . '</legend>';
        $htmlstr .= '     <label for="ressources-addpermission">' . L::table_users_field_groups . '</label>';
        $htmlstr .= '     <select multiple name="ressources-addpermission" id="ressources-addpermission">';
        foreach($groupList as $key=>$group)
        {
            $htmlstr .= '  <option value="' . $group[1] . '">' . $group[1] .'</option>';
        }
        $htmlstr .= '     </select>';
        $htmlstr .= '   </fieldset>';
		
        $htmlstr .= '    <button class="pure-button" disabled id="ressources-savefile">' . L::general_save . '</button>';
		$htmlstr .= '  </div>';
		$htmlstr .= '</div>';
        //add a js var to show a warning for script timeout
        $htmlstr .= '  <script>var debuglevel="' . $html->getLogLevel() . '";</script>';
        
        $logger->debug('adding generate all gpx option');
        $htmlstr .= '<div id="ressources-genGpx-container">';
        $htmlstr .= '  <h2 id="ressources-title-genGPX">' . L::ressources_titleGenGPX .'</h2>';
        $htmlstr .= '  <button id="ressources-genGPX">' . L::ressources_genGPX . '</button>';
        $htmlstr .= '</div>';
	}
    else{
        //add a js var to normal users to prevent js script load failure
        $htmlstr .= '  <script>var debuglevel="undefined";</script>';
    }
	
	$htmlstr .= '<div id="available-ressources">';
	$htmlstr .= '<h2>' . L::ressources_user_title .'</h2>';
	
	//now for each ressources display_group we process data :
	foreach($fileGroups as $display_group => $displayGroupVal){
        $htmlstr .= '<div id="ressources-displayGroup-' . strtolower( $display_group ) . '">';
		$htmlstr .= '<h4 class="ressources-displayGroup-title">' . strtoupper( $display_group ) . '</h4>';
        $htmlstr .= '<div class="ressources-displayGroup">';
        foreach($displayGroupVal as $key => $value){
            $logger->debug('get data from db:' . print_r($value,true) );            
            $id = $value['indexid'];;
            $fileinfo = pathinfo($value['filepath']);
            $icon = getFaIcon( $fileinfo['extension'] );
            $filename = $fileinfo['basename'];
            
            //Build a button to show a delete icon if user have necessary access rights
            $deleteFile = '';
            $acl = $auth->getacl('ade8fdde-1e7c-4abd-9ead-99787a13f099');
            if ( $auth->isSessionValid() && $auth->isMember($acl[0]) )
            {
                $logger->debug('adding delete file icon');
                $deleteFile  = '<div class="ressources-item center-txt">';
                $deleteFile .= '  <span class="fas fa-trash-alt fa-lg ressources-deletefile" data-id="' .  $value['indexid'] . '"></span>';
                $deleteFile .= '</div>';
            }
            
            $htmlstr .= '<div class="ressources-fileitem">';
            $htmlstr .= '  <div id="ressources-filelink-' .  $value['indexid'] . '">';
            $htmlstr .= '   <span class="' . $icon . ' fa-4x" ></span>';
            $htmlstr .= '  </div>';
            $htmlstr .= '  <div class="ressources-item"><a href="'. $value['filepath'] .'">' . $value['display_name'] . ' </a></div>';
            //$htmlstr .= '  <div class="ressources-item">' . . '  </div>';
            $htmlstr .= '  <div class="ressources-item italic">' . $value['description'] . '  </div>';
            $htmlstr .= '  <div class="ressources-item italic"><small>' . L::ressources_added . ':' . date('d/m/Y', $value['creation_date']). '</small></div>';
            
            //show form to set access rights
            $acl = $auth->getacl('ade8fdde-1e7c-4abd-9ead-99787a13f099');
            if ( $auth->isSessionValid() && $auth->isMember($acl[0]) ){
            
                $htmlstr .= '  <form data-ressources-rights-id="'. $value['indexid'] . '" id="ressource-rights-'. $value['indexid'] . '">';
                $currRessourceRights = explode(',', $value['access_rights']);
                $htmlstr .= '  <fieldset><legend>' . L::ressources_file_access_rights . '</legend>';
                foreach($groupList as $key => $group){
                    $checked = '';
                    foreach($currRessourceRights as $key => $currGroup){
                        if ($group[1] == $currGroup){
                            $checked = 'checked';
                            //break;
                        }
                        
                    }
                    $htmlstr .= '<div class="ressources-input-right">';
                    $htmlstr .= '  <label  for="' . $group[1] . '">' . $group[1] .'</label>';
                    $htmlstr .= '  <input value="' . $group[1] . '" type="checkbox" id="' . $group[1] . '" name="' . $group[1] . '" ' .  $checked .'>';
                    $htmlstr .= '</div>';
                    
                }
                $htmlstr .= '    </fieldset>';
                $htmlstr .= '  </form>';
            }
            
            //show a button to delete file 
            $htmlstr .= $deleteFile;
            
            $htmlstr .= '</div>'; //ressources fileitem
            
		
        }
        $htmlstr .= '</div>'; // class = ressources-displayGroup
        $htmlstr .= '</div>'; // id=ressources-displayGroup-xxxxx
	}
	$htmlstr .= '</div>';//end available-ressources">';
	
	$htmlstr .= '<script src="lib/varcave/ressources.js"></script>';
	$htmlstr .= '<script src="lib/varcave/common.js"></script>';
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
		if ( !$auth->isSessionValid() || !$auth->isMember($acl[0]) ){
			throw new exception('Action denied, user is not member of [' . $acl[0] . ']');
		}
		
		if($_POST['action'] == 'add'){
			$logger->info('Adding file');		
			
			if ($_FILES['file']['error'] == UPLOAD_ERR_OK){
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
                'access_rights' => $_POST['access_rights'],
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
            ini_set('max_execution_time', 120);
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
                $lastInsert = $cave->addFilesRessources(L::ressources_gpxFile, 'SIG', $filepath, L::ressources_gpxCavesFile, $_SESSION['uid'], 'users');
                
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
		elseif($_POST['action'] == 'updaterights'){
            $q ='none'; //compatibility if exception is thrown below.
            $cave = new varcaveCave();
            $logger->info('update rights for ressource file :' . $_POST['id']);
            //convert json rights data value to string
            $accessrights = json_decode($_POST['accessrights']);
            $accessrights = implode(',', $accessrights);
            if( !$cave->setFilesRessourcesRights($_POST['id'], $accessrights) ){
				throw new exception(L::errors_ERROR . ' update rights failed' );
            }
            $return = array(
                'title' =>L::general_edit,
                'stateStr' => 'update OK');
            $html->writeJson($return);
        }
        else
		{
            $q= 'none';//compatibility if exception is thrown below.
			throw new exception(L::errors_methodNotSupported);
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
