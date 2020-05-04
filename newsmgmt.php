<?php
require_once('lib/varcave/varcaveHtml.class.php');
require_once('lib/varcave/varcaveAuth.class.php');
require_once('lib/varcave/varcaveNews.class.php');
require_once('lib/varcave/functions.php');


$auth = new varcaveAuth();
$logger = $auth->logger;

$htmlstr = '';
$html = new VarcaveHtml(L::pagename_newsmgmt);

const DATATABLES_JS_CSS = '
	<script src="lib/js-sha256/js-sha256.js"></script>
	<script src="lib/varcave/common.js"></script>
	<script src="lib/varcave/newsmgmt.js"></script>
	<script src="lib/varcave/datatables-i18n.php"></script>
	<script src="lib/jqueryui/jquery-ui-1.12.1/jquery-ui.js"></script>
	<script src="lib/jqueryui/i18n/datepicker-fr.js"></script>
	<link rel="stylesheet" href="lib/jqueryui/jquery-ui-themes-1.12.1/themes/base/jquery-ui.css" />
	
	<link rel="stylesheet" type="text/css" href="lib/Datatables/DataTables-1.10.18/css/dataTables.jqueryui.min.css"/>
	<link rel="stylesheet" type="text/css" href="lib/Datatables/Select-1.2.6/css/select.jqueryui.min.css"/>
	<script type="text/javascript" src="lib/Datatables/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="lib/Datatables/DataTables-1.10.18/js/dataTables.jqueryui.min.js"></script>
	<script type="text/javascript" src="lib/Datatables/Select-1.2.6/js/dataTables.select.min.js"></script>
	
	<script src="/lib/trumbowyg/2.20/trumbowyg.min.js"></script>
	<link rel="stylesheet" href="lib/trumbowyg/2.20/trumbowyg.min.css">
	';

if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
{
	$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $redirect);
	//echo 'vous allez être redirigé vers une connexion sécurisée :<br>'. $redirect; 
	exit();
}

//check if user can acces this page
$acl = $auth->getacl('1abc1ede-a115-4613-9f88-cbc0a86d6778');
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
	

	// get a list of news
	$news = new varcaveNews();
	$data = $news->getNews(0, 1000, true);
	//$data = $news->getNews(0, 5, true);

	//convert some data as human readable
	foreach($data as $key => &$values)
	{
		$dtCreation =  new DateTime('@'.$values[4], new DateTimeZone('UTC'));
		$dtEdit =  new DateTime('@'.$values[5], new DateTimeZone('UTC'));
		
		$dtCreation->setTimezone(new DateTimeZone('Europe/Paris'));
		$dtEdit->setTimezone(new DateTimeZone('Europe/Paris'));
		
		if ($values[1])
		{
			$values[1] = L::_yes;
		}
		else
		{
			$values[1] = L::_no;
		}
		$values[2] = truncateStr( strip_tags($values[2]), 35);
		$values[3] = truncateStr( strip_tags($values[3]), 30);
		$values[4] = $dtCreation->format('Y-m-d H:m:s');
		if(!empty($values[5]) )
		{
			$values[5] = $dtEdit->format('Y-m-d H:m:s'); 
		}
		else
		{
			$values[5] = '---';
		}
		//add col content for action col 
		$values[] = '<button>delete</button>';
	}

	//set some var for Datatables
    $jsData = json_encode($data,   JSON_PRETTY_PRINT);
    if(!$jsData)
    {
        $logger->error('Json enconding failed : ' . json_last_error_msg() );
        $jsData = 'nul';
    }
    
	$htmlstr .= '<script>';
	$htmlstr .= '	var usersData = ' . $jsData  . ';';
	$htmlstr .= '	var colnewsID = "' . L::table_newsmgmt_field_ID . '";';
	$htmlstr .= '	var colnewsDeleted = "' . L::table_newsmgmt_field_Deleted . '";';
	$htmlstr .= '	var colnewsContent = "' . L::table_newsmgmt_field_Content . '";';
	$htmlstr .= '	var colnewsTitle = "' . L::table_newsmgmt_field_Title. '";';
	$htmlstr .= '	var colnewsCreation = "' . L::table_newsmgmt_field_Creation . '";';
	$htmlstr .= '	var colnewsEditdate = "' . L::table_newsmgmt_field_Editdate . '";';
	$htmlstr .= '	var colnewsUsername = "' . L::table_newsmgmt_field_Username . '";';
	$htmlstr .= '	var colnewsFirsname = "' . L::table_newsmgmt_field_Firsname . '";';
	$htmlstr .= '	var colnewsLastname = " ' . L::table_newsmgmt_field_Lastname . '";';
	$htmlstr .= '	var colnewsAction = "' . L::search_actionCol . '";';
	
    // title for Dialog on `add news`
    $htmlstr .= '	var addNewsTitle = "' . L::newsmgmt_addnewsTitle . '";';

	// setup a form to edit/show/delete news
	$htmlstr .=  "\r\n";
	$htmlstr .= '	var formEditnews = ';
	$htmlstr .= '   \'<div id="newsmgmt-form">';
	$htmlstr .= '<fieldset><legend>' . L::table_newsmgmt_field_Title . '</legend>';
	$htmlstr .= '       <input type="text" name="title" id="newsmgmt-form-title" value="" />'; 
	$htmlstr .= '</fieldset>       ';
	
	$htmlstr .= '<fieldset class="newsmgmt-fieldset-delundel"><legend>' . L::newsmgmt_delete_undelete . '</legend>';
	$htmlstr .= '   <input  type="checkbox" name="deleted" id="newsmgmt-form-deleted"/></label>';
	$htmlstr .= '</fieldset>       ';	
	
	$htmlstr .= '       <div name="content" id="newsmgmt-form-content" value=""></div>'; 
	$htmlstr .= '       <input type="hidden" name="id" id="newsmgmt-form-id" value=""/>';
	$htmlstr .= '     </div>\';';
	$htmlstr .= '</script>';

	//add mandatory js scripts and css
	$htmlstr .= DATATABLES_JS_CSS;
	
    $htmlstr .= '<div class="newsmgmt-container-addnews"><span title="' . L::newsmgmt_addnewsTooltip .'" class="fas fa-comments fa-3x" id="newsmgmt-addnews"></span></div>';
    
	//set up the destination table
	$htmlstr .= '<table id="newsmgmt-newstable" class="display" style="width:100%">'; //hardcoded size of 100% for javascript size in px recognition instead of css
	//$htmlstr .= ' <tfoot><tr><th></th></tr></tfoot>';
	$htmlstr .= '</table>';

	$html->insert($htmlstr,true);
	echo $html->save();
}
elseif( ($_SERVER['REQUEST_METHOD']) == 'POST')
{
	$logger->debug('Start News update process');
	try
	{
		$news = new varcaveNews();
	}
	catch (exception $e)
	{
		$logger->error($e->getmessage() );
	}
	
	switch($_POST['action'])
	{
		case 'edit':
			$data =  $news->updateNews($_POST['id'], $_POST['data']);
            
            // if update success send back updated data to user
            if($data)
            {
                $data = $news->getNewsbyID($_POST['id']);
                $dtCreation =  new DateTime('@'.$data['creation_date'], new DateTimeZone('UTC'));
				$dtCreation->setTimezone(new DateTimeZone('Europe/Paris'));
                $dtEdit =  new DateTime('@'. $data['edit_date'], new DateTimeZone('UTC'));
				$dtEdit->setTimezone(new DateTimeZone('Europe/Paris'));
                if ($data['deleted'])
                {
                    $data['deleted'] = L::_yes;
                }
                else
                {
                    $data['deleted'] = L::_no;
                }
                $data['content'] = truncateStr( strip_tags($data['content']), 35);
                $data['title'] = truncateStr( strip_tags($data['title']), 30);
                $data['creation_date'] = $dtCreation->format('Y-m-d H:i:s');
                $data['edit_date'] = $dtEdit->format('Y-m-d H:i:s'); 
            }
            
			break;
		
		case 'add':
			
			if(isset($_POST['data']) && !empty($_POST['data']) )
			{
				// check if $_POST['data'] contains  a valid json object
				// and prepare it
				$dataObj = json_decode($_POST['data']);
				if( ! $dataObj)
				{
					$logger->error( basename(__FILE__) . ' : fail to convert json data while adding news');
					$data = false;
					break;
				}
			}
			else
			{
				$logger->error(basename(__FILE__) . 'malformed _POST[data] arg');
				// $POST contains bad args
				$data = false;
				break;
			}
			
            $insertid =  $news->addNews($dataObj->title, $dataObj->content);
            
			//if insert success, get fresh data and send back to user
			if($insertid)
			{
				$data = $news->getNewsbyID($insertid);
                $dtCreation =  new DateTime('@'.$data['creation_date'], new DateTimeZone('UTC'));
				$dtCreation->setTimezone(new DateTimeZone('Europe/Paris'));
                $data['content'] = truncateStr( strip_tags($data['content']), 35);
                $data['title'] = truncateStr( strip_tags($data['title']), 30);
                $data['creation_date'] = $dtCreation->format('Y-m-d H:i:s');
                $data['edit_date'] = '---';  //no edit date !
				$data['deleted'] = L::_no; // state cannot be deleted on add
			}
		break;
		
		case 'delete':
            $data = '';
		break;
		
		case 'get':
			$data = $news->getNewsbyID($_POST['id']);
            //remove some useless parts
            $data = array(
                'title' => $data['title'],
                'content' => $data['content'],
                'deleted' => $data['deleted'],
                );
		break;
		
		default:
            $data = false;
		break;
		
	}
    
    
    // prepare to send back result to user
	if ( empty($data) )
    {
		$logger->error('News update fail' );
		$return = array(
			'title' => L::errors_ERROR,
			'stateStr'=> L::newsmgmt_updateFail,
			'state' => 0,
			);
		$httpErrorCode = 500;
		$httpErrorStr = ' Internal server error';
	}
    else
    {
        $return = array(
                'title' => L::edit,
                'stateStr'=> L::newsmgmt_editSuccess,
                'userdata' => $data,
                );
        $httpErrorCode = 200;
        $httpErrorStr = ' OK';
    }
    
    //send results
    jsonWrite(json_encode($return, JSON_UNESCAPED_SLASHES), $httpErrorCode, $httpErrorStr);
    exit();
}
else
{
	header('HTTP/1.1 515 ERROR');
}
?>
