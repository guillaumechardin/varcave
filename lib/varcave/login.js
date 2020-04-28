$(document).ready(function() {	
	$("#doLogin").on('click', function(e) {
		e.preventDefault();
		doLogin();

		
	});
	
	$("#connectBtn").on("click", function(){
		
		var thisHost = window.location.origin;
		window.location.replace(thisHost+'/login.php');
		return true;
	});
	
	$("#chgtPasswd").on("submit", checkPassword );
	
	$("#themeChange").on('change',changeTheme);
	$("#geo_api").on('change',changeGeoApi);
	$("#datatablesMaxItems").on('change',changeDatatablesMaxItems);

});

function sendpassword(sha256passwd)
{
	var form_data = new FormData();
	
	form_data.append('passwd', sha256passwd);			
	$.ajax({
			type: 'post',
			url: 'myaccount.php',
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
	showDialog(json.title,json.stateStr);
	return true;
}

function updateError(jqXHR, error, errorThrown)
{
	var msg = JSON.parse(jqXHR.responseText);
	showDialog(msg.title, msg.stateStr);
	return false;
}

function checkPassword(f){
	f.preventDefault();
	
	if ($("#pass1").val() === $("#pass2").val() )
	{
		var pwdsizemin = 5;
		var pass = $("#pass1").val();
		if ( pass.length >= pwdsizemin )
		{
			var hash = sha256.create();
			hash.update($("#pass1").val() );
			sendpassword(hash.hex() );
			return true
		}
		else
		{
			showDialog(errors_ERROR, login_pwdTooShort + ' (5 ' + chars + ' min)' );
			$("#chgtPasswd")[0].reset();
			return false;
		}
	}
	showDialog(errors_ERROR, login_pwdAreNotSame);
	$("#chgtPasswd")[0].reset();
	return false;
}


function authSucceed(json,state)
{
	$('#userpwdform').remove();
	
	//msg = JSON.parse(json.responseText);
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
	clearTextField( "#username" );
	clearTextField( $("#password") );
	showDialog(msg.title, msg.stateStr);
	return true;
}

function doLogin ()
{	
	var form_data = new FormData();
	
	
	var sha256pwd = sha256.create();
	sha256pwd.update( $("#password").val() );
	
	form_data.append('username', $("#username").val() );
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
	
	return false;
	
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