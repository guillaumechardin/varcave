<?php

require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveAuth.class.php');
require_once ('lib/varcave/varcaveCave.class.php');
require_once ('lib/varcave/functions.php');

$auth = new varcaveAuth();
$logger = $auth->logger;

$htmlstr = '';

if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
{
	$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $redirect);
	//echo 'vous allez être redirigé vers une connexion sécurisée :<br>'. $redirect; 
	exit();
}

//check user access rights to this page
$acl = $auth->getacl('939ea0d9-f23c-4451-89ae-da31c498414c');
if ( !$auth->isSessionValid() || !$auth->isMember($acl[0]))
{
    $logger->error(basename(__FILE__) . ': user try to access unauthentified IP : '. $_SERVER['REMOTE_ADDR']);
    $html = new VarcaveHtml(L::errors_ERROR );
    $html->stopWithMessage(L::errors_ERROR, L::errors_pageAccessDenied, 401, 'Unauthorized ');
}

//show form to edit fields display order
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $html = new VarcaveHtml(L::pagename_fieldssettings);

    //retreive field data and settings
    $cave = new varcaveCave();
    $fieldsData = $cave->getI18nCaveFieldsName('ALL');


    $htmlstr .= '<h1>' . L::fieldssettings_title . '</h1>';

    // add field section
    $htmlstr .= '<div id="fieldssettings-addNewField">';
    $htmlstr .= '  <h2 >' . L::fieldssettings_addNewField . '</h2>';
    $htmlstr .= ' <form>';
    $htmlstr .= '  <label for="fieldName">' . L::fieldssettings_newFieldNameID . '</label>';
    $htmlstr .= '  <input type="text" id="fieldName" name="fieldName" maxlength="25" pattern="[^a-zA-Z]+" />';
    $htmlstr .= '  <input type="submit">';
    $htmlstr .= ' </form>';
    $htmlstr .= '  <label for="i18n-fieldName">' . L::fieldssettings_i18nNewFieldName . '</label>';
    $htmlstr .= '  <input type="text" id="i18n-fieldName" name="i18n-fieldName"/>';
   
    $htmlstr .= '  <label for="fieldGroup">' . L::fieldssettings_fieldGroup . '</label>';
    $options = ['main', 'files'];
    $htmlstr .= '  <select name="fieldGroup" id="fieldGroup">';
    foreach($options as $option){
        $htmlstr .= '<option value="' . $option . '">' . $option . '</option>';
    }
    $htmlstr .= '  </select>';
    
    $htmlstr .= '<p id="fieldType">';
    $htmlstr .= ' <input type="radio" id="bool" name="fieldType" value="bool"/>';
    $htmlstr .= '<label for="bool">bool</label>';
    $htmlstr .= ' <input type="radio" id="dec" name="fieldType" value="dec"/>';
    $htmlstr .= '<label for="dec">decimal</label>';
    $htmlstr .= ' <input type="radio" id="text" name="fieldType" value="text"/>';
    $htmlstr .= '<label for="text">text</label>';
    $htmlstr .= '</p>';
    
    $htmlstr .= '  <p><button id="fieldssettings-addNewField-send" name="fieldssettings-addNewField-send">OK</button></p>';
    $htmlstr .= '</div>';
    //end addfield section

    $htmlstr .= '  <h2 id="fieldssettings-orderandshow">' . L::fieldssettings_orderandshow . '</h2>';
    $htmlstr .= '<div id="fieldssettings_table">';
    $htmlstr .= '<div class="loadingSpiner"><i class="fas fa-spinner fa-pulse fa-3x"></i></div>';
    $htmlstr .= '   <div class="fieldssettings_tableCell"><h2>'. L::fieldssettings_fieldName . '</h2></div>';
    $htmlstr .= '   <div class="fieldssettings_tableCell"><h2>' . L::fieldssettings_showOnDisplay . '</h2></div>';
    $htmlstr .= '   <div class="fieldssettings_tableCell"><h2>' . L::fieldssettings_showOnSearch . '</h2></div>';
    $htmlstr .= '   <div class="fieldssettings_tableCell"><h2>' . L::fieldssettings_showOnEdit . '</h2></div>';
    $htmlstr .= '   <div class="fieldssettings_tableCell"><h2>' . L::fieldssettings_order . '</h2></div>' ;
    $htmlstr .= '   <div class="fieldssettings_tableCell"><h2>' . L::fieldssettings_fieldGroup . '</h2></div>';

    foreach($fieldsData as $key => $field){
        //prepare html settings for checkbox
        $checked_display = '';
        $checked_search = '';
        $checked_edit = '';
        $checked = 'checked';
        if($field['show_on_display']){
            $checked_display = $checked;
        } 
        
        if($field['show_on_search']){
            $checked_search = $checked;
        } 
        
        if($field['show_on_edit']){
            $checked_edit = $checked;
        }
        $htmlstr .='   <div class="fieldssettings_tableCell"><h3>' . $field['display_name'] .'</h3></div>';
        $htmlstr .='   <div class="fieldssettings_tableCell"><input  data-id="' . $field['indexid'] . '" type="checkbox" data-name="show_on_display" ' . $checked_display . '/></div>' ;
        $htmlstr .='   <div class="fieldssettings_tableCell"><input  data-id="' . $field['indexid'] . '" type="checkbox" data-name="show_on_search" ' . $checked_search . '/></div>' ;
        $htmlstr .='   <div class="fieldssettings_tableCell"><input  data-id="' . $field['indexid'] . '" type="checkbox" data-name="show_on_edit" ' . $checked_edit . '/></div>' ;
        $htmlstr .='   <div class="fieldssettings_tableCell"><input  data-id="' . $field['indexid'] . '" type="number" data-name="sort_order" value="' . $field['sort_order'] . '"/></div>' ;
        $htmlstr .='   <div class="fieldssettings_tableCell">' . $field['field_group'] . '</div>' ;
        
    }

    $htmlstr .= '</div>';
    $htmlstr .= '<script src="lib/varcave/fieldssettings.js"></script>';
    $htmlstr .= '<script src="lib/varcave/common.js"></script>';
    $htmlstr .= '<script src="lib/jqueryui/jquery-ui-1.12.1/jquery-ui.js"></script>';
    $htmlstr .= '<link rel="stylesheet" href="lib/jqueryui/jquery-ui-themes-1.12.1/themes/base/jquery-ui.css" />';
    $html->insert($htmlstr, true);
    echo $html->save();
    exit();
}
else{
    /*
     * post request
     * try to update settings for row indexid -> field with value name.
     * post data should contain at least :
     * $_POST['id'] = indexid of corresponding row in DB
     * $_POST['field'] = field of row
     * $_POST['value'] = value to update
     * $_POST['action'] = action type to execute, either updField | createNewField
     * 
     * Send back result to browser with json data
     * on sucess http 200
     */
    try{
        /*update field with user given data*/
        if($_POST['action' == 'updField']){
            $logger->debug(basename(__FILE__) . ' : try to update end_user_fields table') ;
            if( empty( $_POST['id']) || empty( $_POST['field']) ){
                throw new Exception(L::errors_ERROR . ' : ' . L::errors_badArgs);
            }
            try {
                $varcave = new varcave();
                $success = $varcave->updateEndUserFields($_POST['id'], $_POST['field'], $_POST['value']);
            }
            catch(Exception $e){
                $logger->error('Unable to update db : ' . $e->getMessage() ); 
                throw new exception('Unable to update db');
            }

            $message['message'] = 'OK';
            $httpcode = 200;
            $httpCodeStr = 'OK';
        }
        /* try to add new field to db and i18 info to lang file*/
        elseif($_POST['action'] == 'createNewField' ){
            //minimal args check
            if( empty($_POST['newField']) || empty($_POST['fieldGroup']) || empty($_POST['i18nfield']) ){
                throw new Exception(L::errors_ERROR . ' : ' . L::errors_badArgs);
            }
            
            $cave = new varcaveCave();
            
            //Update db data and ini file data
            $dbupdate = $cave->addEndUserFields( $_POST['newField'], $_POST['fieldGroup'], $_POST['fieldType'] );
            if( ! $dbupdate){
                throw new exception('unable to update data');
            }
            
            
        }
        else{
            $message['stateStr'] = L::errors_badArgs . '[action]';
            $message['title'] = L::errors_ERROR;
            $httpcode = 500;
            $httpCodeStr = 'Internal Server Error ';
        }
    }
    catch(exception $e){
        $message['stateStr'] = L::errors_unableToUpdateData;
        $message['title'] = L::errors_ERROR;
        $httpcode = 500;
        $httpCodeStr = 'Internal Server Error ';
    }

    //format json and write back to end user agent 
    $json = json_encode($message,JSON_PRETTY_PRINT);
    jsonWrite($json, $httpcode, $httpCodeStr);
    exit();
}

?>