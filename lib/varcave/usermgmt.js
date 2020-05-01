var table = null;
var groupTable = null;
var loadingSpiner = $(".loadingSpiner");

$(document).ready(function()
{
    
	//add new column `delete` to users table
	$.each(usersData, function( index, value ) 
	{	
		value.push('<div class="usermgmt-delete-container"><span data-id="' + value[0] + '" class="fas fa-user-alt-slash usermgmt-deleteuser"></span></div>');
	});
	
	$.each(groupsData, function( index, value ) 
	{	
        
		if ( (value[0] == 1) || (value[0] == 2) || (value[0] == 3) || (value[0] == 4) || (value[0] == 5) ) //prevent button to delete default groups
        { 
           value.push('');
        }
        else
        {
            value.push('<div class="usermgmt-delete-container"><span data-id="' + value[0] + '" class="fas fa-user-alt-slash usermgmt-deletegroup"></span></div>');
        }
	});
	
	//initiate table containing users
	table = $("#usermgmt-userstable").DataTable(
	{
		"rowId": 0, // select the uid col as the id
		"pageLength": 10,
		"language":   i18nMenus,
		//"jQueryUI":   true,
		"data":       usersData,
		columns: [ //defined in main php file
            { title: col1Name,visible:false },
            { title: col2Name },
            { title: col3Name },
            { title: col4Name },
			{ title: col5Name }         
        ],
		select: {
            //style:    'single', //disable row selection
            blurable: true,
        },
	});
	
	//initiate the group table
	groupTable = $("#usermgmt-grouptable").DataTable(
	{
		"rowId": 0,
		"pageLength": 10,
		"language":   i18nMenus,
		//"jQueryUI":   true,
		"data":       groupsData,
		columns: [ //defined in main php file
            { title: colGid,visible:false },
            { title: colGroupName },
            { title: colGroupdescription },
            { title: colGroupDelete }      
        ],
		select: {
            //style:    'single', //disable row selection
            blurable: true,
        },
	});
	
    //initiate table acl
	aclTable = $("#usermgmt-acltable").DataTable(
	{
		"rowId": 0, // select the uid col as the id
		"pageLength": 20,
		"language":   i18nMenus,
		//"jQueryUI":   true,
		"data":       aclData,
		columns: [ //defined in main php file
            { title: "indexid",visible:false, data:0},
            { title: colAclGuid,visible:false, data:1 },
            { title: colAclRO,visible:false , data:4},
            { title: colAclGroups, data:2 },
            { title: colAclDescription, data:6},
            { title: colAclWebpage, data:3 },
			{ title: colAclEditdate, data:5 },
            
        ],
		'select': {
            //style:    'single', //disable row selection
            blurable: true,
        },
        'order': [[ 3, "asc" ]],
	});
    
    //initiate table acl template
	acltemplateTable = $("#usermgmt-loadacltable").DataTable(
	{
		"rowId": 0, // select the uid col as the id
		"pageLength": 20,
		"language":   i18nMenus,
		//"jQueryUI":   true,
		"data":       loadAclData,
		columns: [ //defined in main php file
            { title: "id",visible:false},
            { title: colLoadAclName},
            { title: colLoadAcldescr},
            { title: colLoadAclAction,},
        ],
		'select': {
            //style:    'single', //disable row selection
            blurable: true,
        },
        'order': [[ 0, "asc" ]],
	});
    
	//fire a dialog to edit user details
	$("#usermgmt-userstable tbody").on('click', 'tr', function () 
	{
	    var data = table.row( this ).data();
        var userid  = data[0];
		console.log('user row data :');
		console.log(data);
		//insert form in dialog and populate it
		addFormtoDialog( L_editing + ' ' + data[2] + ' ' + data[3]);
		setDialogUserFormData(userid);
    });
	
    //fire a dialog to edit selected ACL
    $("#usermgmt-acltable tbody").on('click', 'tr', function () 
	{
	    var data = aclTable.row( this ).data();
        var aclguid  = data[1];
		console.log('acl row data :');
		console.log(data);
		//insert form in dialog and populate it
        addAclFormToDialog(L_editing + ' ACL');
		setDialogAclFormData(aclguid);
    });
    
	//fire a dialog to edit group details
	$("#usermgmt-grouptable tbody").on('click', 'tr', function () 
	{
	    var data = groupTable.row( this ).data();
        var gid  = data[0];
		console.log('user row data :'+data);
	
		//populate dialog with form
		addGroupFormToDialog( L_editing + ' ' + data[1]);
		//inject value into form
		setDialogGroupFormData(gid);
    });
	
	//initiate tabs for users/groups
	$( "#usermgmt-tabs" ).tabs({
        create: function() 
        {
            // go to right tab if specified in url 
            var widget = $(this).data('ui-tabs');
            $(window).on('hashchange', function() 
            {
                widget.option('active', widget._getIndex(location.hash));
            });
        },
    });
	
	//main edit form function for user
	$('body').on('change', '#usermgmt-dialogFormUser > fieldset :input', function(e)
	{
		//disable checkbox password protect to bubble
		if($(e.target).is('#changepwd'))
		{
			e.preventDefault();
			return;
		};
		
		var action = 'edit';
		var item = $(this).attr('id');
		if( $(this).attr('type') == 'checkbox')
		{
			console.log('input is a checkbox');
			var itemvalue = +$(this).is( ':checked' )
		}
		else if( $(this).attr('id') == 'expire')
		{
			console.log('input is datepicker object');
			var itemvalue = $('#expire').datepicker('getDate') / 1000;
			console.log(itemvalue); //datepicker is in millis
		}
		else if( $(this).attr('id') == 'password')
		{
			console.log('input is password, convert to hash sha256');
			var hash = sha256.create();
			hash.update($("#password").val() )
			var itemvalue = hash.hex();
			console.log('hash is : ' + itemvalue );
		}
		else
		{
			var itemvalue = $(this).val();
		}
		var userid = $('#uid').val();
		
		console.log( 'Edit : ' + item  + ' = ' + itemvalue );
		updateUserGroupDetail(action, item, itemvalue, userid, 'user');
	});
    
	//main edit form function for group (note : same form to edit or add groups)
	$('body').on('change', '#usermgmt-dialogFormGroup > fieldset :input', function(e)
	{
		
		var action = 'edit';
        var itemvalue = $(this).val();
        var item = $(this).attr('id');
		var gid = $('#gid').val();
		
		console.log( 'Edit ['  +gid + ']: ' + item  + ' = ' + itemvalue );
		updateUserGroupDetail(action, item, itemvalue, gid, 'group');
	});
    
    //Fire edit  function for acl
	$('body').on('change', '#usermgmt-dialogFormAcl > fieldset :input', function(e)
	{
		
		var action = 'edit';
		//var item = $(this).attr('guid');
        var itemvalue = $(this).val();
		var aclid = $('#aclId').val();
		
		console.log( 'Edit ['  + aclid + ']: related groups = ' + itemvalue );
		updateUserGroupDetail(action, 'none', itemvalue, aclid, 'acl');
	});
	
	//add user event
	$('#usermgmt-addUser').on('click', function()
	{
		//add a minimal form to enter username/firstname/lastname
		emptydialog();
		showDialog(L_adduser, '');
		
		$( "#jqUiDialog" ).dialog( "option", "buttons", 
		[
			{
				text: L_add,
				click: function()
				{
					addUser();
					$(this).dialog( "close" );
				}
			}
		]);
		
		$('#jqUiDialogContent').append(dialogFormAddUser);
	});
	
    //delete user event
	$('.usermgmt-delete-container').on('click','.usermgmt-deleteuser', function(e)
	{
		e.stopPropagation();
		var thisJqEl = $(this);
		var id = thisJqEl.attr('data-id'); 
		var target = 'user';
		console.log('Deleting user : ' +id);
		deleteUserGroup(target, id, thisJqEl, table)
	});
	
	//show a dialog from for group creation when clicking on add group icon
	$('#usermgmt-addGroup').on('click', function()
	{
        console.log('Open add group dialog');
		//add a minimal form to enter username/firstname/lastname
		emptydialog();
		showDialog(L_addgroup, '');
		
		$( "#jqUiDialog" ).dialog( "option", "buttons", 
		[
			{
				text: L_add,
				click: function()
				{
					addGroup();
					$(this).dialog( "close" );
				}
			}
		]);
		
		$('#jqUiDialogContent').append(dialogFormAddGroup);
	});
	
	//start group deletion from db and update groupDatatables on click
	$('.usermgmt-delete-container').on('click','.usermgmt-deletegroup', function(e)
	{
		e.stopPropagation();
		var thisJqEl = $(this);
		var id = thisJqEl.attr('data-id'); 
		var target = 'group';
		console.log('Deleting group : ' +id);
		deleteUserGroup(target, id, thisJqEl, groupTable);
	});
    
    //show acl when user clicks on checkbox
    $('#usermgmt-show-aclguid').on('change',function ()
    {
        var chkbxstate = $('#usermgmt-show-aclguid').prop('checked');
        console.log(aclTable.column(1).visible(chkbxstate) );;
    });
	
    // load selected acl template
    $('.loadTemplate').on('click', function(){
        // get the corresponding id.
        var templId = $( this).parents('tr').attr('id');
        console.log('id:'+templId);
        
        updateUserGroupDetail('n/a', 'N/A', templId, 'n/A', 'loadacltemplate')
    });
    
});

/*                               *
 * END OF $(document).ready part *
 *                               */

function addFormtoDialog(usernameTitle)
{
	
	 var data = table.row( this ).data();

	//show edit form 
	showDialog(usernameTitle, '');
	$('#jqUiDialogContent').append(dialogForm);
	
	//tool to togle enable/disable field of change password
	$("#changepwd").on('change',function()
	{
		$( "#password" ).prop( "disabled", function( i, val ) {
			return !val;
		});
	});
	
	var width = $(window).width()/3;
	var height = $(window).height()/1.2;
	$('#jqUiDialog').dialog( "option", "width", width );
	$('#jqUiDialog').dialog( "option", "height", height );
	
	//force flush of dialog form when closed
	$( '#jqUiDialog' ).on( 'dialogclose', function( event, ui )
			{
				emptydialog();
			} );
	//set up date picket for expiration
	$( '#expire, #lastUpdate, #created' ).datepicker({ changeYear: true});
	$( '#expire, #lastUpdate, #created' ).datepicker( $.datepicker.regional[ "fr" ] );
}

function addGroupFormToDialog(groupInfo)
{
	
	 var data = groupTable.row( this ).data();

	//show edit form 
	showDialog(groupInfo, '');
	$('#jqUiDialogContent').append(dialogFormGroup);
	
	//force flush of dialog form when closed
	$( '#jqUiDialog' ).on( 'dialogclose', function( e, ui )
			{
				emptydialog();
				e.preventDefault();
			});

}

function addAclFormToDialog(dialogTitle)
{
	
	 var data = aclTable.row( this ).data();

	//show edit form 
	showDialog(dialogTitle, '');
	$('#jqUiDialogContent').append(dialogFormAcl);
	
	//force flush of dialog form when closed
	$( '#jqUiDialog' ).on( 'dialogclose', function( e, ui )
			{
				emptydialog();
				e.preventDefault();
			});

}

/* this function populate info into user edit form.
 * All data are not present in Table we fetch
 * other data from DB. And populate the html form.
 */
function setDialogUserFormData(userid)
{
	//basic check on provided data 
	if (isNaN(userid))
	{
		console.log('bad user id');
		return false;
	}
	
	console.log('request details for uid:' + userid);
	
	var form_data = new FormData();
	form_data.append('uid', userid);
	form_data.append('action', 'get');
	form_data.append('target', 'user');
	
	$.ajax(
	{
		type: 'post',
		url: 'usermgmt.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success:function(data, textStatus, jqXHR)
				{
					console.log('data from server : '); 
					console.log(data); 
					$.each( data.userdata, function( key, value ) {
						console.log('process :' +  key + "=>" + value );
						//set datepicker on date fields
						if (value == null)
						{
							return;
						}
						
						if(key == 'created' || key == 'lastUpdate' || key =='expire')
						{
								console.log('initiate datepicker');
								if (value == -1)
								{
									console.log('date is -1');
									var d = new Date(5000); // Date is milli
									console.log(d);
								}
								else
								{
									var d = new Date(value * 1000); //*1000 Date is milli
									console.log(d);
								}
								
								$('#' + key).datepicker("setDate", d );
								$('#' + key).datepicker({dateFormat: "yy-mm-dd"});
						}
						else if( key == 'groups')  //handle group selections, and set "selected" those needed
						{
							var arr = value.split(",");
							$.each(arr, function (key, group)
							{
								$('#groups option').each(function(key, value)
								{
									console.log('option : '+ $(this).val());
									if( $(this).val() == group)
									{
										console.log('set selected on:' + value + 'field');
										$(this).attr('selected','selected');
									}
								});
							});
						}
						else if (key == 'disabled')
						{
							if( value != 0)
							{
								$('#disabled').attr('checked', 'checked');
							}
						}
						else
						{
							$('#' + key).val(value);
						}
					});
					return true;
				},
		error: function(jqXHR, error, errorThrown)
				{
					var msg = JSON.parse(jqXHR.responseText);
					emptydialog(); //empty dialog main html for any present data
					closeDialog(); //force close
					showDialog(msg.title, msg.stateStr); //show new message windows
					return false;
				},
	});
	
}

//populate info from db into group edit form
function setDialogGroupFormData(gid)
{
	console.log('populate group form');
	//basic check on provided data 
	if (isNaN(gid))
	{
		console.log('bad group id');
		return false;
	}
	
	console.log('request details for gid:' + gid);
	
	var form_data = new FormData();
	form_data.append('gid', gid);
	form_data.append('action', 'get');
	form_data.append('target', 'group');
	
	$.ajax(
	{
		type: 'post',
		url: 'usermgmt.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success:function(data, textStatus, jqXHR)
				{
					console.log('data from server : ' + data);
					$('#groupName').val(data.userdata.groupName);
					$('#description').val(data.userdata.description);
                    $('#gid').val(data.userdata.gid);
					return true;
				},
		error: function(jqXHR, error, errorThrown)
				{
					var msg = JSON.parse(jqXHR.responseText);
					emptydialog(); //empty dialog main html for any present data
					closeDialog(); //force close
					showDialog(msg.title, msg.stateStr); //show new message windows
					return false;
				},
	});
	
}

// populate info into acl edit form
function setDialogAclFormData(guid)
{
	
	console.log('populate acl form');
	//basic check on provided data 
	if (guid.length != 36 )
	{
		console.log('bad acl guid');
		return false;
	}
	
	console.log('request details for guid:' + guid);
	
	var form_data = new FormData();
	form_data.append('guid', guid);
	form_data.append('action', 'get');
	form_data.append('target', 'acl');
	
	$.ajax(
	{
		type: 'post',
		url: 'usermgmt.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success:function(data, textStatus, jqXHR)
				{
					console.log('data from server [ : ' );
                    console.log( data);
                    //Activate groups into html input multiple form
                    var arrGroups = data.userdata[0].split(",");
                    $('#aclId').val( data.userdata.indexid);
					$('#aclGuid').val(guid);
                    $.each(arrGroups, function (key, group)
                    {
                        $('#related_groups option').each(function(key, value)
                        {
                            console.log('option : '+ $(this).val());
                            if( $(this).val() == group)
                            {
                                console.log('set selected on acl ' + value +' field');
                                $(this).attr('selected','selected');
                            }
                        });
                    });
					$('#aclDescription').html(data.userdata[1]);
					return true;
				},
		error: function(jqXHR, error, errorThrown)
				{
					var msg = JSON.parse(jqXHR.responseText);
					emptydialog(); //empty dialog main html for any present data
					closeDialog(); //force close
					showDialog(msg.title, msg.stateStr); //show new message windows
					return false;
				},
	});
	
}

function emptydialog()
{
	$('#jqUiDialogContent').html('');
	$('#jqUiDialogContent').append('<div class="loadingSpiner"><i class="fas fa-spinner fa-pulse fa-3x"></i></div>');
}

/*
 * this function handle ajax request to server
 * action can be get/edit/delete BUT is hardcoded as "edit" or "loadacl here" 
 * item is the target db field
 * itemvalue is the new value
 * userid is the user or group id
 * target can be 'group' or 'user' or 'acl'
 */ 
function updateUserGroupDetail(action, item, itemvalue, userid, target)
{
	console.log('request update details for uid:' + userid);
	
	var form_data = new FormData();
	
	form_data.append('action', 'edit');
	form_data.append('item', item);
	form_data.append('itemvalue', itemvalue);
	form_data.append('target', target);
	
    //rename POST var for better reading
	if (target == 'user')
	{
		form_data.append('uid', userid);
	}
    else if (target == 'acl')
	{
		form_data.append('aclguid', userid);
	}
    else if(target == 'loadacltemplate'){
        console.log('in template');
        form_data.set('action', 'loadacltemplate');
        form_data.append('templId', itemvalue);
    }
	else if (target == 'group')
	{
		form_data.append('gid', userid);
	}
    else
    {
        console.log('No correct target selected');
    }
	
	var loadingSpiner = $(".loadingSpiner");
	loadingSpiner.toggle();	
	
	$.ajax(
	{
		type: 'post',
		url: 'usermgmt.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success:function(data, textStatus, jqXHR)
				{
					console.log('data from server : ' + data);
					if(target == 'user')
					{
						var rowid = '#'+userid;
						var rdata = table.row(rowid).data();
						console.log('user rowid:'+userid);
						console.log( rdata );
						//return true;
						//refresh the row data for user
						switch(item) {
						  case 'firstname':
							console.log('update datatable firstname');
							rdata[2] = itemvalue;
							table.row(rowid).data(rdata).draw();
							break;
						  case 'lastname':
							console.log('update datatable lastname');
							rdata[3] = itemvalue;
							table.row(rowid).data(rdata).draw();
							break;
						  default:
							console.log('Nothing to update');
							//nothing to do, not a datatable column
						}
						
					}
					else if(target == 'group')
					{
						// a group was updated fetch orignal value alter them hereafter
						var rowid = '#'+userid;
						var rdata = groupTable.row(rowid).data();
						console.log('group rowid:'+userid);
						console.log( rdata );
						//return true;
						//refresh the row data for user
						switch(item) {
						  case 'groupName':
							console.log('update datatable groupName');
							rdata[1] = itemvalue;
							groupTable.row(rowid).data(rdata).draw();
							break;
						  case 'description':
							console.log('update datatable description');
							rdata[2] = itemvalue;
							groupTable.row(rowid).data(rdata).draw();
							break;
						  default:
							console.log('This field is not to update');
							//nothing to do, not a datatable column
						}
						
					}
					else if(target == 'acl')
					{
						//update table data to reflect changes
                        var rowid = '#'+userid; // userid represent the acl indexid  
                        var rdata = aclTable.row(rowid).data();
                        console.log('update datatable ACL');
                        console.log(rdata);
                        rdata[2] = itemvalue;
                        console.log('apres');
                        console.log(rdata);
                        aclTable.row(rowid).data(rdata).draw();
                        return true;
					}
                    else if(target == 'loadacltemplate')
					{
						//refresh page to reload changes
                        console.log('load acl complete');
                        emptydialog();
                        showDialog('OK', '');
                        $('#jqUiDialogContent').html(data.userdata);
                        
                        //redirect 
                        setTimeout(function () {
                            console.log(window.location);
                            console.log(window.location.href);
                            //redirect to samepage woth no #tab defined
                            window.location.href = window.location.origin+window.location.pathname;
                            
                        }, 3000); //pause for 5 sec before close.
                        
                        return true;
					}
					else
					{
						console.log('Nothing to update in datatable');
					}
					
				},
		error: function(jqXHR, error, errorThrown)
				{
					var msg = JSON.parse(jqXHR.responseText);
					emptydialog(); //empty dialog main html for any present data
					closeDialog(); //force close
					showDialog(msg.title, msg.stateStr); //show new message windows
				},
	});
	//shutdown spinner on success
	loadingSpiner.delay(500).hide('slow');
}


function addUser()
{
	
	//removing the event on dialog close
	$('#jqUiDialog').dialog(
	{
		beforeclose: undefined,
		closeText: undefined
	});
	
	var newuid = null;
	var form_data = new FormData();
	form_data.append('action', 'add');
	form_data.append('target', 'user');
	form_data.append('username', $('#username').val() );
	form_data.append('firstname', $('#firstname').val() );
	form_data.append('lastname', $('#lastname').val() );
	
	console.log('Adding a new user');
	$.ajax(
	{
		type: 'post',
		url: 'usermgmt.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success:function(data, textStatus, jqXHR)
				{
					// var data is array defined as :
					//data[0] uid, [1] firstname, [2]lastname, [3] username,
					//once user added, populate a new form to edit user settings
					console.log('New user id is:[' + data.userdata[0] + ']');
					emptydialog();
					addFormtoDialog( L_editing + ' ' + data.userdata[1] + ' ' + data.userdata[2]);
					setDialogUserFormData(data.userdata[0]);
					//inject new data into datatables
					console.log('datatable data:');
					console.log(data.userdata);
					var deleteBtn = '<div class="usermgmt-delete-container"><span data-id="' + data.userdata[0] + '" class="fas fa-user-alt-slash usermgmt-deleteuser"></span></div>';
					table.row.add( [ data.userdata[0], data.userdata[3], data.userdata[1], data.userdata[2], deleteBtn ] );
					table.draw(false);
				},
		error: function(jqXHR, error, errorThrown)
				{
					var msg = JSON.parse(jqXHR.responseText);
					emptydialog(); //empty dialog main html for any present data
					closeDialog(); //force close
					showDialog(msg.title, msg.stateStr); //show new message windows
					return false
				},
	});
	
	//shutdown spinner on success
	console.log('ending spinner');
	loadingSpiner.delay(500).hide('slow');
	return true;
	
}

function addGroup()
{	
	var newuid = null;
	var groupName = $('#groupName').val();
	var description = $('#description').val();
	var form_data = new FormData();
	form_data.append('action', 'add');
	form_data.append('target', 'group');
	form_data.append('groupName',  groupName);
	form_data.append('description', description);
	
	console.log('Adding a new group:' + groupName + '|' + description);
	$.ajax(
	{
		type: 'post',
		url: 'usermgmt.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success:function(data, textStatus, jqXHR)
				{
					// var data is array defined as :
					//data[0] uid, [1] groupName, [2]description
					//once group added in db, populate the groupTable
					var grpGid =  data.userdata[0]; //converting var name for better visibility
					var grpGroupName =  data.userdata[1];
					var grpDescription =  data.userdata[2];
					var delGroupBtn = '<div class="usermgmt-delete-container"><span data-id="' + grpGid + '" class="fas fa-user-alt-slash usermgmt-deletegroup"></span></div>';
					
					console.log('New group id is:[' + grpGid + ']');
					emptydialog();
					
					//inject new data into datatables
					console.log('Adding datatables new data');					
					groupTable.row.add( [ grpGid, grpGroupName, grpDescription, delGroupBtn] );
					groupTable.draw(false);
					//shutdown spinner on success
					loadingSpiner.delay(500).hide('slow');
					return true;
				},
		error: function(jqXHR, error, errorThrown)
				{
					var msg = JSON.parse(jqXHR.responseText);
					emptydialog(); //empty dialog main html for any present data
					closeDialog(); //force close
					showDialog(msg.title, msg.stateStr); //show new message windows
					return false
				},
	});
	

	return true;
	
}

//compute the username as lowerchar while admin is typing firstname/lastname
$('body').on('keyup', '#usermgmt-dialogFormAddUser > fieldset > #lastname, #usermgmt-dialogFormAddUser > fieldset > #firstname#lastname', function()
{
	var username = $('#firstname').val().charAt(0) + $('#lastname').val().replace(/ /g,'');
	$('#username').val(username.toLowerCase());
	return true;
});

//this function tell server to remove specified user and 
//remove the current entry from table²
function deleteUserGroup(target, id, row, datatablesObj)
{
	loadingSpiner.show();
	var form_data = new FormData();
	form_data.append('action', 'delete');
	form_data.append('target', target);
	form_data.append('userid', id);
	
	console.log('deleting ' + target +':' + id);
	$.ajax(
	{
		type: 'post',
		url: 'usermgmt.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success:function(data, textStatus, jqXHR)
				{
					//remove row containing user from table
					datatablesObj.row( row.parents('tr') ).remove().draw();
					loadingSpiner.delay(500).hide('slow');
				},
		error: function(jqXHR, error, errorThrown)
				{
					var msg = JSON.parse(jqXHR.responseText);
					emptydialog(); //empty dialog main html for any present data
					closeDialog(); //force close
					showDialog(msg.title, msg.stateStr); //show new message windows
					return false
				},
	});
	
	//shutdown spinner on success
	console.log('ending spinner');
	return true;
	
	
}
