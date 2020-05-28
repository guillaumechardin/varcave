$(document).ready(function () {
    $("input[type='number'], input[type='checkbox']").not('#quickSearchInput').on('change', function() {
        var id = $(this).attr('data-id');
        var field =  $(this).attr('data-name');
        
        //get value of element
        if( $(this).attr("type") == 'checkbox' )
        {
            if ($(this).is(':checked') )
            {
                var value=1;
            }
            else
            {
               var value=0;
            }
        }
        else{
            var value = $(this).val();
        }
        updateEndUserFields(id, field, value);
    });
});

var loadingSpiner = $(".loadingSpiner");


function updateEndUserFields(id, field, value){
    console.log ('update ' + id + ' request [' + field+ ']' + value);
    var form_data = new FormData();
    
    form_data.append('id', id);
    form_data.append('field', field);
    form_data.append('value', value);
    
    loadingSpiner.toggle();
	$.ajax({
		type: 'post',
		url: 'fieldssettings.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(data, textStatus, jqXHR){
            loadingSpiner.toggle();
        },
        error: updateError,
    });
    
}

function updateError(jqXHR, error, errorThrown)
{
	loadingSpiner.toggle();
	var msg = JSON.parse(jqXHR.responseText);
	showDialog(msg.title, msg.stateStr);
	return false;
}