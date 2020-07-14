$(document).ready(function()
{
	$('input, textarea, select').not('#quickSearchInput').on('change', function()
	{
		updateConfig( $(this) );
	});
	
});


var loadingSpiner = $(".loadingSpiner");

function updateConfig(inputForm)
{
	var form_data = new FormData();
	var itemname  = inputForm.attr('name');
	var itemid    = inputForm.next('input[data="id"]').val();
    
    //if checkbox set right datatype for db 1|0
    if( $(inputForm).is(":checkbox") ){
        var newval  =  +$(inputForm).prop("checked");
    }
    else{
        var newval  = inputForm.val();
    }
    
    
	console.log('Updating : ' + itemname + ' with value : ' + newval + ' [' + itemid + ']');
	form_data.append('itemname', itemname);
	form_data.append('itemvalue', newval);
	form_data.append('itemid', itemid);
	loadingSpiner.toggle();
	
	$.ajax(
	{
		type: 'post',
		url: 'siteconfig.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success:function(data, textStatus, jqXHR)
				{
					console.log('update succeed.');
					updateSuccess(data, textStatus, jqXHR);
				},
		error: updateError,
	});
	
	loadingSpiner.delay(500).hide('slow');
	return true
}



function updateSuccess(data, textStatus, jqXHR)
{
	
	return true;

}

function updateError(jqXHR, error, errorThrown)
{
	loadingSpiner.hide();
	var msg = JSON.parse(jqXHR.responseText);
	showDialog(msg.title, msg.stateStr);
	return false;
}
