$(document).ready(function() {	
	$("#login-login-form").on('submit', function(e) {
		e.preventDefault();
        
		doLogin();
        return true;
		
	});

	$("#connectBtn").on("click", function(){
		
		var thisHost = window.location.origin;
		window.location.replace(thisHost+'/login.php');
		return true;
	});
	
	$("#chgtPasswd").on("submit", function(event)
    {
        if (checkPassword(event) == true){
            var hash = sha256.create();
			hash.update($("#pass1").val() );
            sendpassword(hash.hex(), 'myaccount.php');
            return true;
        }
        return false
    });
    
    $("#rstPasswd").on("submit", function(event)
    {
        if (checkPassword(event) == true){
            var hash = sha256.create();
			hash.update($("#pass1").val() );
            sendpassword(hash.hex(), 'resetpassword.php');
            return true;
        }
        return false
    });
	
	$("#themeChange").on('change', changeTheme);
	$("#geo_api").on('change', changeGeoApi);
	$("#datatablesMaxItems").on('change', changeDatatablesMaxItems);
    $('#myaccount_personal_data > fieldset > input').on('change', changeUserData );
});

function sendpassword(sha256passwd, targeturl)
{
	var form_data = new FormData();
	
	form_data.append('passwd', sha256passwd);
    if($('#reset-link'))
    {
        form_data.append('resetlink', $('#reset-link').val());
    }
	$.ajax({
			type: 'post',
			url: targeturl,
			processData: false,
			contentType: false,
			data: form_data,
			dataType: "json",
			success: updateSuccess,
			error: updateError,
		});
	clearTextField("#pass1");
	clearTextField("#pass2");
}

function updateSuccess(json,state)
{
    //change dialog close function when reseting password reset
    showDialog(json.title,json.stateStr);
    if(typeof json.data != 'undefined' && json.data.option.pwdreset == '1'){
        //leave to login.php page on reset password
        $("#jqUiDialog").dialog('option', 'buttons', {
            'Ok': function() {
                $(this).dialog('close');
                window.location.href = 'login.php';
            }
        });
    }
	return true;
}

function updateError(jqXHR, error, errorThrown)
{
	var msg = JSON.parse(jqXHR.responseText);
    showDialog(msg.title, msg.stateStr);
	return false;
}

function checkPassword(e){
	e.preventDefault();
	
	if ($("#pass1").val() === $("#pass2").val() )
	{
		var pwdsizemin = 5;
		var pass = $("#pass1").val();
		if ( pass.length >= pwdsizemin )
		{
			return true
		}
		else
		{
			showDialog(errors_ERROR, login_pwdTooShort + ' (5 ' + chars + ' min)' );
			$("#chgtPasswd, #rstPasswd")[0].reset();
			return false;
		}
	}
	showDialog(errors_ERROR, login_pwdAreNotSame);
	$("#chgtPasswd, #rstPasswd")[0].reset();
	return false;
}


function authSucceed(json,state)
{
	$('#userpwdform').remove();

	showDialog(json.title,json.stateStr);
	var thisHost = window.location.origin;
	window.location.replace(thisHost);
	return true;
}

function authFail(jqXHR, error, errorThrown) 
{
	var msg = JSON.parse(jqXHR.responseText);
	//$("#username").val('');
	//$("#password").val('');
	
    showDialog(msg.title, msg.stateStr);
    $('#login-resetpwd').show();
	return true;
}

function doLogin ()
{	
	var form_data = new FormData();
	
	
	var sha256pwd = sha256.create();
	sha256pwd.update( $("#login-password").val() );
	
	form_data.append('username', $("#login-username").val() );
	form_data.append('password', sha256pwd.hex() );
	
	$.ajax({
		type: 'post',
		url: 'login.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: 'json',
		success: authSucceed,
		error: authFail,
	});
	
	return true;
	
}

function changeTheme()
{
	var form_data = new FormData();
	var selectedVal = $(this).val();
	form_data.append('update', 'theme' );
	form_data.append('value', selectedVal );

	$.ajax({
		type: 'post',
		url: 'myaccount.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: 'json',
		success: updateSuccess,
		error: updateError,
	});
}

function changeGeoApi()
{
	var form_data = new FormData();
	var selectedVal = $(this).val();
	form_data.append('update', 'geo_api' );
	form_data.append('value', selectedVal );

	$.ajax({
		type: 'post',
		url: 'myaccount.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: 'json',
		success: updateSuccess,
		error: updateError,
	});
}


/*
 * change user max displayed item per table
 */
function changeDatatablesMaxItems()
{
	var form_data = new FormData();
	var selectedVal = $(this).val();
	form_data.append('update', 'datatablesMaxItems' );
	form_data.append('value', selectedVal );

	$.ajax({
		type: 'post',
		url: 'myaccount.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: 'json',
		success: updateSuccess,
		error: updateError,
	});
}

function changeUserData()
{
	var form_data = new FormData();
	var selectedVal = $(this).val();
    var targetData = $(this).attr('id')
	form_data.append('update', targetData);
	form_data.append('value', selectedVal );

	$.ajax({
		type: 'post',
		url: 'myaccount.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: 'json',
		success: updateSuccess,
		error: updateError,
	});
}