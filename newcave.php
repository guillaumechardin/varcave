<?php

require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveAuth.class.php');
require_once ('lib/varcave/varcaveCave.class.php');
require_once ('lib/varcave/functions.php');



$htmlstr = '';
$html = new VarcaveHtml(L::pagename_newcave);
$auth = new VarcaveAuth();
$cave = new varcaveCave();
$logger = $auth->logger;


if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
{
	$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $redirect);
	//echo 'vous allez être redirigé vers une connexion sécurisée :<br>'. $redirect; 
	exit();
}

$acl = $auth->getacl('3935e285-9367-4945-98c1-be528995c9d0');
if ( !$auth->isSessionValid() || !$auth->isMember($acl[0]) )
{
    $logger->error('editcave.php : user try to access unauthentified');
    $logger->error('IP : '. $_SERVER['REMOTE_ADDR']);
    $html = new VarcaveHtml($pageName);
    $htmlstr .= '<h2>' . L::errors_ERROR . '</h2>';
    $htmlstr .= L::errors_pageAccessDenied . '.';
    $html->insert($htmlstr,true);
    echo $html->save();
    exit();
}



//show default cave creation form 
if( !isset($_POST) || empty($_POST) )
{
	//check if copy cave process was ordered
	$cpySrcGuid = '';
	if(  isset($_GET['srcguid']) && !empty($_GET['srcguid']) )
	{
		$srcCave = $cave->selectByGuid($_GET['srcguid']);
		$htmlstr .= '<h2>' . L::copycave_copytonewcave . '</h2>';
		$htmlstr .= '<div class="copycave-src-cavename">' . L::copycave_srccavename . ' : ' . $srcCave['name'] . '</div>';
	
		$cpySrcGuid = '<input id="srcguid" name="srcguid" type="hidden" value="' . $_GET['srcguid'] . '"></input>';
		
	}
	else
	{
		//normal cave creation
		$htmlstr .= '<h2>' . L::newcave_addnewcave . '</h2>';
	}
	$htmlstr .= '<fieldset id="fieldset">';
	$htmlstr .=   '<legend>' . L::newcave_addnewcave . '</legend>';
	$htmlstr .=   '<div>';
	$htmlstr .=   '<label for="newcavename">nom cavité</label>';
	$htmlstr .=   '<input type="text" id="newcave-newcavename" name="newcavename" placeholder="' . L::newcave_cavename . '"></input>';
	$htmlstr .=   '</div>';
	$htmlstr .=   '<div>';
	$htmlstr .=     '<label for="inputcomment">' . L::comment . '</label>';
	$htmlstr .=     '<input type="text" class="changeloginput edit-changelog-input-text" name="inputcomment" id="changelogEntry" value="' . L::editcave_changelogEditexemple . '">';    
	$htmlstr .=     '<input type="checkbox" class="changeloginput edit-changelog-input-cbx" id="changelogEntryVisibility" checked="checked">';
	$htmlstr .=     '<label class="after">' . L::editcave_changelogVisiblity .'</label>';
	$htmlstr .=     $cpySrcGuid;
	$htmlstr .=   '</div>';
	$htmlstr .=   '<div  class="newcave-savebtn">';
	$htmlstr .=     '<span class="pure-button" id="newcave-btn">' . L::general_save . '</span>';
	$htmlstr .=   '</div>';
	$htmlstr .= '</fieldset>';

	$htmlstr .= '<script src="lib/varcave/common.js"></script>';
	$htmlstr .= '<script src="lib/varcave/newcave.js"></script>';
	
	//send output to browser
	$html->insert($htmlstr,true);
	echo $html->save();
}
else //try to create new cave with user supplied infos
{
	//check post data
	try
	{
		if (empty($_POST['cavename']) || empty($_POST['changelogEntry']) )
		{
			$logger->error($_SERVER['PHP_SELF'] . ' : bad POST args');
			$logger->debug('POST data : '. print_r($_POST,true) );
	
			throw new exception (L::errors_badArgs);
		}
	
        $logger->debug('Trying to add new cave : `' . $_POST['cavename'] . '` with changelog data :' . $_POST['changelogEntry'] );
	
	
        $newguid = $cave->createNewCave($_POST['cavename']);
        $cave->AddLastModificationsLog($newguid, $_POST['changelogEntry'],  $_POST['changelogEntryVisibility']);
	
        //copy cave data from user specified cave guid to new cave
        if( isset($_POST['srccaveguid']) && !empty($_POST['srccaveguid']) )
        {
            $logger->info('newcave.php : starting cave copy process from : [' . $_POST['srccaveguid'] . ']');
            $caveSrc = $cave->selectByGuid($_POST['srccaveguid']);

            //get excluded fields to copy on new cave
            $excluded = $cave->getConfigElement('excludedcopyfields');
            
            //convert csv to array
            $excluded = array_map('trim', explode(',', $excluded ));
            $logger->debug('excluded copy fields:' . print_r($excluded,true) );
            
            
            //start copy process 
            foreach($caveSrc as $key => $value)
            {
                //skip current if excluded from field copy
                $logger->debug('update cave field :' . $key  . ' ' . print_r($excluded, true) );
                if( in_array($key, $excluded) )
                {
                    $logger->debug('skip field: [' . $key . '] from cave copy');
                    continue;
                }
                //else we copy data from the originating cave
                try {
                    $logger->debug( 'copy data [' . $key . '] with value [' . substr($caveSrc[$key],0,15) .']' );
                    $cave->updateCaveProperty( $newguid, $key, $caveSrc[$key] );
                }
                catch (exception $e){
                    //updating new cave fail
                    throw new exception( $e->getmessage() );
                }
            }
            $logger->info('Cave Copy done');
        }
	
        //setting up ajax return
        $return = array(
                'title' =>L::general_edit,
                'stateStr'=> L::newcave_creationSucceed,
                'guid' => $newguid,
                );
        $httpError = 200;
        $httpErrorStr = ' OK';
        
        $logger->info('Cave creation success');
	}
	catch (exception $e)
	{
		$logger->error('fail create new cave : ' . $e->getmessage() );
		$return = array(
			'title' => L::errors_ERROR,
			'stateStr'=> L::newcave_creationFail,
			'state' => 1,
			);
		$httpError = 500;
		$httpErrorStr = ' Internal server error';
	}
	
	
	
	jsonWrite(json_encode($return, JSON_UNESCAPED_SLASHES), $httpError, $httpErrorStr);
	exit();
	
}

?>
