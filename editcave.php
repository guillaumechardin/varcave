<?php
require_once(__DIR__ . '/lib/varcave/varcaveHtml.class.php');
require_once(__DIR__ . '/lib/varcave/varcaveCave.class.php');
require_once(__DIR__ . '/lib/varcave/functions.php');

$auth = new varcaveAuth();
$caveObj = new varcaveCave();
$logger = $caveObj->logger;

$htmlstr = '';
$pageName = L::pagename_editCave;


if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
{
	$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $redirect);
	//echo 'vous allez être redirigé vers une connexion sécurisée :<br>'. $redirect; 
	exit();
}

$acl = $auth->getacl('b3c16122-c6cb-417f-a0a8-b981f09acb37');
if ( !$auth->isSessionValid() ||  !$auth->isMember( $acl[0]) )
{
    $logger->error('editcave.php : user try to access unauthentified' . 'IP : '. $_SERVER['REMOTE_ADDR'] );
    $html = new VarcaveHtml(L::errors_ERROR);
    $html->stopWithMessage(L::errors_ERROR, L::errors_pageAccessDenied, 401, 'Unauthorized ');
}

if( isset($_GET['guid']) ){
	//display editing form
    try
    {
        $cave = $caveObj->selectByGuid($_GET['guid'], false, false);
        if ($cave === false)
        {
            throw new exception(L::errors_badGuid);
        }
        $logger->debug('Cave guid found for edit page');
        /*
         * guid seems to be ok and result are found.
         * Display form to edit data
         */
        $html = new VarcaveHtml(htmlentities($pageName) . ' : ' . htmlentities($cave['name']));
		
        $htmlstr .= '<h2>' . L::edit . '</h2>';
		$htmlstr .= '<fieldset>';
		$htmlstr .=   '<legend>' . L::editcave_changelogTitle . '</legend>';
		$htmlstr .=   '<input type="text" class="changeloginput edit-changelog-input-text" id="changelogEntry"   placeholder="' . L::editcave_changelogEditexemple . '">';
		//$htmlstr .=     
		$htmlstr .=   '<input type="checkbox" class="changeloginput edit-changelog-input-cbx" id="changelogEntryVisibility" checked="checked">';
	    $htmlstr .=   '<label class="after">' . L::editcave_changelogVisiblity .'</label>';
		$htmlstr .=   '<span class="edit-add-changelog fas fa-plus-square fa-lg"></span> ';
		$htmlstr .= '</fieldset>';
        
        $fieldList = $caveObj->getI18nCaveFieldsName('ONEDIT');
        $chkbxs = array();
        $areas = array();
        $texts = array(); 
		$filesHTML = '';
        $sketchAccessHtml = '';
		$coordsHtml = '';
		
        //set needles to permit edit items from a form.
        $listOfFilesInput = ['biologyDocuments', 'cave_maps', 'documents', 'photos', 'sketch_access'];
        
        //build cave `files` from json
		if(!empty($cave['files']) )
		{
			$filesObj = json_decode($cave['files']);
		}
		
		foreach($fieldList as $fieldInfo)
		{   
			/**
			 * fieldInfo is defined as: 
			 *    $array( 
			 * 			 [field]  => non localized fieldname,
			 *           [display_name] => localized name,
			 * 			 [type] => text);
			 **/
			
			/*
			 * changing to human readable some info like bool(1) as YES or bool(0) = NO
			 */
			if ( strstr( $fieldInfo['type'] , 'bool') )
			{
                $currentField = htmlentities($fieldInfo['field']);
                $fieldType = 'checkbox';
				if ( $cave[ $fieldInfo['field'] ] == 1) 
				{
					$chkbxs[] = '<div class="edit-flexItem"><span class="editDisplayName-Title">'
                     . $fieldInfo['display_name'] 
                     . ': <input class="editDisplayName-checkbox" name="' . $currentField . '" type="checkbox" checked value="1"></span>
                       </div>';
				}
				else
				{
                    $chkbxs[] = '<div class="edit-flexItem"><span class="editDisplayName-Title">'
                      . $fieldInfo['display_name'] 
                      . ': <input  class="editDisplayName-checkbox" name="' . $currentField . '" type="checkbox" value="0"></span>
                     </div>';
				}
			}
            elseif(  $fieldInfo['field'] == 'json_coords' )
            {
                $logger->debug('editcave.php : process field edit elements: json_coords' );
                $coordsHtml = '<div class="edit-flexItem"><span class="editDisplayName-Title">'
                . $fieldInfo['display_name'] . ':</span>';
                $coordsHtml .= '<div>';
                $coordsHtml .=    L::editcave_showCoordsAs;
                
                
                $availCoordSyst = $caveObj->getCoordsSysList();
		
                $coordsHtml .= ' <select id="coordSystem">';
                foreach($availCoordSyst as $key => $value)
                {
                    $coordsHtml .= '<option id="' . $value['name'] . '" value="' . $value['name'] . '">' . $value['display_name'] .'</option>';
                    $coordsHtml .= '<script src="/lib/varcave/' . $value['js_lib_filename'] . '"></script>';
                }
                $coordsHtml .= '</select>';
               


                $coordsHtml .= '</div>';
                
                if ( !empty ($cave['json_coords']) )
                {
                    $coordsHtml .= '<div id="edit-' .  $fieldInfo['field'] . '">';
                    
                    $coordList = json_decode($cave['json_coords']);
                    // print_r($coordList->features[0]->geometry->coordinates);
                    $i=0;
                    foreach($coordList->features[0]->geometry->coordinates as $coord )
                    {
                        $coordsHtml .= '<div class="editCoords" data-elNumber="' . $i . '">'; 
                        $coordsHtml .= '   X:<input type="text" class="coords" data-elNumber="0" value="' . $coord[0] . '" />';
                        $coordsHtml .= '   Y:<input type="text" class="coords" data-elNumber="1" value="' . $coord[1] . '" />';
                        $coordsHtml .= '   Z:<input type="text" class="coords" data-elNumber="2" value="' . $coord[2] . '" />';
                        $coordsHtml .= '  &nbsp<span class="fas fa-trash-alt fa-lg"  id="edit-delCoordSet-' . $i . '"></span>';
                        $coordsHtml .= '</div>';
                        
                        $i++;
                    }
                    $coordsHtml .= '</div>'; //edit-json_coords
                    $coordsHtml .= '  <span id="edit-addItem-' .  $fieldInfo['field'] . '">';
                    $coordsHtml .= '    <i class="fas fa-plus fa-lg"></i>';
                    $coordsHtml .= '  </span>';
                }
                else
                {
                    $coordsHtml .= '<div id="edit-' .  $fieldInfo['field'] . '"></div>';
                    $coordsHtml .= '  <span id="edit-addItem-' .  $fieldInfo['field'] . '">';
                    $coordsHtml .= '    <i class="fas fa-plus fa-lg"></i>';
                    $coordsHtml .= '  </span>';
                }

                $coordsHtml .= '</div>'; //flexItem
                
            }
			//process bioDocs or cave_maps or documents and so on depending on $listOfFilesInput
            elseif( strstr_from_arr($listOfFilesInput, $fieldInfo['field'] ) ) 
            {   

                $currentField = $fieldInfo['field'];
                $logger->debug('editcave.php : process field edit elements:' . $currentField );
    
                $formFile = '  <form name="' . $currentField . '" id="fileSelectorForm-' . $currentField . '" style="display:none" >';
                $formFile .= '    <input  type="file" id="fileSelector-' . $currentField . '"/>';
               // $formFile .= '    <label for="fileSelector-' . $currentField . '">add file</label>';
                $formFile .= '    <span id="sendFile-' . $currentField . '" class="pure-button">OK</span>';
                $formFile .= '  </form>';  
                
                
                
				$curHtml = '<div class="edit-flexItem">';
                $curHtml .= '   <span class="editDisplayName-Title">';
				$curHtml .=         $fieldInfo['display_name'] . ':';
                $curHtml .= '  </span>';
				
				// the file input is empty in db, we show the "add item" icon
                if ( !isset($filesObj->$currentField) || empty($filesObj->$currentField) )
                {
                    //NO data to display, processing next col after adding + symbol
                    $curHtml .= '<div id="edit-' .  $currentField . '"></div>';
                    $curHtml .= '  <span id="edit-addItem-' .  $currentField. '">';
                    $curHtml .= '    <i class="fas fa-plus fa-lg"></i>';
                    $curHtml .= '  </span>';
                    $curHtml .= $formFile;

                    $curHtml .= '</div>'; //flexItem
                }
                else
				{
					//There are some data, show form with cave info, depending on data type( photos, bioDocs...)
                    $curHtml .= '<div id="edit-' .  $currentField . '">';
                    
                   
                    
                    foreach($filesObj->$currentField as $key=>$value )
                    {
						
                        $curHtml .= '<div id="edit-' . $currentField . '-elNumber-' . $key . '">';
                        //photos are in 2 dimentionnal array
						if($currentField == 'photos')
						{
							$fileType = pathinfo($value[0]);
							$fileName = $value[0];
						}
						else
						{
							$fileType = pathinfo($value);
							$fileName = $value;
						}	
						
						$logger->debug('editcave.php : tring to find filetype for:' . $fileType['extension'] . '('. $fileType['basename'] .')' ) ;
                        if ($fileType['extension']  != 'jpg')
                        {
                                $curHtml .= '<i class="' . getFaIcon($fileType['extension'],'far') . ' fa-2x"></i> ' . $fileType['basename'] ;
                        }
						else
                        {
                                $curHtml .= '<i id="edit-rotLeft-' . $currentField . '-' . $key . '" class="fas fa-undo fa-lg"></i>';
                                $curHtml .= '<img class="edit-CaveMini" src="' . $fileName . '" />';
                                $curHtml .= '<i id="edit-rotRight-' . $currentField . '-' . $key . '" class="fas fa-undo fa-flip-horizontal fa-lg"></i>';
                                if($currentField == 'photos')
								{
									 $curHtml .= '<input type="text" class="edit-photoComment" value="' . $value[1] . '" data-elNumber="' . $key . '"/>';
								}
                        } 
                        
                       
                        $curHtml .= '  &nbsp;&nbsp;&nbsp;&nbsp;<span class="fas fa-trash-alt fa-lg" name="' . $currentField . '" id="edit-trash-' . $currentField . '" data-elNumber="' . $key . '"></span> ';
                        $curHtml .= '</div>'; //el-$key
                    }
                    
                    $curHtml .= '</div>'; //edit- $currentField
                    $curHtml .= '  <span id="edit-addItem-' .  $currentField . '">';
                    $curHtml .= '    <i class="fas fa-plus fa-lg"></i>';
                    $curHtml .= '  </span>';
                    $curHtml .= $formFile;
                    $curHtml .= '</div>'; //flexItem
                }
				
				$filesHTML .= $curHtml;

            }
            /*elseif( $fieldInfo['field'] == 'sketchAccessPath' && !empty($cave['sketchAccessPath']) )
            {
                 $sketchAccessHtml = '<div class="edit-flexItem"><span class="editDisplayName-Title">'
                . $fieldInfo['display_name'] . ':</span>';
                $sketchAccessHtml .=  '<span>test</span>';
                $sketchAccessHtml .= '<div class="sketchItem">'; //force new line
                $sketchAccessHtml .= '<i class="fas fa-undo fa-lg" style="margin:0 0.1em 0"></i> <img class="edit-sketchMini" src="' . $cave['sketchAccessPath'] . '" /> <i class="fas fa-undo  fa-lg fa-flip-horizontal "></i>';
                $sketchAccessHtml .= '<span>&nbsp<i class="fas fa-trash-alt fa-lg fa-pull-left"  name="sketchImg"></i></span><form><input type="file" name="sketchImg"> <button>' . L::save . '</button></form> ';
                $sketchAccessHtml .= '</div>';
                
                 $sketchAccessHtml .= '</div>';
                 
            }*/
			elseif( $fieldInfo['type'] == 'text' &&  strlen($cave[ $fieldInfo['field'] ]) > 40 )
            {
                $areas[] = '<div class="edit-flexItem"><span class="editDisplayName-Title">'
                . $fieldInfo['display_name']
                . ': <textarea name="' . $fieldInfo['field'] . '" class="editDisplayName-textArea" rows="10" cols="30">' . $cave[ $fieldInfo['field'] ]
                . '</textarea> </div>';
            }
            else
            {
                $texts[] = '<div class="edit-flexItem"><span class="editDisplayName-Title">'
                 . $fieldInfo['display_name'] 
                 . '</span>: <input name="' . $fieldInfo['field'] . '" class="editDisplayName-textField" type="text" value="' 
                 . $cave[ $fieldInfo['field'] ]  . '"></div>';
            }	
    }

    // creating cols containing  <inputs> field
   
        $htmlstr .= '<div class="genFlexContainerWrap">';
        //$htmlstr .= print_r($texts,true);
        foreach($texts as $text)
        {
          $htmlstr .= $text;
        }
        
        $htmlstr .= $coordsHtml;
        
        foreach($chkbxs as $chkbx)
        {
          $htmlstr .= $chkbx;
        }
     
        
        foreach($areas as $area)
        {
          $htmlstr .= $area;
        }
    
    
        $htmlstr .= $filesHTML;
        $htmlstr .= $sketchAccessHtml;
        
		
		
		
		
		
        $htmlstr .= '</div>'; //genFlexContainerWrap
		
		//edit change log
		$htmlstr .= '<h2>' .  L::display_caveChangeLog . '</h2>';
		$htmlstr .= '<div class="displayChangeLog">';
		$logs = $caveObj->findLastModificationsLog(999, $cave['indexid'], 2);
		
		if ( $logs)
		{
			$htmlstr .= '<ul class="fa-ul">';
			foreach ($logs as $caveMods)
			{	
				//$htmlstr .= '<div id="caveMod-' . $caveMods['indexid'] . '">';
				$htmlstr .=  '<li>';
				$htmlstr .=    '<i class="fas fa-edit fa-lg"></i>' . $caveMods['date'] . ' » ' .  $caveMods['chgLogTxt'];
				$htmlstr .=    ' <span name="changelog" data-elNumber="' . $caveMods['indexid']   . '" id="edit-trash-changelog" class="fas fa-trash-alt"></span>';
				$htmlstr .=  '</li>';
				//$htmlstr .= '</div>';
			}
			$htmlstr .= '</ul>';
		}
		else
		{
			$htmlstr .='no data';
		}
		$htmlstr .= '</div>';
		
		
		//spinner to show db write progress
		$htmlstr .= '<div class="loadingSpiner"><i class="fas fa-spinner fa-pulse fa-3x"></i></div>';
        $htmlstr .= '<script src="lib/jqueryui/jquery-ui-1.12.1/jquery-ui.js"></script>';
        $htmlstr .= '<link rel="stylesheet" href="lib/jqueryui/jquery-ui-themes-1.12.1/themes/base/jquery-ui.css" />';
        $htmlstr .= '<script src="lib/varcave/common.js"></script>';
        $htmlstr .= '<script>var guid="' . $cave['guidv4'] . '"</script>';
        $htmlstr .= '<script src="lib/varcave/editcave.js"></script>';
        $html->insert($htmlstr, true);
        echo $html->save();
        exit();
        
    }
    catch(exception $e)
    {
        $logger->error('editcave.php : guid process failed : [' . $_GET['guid'] . ']');
        $html = new VarcaveHtml($pageName);
        $htmlstr .= '<h2>' . L::errors_ERROR . '</h2>';
        $htmlstr .= htmlentities($e->getmessage() ) . '.';
        $html->insert($htmlstr, true);
        echo $html->save();
    }
   
}
elseif( isset($_POST['update'] ) )
{
	//update bd from input given by user, using ajax query
	$logger->info('editpage.php: cave update requested (' . $_POST['guid'] . ')' );
	$logger->debug('State of user POST input : ' . print_r($_POST, true) );
	
	//update <input text or textarea data for cave
    if(!isset($_POST['guid']) || !isset($_POST['item']) || !isset($_POST['value']) ||  empty($_POST['item'] )  ) 
	{
		//show error if user do not provides sufficients informations
		$logger->error('edit details failed. args error');
		$return = array(
			'title' => L::errors_ERROR,
			'stateStr'=> L::editcave_fail,
			'state' => 1,
			);
		$httpError = 400;
		$httpErrorStr = ' Bad Request';

		jsonWrite(json_encode($return), $httpError, $httpErrorStr);
		exit();
	}
	else
	{
		$logger->debug('update field : ' . $_POST['item'] . ', with value :[' . $_POST['value'] . ']' );
		if ( !isset($_POST['json']) && !isset($_FILES['file']) && !isset($_POST['rotate']) && $_POST['item'] != 'changelog' )
		{
			//this is a normal text input or textarea and checkboxes if all criteria met
			$logger->debug('Processing normal text input');
			try
			{
                if( isset($_POST['checkbox']) )
                {
                    if( ! $caveObj->updateCaveProperty($_POST['guid'], $_POST['item'], $_POST['checkboxValue'] ) )
                    {
                        throw new exception('updateCaveProperty: checkbox fail');
                    }
                }
                else
                {
                    if( ! $caveObj->updateCaveProperty($_POST['guid'], $_POST['item'], $_POST['value'] ) )
                    {
                        throw new exception('updateCaveProperty: text fail');
                    }
                }
                $return = array(
                    'title' => L::edit,
                    'stateStr'=> L::editcave_success,
                    'newVal' => htmlentities($_POST['value']),
                    );
                $httpError = 200;
                $httpErrorStr = ' OK';
            }
			catch (exception $e)
			{
				$logger->error('fail to update db : ' . $e->getmessage() );
				$return = array(
					'title' => L::errors_ERROR,
					'stateStr'=> L::editcave_fail,
					'state' => 1,
					);
				$httpError = 500;
				$httpErrorStr = ' Internal server error';

			}
		}
		elseif( isset($_POST['json']) && !isset($_FILES['file']) && $_POST['item'] != 'changelog' )  //json value to handle like json_coords and files
		{
			$logger->debug('Processing json values like coords and files');
			try
            {
                if( $_POST['item'] == 'jsonCoords' )
                {
					$valueIdx = '';
					$value = '';
					$insertIndex = false;
					if ($_POST['actionType'] == 'modify')
					{
						$valueIdx = $_POST['valueIdx'];
						$value = $_POST['value'];
					}
          
					$logger->info('update Geojson field : ['  . $_POST['coordSetIndex'] . ','. $valueIdx . '] with array index value :[' . $value . ']');
					$logger->info('update action type   : ' . $_POST['actionType'] );
					$insertIndex = $caveObj->updateCaveGeoJsonProperty($_POST['guid'], $_POST['actionType'], $_POST['coordSetIndex'], $valueIdx, $value ) ;
					if( $insertIndex === false )
                    {
                        throw new exception('update geojson fail');
                    }
					
                    $return = array(
							'title' => L::edit,
							'stateStr'=> L::editcave_success,
							'newVal' => htmlentities($_POST['value']),
							'actionType' => $_POST['actionType'],
							'insertIndex' => $insertIndex,
						);
				$httpError = 200;
				$httpErrorStr = ' OK';
                }
                else
                {
                    switch($_POST['actionType'])
					{
						case "add":
							//addin data to the $file json object. $Item is the attribute name and value the resquested value.
							$caveObj->addDataToCaveFileList($_POST['guid'], $_POST['item'], $_POST['value']);
							break;
						case "delete":
							//for delete operation, $value is the index value of the element to remove
							$caveObj->delDataCaveFileList($_POST['guid'], $_POST['item'], $_POST['value'] );
							break;
						case "edit":
							$caveObj->editDataCaveFileList($_POST['guid'], $_POST['item'], $_POST['elNumber'], $_POST['value']);
							break;

						default:
							throw new Exception(L::errors_methodNotSupported);
					}
					
                    $return = array(
						'title' => L::edit,
						'stateStr'=> L::editcave_success,
						'newVal' => htmlentities($_POST['value']),
						'actionType' => $_POST['actionType'],
						);
					$httpError = 200;
					$httpErrorStr = ' OK';
                }
                
                
			}
			catch (exception $e)
			{
				$logger->error('fail to update db : ' . $e->getmessage() );
				$return = array(
					'title' => L::errors_ERROR,
					'stateStr'=> L::editcave_fail . '(' . $e->getmessage() . ')',
					'state' => 1,
					);
				$httpError = 500;
				$httpErrorStr = ' Internal server error';
			}
			
		}
		elseif( isset($_FILES['file']) ) //file input form like documents or cave_maps
        {
            $caveInfo = $caveObj->selectByGuid($_POST['guid']);
            //we want to upload some files
            $logger->info('uploading file');
            $logger->debug(print_r($_FILES,true) );
            
            $fileInfo = pathinfo($_FILES['file']['name']);
            
            //check if file is authorized
            $permitedFileTypes = array(
                    'jpg', 'jpeg',
                    'pdf',
                    'doc', 'docx',
                    'xls', 'xlsx',
                    'png',
                    'zip',
                    'txt','csv'
                    );
                    
            $logger->debug('check if filetype [' . $fileInfo['extension'] . '] is ok  on ' . print_r($permitedFileTypes, true));
            if ( !  strstr_from_arr($permitedFileTypes, $fileInfo['extension'] ) )
            {
                $return = array(
                    'title' => L::errors_ERROR,
                    'stateStr'=> L::errors_badFileType,
				);
                $httpError = 400;
                $httpErrorStr = ' BAD REQUEST';  
                jsonWrite(json_encode($return), $httpError, $httpErrorStr);
                $logger->error('bad filetype');
                exit();
            }
            $logger->debug('filetype ok');
            
            switch($_POST['item'])
            {
               case 'cave_maps':
                    $dstSubDir = 'maps';
                    break;
               case 'photos':
                    $dstSubDir = 'photos';
                    break;
               default:
                    $dstSubDir = 'documents';
                    break;                 
            }
            $logger->debug('set subdir to:[' . $dstSubDir . ']');
            
            
            try
            {
                //move file to destination and update DB
                if ($_FILES['file']['error'] == UPLOAD_ERR_OK)
                {
                    $srcFile = $_FILES['file']['tmp_name'];
                    
                    $dstRootDir = $caveObj->getConfigElement('caves_files_path');
                    $dstName = cleanStringFilename( $_FILES['file']['name'] );
                    $dstFullPath = $dstRootDir . '/' . $caveInfo['guidv4'] . '/' . $dstSubDir  . '/' . $dstName;
                    
                    $logger->info('move uploaded file to [' .  $dstFullPath . ']');
                    
					//change filename if filealready exists to prevent a problem on deletion and have a kind of uniqueness
                    if( file_exists($dstFullPath) )
                    {
                        $dstFullPath = $dstRootDir . '/' . $caveInfo['guidv4'] . '/' . $dstSubDir  . '/' . rand(100,999) . '_' . $dstName;
                    }
                    if( !file_exists( dirname($dstFullPath) ) )
                    {
                        $logger->debug('destination folder do not exists, creating');
                        mkdir( dirname($dstFullPath), 0777, true);
                    }
                    if(! move_uploaded_file($srcFile, $dstFullPath) )
                    {
                        throw new exception('file upload fail');
                    }
                }
				else
				{
					 throw new exception('Error on file upload, upload error $_FILES[file][error] :' . $_FILES['file']['error']);
				}
                
				//update json info in field `files`
				$lastInsertItem = $caveObj->addDataToCaveFileList($_POST['guid'], $_POST['item'], $dstFullPath );
				
				if( $lastInsertItem === false )
                {
                    throw new exception('update json fail on file upload inscription');
                }
                $return = array(
					'title' => L::edit,
					'stateStr'=> L::editcave_success,
					'newVal' => $dstFullPath,
                    'insertIndex' => $lastInsertItem,
					'actionType' => 'add',
                    'extension' => $fileInfo['extension'],
                    'filename' => $fileInfo['filename'].'.'.$fileInfo['extension'],
					);
				$httpError = 200;
				$httpErrorStr = ' OK';
                
			}
			catch (exception $e)
			{
				$logger->error('fail to update db : ' . $e->getmessage() );
				$return = array(
					'title' => L::errors_ERROR,
					'stateStr'=> L::editcave_fail . '(json)',
					'state' => 1,
					);
				$httpError = 500;
				$httpErrorStr = ' Internal server error';
			}
            
            
            
        }
        elseif( isset($_POST['rotate']) && isset($_POST['imgPath']) ) //image rotation only
        {
            $rotate = $_POST['rotate'];
            $imgPath = $_POST['imgPath'];
            
            //extract filename if contain url separator (added previously to force browser refresh)
            if ( $realName = strstr ( $imgPath , '?' , true ) )
            {
                $logger->debug('filepath contains ?');
                $imgPath = $realName;
            }
            
            $logger->info('user request image rotation [' . $imgPath . ']');
            switch($rotate)
            {
                case 'left':
                    $angle = 270;
                    break;
                case 'right':
                    $angle = 90;
                    break;
            }
            // Load the image in mem
            $source = imagecreatefromjpeg($imgPath);
            // Rotation
            $rotate = imagerotate($source, $angle, 0);
            //save to file
            if( imagejpeg($rotate, $imgPath) )
            {
                $logger->info('image rotation successful');
                $return = array(
                    'newPath' => $imgPath . '?'. time(),
                    );
                $httpError = 200;
                $httpErrorStr = ' OK';
            }
            else
            {
                $logger->error('fail to rotate image');
				$return = array(
					'title' => L::errors_ERROR,
					'stateStr'=> L::editcave_fail,
					'state' => 1,
					);
				$httpError = 500;
				$httpErrorStr = ' Internal server error';
                
            }
            
        }
        elseif($_POST['item'] == 'changelog')
		{
			try
			{
				$logger->info('update changelog');
				
				$newEntry = false;
				$logData = 'false';
				switch($_POST['actionType'])
				{
					case 'add':
						$newEntry = $caveObj->AddLastModificationsLog($_POST['guid'], $_POST['value'], $_POST['visility']);
						$logData = $caveObj->findLastModificationsLog(1, $cave['indexid'], 2,true);
						$actionType = 'add';
						break;
					
					case 'delete':
						$caveObj->delLastModificationsLog($_POST['value']);
						$newEntry = 'false';
						$actionType = 'delete';
						break;
					
					default:
						throw new exception ("changelog:" . L::errors_methodNotSupported);
					
				}
               
                $return = array(
                    'title' => L::edit,
                    'stateStr'=> L::editcave_success,
                    'newEntry' => $newEntry,
                    'actionType' => $actionType,
					'logData' => $logData,
                    );
                $httpError = 200;
                $httpErrorStr = ' OK';
            }
			catch (exception $e)
			{
				$logger->error('fail to update db : ' . $e->getmessage() );
				$return = array(
					'title' => L::errors_ERROR,
					'stateStr'=> L::editcave_fail,
					'state' => 1,
					);
				$httpError = 500;
				$httpErrorStr = ' Internal server error';

			}	
		}
		else //cannot process
		{
			$return = array(
				'title' => 'bad type',
				'stateStr'=> 'not json or text, bad args',
				);
			$httpError = 400;
			$httpErrorStr = ' BAD REQUEST';
			
		}
		
		jsonWrite(json_encode($return, JSON_UNESCAPED_SLASHES), $httpError, $httpErrorStr);
		exit();
		
	}
}
else
{    
	header('HTTP/1.1 500 Internal Server Error');
    echo 'Internal Server Error<br>';
	echo 'Bad request to page';
}

?>
