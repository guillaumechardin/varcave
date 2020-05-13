$('document').ready( function()
{
	// insert contact form only if on page contact.php.
	if( $('#contact-contactform') != null)
	{
		$('#contact-contactform').html(sendEmailForm);
		$('#contact-contactform').append('<hr><button id="contact-sendemail">' + send + '</button>');
        $('#contact-sendemail').button();
	};
	
    /*
     * send form data as an email on clic send button in contact.php
     */
	$('body').on('click','#contact-sendemail', function()
	{
		console.log('send email to smtp server(body)');
        checkContactForm(fields);
        
        // check if captcha as been clicked by user
        if(validateRecaptcha() == false)
        {
            //exit the action.
            return false;
        }
        
        var sender = $('#mail-usermail').val();
        var subject = $('#mail-subject').val();
        var body = $('#mail-content').val();
        var files = document.getElementById('mail-files').files; 
        var webot = $('#mail-webot').val();
        sendmail(sender, subject, body, files, webot);
        
        
		return false;
	});
    
    /* set  email subject (some subject are generated in main php file and loaded by <script> tag 
     * only if subject field is present else, wait for the event "" to populate field 
     */
    if( $('#mail-subject') )
    {
        $('#mail-subject').val(subject);
    };
    
    $('body').on("change",'[id^="mail-"]', function () {
        console.log('changing'+$(this) );
        checkContactForm(fields);
        checkmailvalidity();
    });
    
});

/*
 * Check email contact form for data.
 * Return true if all fields are sets
 * return false if it fails to validate data
 */
function checkContactForm(fields)
{
    //check if all fields are sets and contain valid data
    var error = false;
    $.each(fields, function( key, field )
    {
        console.log('check field ' + field + ' for content ');
        if( $('#'+field).val().length == 0)
        {
            console.log('#'+field +'is zero length');
            $('#'+field).addClass("required");
            $('#'+field).attr('placeholder',infoRequired);
            error = true;
        }
        else
        {
            if( $('#'+field).hasClass("required") )
            {
                $('#'+field).removeClass("required");
            }
        }
    });
     
    if(error)
    {
        disablesendbtn('#contact-sendemail');
        return false;
    }
    else
    {
        console.log('realease btn send');
        enablesendbtn('#contact-sendemail');
        return true;
    }
}

/*
 * send data to server for mail flow creation
 */
function sendmail(sender, subject, body, files, webot) 
{
    console.log('Sending email...');
    
    var loadingSpiner = $(".loadingSpiner");
    loadingSpiner.show();
    
    //disable scrolling
    $('html, body').css({
        overflow: 'hidden',
        height: '100%',
    });
    
    var form_data = new FormData();
	form_data.append('sender', sender);
	form_data.append('subject', subject);
	form_data.append('body', body);
	form_data.append('referer', 'display.php');
    form_data.append('webot', webot);
    form_data.append('origin', 'cave');
    form_data.append('captcha', grecaptcha.getResponse() ),
    
    $.each(files, function( index, value ) 
    {
       // console.log( index + ": " + value );
        console.log(value.name );
        form_data.append("files[]", value);
    });
    
    
	$.ajax(
	{
		type: 'post',
		url: 'contact.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success:function(data, textStatus, jqXHR)
				{
					loadingSpiner.delay(500).hide('slow');
                    
                    // reset form needed on contact.php
                    $('#mail-usermail').val('');
                    $('#mail-subject').val('');
                    $('#mail-content').val('');
                    document.getElementById('mail-files').value=''; 
                    
                    //reset recaptcha needed on contact.php
                    grecaptcha.reset(); 
                    
                    //close dialog if any
                    emptydialog();
                    showDialog(data.title, data.stateStr); 
                    
                    
                    
                    //restore scrolling option 
                    $('html, body').css({
                        overflow: 'auto',
                        height: 'auto'
                    });
                    
				},
		error: function(jqXHR, error, errorThrown)
				{
                    loadingSpiner.delay(500).hide('slow');
					var msg = JSON.parse(jqXHR.responseText);
					emptydialog(); //empty dialog main html for any present data
					closeDialog(); //force close
					showDialog(msg.title, msg.stateStr); //show new message windows
                    
                    //restore scrolling option 
                    $('html, body').css({
                        overflow: 'auto',
                        height: 'auto'
                    });
					return false
				},
	});
}

function emptydialog()
{
	$('#jqUiDialogContent').html('');
}

/*
 * Check email address validity
 */
function checkmailvalidity()
{
    console.log('check email address validity');
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    var email = $('#mail-usermail').val();
    if( !email.match(re) ) 
    {
        $('#display-email-inputwrapper').removeClass('valid-email').addClass('invalid-email');
         $('#mail-usermail').removeClass('valid');
         $('#mail-usermail').addClass('invalid');
         disablesendbtn('#contact-sendemail');
        return false;
    }
    else
    {
        $('#display-email-inputwrapper').removeClass('invalid-email').addClass('valid-email');
         $('#mail-usermail').removeClass('invalid');
         $('#mail-usermail').addClass('valid');
         enablesendbtn('#contact-sendemail');
        return true;
    }
};


/*
 * check global file size before upload
 */
$('#jqUiDialogContent, #contact-contactform').on('change', '#mail-files', function()
{
    console.log('Check files size');
    console.log('Limits file :' + maxfilesize);
    console.log('Max msg size :' + maxtotalfilessize);
    var totalSize = 0;
    var error = false;
    $.each(this.files, function (key,file)
    {
        totalSize = totalSize + file.size;
        if(file.size > maxfilesize)
        {
            console.log('File too large: '+file.name + '(' + file.size+')');
            error = true;
            
        }
        else if(totalSize > maxtotalfilessize)
        {
            console.log('Too larges attachements : '+totalSize);
            error = true;   
        }
        else{
            //none
        }
    });
    
    
    if (error != true)
    {
        $('#contact-fileserror').html('');
        enablesendbtn('#contact-sendemail')
    }
    else
    {
        $('#contact-fileserror').html('<div>One attachments or message too large</div>');
        disablesendbtn('#contact-sendemail')
    }    
    
    
}); 


/* is this obsolete ?*/

$('#contact-btn-send').on('click', function()
{
    console.log('this is not obsolete');
    var sender = $('#mail-usermail').val();
    var subject = $('#mail-subject').val();
    var body = $('#mail-content').val();
    var files = document.getElementById('mail-files').files; 
    var webot = $('#mail-webot').val();
    
   
    //show and hide a dummy Dialog box to prevent error into sendmail function
    showDialog('','');
    emptydialog(); //empty dialog main html for any present data
    closeDialog(); //force close
    
    sendmail(sender, subject, body, files, webot); 
});

var fields = ['mail-usermail','mail-subject','mail-content'];


/*
 * set subject field if from other page than contact.php
 * else subject is set in js from parent page.
 */
if(subject == null)
{
    console.log("subject not defined");
    var subject = 'objet';
}

// handle captcha status
if( mailUseCaptcha )
{
    // add captcha to form
    captchaDiv = '<div id="contact-wrapper-captcha">\
      <script src="https://www.google.com/recaptcha/api.js" async defer></script>\
      <div class="g-recaptcha" data-sitekey="' + captchaPubKey +  '"></div>\
    </div>';
    //disable captcha check
    disableCaptCheck = true;
}
else{
     //ENABLE captcha check
    disableCaptCheck = false;
    captchaDiv ='';
}


/*
 * generate send mail form
 *   note : mail-subject will be load by jquery on document load
 */
var sendEmailForm = '\
<div class="loadingSpiner"><i class="fas fa-spinner fa-pulse fa-3x"></i></div>\
<div class="display-emailform-field">\
  <label for="mail-usermail">' + email_usermail  + '</label>\
  <div id="display-email-inputwrapper">\
    <input class="" type="text" name="mail-usermail" id="mail-usermail"/>\
  </div>\
</div>\
<div class="display-emailform-field">\
  <label for="mail-subject">' + email_subject + '</label>\
  <input type="text" name="mail-subject" id="mail-subject" value=""/>\
</div>\
<div class="display-emailform-field">\
  <label for="mail-content">' + email_yourmessage + '</label>\
  <textarea name="mail-content" id="mail-content"></textarea>\
</div>\
<div class="display-emailform-field">\
  <label for="mail-files">' + email_attachfiles + '</label>\
  <input type="file" name="mail-files" id="mail-files" multiple/>\
  <div id="display-files-sizenotice">' +  contact_fileSizeNotice +  '. ' + contact_TotalFileSizeNotice + '</div>\
  <div id="contact-fileserror"></div>\
</div>\
<div class="display-emailform-field">\
  <input type="hidden" name="mail-webot" id="mail-webot"/>\
</div>' + captchaDiv;

function disablesendbtn(btnid)
{
    $(btnid).button('disable');
};

function enablesendbtn(btnid)
{
    $(btnid).button('enable');
};

/*
 * Check that user has click on im not a robot
 * (only if use captcha is set on website settings).
 */
function validateRecaptcha(disableCaptCheck) {
    console.log('Check if captcha has been triggered :');
    if(disableCaptCheck)
    {
        console.log('Captcha check is disabled !');
        return true;
    }
    var response = grecaptcha.getResponse();
    if (response.length === 0) {
        console.log(' captcha was NOT clicked');
        $('#contact-wrapper-captcha').addClass('invalidCaptcha');
        return false;
    } else {
         console.log(' captcha was clicked');
        $('#contact-wrapper-captcha').removeClass('invalidCaptcha');
        return true;
    }
}
