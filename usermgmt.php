<?php

require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveAuth.class.php');
require_once ('lib/varcave/varcaveUsers.class.php');


$auth = new varcaveAuth();
$users = new varcaveUsers();
$logger = $auth->logger;

const DATATABLES_JS_CSS = '
	<script src="lib/js-sha256/js-sha256.js"></script>
	<script src="lib/varcave/common.js"></script>
	<script src="lib/varcave/usermgmt.js"></script>
	<script src="lib/varcave/datatables-i18n.php"></script>
	<script src="lib/jqueryui/jquery-ui-1.12.1/jquery-ui.js"></script>
	<script src="lib/jqueryui/i18n/datepicker-fr.js"></script>
	<link rel="stylesheet" href="lib/jqueryui/jquery-ui-themes-1.12.1/themes/base/jquery-ui.css" />
	
	<link rel="stylesheet" type="text/css" href="lib/Datatables/DataTables-1.10.18/css/dataTables.jqueryui.min.css"/>
	<link rel="stylesheet" type="text/css" href="lib/Datatables/Select-1.2.6/css/select.jqueryui.min.css"/>
	<script type="text/javascript" src="lib/Datatables/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="lib/Datatables/DataTables-1.10.18/js/dataTables.jqueryui.min.js"></script>
	<script type="text/javascript" src="lib/Datatables/Select-1.2.6/js/dataTables.select.min.js"></script>
	';


$htmlstr = '';
$html = new VarcaveHtml(L::pagename_editusersgroups);


if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
{
	$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $redirect);
	//echo 'vous allez être redirigé vers une connexion sécurisée :<br>'. $redirect; 
	exit();
}

$acl = $auth->getacl('39e3c075-9d59-5c71-888c-c45527ad05b8');
if ( !$auth->isSessionValid() || !$auth->isMember($acl[0]) )
{
    $logger->error('editcave.php : user try to access unauthentified');
    $logger->error('IP : '. $_SERVER['REMOTE_ADDR']);
    $html = new VarcaveHtml(L::errors_ERROR);
    $htmlstr .= '<h2>' . L::errors_ERROR . '</h2>';
    $htmlstr .= L::errors_pageAccessDenied . '.';
    $html->insert($htmlstr,true);
    echo $html->save();
    exit();
}

if( ($_SERVER['REQUEST_METHOD']) == 'GET')
{  //dsiplay default edit site configuration page
	$html->logger->info('User access users and group mgmt');
	
	$htmlstr .= '<h2>' . L::usermgmt_title .'</h2>';
	//display a list of existing users as a non assoc array to get correct json conversion data
	$userList =  $users->getUsersList(false);
	$groupList = $users->getGroupsList(false);
    $aclList = array();
    
    //force list of all acl if user specified opition $_GET['showallacl']
    $allAcl = false;
    if(isset($_GET['showallacl']) )
    {
		$allAcl = true;
	}
    
    foreach($auth->getacllist($allAcl) as $key => $values)
    {
        $datetime =  new DateTime('@'.$values['editdate'], new DateTimeZone('UTC'));
		$datetime->setTimezone(new DateTimeZone('Europe/Paris'));
		
        $values['editdate'] = $datetime->format('Y-m-d'); 
        $aclList[] = array_values($values);
    }
    
	
	//setting up tab browsing, one for users, one for groups	
	$htmlstr .= '<div id="usermgmt-tabs">';
	$htmlstr .= '  <ul>';
	$htmlstr .= '    <li><a href="#tab-user">' . L::usermgmt_users . '</a></li>';
	$htmlstr .= '    <li><a href="#tab-groups">' . L::usermgmt_groups . '</a></li>';
	$htmlstr .= '    <li><a href="#tab-acl">' . L::usermgmt_acl . '</a></li>';
    $htmlstr .= '    <li><a href="#tab-acltemplate">' . L::usermgmt_aclTemplate . '</a></li>';
	$htmlstr .= '  </ul>';

	
	//
	$htmlstr .= '  <div id="tab-user">';
	$htmlstr .= '    <div id="usermgmt-container-addUser">
						<span title="' . L::usermgmt_addnewuser . '" id="usermgmt-addUser" class="fas fa-user-plus fa-2x ">
						</span>
                     </div>';
	$htmlstr .= '    <div id="usermgmt-container-tableuser">';
	$htmlstr .= '      <table id="usermgmt-userstable" class="display" style="width:100%">'; //hardcoded size of 100% for javascript size in px recognition instead of css
	//$htmlstr .= ' <tfoot><tr><th></th></tr></tfoot>';
	$htmlstr .= '      </table>';
	$htmlstr .= '    </div>'; //end tab-user
	$htmlstr .= '  </div>'; //end tab-user
	
	$htmlstr .= '  <div id="tab-groups">';	
	$htmlstr .= '    <div id="usermgmt-container-addGroup">
						<span title="' . L::usermgmt_addnewgroup . '" id="usermgmt-addGroup" class="fas fa-user-plus fa-2x ">
						</span>
                     </div>';
	
	$htmlstr .= '    <div id="usermgmt-container-tablegroups">';
	$htmlstr .= '      <table id="usermgmt-grouptable" class="display" style="width:100%">'; //hardcoded size of 100% for javascript size in px recognition instead of css
	//$htmlstr .= ' <tfoot><tr><th></th></tr></tfoot>';
	$htmlstr .= '      </table>';
	$htmlstr .= '    </div>'; 
	$htmlstr .= '  </div>'; //end tab-groups
    
    //ACL tab
    $htmlstr .= '  <div id="tab-acl">';	
	$htmlstr .= '    <div id="usermgmt-container-tableacl">';
    $htmlstr .= '    <div class="usermgmt-showguid">' . L::usermgmt_showAclGuid . '<input type="checkbox" id="usermgmt-show-aclguid"/></div>';
	$htmlstr .= '      <table id="usermgmt-acltable" class="display" style="width:100%">'; //hardcoded size of 100% for javascript size in px recognition instead of css
	//$htmlstr .= ' <tfoot><tr><th></th></tr></tfoot>';
	$htmlstr .= '      </table>';
	$htmlstr .= '    </div>'; 
	$htmlstr .= '  </div>'; //end tab-acl
    
    $htmlstr .= '  <div id="tab-acltemplate">';
    $htmlstr .= '    <div id="usermgmt-container-loadAcl" class="">';
    $htmlstr .= '       <div id="usermgmt-container-tableloadAcl">';
    $htmlstr .= '          <table id="usermgmt-loadacltable" class="display" style="width:100%">'; //hardcoded size of 100% for javascript size in px recognition instead of css
    $htmlstr .= '<script>';
    $htmlstr .= '  var loadAclData = [';
    $htmlstr .= '    ["0", \'' . L::acltemplate_name_0 . '\',\'' . L::acltemplate_descr_0 . '\',\'<button class="loadTemplate">Charger</button>\'],';
    $htmlstr .= '    ["1", \'' . L::acltemplate_name_1 . '\',\'' . L::acltemplate_descr_1 . '\',\'<button class="loadTemplate">Charger</button>\'],';
    $htmlstr .= '  ];';
    $htmlstr .= '</script>';

    $htmlstr .= '         </table>';
    $htmlstr .= '       </div>'; //tableloadAcl
    $htmlstr .= '    </div>';
    $htmlstr .= '  </div>'; //end tab-predifinedACL
     
	
	$htmlstr .= '</div>'; //end of tabs browsing
	
	
	//define some JS var from dynamic infos
	$htmlstr .= '<script>';
	$htmlstr .= '	var usersData = ' .  json_encode($userList,   JSON_PRETTY_PRINT) . ';';
	$htmlstr .= '	var groupsData = ' . json_encode($groupList,  JSON_PRETTY_PRINT) . ';';
    $htmlstr .= '   var aclData = '.     json_encode($aclList,    JSON_PRETTY_PRINT) . ';';
	//$htmlstr .= 'console.log(usersData);'; //some inline debug
	//$htmlstr .= 'console.log(groupsData);';  //some inline debug
	
	//datatables i18n users col names
	$htmlstr .= '	var col1Name = "id";';
	$htmlstr .= '	var col2Name = "' .  L::table_users_field_username .'";';
	$htmlstr .= '	var col3Name = "' . L::table_users_field_firstname .'";';
	$htmlstr .= '	var col4Name = "' . L::table_users_field_lastname .'";';
	$htmlstr .= '	var col5Name = "' . L::delete .'";';
	
	//datatables i18n group col names
	$htmlstr .= '	var colGid = "gid";';
	$htmlstr .= '	var colGroupName = "' .  L::table_groups_field_groupname .'";';
	$htmlstr .= '	var colGroupdescription = "' .  L::table_groups_field_description .'";';
	$htmlstr .= '	var colGroupDelete = "' .  L::delete .'";';
	
	//datatables i18n group colnames for ACL
    $htmlstr .= '	var colAclGuid = "guid";';
    $htmlstr .= '	var colAclGroups = " ' . L::table_acl_field_related_groups . '";';
    $htmlstr .= '	var colAclWebpage = " ' . L::table_acl_field_related_webpage . '";';
    $htmlstr .= '	var colAclRO = " ' . L::table_acl_field_read_only . '";';
	$htmlstr .= '	var colAclEditdate = " ' . L::table_acl_field_editdate . '";';
    $htmlstr .= '	var colAclDescription = " ' . L::table_acl_field_description . '";';
    
    $htmlstr .= '	var colLoadAclName = " ' . L::usermgmt_loadacl_col_name . '";';
    $htmlstr .= '	var colLoadAcldescr = " ' . L::usermgmt_loadacl_col_descr . '";';
    $htmlstr .= '	var colLoadAclAction = " ' . L::usermgmt_loadacl_col_action . '";';
	
    
	//some generic i18n info
	$htmlstr .= '	var L_editing = "' . L::usermgmt_editing .'";';
	$htmlstr .= '	var L_adduser = "' . L::usermgmt_adduser .'";';
	$htmlstr .= '	var L_addgroup = "' . L::usermgmt_addgroup .'";';
	$htmlstr .= '	var L_add    =  "' . L::add . '";';
	
	
	// ## begin of setting up form for user modification
    $htmlstr .= '   var dialogForm = ';
	$htmlstr .= '  \'<div id="usermgmt-dialogFormUser">';
	$htmlstr .= '   <input disabled type="hidden" name="uid" id="uid" value=""></input>';
	$htmlstr .= '   <div class="loadingSpiner"><i class="fas fa-spinner fa-pulse fa-3x"></i></div>';
	$htmlstr .= '   <fieldset><legend>' . L::usermgmt_identification  . '</legend>';
	$htmlstr .=       '<label for="username">' .  L::table_users_field_username  .  '</label><input disabled type="text" name="username" id="username" value=""></input>';
	$htmlstr .=       '<label for="firstname">' . L::table_users_field_firstname  . '</label><input type="text"  name="firstname" id="firstname" value=""></input>';
	$htmlstr .=       '<label for="lastname">' .  L::table_users_field_lastname  .   '</label><input type="text" name="lastname" id="lastname" value=""></input>';
	$htmlstr .= '   </fieldset>';
	
	// >>groups handling
	$htmlstr .= '   <fieldset><legend>' . L::usermgmt_groups  . '</legend>';
	$htmlstr .=        '<label for="groups">' . L::table_users_field_groups . '</label><select multiple name="groups" id="groups">';
	
	//computing a list of available groups
	// id $group[0] ; groupName $group[1] ; description $group[2]
	foreach($groupList as $key=>$group)
	{
		$htmlstr .= '  <option value="' . $group[1] . '">' . $group[1] .'</option>';
	}
    $htmlstr .= '     </select>';
	$htmlstr .= '   </fieldset>';
	
	//special auth informations
	$htmlstr .= '   <fieldset><legend>' . L::usermgmt_identification  . '</legend>';
	$htmlstr .=     '<label for="created">' .  L::table_users_field_created  . '</label><input type="text" disabled id="created"  value=""></input>';
	$htmlstr .=     '<label for="lastUpdate">' .  L::table_users_field_lastUpdate  . ' : <input type="text" disabled id="lastUpdate" value=""></input>';
	$htmlstr .=     '<label for="expire">' .  L::table_users_field_expire  . '</label><input type="text" name="expire" id="expire" value=""></input>';
	$htmlstr .=     '<label for="disabled">' .  L::table_users_field_disabled  . '</label><input type="checkbox" name="disabled" id="disabled"></input>';
	$htmlstr .=     '<label for="password">' .   L::table_users_field_password  . '</label><input type="text" name="password" id="password" disabled value=""></input><label for="changepwd">' . L::usermgmt_change . '</label><input type="checkbox" id="changepwd"></input>';
	$htmlstr .= '   </fieldset>';
	
	//website settings
	$htmlstr .= '   <fieldset><legend>' . L::usermgmt_sitesettings  . '</legend>';
	$htmlstr .=     '<label for="theme">' .  L::table_users_field_theme  . '</label><input type="text" name="theme" id="theme" value=""></input>';
	$htmlstr .=     '<label for="geo_api">' .  L::table_users_field_geoapi  . '</label><input type="text" name="geo_api" id="geo_api" value=""></input>';
	$htmlstr .= '   </fieldset>';
	
	//personal informations
	$htmlstr .= '   <fieldset><legend>' . L::usermgmt_personnalinfo  . '</legend>';
	$htmlstr .=     '<label for="emailaddr">' .  L::table_users_field_emailaddr  . '</label><input type="text" name="emailaddr" id="emailaddr" value=""></input>';
	$htmlstr .=     '<label for="streetNum">' .  L::table_users_field_streetNum  . '</label><input type="text" name="streetNum" id="streetNum" value=""></input>';
	$htmlstr .=     '<label for="address1">' .  L::table_users_field_address1  . '</label><input type="text" name="address1" id="address1" value=""></input>';
	$htmlstr .=     '<label for="address2">' .  L::table_users_field_address2  . '</label><input type="text" name="address2" id="address2" value=""></input>';
	$htmlstr .=     '<label for="postCode">' .  L::table_users_field_postCode  . '</label><input type="text" name="postCode" id="postCode" value=""></input>';
	$htmlstr .=     '<label for="town">' .  L::table_users_field_town . '</label><input type="text" name="town" id="town" value=""></input>';
	$htmlstr .=     '<label for="country ">' .  L::table_users_field_country  . '</label><input type="text" name="country" id="country" value=""></input>';
	$htmlstr .= '   </fieldset>';
	
	//other
	$htmlstr .= '   <fieldset><legend>' . L::usermgmt_other  . '</legend>';
	$htmlstr .=     '<label for="licenceNumber">' .  L::table_users_field_licenceNumber  . '</label><input type="text" name="licenceNumber" id="licenceNumber" value=""></input>';
	$htmlstr .=     '<label for="phoneNum">' .  L::table_users_field_phoneNum  . '</label><input type="text" name="phoneNum" id="phoneNum" value=""></input>';
	$htmlstr .=     '<label for="cavingGroup">' .  L::table_users_field_cavingGroup  . '</label><input type="text" name="cavingGroup" id="cavingGroup" value=""></input>';
	$htmlstr .=     '<label for="notes">' .  L::table_users_field_notes  . '</label><textarea type="text" name="notes" id="notes" value=""></textarea>';
	$htmlstr .=     '<label for="uiLanguage">' .  L::table_users_field_uiLanguage  . '</label><input type="text" name="uiLanguage" id="uiLanguage" value=""></input>';
	$htmlstr .= '   </fieldset>';
	
	$htmlstr .= '  </div>\';'; 
    // ### END of usermgmt-dialogFormUser
	
	//small dialog for new user creation
	$htmlstr .= 'var dialogFormAddUser = ';
	$htmlstr .= '  \'<div id="usermgmt-dialogFormAddUser"> ';
	$htmlstr .= '    <fieldset><legend>' . L::usermgmt_identification  . '</legend>';
	$htmlstr .= '      <label for="username">' .  L::table_users_field_username  .  '</label><input disabled type="text" name="username" id="username" value=""></input>';
	$htmlstr .= '      <label for="firstname">' . L::table_users_field_firstname  . '</label><input type="text"  name="firstname" id="firstname" value=""></input>';
	$htmlstr .= '      <label for="lastname">' .  L::table_users_field_lastname  .   '</label><input type="text" name="lastname" id="lastname" value=""></input>';
	$htmlstr .= '      </fieldset>';
	$htmlstr .= '  </div>';
	$htmlstr .= '\';';
	
	//small same form for groups creation
	$htmlstr .= 'var dialogFormAddGroup = ';
	$htmlstr .= '  \'<div id="usermgmt-dialogFormAddGroup"> ';
    $htmlstr .= '    <input disabled type="hidden" name="gid" id="gid" value=""></input>';
	$htmlstr .= '    <fieldset><legend>' . L::usermgmt_identification  . '</legend>';
	$htmlstr .= '      <label for="groupName">' .  L::table_groups_field_groupname  .  '</label><input type="text" name="groupName" id="groupName" value=""></input>';
	$htmlstr .= '      <label for="description">' . L::table_groups_field_description  . '</label><input type="text"  name="description" id="description" value=""></input>';
	$htmlstr .= '      </fieldset>';
	$htmlstr .= '  </div>';
	$htmlstr .= '\';';
	
	//small same form for groups editing
	$htmlstr .= 'var dialogFormGroup = ';
	$htmlstr .= '  \'<div id="usermgmt-dialogFormGroup"> ';
    $htmlstr .= '    <input disabled type="hidden" name="gid" id="gid" value=""></input>';
	$htmlstr .= '    <div class="loadingSpiner"><i class="fas fa-spinner fa-pulse fa-3x"></i></div>';
	$htmlstr .= '    <fieldset><legend>' . L::usermgmt_identification  . '</legend>';
	$htmlstr .= '      <label for="groupName">' .  L::table_groups_field_groupname  .  '</label><input type="text" name="groupName" id="groupName" value=""></input>';
	$htmlstr .= '      <label for="description">' . L::table_groups_field_description  . '</label><input type="text"  name="description" id="description" value=""></input>';
	$htmlstr .= '      </fieldset>';
	$htmlstr .= '  </div>';
	$htmlstr .= '\';';
    	
        
    // ## begin setting up form for ACL edition
    $htmlstr .= '   var dialogFormAcl = ';
	$htmlstr .= '  \'<div id="usermgmt-dialogFormAcl">';
	$htmlstr .= '   <input disabled type="hidden" name="aclId" id="aclId" value=""></input>';
	$htmlstr .= '   <input disabled type="hidden" name="aclGuid" id="aclGuid" value=""></input>';
	$htmlstr .= '   <div class="loadingSpiner"><i class="fas fa-spinner fa-pulse fa-3x"></i></div>';
	$htmlstr .= '   <fieldset><legend>' . L::edit  . ' ACL</legend>';
    // >>groups handling
    $htmlstr .= '   <span id="aclDescription">' . L::table_acl_field_description  . '</span>';
	$htmlstr .= '   <fieldset><legend>' . L::usermgmt_groups  . '</legend>';
	$htmlstr .=        '<select multiple name="related_groups" id="related_groups">';
	
	//computing a list of available groups in ACL
	// id $group[0] ; groupName $group[1] ; description $group[2]
	foreach($groupList as $key=>$group)
	{
		$htmlstr .= '  <option value="' . $group[1] . '">' . $group[1] .'</option>';
	}
    $htmlstr .= '     </select>';
	
	
	
    $htmlstr .= '  </div>\';'; 
    // ### end of acl edit form 
    
    
	$htmlstr .= '</script>';
	
	//$htmlstr .= 'PHP gc_sessioLifetime (s) :' . ini_get(session.gc_maxlifetime);
	$htmlstr .= DATATABLES_JS_CSS;
	
	
	$html->insert($htmlstr,true);
	echo $html->save();
}
else
{ //Handling ajax with post request
	$data = '';

	$logger->info('User try edit user, acl or group');
	try
	{
		if($_POST['action'] == 'get')
		{
			$logger->debug('action is get, target is:' . $_POST['target']);
			if($_POST['target'] == 'user')
			{
				$logger->debug('user id is:' . $_POST['uid']);
				$data = $users->getUserDetails($_POST['uid']);
				if( $data == false)
				{
					throw new Exception(L::usermgmt_noUserFound);
					
				}
			}
            elseif($_POST['target'] == 'acl')
			{
				$logger->debug('acl guid is:' . $_POST['guid']);
				$data = $auth->getacl($_POST['guid'],true,true);
				if( $data == false)
				{
					throw new Exception(L::usermgmt_noAclFound);
				}
			}
			else
			{
				$logger->debug('group id is:' . $_POST['gid']);
				$data = $users->getGroupDetails($_POST['gid']);
			}
		}
		elseif($_POST['action'] == 'edit')
		{
			$logger->debug('action is edit, target is:' . $_POST['target']);
			//check user data
			if( empty($_POST['item']) || !isset($_POST['itemvalue']) )
			{
				throw new Exception(L::errors_badArgs );
			}
			
			if($_POST['target'] == 'user')
			{
				$logger->debug('user id is:' . $_POST['uid']  . 'data is : ' . $_POST['itemvalue'] );
				$users->setUserProp($_POST['uid'], $_POST['item'], $_POST['itemvalue']);
				$data = L::usermgmt_editsuccess;
			}
            elseif($_POST['target'] == 'acl')
			{
				$logger->debug('acl guid is:' . $_POST['aclguid']  . 'data is : ' . $_POST['itemvalue'] );
				$auth->setaclgrouplist($_POST['aclguid'], $_POST['itemvalue']);
				$data = array ( 
                    "msg" => L::usermgmt_editsuccess,
                    'id' => '',
                );
			}
			else
			{
				$logger->debug('group id is:' . $_POST['gid'] . 'data is : ' . $_POST['itemvalue'] );
				$users->setGroupProp($_POST['gid'], $_POST['item'], $_POST['itemvalue']);
				$data = L::usermgmt_editsuccess;
			}
		}
		elseif($_POST['action'] == 'add')
		{
			$logger->debug('action is add, target is:' . $_POST['target']);
			
			if($_POST['target'] == 'user')
			{
				//adding user into db.
				$logger->debug('adding new user : '   . $_POST['username'] . '|' . $_POST['firstname'] . '|' . $_POST['lastname'] );
				
				$userValues = array(
							'username' => strtolower($_POST['username']),
							'firstname' => $_POST['firstname'],
							'lastname' => $_POST['lastname'],
							'password' => str_shuffle( hash('sha256',microtime()) ), //generate random password to prevent unwanted access.
						);
				
				$newUserId = $users->adduser($userValues);
				
				$data = array($newUserId, $_POST['firstname'], $_POST['lastname'], $_POST['username'] );
				$logger->info('User added successfully');
			}
			else
			{
				//adding group to db
				$logger->debug('adding new group : '   . $_POST['groupName'] . '|' . $_POST['description'] );
				
				$groupValues = array(
							'groupName' => strtolower($_POST['groupName']),
							'description' => $_POST['description'],
							);
				
				$newGroupId = $users->addGroup($groupValues);
				
				$data = array($newGroupId, $groupValues['groupName'], $groupValues['description']);
				$logger->info('Group added successfully');
			}
		}
		elseif($_POST['action'] == 'delete')
		{
			$logger->info('action is delete, target is:' . $_POST['target']);

			if($_POST['target'] == 'user')
			{
				//deleting user from db.
				$logger->debug('deleting user :' . $_POST['userid']);
				$users->deluser($_POST['userid']);
				$logger->info('User deleted successfully');
			}
			else
			{
				//deleting user from db.
				$gid = $_POST['userid'] ;
				$logger->debug('deleting user :' . $_POST['userid']);
				$users->delGroup($gid);
				$logger->info('User deleted successfully');
				$data = L::usermgmt_editsuccess;
			}
		}
        elseif($_POST['target'] == 'loadacltemplate'){ // load a specified template ACL
            $logger->debug('try to load template acl:[' . $_POST['templId'] . ']');
            
            if( isset($_POST['templId']) )
            {
                $loadstate = $auth->setAclTemplate( (int)$_POST['templId'] );
                if($loadstate){
                    $data = L::usermgmt_templateLoadSuccess . ' <hr> <strong>'. L::pageWillAutoReload . '</strong>';
                }
                else
                {
                    throw new Exception( L::usermgmt_templateLoadFailed );
                }
                
            }
            else{
       
                throw new Exception('load ACL template:' . L::error_badAction);
            }
        }
        else
		{
			throw new Exception(L::error_badAction);
		}
		
		//preparing info back to user
		$return = array(
                    'title' => L::edit,
                    'stateStr'=> L::editcave_success,
                    'userdata' => $data,
                    );
		$httpError = 200;
		$httpErrorStr = ' OK';
		
	}
	catch(Exception $e)
	{
		$logger->error('User/group update fail : '. $e->getmessage() );
		$return = array(
			'title' => L::errors_ERROR,
			'stateStr'=> L::usermgmt_editGroupUserFail,
			'state' => 0,
			);
		$httpError = 500;
		$httpErrorStr = ' Internal server error';
	}
	
	jsonWrite(json_encode($return, JSON_UNESCAPED_SLASHES), $httpError, $httpErrorStr);
	exit();
	
	
}

?>
