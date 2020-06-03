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
    
    //Try to add new field to db
    $("#fieldssettings-addNewField-send").on('click', function() {
        console.log('add new field to db');
        var newField =  $('#fieldName').val();
        var i18nfield = $('#i18n-fieldName').val();
        var fieldGroup = $('#fieldGroup').val();
        
        addNewField(newField,i18nfield,fieldGroup);
    }); 
    
    //hide/show radio button if required
    $('#fieldGroup').change( function(){
        console.log('change fieldgroup:'+ $(this).val());
        if($(this).val() == 'files'){
            $('#fieldType').hide();
        }
        else{
            $('#fieldType').show();
        }
    });
    
    
});

var loadingSpiner = $(".loadingSpiner");


function updateEndUserFields(id, field, value){
    console.log ('update ' + id + ' request [' + field+ ']' + value);
    var form_data = new FormData();
    
    form_data.append('action', 'updField');
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


function addNewField(newField,i18nfield,fieldGroup){
    console.log ('Try to add ' + id + ' request [' + field+ ']' + value);
    var form_data = new FormData();
    
    form_data.append('action', 'createNewField');
    form_data.append('newfield', newField);
    form_data.append('i18nfield', i18nfield);
    form_data.append('fieldGroup', fieldGroup);
    
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
            showDialog(data.title, data.msg);
        },
        error: updateError,
    });

    
}