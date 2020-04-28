var newstable = null;
var loadingSpiner = $(".loadingSpiner");

$(document).ready(function()
{	
	//initiate table containing users
	newstable = $("#newsmgmt-newstable").DataTable(
	{
		"rowId": 0, // select the indexid col as the id
		"pageLength": 10,
        "order": [4],
		"language":   i18nMenus,
		//"jQueryUI":   true,
		"data":       usersData,
		columns: [ //defined in main php file
            { title: colnewsID,       visible:false,  data:0},
			{ title: colnewsTitle,                    data:3 },
            { title: colnewsContent,                  data:2},
            { title: colnewsUsername,                 data:9 },
            { title: colnewsCreation,                 data:4 },
            { title: colnewsEditdate,                 data:5},
            { title: colnewsDeleted,                  data:1},
            { title: colnewsFirsname, visible:false,  data:10 },
            { title: colnewsLastname, visible:false,  data:11 },
            { title: colnewsAction,   visible:false,  data:12 } 
        ],
		select: {
            //style:    'single', //disable row selection
            blurable: true,
        },
	});
    
    //open a dialog when clicking on table row
    $("#newsmgmt-newstable tbody").on('click', 'tr', function () 
	{
	    var data = newstable.row( this ).data();
        var newsid  = data[0];
		console.log('News row data :');
		console.log(data);
        
        //populate data info the modal jqUI Dialog 
        showDialog(data[3], '');
		//get all news data from db
        $('#jqUiDialogContent').append(formEditnews);
		var thisNewsData  = processNews('get', newsid, '');
		
		// adjust Dialog position on screen
        $('#jqUiDialog').dialog("option", "width", "40%");
        $('#jqUiDialog').dialog("option", "position", 
		{
			my: "center",
			at: "center",
			of: "#newsmgmt-newstable_wrapper",
			collision: "none"
		});
		
		//$('#jqUiDialog').dialog("option", "buttons",{});
		$('#jqUiDialog').dialog("option", "buttons",
		[
			{
				text: "OK",
				click: function()
				{
					processNews('edit', newsid, { 
									title:$('#newsmgmt-form-title').val(),
									content:$('#newsmgmt-form-content').html(),
									deleted:$('#newsmgmt-form-deleted').prop('checked')
									});
                    $( this ).dialog( "close" );
				},
			},			
		]);
		
		/* load trumbowyg tools */
		$('#newsmgmt-form-content').trumbowyg();
    
    });
	
	//force flush of dialog form when closed
	$( '#jqUiDialog' ).on( 'dialogclose', function( e, ui )
	{
		emptydialog();
		e.preventDefault();
	});
    
   
   // Open Dialog when click add news button
    $('#newsmgmt-addnews').on('click', function ()
    {
        console.log('Add new News');
        
        //populate data info the modal jqUI Dialog 
        showDialog(addNewsTitle, '');
        $('#jqUiDialog').dialog("option", "width", "40%");
        $('#jqUiDialog').dialog("option", "buttons",
		[
			{
				text: "OK",
				click: function()
				{
					processNews('add', null, { 
									title:$('#newsmgmt-form-title').val(),
									content:$('#newsmgmt-form-content').html(),
									});
                    $( this ).dialog( "close" );
				},
			},			
		]);
        // add empty form
        $('#jqUiDialogContent').append(formEditnews);
		// remove the delete part from the form
		$('fieldset[class="newsmgmt-fieldset-delundel"]').remove();
        
		//load wisigig editor
		$('#newsmgmt-form-content').trumbowyg();
        
        // set up OK button to send data to server
        
        
    });
    
});

/*
 * get,set,delete news
 * action can be add | delete | edit | get
 * newsid is the news id
 * data is the corresponding data to update
 */
function processNews(action, newsid, data = '')
{
	console.log('Processing news action:' + action + ' | newsid:'+newsid);
	
	//convert data string as a json object
	if (action == 'edit' || action == 'add')
	{
		data = JSON.stringify(data);
	}
	
	var form_data = new FormData();
	form_data.append('action', action);
	form_data.append('id', newsid);
	form_data.append('data', data);
	
	
	$.ajax(
	{
		type: 'post',
		url: 'newsmgmt.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success:function(data, textStatus, jqXHR)
				{
					console.log('data from server : ');
					console.log(data);
					if(action == 'get')
                    {
                        console.log('processing `get` results');
                        popFormNewsDialog(data.userdata);
                        return true;
                    }
					if(action == 'edit' | action == 'add')
					{
						console.log('News edited/added...');
						console.log(data);
                          
                        //update datatable 
                        var d =  [
                                    data.userdata.indexid,
                                    data.userdata.deleted,
                                    data.userdata.content,
                                    data.userdata.title,
                                    data.userdata.creation_date,
									data.userdata.edit_date,
									'',
									'',
									'',
                                    data.userdata.username,
                                    '',
                                    '',
                                    "<button>delete<\/button>",
                                ];
						/*
						{ title: colnewsID,       visible:false,  data:0},
			
			{ title: colnewsDeleted,                  data:1}
			{ title: colnewsContent,                  data:2},
			{ title: colnewsTitle,                    data:3 },
            { title: colnewsCreation,                 data:4 },
            { title: colnewsEditdate,                 data:5},
			6
			7
			8
            { title: colnewsUsername,                 data:9 },
            
            ,
            { title: colnewsFirsname, visible:false,  data:10 },
            { title: colnewsLastname, visible:false,  data:11 },
            { title: colnewsAction,   visible:false,  data:12 }
						
						
						
						*/
                        // write back change to table
                        
                        
						// remplace data on edit or 
						// add row on add
						if(action == 'edit')
						{
							newstable.row('#'+data.userdata.indexid).data(d).draw();
                        }
						else
						{
							//add news to datatables
							newstable.row.add(d).draw();
						}
                        return true;
						
					}
				},
		error: function(jqXHR, error, errorThrown)
				{
					var msg = JSON.parse(jqXHR.responseText);
					emptydialog(); //empty dialog main
					closeDialog(); //force close
					showDialog(msg.title, msg.stateStr); //show new message windows
					return false;
				},
    });
}

function popFormNewsDialog(data)
{
    console.log('update news data from :');
    console.log(data);
    
    $('#newsmgmt-form-title').val(data.title);
    $('#newsmgmt-form-content').html(data.content);
    
    //set the checkbox on (off on default form)
    if(data.deleted == 1)
    {
        $('#newsmgmt-form-deleted').prop('checked', true);
    }
}

function emptydialog()
{
	$('#jqUiDialogContent').html('');
	$('#jqUiDialogContent').append('<div class="loadingSpiner"><i class="fas fa-spinner fa-pulse fa-3x"></i></div>');
}



