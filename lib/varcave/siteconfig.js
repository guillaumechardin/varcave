$(document).ready(function()
{
	$('input, textarea, select').not('#quickSearchInput').on('change', function()
	{
		updateConfig( $(this) );
	});

	// enable tabs
	$( "#siteconfig_tabs" ).tabs();
	$( "#siteconfig_editEULA" ).trumbowyg();

	//trigger ok button
	$("#siteconfig-eula-save").on("click", function() {
		saveEula();
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
	
	form_data.append('target','updateConfig');

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

function saveEula()
{
	console.log('saving current eula');
	var htmlEula = $("#siteconfig_editEULA").val();
	//console.log(htmlEula);

	var form_data = new FormData();
	form_data.append('eulaContent', htmlEula);
	form_data.append('target', 'updateEULA');


	varcaveSendAjaxData(form_data, 'siteconfig.php', '');
}

/**
 * Checks if a JavaScript value is empty
 * @example
 *    isEmpty(null); // true
 *    isEmpty(undefined); // true
 *    isEmpty(''); // true
 *    isEmpty([]); // true
 *    isEmpty({}); // true
 * @param {any} value - item to test
 * @returns {boolean} true if empty, otherwise false
 */
function isEmpty(value) {
    return (
        value === null || // check for null
        value === undefined || // check for undefined
        value === '' || // check for empty string
        (Array.isArray(value) && value.length === 0) || // check for empty array
        (typeof value === 'object' && Object.keys(value).length === 0) // check for empty object
    );
}

function varcaveSendAjaxData(formdata, targeturl, JSconsoleInfo, method = 'post', showSpinner = true)
{
	if ( ! (formdata instanceof FormData) )
	{
		console.log("data is not a formdata");
		showDialog('Error', "data is not formata");
		return false;
	}

	if( ! isEmpty(JSconsoleInfo) )
	{
		console.log(JSconsoleInfo);
	}

	var loadingSpiner = $(".loadingSpiner");
	//show loading spinner
	if(showSpinner)
	{
		console.log('s^piiner');

		loadingSpiner.toggle();
	}	
	
	$.ajax(
	{
		type: method,
		url: targeturl,
		processData: false,
		contentType: false,
		data: formdata,
		dataType: "json",
		success:function(data, textStatus, jqXHR)
				{
					console.log('ajax succeed.');
					showDialog('Succès', "Données enregistrées");
					//updateSuccess(data, textStatus, jqXHR);
				},
		error: updateError,
	});
	
	//hide loading spinner
	if(showSpinner)
	{
		loadingSpiner.delay(500).hide('slow');
	}
	return true


}

