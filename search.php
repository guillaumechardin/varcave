<?php
require_once (__DIR__ . '/lib/varcave/varcaveHtml.class.php');
require_once (__DIR__ . '/lib/varcave/varcaveAuth.class.php');
require_once (__DIR__ . '/lib/varcave/varcaveCave.class.php');

$htmlstr = '';

$html = new VarcaveHtml(L::pagename_search);


$cave = new varcaveCave(); 
$auth = new varcaveAuth();
$logger = $cave->logger;

const DATATABLES_JS_CSS = '
	<script src="lib/varcave/search.js"></script>
	<script src="lib/varcave/datatables-i18n.php"></script>
	<script src="lib/jqueryui/jquery-ui-1.12.1/jquery-ui.js"></script>
	<link rel="stylesheet" href="lib/jqueryui/jquery-ui-themes-1.12.1/themes/base/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="lib/Datatables/DataTables-1.10.18/css/dataTables.jqueryui.min.css"/>
	<link rel="stylesheet" type="text/css" href="lib/Datatables/Select-1.2.6/css/select.jqueryui.min.css"/>
	<script type="text/javascript" src="lib/Datatables/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="lib/Datatables/DataTables-1.10.18/js/dataTables.jqueryui.min.js"></script>
	<script type="text/javascript" src="lib/Datatables/Select-1.2.6/js/dataTables.select.min.js"></script>
';


//add some js vars from i18n and other
$htmlstr .= '<script>';
$htmlstr .= '  var search_totalDepth = "' .  L::search_totalDepth . '";';
$htmlstr .= '  var search_totalLength = "' . L::search_totalLength . '";';
if( isset($_SESSION['datatablesMaxItems']) ){
    $htmlstr .= '  var maxSearchResults = "' . $_SESSION['datatablesMaxItems'] . '";';
    $htmlstr .= '  console.log("found session"+maxSearchResults);';
}
else{
    $htmlstr .= '  var maxSearchResults = ' . $html->getconfigelement('maxSearchResults_default') . ';';
}


$htmlstr .= '</script>';

if(!IsNullOrEmptyArray($_GET) && isset($_GET['search']) ) {
	/*
	 * return all caves from bd $_GET['search'] should be == "all"
	 */
	$logger->debug('search.php : input from GET, search=all' );
	
	$htmlstr .= '<h2>Rechercher des cavités</h2>';
	$htmlstr .= '<div id="resultTableDiv">';
    $htmlstr .= '<div class="loadingSpiner"><i class="fas fa-spinner fa-pulse fa-3x"></i></div>';
	$htmlstr .= '<table id="tableSearch" class="display">';
	$htmlstr .= '</table>';
	$htmlstr .= '</div>'; //end resultTableDiv

    //run post AJAX request on page load to search all caves
	$htmlstr .= "\n<script>";
	$htmlstr .= '$(document).ready(function()
	{
        $(".loadingSpiner").show();
		var form_data = new FormData();
		form_data.append("type_indexid", ">");
		form_data.append("value_indexid", 0);
		$.ajax({
				type: "post",
				url: "search.php",
				processData: false,
				contentType: false,
				data: form_data,
				dataType: "json",
				success: searchSuccess,
				error: searchError,
				
		});
	});';
	$htmlstr .= '</script>';
	
	$htmlstr .= DATATABLES_JS_CSS;
	
	$html->insert($htmlstr,true);
	echo $html->save();
	
}
elseif( !IsNullOrEmptyArray($_POST) )
{
    set_time_limit(60); //change to 60s for long research list because geting cave data (name, location, area, depth...) with selectByGuid take around 20ms for each cave
    
	/*
	 * search  caves from DB 
	 * else we use $_POST or $_GET from form to search specific caves 
	 * $_GET is only used when coming from quickSearch input field
	 */
	$logger->debug('Computing search request from user input' );
	$searchTerms = array();
	$logger->debug('input is from form' );
	$logger->debug('$_POST : ' . print_r($_POST, true) );
	$search = $_POST; //keep $_post intact

	/*
	 * parse  $search array to get value set by user
	 * and compute query to retrieve infos from db 
	 */
	foreach($search as $key=>$value)
	{
		
		if ( $value == ''  | strpos($key, 'type_') !== false ) //we do not process the value if empty or is the type of search. This last can be computed later
		{
			continue;
		}
		elseif( strpos($key, 'value_') !== false  ) //if the current field is the value
		{
			$fieldName = substr($key, 6); //trim  the 6th first char of  "value_myvalue to find field name
			if ( substr($key, -8,-1) == 'BETWEEN') // type field is not autopopulated when search type is between
			{
				$searchTerms[] = array(
								'field' => $fieldName, 
								'value' => $value,
								'type'=> 'BETWEEN', //defining type field
							);
				
			}
			else
			{
				$fieldName = substr($key, 6); //trim  the 6th char of  "value_myvalue
				$searchTerms[] = array(
									'field' => $fieldName, 
									'value' => $value,
									'type'=> $_POST['type_'.$fieldName],
								);
			}
		}
		else
		{
			$logger->debug('Computing failed. User input:' . print_r($_POST,true) );
			$return = array(
				'title' => L::errors_ERROR,
				'stateStr'=> L::search_unsupported,
				'state' => 1,
				);
			$httpError = 400;
			$httpErrorStr = ' Bad Request';
			
			//set last search for later use (even if search is bad)
			//$_SESSION['lastSearch'] = $_POST;
			
			jsonWrite(json_encode($return), $httpError, $httpErrorStr);
			exit();
		}
	}

	/*
	 * use the previouly computed/formated query 
	 */
	try 
	{
		//search caves from user input
        //$searchInput, $sortField, $ascDesc = 'ASC', $limitOffset = 0,$limitMax = 9999999, $noSaveSearch = false
		$list = $cave->search($searchTerms, 'name');
        
        //save search terms for later use
        $_SESSION['lastSearch'] = $_POST;
        
		if (!$list)
		{
			throw new Exception ("Empty search");
		}
		$caveList = $list[0]->fetchall(PDO::FETCH_ASSOC);
		
		//get current cols i18n, ONSEARCH define the search infos (which cols to retrive)
		// and their corresponding localization name (if any)
		$i18nColNames = $cave->getI18nCaveFieldsName('ONSEARCH');
		//print_r($i18nColNames);
        
		/*
		 * filtering colnames to send back to user. We do not send indexid col
		 */
        //$searchTableFields = array('guidv4','name','maxDepth','length','town','topographer');
        $searchTableFields = array_map('trim',  explode(',', $cave->getConfigElement('returnSearchFields') ) );
		$i18nColNamesFilter = array();
		
        foreach ($searchTableFields as $key => $field)
		{
			//get indexid of $field in $i18nColNames
			$key = array_search($field, array_column($i18nColNames, 'field'));
            //echo $field . 'is at i18nColNames'. $key;
			
			$i18nColNamesFilter[$field] = $i18nColNames[$key]['display_name'];
		}
        //adding 'static' column header keep this line here to preserve column order
        $i18nColNamesFilter['action'] = L::search_actionCol;
	}
	catch( Exception $e)
	{
		//empty search only error :  $list[1]=0 to output json whith no result found message.
		/* this simulate behavior of caveClass->search() method
		 * by setting $list[1] to 0
		 */
		$logger->debug($e->getmessage() );
		$list[1] = 0;
	
	}
	
	/*
	 * Adding for each cave a button to open a new window for display.php
	 * This can be improve on another loop
	 */
    // add ability to edit each row/cave if permited
    $addEditPermit = false;
	$acl = $auth->getacl('b3c16122-c6cb-417f-a0a8-b981f09acb37');  //same ACL as editcave.php
	if ($auth->isSessionValid() && $auth->isMember($acl[0]))
	{
        $addEditPermit = true;
    }
    
    $cave = new VarcaveCave();
    $i= 0;
	foreach($caveList as $arrIndex => $valArr)
	{	
        //$start = microtime(true);  //for optimization purpose
    
        //$res = $cave->selectByGUID( $valArr['guidv4'] );
        $res = $valArr;

        // add data to list
        foreach($searchTableFields as $idx => $colname){
            $caveList[$arrIndex][$colname] = $res[$colname];
        }
         
        // add permited actions
        $caveList[$arrIndex]['action'] = '<div class="showCaveDetails"><a class="pure-button showCaveBtn" href="display.php?guid=' . $res['guidv4'] . '">' . L::search_showCaveButton . '</a></div>';
        if($addEditPermit){
            $caveList[$arrIndex]['action'] .= '<div class="showCaveDetails"><a class="pure-button editCaveBtn" href="editcave.php?guid=' .$res['guidv4'] . '">' . L::search_editCaveButton . '</a></div>';
            $caveList[$arrIndex]['action'] .= '<div class="showCaveDetails"><a class="pure-button copyCaveBtn" href="newcave.php?srcguid=' . $res['guidv4'] . '">' . L::search_copyCaveButton . '</a></div>';
        }
        //$end =  microtime(true) -  $start ;
         //echo '['. $i++ . '(' .  round($end,4) . 'ms)] ';
	}

	$httpError = 200;
	$httpErrorStr = 'OK';
	
	if($list[1] == 0)
	{
		
		$httpError = 400;
		$httpErrorStr = ' Bad Request';
		$return = array(
				'title' => L::errors_WARNING,
				'stateStr'=> L::search_noResults,
				'state' => 1,
				);
		
	}
	else
	{
		$return = array (
			"caves"=>$caveList,
			"colsName" => $i18nColNamesFilter,
			"totalDepth" => $list[2],
			"totalLength" => $list[3],
			);
		
	}
	
	
	
	//send back to browser
	$json = json_encode($return,JSON_FORCE_OBJECT);
	jsonWrite($json, $httpError, $httpErrorStr);
	exit();
}
else
{
	/*
	 * Show the form to invite user for some input
	 * The input is sent back to this page as ajax POST and
	 * computed to retrive info and send it back to user as json
	 * 
	 * This part use $_GET[quicksearch] to perform search when user input some info into 
	 * the quicksearch field
	 * 
	 * If $_GET['loadPrevSearch'] is set, user seems to get back from a previously selected cave.
	 * So we display a result table with the last results.
	 * /
	 
	/*
	 * get back from cave display.php when user click on "back to serach button"
	 * display the result table
	 */
	$resumeSearchJs  = false;
	if( isset($_GET['loadPrevSearch']))
	{
		$logger->debug('lastSearch : ' .  print_r($_SESSION['lastSearch'],true) );
		if(isNullOrEmptyArray($_SESSION['lastSearch']) )
		{
			//empty last search
			header('Location: search.php'); 
		}
		
		$resumeSearchJs .= 'console.log("processing resume search request:");';
		$resumeSearchJs .= 'var form_data = new FormData();';
		foreach($_SESSION['lastSearch'] as $key => $value)
		{
			$resumeSearchJs .= 'form_data.append("'. $key . '","' . $value .'");';
		}

		//$resumeSearchJs .= 'doSearch(formResume);';
		
		$resumeSearchJs .= '

		$.ajax({
				type: "post",
				url: "search.php",
				processData: false,
				contentType: false,
				data: form_data,
				dataType: "json",
				success: searchSuccess,
				error: searchError,
				
		});
		';
	}
	
	$quickSearchJs  = false;
	if( isset($_GET['quicksearch'])  &&  $_GET['quicksearch'] != '')
	{
		$quickSearchJs .= 'console.log("processing resume search request:");';
		$quickSearchJs .= '$(".loadingSpiner").show();';
		$quickSearchJs .= 'var form_data = new FormData();';
		$quickSearchJs .= 'form_data.append("type_name","LIKE");';
		$quickSearchJs .= 'form_data.append("value_name","' . $_GET['quicksearch'] . '");';
		$quickSearchJs .= '$.ajax({
				type: "post",
				url: "search.php",
				processData: false,
				contentType: false,
				data: form_data,
				dataType: "json",
				success: searchSuccess,
				error: searchError,
				
		});
		';
	}
	
	try
	{
		$logger->debug('initiate displaying search form' );
		
		//computing settings
		$reqTotalFieldsToDisplay = 'SELECT COUNT(*) FROM ' . $cave->getTablePrefix() . 'end_user_fields WHERE  	show_on_search  = 1';
		$fieldPDOStmt = $cave->PDO->query($reqTotalFieldsToDisplay);
		$pdoStmt = $fieldPDOStmt->fetch(PDO::FETCH_NUM);
		$totalFieldsToDisplay = $pdoStmt[0];
		
		//number of cols on search page 
		$nbrFlexColsSearchPage = 3;
		$nbrOfFieldsPerCols = ceil($totalFieldsToDisplay / $nbrFlexColsSearchPage);
		
        // *** GET i18n Cols
        //get current cols i18n, ONSEARCH define the search infos (which cols to retrive)
		// and their corresponding localization name (if any)
		$i18nColNames = $cave->getI18nCaveFieldsName('ONSEARCH');
        
        $i18nCols = array();
        ///re-organise element by name
        foreach ($i18nColNames as $key => $fieldData)
		{
            if( empty($fieldData['display_name']) )
            {
                //no translation, keep col header name
                $i18nCols[ $i18nColNames[$key]['field'] ] = $i18nColNames[$key]['field'];
            }
            else
            {
                $i18nCols[ $i18nColNames[$key]['field'] ] = $i18nColNames[$key]['display_name'];
            }
		}
        //*** END
        
		$reqCols = array();
		$limit1 = 0;
		for($i = 1 ; $i <= $nbrFlexColsSearchPage ; $i++)
		{
			$reqCols[] = 'SELECT field,type  FROM ' . $cave->getTablePrefix() . 'end_user_fields WHERE show_on_search = 1 ORDER BY sort_order,field ASC LIMIT ' . $limit1 . ',' . $nbrOfFieldsPerCols; 
			$limit1 = $nbrOfFieldsPerCols * $i - 1;
		}
		
		$logger->debug(basename(__FILE__) . ": Request to get localized col name from db in advanced search \n" . print_r($reqCols,true) );
		
		$htmlstr .= '<h2>Rechercher des cavités</h2>';
		$htmlstr .= '<div id="resultTableDiv">';
        $htmlstr .= '  <div class="loadingSpiner"><i class="fas fa-spinner fa-pulse fa-3x"></i></div>';
		$htmlstr .= '  <table id="tableSearch" class="display" >';
		$htmlstr .= '   <tfoot><tr><th></th></tr></tfoot>';
		$htmlstr .= '  </table>';
		$htmlstr .= '</div>'; //end resultTableDiv
		$htmlstr .= '<form id="caveSearchForm">';
		$htmlstr .= '<div class="flexContainer flexSpaceBetween">';
		
		//counter for col number
		$i = 0;
		foreach($reqCols as $reqCol)
		{
			$logger->debug(basename(__FILE__) . ': populating flexCol nbr : ' . $i);
			$logger->debug(basename(__FILE__). ': start getting info for : ' . $reqCol);
			
            //fecth cols names from DB to display in the html flex table
			$colPDOStmt = $cave->PDO->query($reqCol);
			$fields = $colPDOStmt->fetchall(PDO::FETCH_NUM);
			
			$htmlstr .= '<div class="search-flexCol-'. $i . '">';
			$htmlstr .= '<input type="submit" class="search-btnDoSearch" value="' . L::search_doSearchBtn . '">';
			$i++;
            
			foreach($fields as $field)
			{
				
				$htmlstr .= '<div id="' . $field[0] . '"><span class="searchColLocalName">' . $i18nCols[ $field[0] ]  . '</span> ';
				if ( strstr( $field[1] , 'text') )
				{
					$htmlstr .= '<select name="type_' . $field[0] . '">';
					$htmlstr .= '  <option value="=">=</option>';
					$htmlstr .= '  <option selected value="LIKE">' . L::search_fieldtypeLIKE . '</option>';
					$htmlstr .= '</select>';
					$htmlstr .= '<input type="text" size="25" value="" name="value_' . $field[0] . '">';
				}
				elseif ( strstr( $field[1] , 'bool') )
				{
					$htmlstr .= '<input type="checkbox" name="value_' . $field[0] . '">';
				}
				else //Decimal values and other
				{
					
					$htmlstr .= '<select name="type_' . $field[0] . '">';
					$htmlstr .= '  <option value="=">=</option>';
					$htmlstr .= '  <option value="&lt">&lt;</option>';
					$htmlstr .= '  <option value="&gt">&gt;</option>';
					$htmlstr .= '  <option value="BETWEEN">' . L::search_fieldtypeBETWEEN . '</option>';
					$htmlstr .= '</select>';
					$htmlstr .= '<input type="text"  size="10" name="value_' . $field[0] . '">';
					$htmlstr .= '<input type="text" style="display:none;background:red" value="" size="10" name="value_' . $field[0] . '-BETWEEN1"><span style="display:none" name="value_' . $field[0] . '-SEPARATOR"> AND </span>';
					$htmlstr .= '<input type="text" style="display:none;background:red" value="" size="10" name="value_' . $field[0] . '-BETWEEN2">';
				}
				
				$htmlstr .= '</div>'; //end  $fields[$fieldCounter][0]
				
			}
			$htmlstr .= '<div class="search-btnDoSearch"><button>' . L::search_doSearchBtn . '</button></div>';
			$htmlstr .= '</div>'; // end flexCol
		}
		
		$htmlstr .= '</div>';  //flexContainer
		$htmlstr .= '</form>'; //form caveSearchForm
		$htmlstr .= DATATABLES_JS_CSS;
		
		//add content of $resumeSearchJs if needed
		if($resumeSearchJs !== false)
		{
			$htmlstr .= '<script>' . $resumeSearchJs . '</script>';
		}
		
		//add content of $quickSearchJs if needed
		if($quickSearchJs !== false)
		{
			$htmlstr .= '<script>' . $quickSearchJs . '</script>';
		}
        
        
		
		$html->insert($htmlstr,true);
		
		echo $html->save();
	
	}
	catch(exception $e)
	{
		
		$logger->error('error getting table info : ' . $e->getmessage() );
		
	}
}


?>
