$(document).ready(function () {
			$("#formEnvoyer").click(function()
			{
				var spamQ = $("#formSpam option:selected").text();
				if( spamQ != "4")
				{
					$("#formulaireContactCavite").hide();
					$("#formModal-titre").html("Erreur");
					$("#formModal-resultat").html('Erreur : Veuillez correctement répondre à l\'antispam');
					$("#formModal-content").show();
					var formError = true;
					return false;
				}
				
				formdata = new FormData();
				formdata.append("ref", "cavite");
				formdata.append("sendmail", "true");
				formdata.append("nom",     $("#formNom").val() );
				formdata.append("email",   $("#formEmail").val() );
				formdata.append("objet",   $("#formObjet").val() );
				formdata.append("message", $("#formMessage").val() );
				formdata.append("antispam", spamQ);
				formdata.append("copie", 1); //on force l'envoi d'une copie à l'expediteur du message
				
				$.ajax(
				{
					url: "contact.php",
					type: "post",
					contentType: false, // obligatoire pour de l'upload
					processData: false, // obligatoire pour de l'upload
					//dataType: 'json', // selon le retour attendu
					data: formdata,
					success: function(json)
					{
						var valeurs = jQuery.parseJSON( json );
						$("#formulaireContactCavite").hide();
						$("#formModal-titre").html("Info");
						$("#formModal-resultat").html(valeurs.message);
						$("#formModal-content").show();
						if(valeurs.statut == 0)
						{
								var thisForm = $("#formulaireContact");
								$("#formulaireContact").find('input:text, input:password, input:file, select, textarea').val('');
								$("#formulaireContact").find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
						}
						var formError = false;
						return true;
						
					},
					error: function(jqXHR, error, errorThrown ) 
					{
						$("#formulaireContactCavite").hide();
						$("#formModal-titre").html("Erreur");
						$("#formModal-resultat").html('Erreur envoi : ' + error + '/' + errorThrown);
						$("#formModal-content").show();
						var formError = true;
						return false;
					},
				});
			});
			
			$("#showForm").click(function()
			{

				$("#formModal-background").show(500);
				return true;
			});
			
			$("#formFermer").click(function()
			{

				$("#formModal-background").hide(500);
				return true;
			});
			
			$("#formModal-close").click(function()
			{
				if (formError = true)
				{
					$("#formModal-content").hide();
					$("#formulaireContactCavite").show();
					formError = false;
				}
				else
				{
					$("#formModal-content").hide();
					$("#formModal-background").delay(300).hide(200);
				}
			});
			
			$("#formReset").click(function()
			{
				var thisForm = $("#formulaireContact");
				$("#formulaireContact").find('input:text, input:password, input:file, select, textarea').val('');
				$("#formulaireContact").find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
			});
	});
		
		
