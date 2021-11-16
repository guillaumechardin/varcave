$(document).ready(function() 
{    
    $('#resetpassword-doreset').on('click', function(event)
	{        
        
        event.preventDefault();
        // check if captcha as been clicked by user
        if(validateRecaptcha(0, '#resetpassword-wrapcaptcha') == false)
        {
            //exit the action.
            return false;
        }
        
        if( $('#resetpassword-username').val() === '' )
        {
            $('#resetpassword-username').addClass('invalidCaptcha');
            return false;
        }else
        {
            $('#resetpassword-username').removeClass('invalidCaptcha');
        }
        
        doreset( $('#resetpassword-username').val() );
        
		return true;
	});
});


function doreset(username)
{
    var loadingSpiner = $(".loadingSpiner");
    loadingSpiner.show(500);
    var form_data = new FormData();
    form_data.append('resetpwd', '1');
    form_data.append('username', username);
    
    
    $.ajax(
	{
		type: 'post',
		url: 'resetpassword.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success:function(data, status, jqXHR)
				{
					loadingSpiner.delay(500).hide('slow');
                    //reset recaptcha data
                    //grecaptcha.reset(); 
                    
                    //close dialog if any
                    emptydialog();
                    showDialog(data.title, data.data.message + data.data.emailaddr);
                    //$('#resetpassword-wrapform').html('');
				},
		error: function(jqXHR, error, errorThrown)
				{
                    loadingSpiner.delay(500).hide('slow');
					var msg = JSON.parse(jqXHR.responseText);
					emptydialog(); //empty dialog
					showDialog(msg.title, msg.stringmsg); //show new message windows
					return false
				},
	});
    
    function emptydialog()
    {
        $('#jqUiDialogContent').html('');
    }
    
}