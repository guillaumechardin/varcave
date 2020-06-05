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
        if( fieldGroup == 'main' ){
            var fieldType = $('input[name="fieldType"]:checked').val();
        }
        else{
            var fieldType = '';
            
        }
        
        //check if all fields populated
        if ( isEmpty(newField) || isEmpty(i18nfield) || isEmpty(fieldGroup) ){
            showDialog(errorTitle, fillFields+'(1)');
            return false;
        }else if( fieldGroup == 'main' && isEmpty(fieldType) )
        {
            showDialog(errorTitle, fillFields+'(2)');
            return false;
        }else{
            //n/a
        }
        
        addNewField(newField,i18nfield,fieldGroup,fieldType);
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
    
    $("#fieldName").on('change', function(){
        if( $(this).is(":invalid") ) {
            this.setCustomValidity(mustBeLetters);
        };
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


function addNewField(newField,i18nfield,fieldGroup,fieldType){
    console.log ('Try to add request [' + newField+ ']');
    var form_data = new FormData();
    
    form_data.append('action', 'createNewField');
    form_data.append('newField', newField);
    form_data.append('i18nField', i18nfield);
    form_data.append('fieldGroup', fieldGroup);
    form_data.append('fieldType', fieldType);
    
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
            showDialog(data.title, data.message + '. ' + reloadPage);
            $('#fieldName').val('');
            $('#i18n-fieldName').val('');
            $('#fieldGroup').val('');
            $('input[name="fieldType"').val('');
            //force page reload to display new fields
            setTimeout(function(){
                    window.location.reload(1);
            }, 5000);
        },
        error: function(jqXHR, error, errorThrown) {
            console.log('error on update');
            $('#fieldName').val('');
            $('#i18n-fieldName').val('');
            $('#fieldGroup').val('');
            $('input[name="fieldType"').val('');
            updateError(jqXHR, error, errorThrown);
        }
    });

    
}