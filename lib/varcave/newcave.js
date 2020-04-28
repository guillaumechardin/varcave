$(document).ready(function ()
{
	$('#newcave-btn').on('click', function()
	{
		createcave();
	
	});
	
});


function createcave()
{
	//get all data from user inputs
	
	var cavename = $('#newcave-newcavename').val();
	var changelogEntry = $('#changelogEntry').val();
	var changelogEntryVisibility = +$('#changelogEntryVisibility').is(":checked");
	
	var srcguid = $('#srcguid').val();
	
	
	console.log('user data : name:' + cavename + '| log-entry:' + changelogEntry + '|visibility:' + changelogEntryVisibility);
	
	
	var form_data = new FormData();
	form_data.append('cavename', cavename);
	form_data.append('changelogEntry', changelogEntry);
	form_data.append('changelogEntryVisibility', changelogEntryVisibility);
	if (srcguid != undefined)
	{
		console.log('copy flasg set');
		form_data.append('srccaveguid', srcguid);
	}
	
	$.ajax(
	{
		type: 'post',
		url: 'newcave.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success:function(data, textStatus, jqXHR)
				{
					updateSuccess(data, textStatus, jqXHR);
					console.log('creation succeed.');
					//redirect to new created cave
					var newhtmldata = '<div>'+data.stateStr +'</div><a href="editcave.php?guid='+data.guid+'">' + cavename + '</a>';
					console.log(newhtmldata);
					$("#fieldset").html(newhtmldata);
					window.location.href = "editcave.php?guid="+data.guid;
				},
		error: updateError,
	});



function updateSuccess(json,state,jqXHR )
{
	//showDialog(json.title,json.stateStr);
	
	return true;
}

function updateError(jqXHR, error, errorThrown)
{
	var msg = JSON.parse(jqXHR.responseText);
	showDialog(msg.title, msg.stateStr);
	
	//reset input user data
	$('#newcave-newcavename').val('');
	$('#changelogEntry').val('');
	
	return false;
}

}