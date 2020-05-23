<?php
require_once(__DIR__ . '/lib/varcave/varcaveHtml.class.php');
require_once(__DIR__ . '/lib/varcave/varcaveCave.class.php');
require_once(__DIR__ . '/lib/varcave/functions.php');

$htmlstr = '';

$auth = new varcaveAuth();
$cave = new varcaveCave;
$logger = $cave->logger;



$acl = $auth->getacl('c47d51c4-62c4-5f40-9047-c466388cc52b');
if ( !$auth->isSessionValid() ||  !$auth->isMember( $acl[0]) )
{
    $logger->error('display.php : user try to access unauthentified');
    $html = new VarcaveHtml(L::errors_ERROR);
    $htmlstr .= '<h2>' . L::errors_ERROR . '</h2>';
    $htmlstr .= L::errors_pageAccessDenied . '.';
    $html->insert($htmlstr,true);
    echo $html->save();
    exit();
}


if (isset($_GET['guid']) )
{	
	try
	{
		$caveData = $cave->selectByGUID($_GET['guid'], 0, false);

	}
	catch (Exception $e)
	{
		$html = new VarcaveHtml(L::errors_ERROR);
		$htmlstr .= '<h1>'. L::errors_ERROR . ' : ' . L::errors_CONTACTWEBSITEOWNER . '</h1>';
		$html->insert($htmlstr,true);
		echo $html->save();
		exit();
	}
	
	if ( !$caveData )
	{
		//cave selection result is empty.
		$html = new VarcaveHtml(L::errors_ERROR);
		$htmlstr .= '<h1>' . L::errors_ERROR . '2</h1>';
		
		$htmlstr .= '<i class="fas fa-exclamation-triangle"> </i> ' . L::display_inexistantCaveGuid;
		$html->insert($htmlstr,true);
		echo $html->save();
		exit();
		
	}
	
	$html = new VarcaveHtml(L::pagename_display . ' ' . $caveData['name']);
	
	
	if($html->getConfigElement('stats') )
	{
		$cave->stats_exists($caveData['indexid']);
		$cave->updateStats($caveData['indexid']);
		
	}
	
    $acl = $auth->getacl('91562650-629a-4461-aa38-e9e5c7cbd432');
	if ( $auth->isSessionValid() &&  $auth->isMember( $acl[0]) )
	{
		$logger->debug('Admin icons added to display.php');

		$adminBar = '<a id="display-adminBar" title="' . L::edit . '" class="fa-3x" href="editcave.php?guid=' . $caveData['guidv4'] . '">';
		$adminBar .= '  <i  class="fas fa-edit"></i>';
		$adminBar .= '</a>';
	}
	else
	{
		$logger->debug(basename(__FILE__). ' *NO* admin icons added');
		$adminBar ='';
	}
	
	$htmlstr .= '<div class="displayTitleWrapper genFlexContainer">';
    $htmlstr .= '  <a href="search.php?loadPrevSearch=1" title="' . L::returnToSearch . '"class="fa-3x" id="returnToList"><i class="fas fa-arrow-circle-left"></i></a>';
	if ( isset($_SESSION['nextPreviousCaveList']) )
	{
		
		$htmlstr .= '  <span id="previous" data-caveGuid="' . $caveData['guidv4'] . '"  title="' . L::previous . '" class="fa-3x"><i class="fas fa-chevron-left "></i></span>';
		$htmlstr .= '  <h1>'. $caveData['name'] . '</h1>';
		$htmlstr .= '  <span  id="next" data-caveGuid="' . $caveData['guidv4'] . '"  title="' . L::next . '"class="fa-3x"><i class="fas fa-chevron-right"></i></span>';
	}
	else
	{
	   /*
		* Direct access to cave with guid specified. 
		* isset($_SESSION['nextPreviousCaveList'] is not set no recent search done.
		* SO no cave navbar
		*/
		$htmlstr .= '  <h1>'. $caveData['name'] . '</h1>';
        $htmlstr .= '  <span  id="display-dummynext"></span>'; //add a dummy next item to set right space between icons
	}
    //add some icons to cave
		$htmlstr .= $adminBar;
		$htmlstr .= '    <div class="fa-3x display-gpx-dwnld" data-guid="' . $caveData['guidv4'] . '">
		                  <span class="fa-layers fa-fw">
							<i class="fas fa-map-marker-alt" ></i>
							<span class="fa-layers-counter display-gpxicon">GPX</span>
						  </span>
						  </div>';
        $htmlstr .= '   <div class="fa-3x display-pdf-dwnld" data-guid="' . $caveData['guidv4'] . '">
		                  <span class="fas fa-file-pdf">
						  </span>
                        </div>';
        $htmlstr .= '   <div class="fa-3x display-send-msg" data-guid="' . $caveData['guidv4'] . '">
		                  <span class="fas fa-envelope"></span>
                          <script>
                            var subject = \'' . L::display_updatecave . ' : '. $caveData['caveRef'] . '\';
                            var newmessage = \'' . L::email_newmessage . '\'
                            
                            var maxfilesize = "' . $html->getConfigElement('smtp_max_attach_size') * 1000 . '";
                            var maxtotalfilessize = "' . $html->getConfigElement('smtp_max_attach_global_size') * 1000 . '";
                            var infoRequired = "' . L::errors_inforequired . '";
                            var send = "' . L::email_send . '";
                            var newmessage = "' . L::email_newmessage . '";
                            var mailUseCaptcha = "' . $html->getConfigElement('mail_use_captcha') . '";
                            var email_usermail = "' . L::email_usermail . '";
                            var email_subject = "' . L::email_subject . '";
                            var email_yourmessage = "' . L::email_yourmessage . '";
                            var email_attachfiles = "' . L::email_attachfiles . '";
                            var contact_fileSizeNotice = "' . L::contact_fileSizeNotice . ' ' . round($html->getConfigElement('smtp_max_attach_size')/1024,1) . ' ' . 'Mo.";
                            var contact_TotalFileSizeNotice = "' . L::contact_TotalFileSizeNotice . ' ' . round($html->getConfigElement('smtp_max_attach_global_size')/1024,1) . ' ' . 'Mo.";
                            var captchaPubKey = "' . $html->getConfigElement('captcha_public_key') . '"; 
        
                          </script>
                          <script src="lib/varcave/contact.js"></script>
                        </div>';
	
	$htmlstr .= '</div>'; // end displayTitleWrapper
	
	
	try
	{
		//getting list of fields to display
		$fields = $cave->getI18nCaveFieldsName('ONDISPLAY');
		
		$htmlstr .= '<h2>' .  L::display_caveSpeleometry . '</h2>';
		$htmlstr .= '<div class="flexContainer flexWrap flexSpaceBetween">';

		foreach($fields as $subArray)
		{
			if( empty( $caveData[ $subArray['field'] ] ) )
			{ 
				//skip empty fields
				continue;
			}
			/**
			 * subArray is defined as: 
			 *    $array( 
			 * 			 [field]  => non localized fieldname,
			 *           [display_name] => localized name,
			 * 			 [type] => text);
			 **/
			
			/*
			 * changing to human readable some info like bool(1) as YES or bool(0) = NO
			 */
			if ( isset( $subArray['type'] ) && strstr( $subArray['type'] , 'bool') )
			{
				if ( $caveData[ $subArray['field'] ] == 1) 
				{
					$caveData[ $subArray['field'] ] = L::_yes ;
				}
				else
				{
					$caveData[ $subArray['field'] ] = L::_no ;
				}
			}
			
			/*
			 * Format editDate unixtimestamp to human readable date
			 */
			if($subArray['field'] == 'editDate')
			{
				$caveData[ 'editDate' ] = date('d/m/Y', $caveData[ 'editDate'] );
				
			}
			
				
			$htmlstr .= '<div class="flexColDisplay-0">';
			$htmlstr .= '  <div class="displayItem">' . $subArray['display_name'] . '</div> ';
			$htmlstr .= '  <div class="displayItemValue">' . $caveData[ $subArray['field'] ] . '</div>';
			$htmlstr .= '</div>'; 
		}
		
		
		$htmlstr .= '</div>' ;//flexContainer
	
		/**
		 * Cave access
		 **/
		
		 		
		/*
		 * fetching coords from DB see beelow usage of $coordList
		 * geoJson store multipoint coord in this namespace
		 * Obj->features[0]->geometry->coordinates[0];
		 */
		$coordsObj = json_decode($caveData['json_coords']);
		$coordList = $coordsObj->features[0]->geometry->coordinates;        
		
		$htmlstr .= '<h2>' .  L::display_caveAccessTitle . '</h2>';
		$htmlstr .= '<div class="flexContainer flexWrap">';
		$htmlstr .= '  <div id="displayCaveAccess">';
		$htmlstr .= '    <div class="displayCaveAccessImg">';
        
        //display access sketch if exists
        $sketchAccessArr = $cave->getCaveFileList($caveData['guidv4'], 'sketch_access');
		if ( ! empty($sketchAccessArr ) )
		{
			$htmlstr .= '<img class="displaySketchAccessImg" src="' . $sketchAccessArr[1]. '"></img>';
			$htmlstr .= '<div id="miniMap" style="display:none"></div>';
			$htmlstr .= '<div id="displayOpenMap" href="#">' . L::display_clickForMinimap . '</div>';
			$htmlstr .= '<script> var miniMapHidden=true;</script>';
		}
		else
		{
			$htmlstr .= '<div id="miniMap"></div>';
			$htmlstr .= '<div id="displayOpenMap" href="#">' . L::display_clickForMinimap . '</div>';
			$htmlstr .= '<script> var miniMapHidden=false;</script>';
			
		}
		
		if( isset($_SESSION['geo_api']) && $_SESSION['geo_api'] == 'googlemaps')
		{
			$logger->debug("using googlemaps API");
			$htmlstr .= '<script async defer src="https://maps.googleapis.com/maps/api/js?key=' . $cave->getConfigElement('googlemaps_api_key') . '&callback=initMap"></script>';
			$htmlstr .= '<script src="./lib/varcave/getjsgeoapi.php?caveguid='  . $caveData['guidv4'] . '&api=googlemaps"></script>';
			
		}
		elseif( isset($_SESSION['geo_api']) && $_SESSION['geo_api'] == 'geoportail')
		{
			$logger->debug("using geoportail API");
			$htmlstr .= '<script async defer src="https://maps.googleapis.com/maps/api/js?key=' . $cave->getConfigElement('googlemaps_api_key') . '&callback=initMap"></script>';
			$htmlstr .= '<script src="lib/varcave/getjsgeoapi.php?caveguid='  . $caveData['guidv4'] . '&api=geoportail"></script>';	
		}
		else
		{
			$logger->error("No geo api defined");
			$htmlstr .= '<span style="color: #FF0000;style:italic">UNDEFINED GEOAPI</span><!-- no user map defined -->';
		}
		
        //load jqueryUI for Dialog
		$htmlstr .= '<script src="lib/jqueryui/jquery-ui-1.12.1/jquery-ui.js"></script>';
		$htmlstr .= '<link rel="stylesheet" href="lib/jqueryui/jquery-ui-themes-1.12.1/themes/base/jquery-ui.css" />';
			

		
		$htmlstr .= '    </div>';
		$htmlstr .= '    <div class="sketchAccessTxt">';
        if($caveData['noAccess']){
            $htmlstr .=  '<div id="display-noaccess">' . $cave->getConfigElement('noAccessDisclaimer') . '</div>';
            
        }
		$htmlstr .= '		<span>' . htmlentities($caveData['accessSketchText']) . '</span>';
		
		
		//show cave coords
		$htmlstr .= '  <div class="coordinates" >';
		$htmlstr .= '    <h3 class="inline-block">'.  L::display_caveCoords. '</h3>';
		
		// show options to change coordinates system
        $htmlstr .= '<script src="lib/proj4js/2.5.0/proj4.js"></script>';
		
        $availCoordSyst = $cave->getCoordsSysList();
		
        $htmlstr .= ' <select id="coordSystem">';
		foreach($availCoordSyst as $key => $value)
		{
			$htmlstr .= '<option id="' . $value['name'] . '" value="' . $value['name'] . '">' . $value['display_name'] .'</option>';
            $htmlstr .= '<script src="/lib/varcave/' . $value['js_lib_filename'] . '"></script>';
        }
		$htmlstr .= '</select>';
		
		if ($caveData['random_coordinates'])
		{
			$htmlstr .= '<div class="disclaimRandomCoords red italic">' . L::disclaimRandomCoords . '</div>';
			$htmlstr .= '';
		}
		if ( ! $caveData['coords_GPS_checked'])
		{
			$htmlstr .= '<div class="disclaimCoordsNotChecked">' . L::disclaimCoordsNotChecked . '</div>';
		}
		
		
		//print_r($coordList);
        $htmlstr .= '<script>';
        $htmlstr .= 'var coordinatesList = ' . $caveData['json_coords'] . ';';
        $htmlstr .= '</script>';
		
        $htmlstr .= '<ol id="coordList">';
		foreach ($coordList as $key=>$coord)
		{
			$htmlstr .= '<li data-id="' . $key . '">';
			$htmlstr .= '<span id="x-' . $key . '">X:' . $coord[0] . '</span> '.
                        '<span id="y-' . $key . '">Y:' . $coord[1] . '</span> '.
                        '<span id="z-' . $key . '">Z:' . $coord[2] . '</span>m';
			$htmlstr .= '</li>';
			
		}
		$htmlstr .= '</ol>';
		$htmlstr .= '  </div>'; //coordinates
		$htmlstr .= '</div>';  //sketchAccessTxt
		
		
		$htmlstr .= '  </div>'; //displayCaveAccess
		$htmlstr .= '</div>';//flexContainer
		
		/**
		 * Cave Description
		 **/
		$htmlstr .= '<h2>' .  L::display_caveDescription . '</h2>';
		$htmlstr .= '<div class="flexContainer flexWrap displayShortDescription">';
		$htmlstr .= '	<p>' . htmlentities($caveData['shortDescription']) . '</p>';
		
		if( ! IsNullOrEmptyString($caveData['annex']) )
		{
			$htmlstr .= '<p>' . htmlentities($caveData['annex']) . '</p>';
		}
		$htmlstr .= '</div>'; //flexContainer
		
        
        
		/**
		 * Cave's topos
		 **/
		$cave->logger->info('Getting cave_maps');		 
		$topoArr = $cave->getCaveFileList($caveData['guidv4'], 'cave_maps');
		
		$htmlstr .= '<h2>' .  L::display_caveTopos . '</h2>';
		$htmlstr .= '<div class="displayCaveMaps">';
		
		//increment to set the lightbox element
		$i=0;
		
		//display a max number of $nbrOfRow per row
		//while loop handle row creation
		if( !empty($topoArr) )
		{	foreach($topoArr as $key=>$mapPath)
			{
				$htmlstr .= '<div class="displayCaveMap">';
				$htmlstr .= '  <a  href="' . $mapPath . '" data-lightbox="cave-maps">';
				$htmlstr .= '    <img class="displayCaveMapsImg" src="' . $mapPath . '"></img>';
				$htmlstr .= '  </a>';
				$htmlstr .= '</div>';
				$i++;
			}
		}
		$htmlstr .= '</div>';
		$htmlstr .= '<script src="lib/lightbox/2.10.0/dist/js/lightbox.min.js"></script>';
		$htmlstr .= '<link href="lib/lightbox/2.10.0/dist/css/lightbox.css" rel="stylesheet">';
		$htmlstr .= '</div>';
		

		

        
        /*
         * Cave photos
         */
        
        try
        {
            $cave->logger->info('Getting photos');		 
			$photosArr = $cave->getCaveFileList($caveData['guidv4'], 'photos');
		}
        catch (exception $e)
        {
            $logger->error('display.php : failed to get photos for caveid : ' . $caveData['indexid']);
        }
        
		
		if ( ! isNullOrEmptyArray($photosArr) )
		{   
            $htmlstr .= '<h2>' .  L::display_cavePhotos . '</h2>';
            $htmlstr .= '<div class="displayPhotos">';
			$htmlstr .= '<div class="genFlexContainerWrap">';
			foreach ($photosArr as $photo)
            {
                $htmlstr .= '<div class="cavePhoto">';
                $htmlstr .= '    <img class="displayCavePhotos" src="' . $photo[0] . '"/>';
                $htmlstr .= '<p>' . htmlentities($photo[1]) . '</p>';
                $htmlstr .= '</div>';
            }
            $htmlstr .= '</div>'; //genFlexContainer
            $htmlstr .= '</div>'; //displayPhotos 
			
		}
		else
		{
            //nothing to do right now....
            // $htmlstr .= L::display_noPhotos;
		}
		
        
		
		/**
		 * Search and display Cave documents data
		 **/
		$htmlstr .= '<h2>' .  L::display_caveDocuments . '</h2>';
		$htmlstr .= '<div class="displaySciData">';
		
		$documentsFields = array("biologyDocuments", "documents");
		$i=0; //increment to  columns numbering
		
		foreach ($documentsFields as $key=>$docField)
		{	try
			{
				$allfields = $cave->getI18nCaveFieldsName('ALL');
				$cave->logger->info('Getting biology docs');		 
				$docsArr = $cave->getCaveFileList($caveData['guidv4'], $docField);
			}
			catch (exception $e)
			{
				$logger->error('display.php : failed to get documents : [' . $docField . '] for caveid : ' . $caveData['indexid']);
			}
			
			if ( ! isNullOrEmptyArray($docsArr) )
			{
				//find key in array of localized fields name for current field
				$i18n_key = array_search($docField, array_column($allfields, 'field'));
				
				//1st col or thumb (todo :) )
				$htmlstr .= '<div class="displaySciDataCol-'. $i . '">';
				
				$htmlstr .= '  <h3>' . $allfields[$i18n_key]['display_name'] . '</h3>';
				$htmlstr .= '<ul class="fa-ul">';
				
				//get file extension to get correct icon
				foreach ($docsArr as $key => $doc)
				{
					$fileType = pathinfo($doc);
					$icon = '<i class="' . getFaIcon($fileType['extension'], 'far') . ' fa-2x"></i>';
					$htmlstr .= '<li>' . $icon . ' <a href="' . $doc . '"> ' . basename($doc). '</a></li>';

				}
				$htmlstr .= '</ul>';
			}
			else
			{
				//$htmlstr .= L::display_noBioDocData;
			}
			$htmlstr .= '</div>'; //displaySciDataCol
			$i++;
		}
		
		
		$htmlstr .= '</div>'; //displaySciData
        
        /**
		 * Cave change history
		 **/
		$htmlstr .= '<h2>' .  L::display_caveChangeLog . '</h2>';
		$htmlstr .= '<div class="displayChangeLog">';
        
		$logs = $cave->findLastModificationsLog(100, $caveData['indexid'], 2);
		
		if ( $logs)
		{
			$htmlstr .= '<ul class="fa-ul">';
			foreach ($logs as $caveMods)
			{	
				$htmlstr .= '<div class="caveMod ">';
				$htmlstr .=  '<li><i class="fas fa-edit fa-lg"></i>' . $caveMods['date'] . ' » ' .  $caveMods['chgLogTxt'] . '</li>';
				$htmlstr .= '</div>';
			}
			$htmlstr .= '</ul>';
			
		}
		else
		{
			$htmlstr .= L::nodataavailable;
		}
		$htmlstr .= '</div>';
		$htmlstr .= '<script src="lib/varcave/display.js"></script>';
		
	}
	catch (Exception $e)
	{
		$logger->debug('Unable to fetch fields to display ' . $e->getmessage() . ".\n Query : " . $query);
		$htmlstr .= 'CRITICAL : Unable to fetch fields to display';
	}
		
		
		
}
elseif( isset($_POST['nextPrev'] ) & $_POST['nextPrev'] != '' )
{
	/*
	 * search next/previous cave id from the $_SESSION['nextPreviousCaveList']
	 * this var is updated on each search
	 */
	try
	{
		$logger->debug('Fetching next/previous info in display.php');
		
		if (strlen($_POST['nextPrev']) != 36)
		{
			throw new exception('Bad guid : ' . $_POST['nextPrev'] );
		}
		
		$qIndexid = 'SELECT indexid FROM ' . $cave->getTablePrefix() . 'caves WHERE guidv4=' . $cave->PDO->quote($_POST['nextPrev']);	
		$PDOStmt = $cave->PDO->query($qIndexid);
		
		$results = $PDOStmt->fetch(PDO::FETCH_ASSOC);
		$indexid = $results['indexid'];

		$arrMaxVal = count($_SESSION['nextPreviousCaveList']) - 1; //minus 1 to avoid array decay.
		$currentArrPos = array_keys( $_SESSION['nextPreviousCaveList'],$indexid ) ;
		$currentArrPos = $currentArrPos[0] ;
		
		
		$logger->debug('current cave guid : ' . $_POST['nextPrev'] );
		$logger->debug('Find indexid for cave : ' . $qIndexid );
		$logger->debug('Indexid is : ' . $indexid);
		$logger->debug('Total index entries : ' . ($arrMaxVal + 1 ));
		$logger->debug('found cave at position : ' . $currentArrPos );
		$logger->debug('index list:' . print_r($_SESSION['nextPreviousCaveList'],true) );
		
		if ($arrMaxVal == 0)
		{
			$logger->debug('no next/prev cave : only one cave');
			$values = array( 0 => -1, 1 => -1);
			$ret = json_encode($values,JSON_PRETTY_PRINT| JSON_FORCE_OBJECT) ;
			jsonWrite( $ret );
			//no code will be exectuted after jsonWrite this is and ending function
		}
		elseif ( $currentArrPos <= 0 )
		{
			$prevArrPos = $arrMaxVal;
			$nextArrPos = $currentArrPos + 1;
		}
		elseif ( $currentArrPos >= $arrMaxVal )
		{
			$prevArrPos = $currentArrPos - 1;
			$nextArrPos = 0;
		}
		else
		{
			$prevArrPos = $currentArrPos - 1;
			$nextArrPos = $currentArrPos + 1;
		}
		
	

		$logger->debug('fetching cave info from DB...');
		$qPrev = 'SELECT guidv4,name FROM ' .  $cave->getTablePrefix() . 'caves WHERE indexid=' . $_SESSION['nextPreviousCaveList'][$prevArrPos];
		$qNext = 'SELECT guidv4,name FROM ' .  $cave->getTablePrefix() . 'caves WHERE indexid=' . $_SESSION['nextPreviousCaveList'][$nextArrPos];
		
		$PDOStmt = $cave->PDO->query($qPrev);
		$prev = $PDOStmt->fetch(PDO::FETCH_ASSOC);
		
		$PDOStmt = $cave->PDO->query($qNext);
		$next = $PDOStmt->fetch(PDO::FETCH_ASSOC);
		$logger->debug('Success');
	}
	catch (exception $e)
	{
		$logger->debug('fetching failed : ' . $e->getmessage() );
		$return = array(
				'title' => L::errors_ERROR,
				'stateStr'=> L::display_prevNextFetchOpFailed,
				'state' => 1,
				);
		$ret = json_encode($values,JSON_PRETTY_PRINT| JSON_FORCE_OBJECT) ;
		jsonWrite( $return, 500, 'Bad Request' );
		//no code will be exectuted after jsonWrite this is and ending function
		
	}
	
	$ret = array (
				'previous' => array (
						'guid' => $prev['guidv4'],
						'name' => $prev['name']
						),
				'next' => array (
						'guid' => $next['guidv4'],
						'name' => $next['name']
						),
				);
	$ret =  json_encode($ret,JSON_PRETTY_PRINT);
	$logger->debug('Send back json to user agent : ' . print_r($ret,true) );
	jsonWrite($ret);
	// no code will be executed after jsonWrite
}
else
{
	$html = new VarcaveHtml( L::errors_ERROR);
	$htmlstr .= '<h1>' . L::errors_ERROR . '</h1>';
	$htmlstr .= '<span>' . L::display_selectCave . '</span>';
	
	
}


$html->insert($htmlstr,true);
echo $html->save();

?>
