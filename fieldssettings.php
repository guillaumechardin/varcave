<?php

require_once (__DIR__ . '/lib/varcave/varcaveHtml.class.php');
require_once (__DIR__ . '/lib/varcave/varcaveAuth.class.php');
require_once (__DIR__ . '/lib/varcave/varcaveCave.class.php');
require_once (__DIR__ . '/lib/varcave/functions.php');

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
    $htmlstr .= '  <label for="fieldName">' . L::fieldssettings_newFieldNameID . '</label>';
    $htmlstr .= '  <input type="text" id="fieldName" name="fieldName" maxlength="25" pattern="[a-zA-Z]+" value=""/>';

    $htmlstr .= ' </form>';
    $htmlstr .= '  <label for="i18n-fieldName">' . L::fieldssettings_i18nNewFieldName . '</label>';
    $htmlstr .= '  <input type="text" id="i18n-fieldName" name="i18n-fieldName" />';
   
    $options = ['main', 'files'];
    $htmlstr .= '  <label for="fieldGroup">' . L::fieldssettings_fieldGroup . '</label>';
    $htmlstr .= '  <select name="fieldGroup" id="fieldGroup">';
    foreach($options as $option){
        $htmlstr .= '<option value="' . $option . '">' . $option . '</option>';
    }
    $htmlstr .= '  </select>';
    
    //build a list of field type for user to select
    $htmlstr .= '<p id="fieldType">';
    $fieldsTypeList = $cave->getListElements('cave_field_type');
    foreach($fieldsTypeList as $field){        
        $htmlstr .= '  <input type="radio" id="' . $field['list_item'] . '" name="fieldType" value="' . $field['list_item'] . '"/>';
        $htmlstr .= '  <label for="' . $field['list_item'] . '">' . $field['list_item'] . '</label>';
    }

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
    $htmlstr .= '   <div class="fieldssettings_tableCell"><h2>' . L::fieldssettings_fieldType . '</h2></div>';

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
        $htmlstr .='   <div class="fieldssettings_tableCell">' . $field['type'] . '</div>' ;
        
    }

    $htmlstr .= '</div>';
    $htmlstr .= '<script src="lib/varcave/fieldssettings.js"></script>';
    $htmlstr .= '<script>';
    $htmlstr .= '  var reloadPage = "' . L::general_pageWillAutoReload . '";';
    $htmlstr .= '  var errorTitle = "' . L::errors_ERROR . '";';
    $htmlstr .= '  var fillFields = "' . L::general_fillAllFields . '";';
    $htmlstr .= '  var mustBeLetters = "' . html_entity_decode(L::errors_mustBeLetters) . '";';
    $htmlstr .= '</script>';
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
        if($_POST['action'] == 'updField'){
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
            $varcave = new varcave();
            if( empty($_POST['newField']) || empty($_POST['fieldGroup']) || empty($_POST['i18nField']) ){
                throw new Exception(L::errors_ERROR . ': ' . L::errors_badArgs);
            }
            
            //set default field type if group il files
            if($_POST['fieldGroup'] == 'files'){
                $fieldType = 'json';
            }
            else{
                $fieldType = $_POST['fieldType'];
            }
            
            $cave = new varcaveCave();
            
            $logger->debug('Update data with provided settings : fieldname:' . $_POST['newField'] . ' | fieldGroup :'. $_POST['fieldGroup'] . ' | i18nField :' . $_POST['i18nField'] . '| fieldtype:' .$fieldType ); 
            //Update db data and ini file data
            $dbupdate = $cave->addEndUserFields( $_POST['newField'], $_POST['fieldGroup'], $fieldType );
            if( ! $dbupdate){
                throw new exception('unable to update data');
            }

            $writeIni = $varcave->updatei18nIniVal('lang/local/custom_fr.ini','table_cave','field_'.$_POST['newField'],$_POST['i18nField']);
            if( ! $writeIni){
                throw new exception('unable to write ini file');
            }
            
            //alter database only if type is not json. This is because json type is currently used only with `files` db column
            //so no need to add new col
            if( $fieldType != 'json'){
                $alterTable = $varcave->addCaveCol($_POST['newField'], $_POST['fieldType']);
                 if( ! $alterTable){
                    throw new exception('unable alter table caves');
                }
            } else{
                 $logger->info('col will not be added to `caves`since its json files element');
            }
            
            $message['title'] =L::general_edit;
            $message['message'] = L::fieldssettings_addComplete;
            $message['dbfieldID'] = $dbupdate;
            $message['i18nField'] = $writeIni;
            $httpcode = 200;
            $httpCodeStr = 'OK';
            
        }
        else{
            $message['stateStr'] = L::errors_badArgs . '[action]';
            $message['title'] = L::errors_ERROR;
            $httpcode = 500;
            $httpCodeStr = 'Internal Server Error ';
        }
    }
    catch(exception $e){
        $logger->error('unable to update tables or ini file :' . $e->getMessage() );
        $message['stateStr'] = L::errors_unableToUpdateData . ':' . $e->getMessage();
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