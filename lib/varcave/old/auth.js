$(document).ready( function (){
		$("#chgtPasswd").on("submit", checkPassword );
})
	 
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
			showDialog("Erreur1","Le mot de passe doit comporter au moins " + pwdsizemin + " caractères.");
			$("#chgtPasswd")[0].reset();
			return false;
		}
	}
	showDialog("Erreur2",'Les mots de passe ne sont pas identiques');
	$("#chgtPasswd")[0].reset();
	return false;
}


function sendpassword(sha256passwd)
{
	var form_data = new FormData();
	
	form_data.append('passwd', sha256passwd);			
	$.ajax({
			type: 'post',
			url: 'moncompte.php?setpasswd=true',
			processData: false,
			contentType: false,
			data: form_data,
			dataType: "json",
			success: showResults,
			error: showError,
		});
}

function showResults(json,statut)
{
	showDialog("Opération réussie",json.message);
	$("#chgtPasswd")[0].reset();
	return true;
}

function showError(jqXHR, error, errorThrown) 
{
	var ret = JSON.parse(jqXHR.responseText);
	//var msg = json.message;
	$("#chgtPasswd")[0].reset();
	showDialog('ERREUR', ret.message);
	return false;
}

function showDialog(titre,text)
{
	$("#content_resultats").text(text);
	
    $("#pwd_resultats").dialog({
	  title: titre,
      resizable: false,
      height: "auto",
      width: 400,
      modal: true,
      buttons: {
        "OK": function() {
          $( this ).dialog( "close" );
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
    });
}	
	
