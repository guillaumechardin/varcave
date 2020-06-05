$(document).ready(function() 
{
	$(".quickSearchBtn").on("click", function()
	{
		redirectToSearch();
	});
		
	$('#quickSearchInput').keyup(function(e)
	{
		console.log(e.keyCode);
		if(e.keyCode == 13)
		{
			redirectToSearch();
		}
	});
    
    $('#quickSearchInput').bind("enterKey",function(e){
		redirectToSearch;
	});
    
    
    $('#alert-bar-close').on("click", function() {
        $('#alert-bar').hide('slow');
        document.cookie = "close-alert=1;max-age=3600";
    });
    
    
    if (readCookie('close-alert') == 1)
    {
        $('#alert-bar').css('display','none');
    }
});
	

//clear field content must be text only
function clearTextField(jQfieldName)
{
    $(jQfieldName).val('');
    return true
};

//reset field content to default value (if exists)
function restoreText(jQfieldName)
{
    
    //$(jQfieldName).val() = $(jQfieldName).prop('defaultValue');
    return true;
};

/**
 * show simple  jQuery UI modal dialog box 
 * whith specified text and OK button
*/
function showDialog(title, msg, duration=0, jQueryUiDivId = "jqUiDialog", jQueryUiDivIdContentMsg = "jqUiDialogContent" )
{
    // must use this to get back from htmlentities
    var decodedMsg = $('<textarea/>').html(msg).text();
    var decodedTitle = $('<textarea/>').html(title).text();
    
    $("#" + jQueryUiDivIdContentMsg).text(decodedMsg);
    
    $("#" + jQueryUiDivId).dialog({
    show: { effect: "blind", duration: duration },
      title: decodedTitle,
      resizable: false,
      height: "auto",
      width: 400,
      modal: true,
      buttons: {
        "OK": function() {
          $( this ).dialog( "close" );
          
        },
      }
    });
    return;
}

/*
 *Close the Dialog box
 */
 function closeDialog(jQueryUiDivId = "jqUiDialog")
 {
    $("#" + jQueryUiDivId).dialog('close');
 }

/*
 * Redirect to search page when user use quick search tool
 */
function redirectToSearch()
{
    console.log('redirect');
    var caveName = $("#quickSearchInput").val();
    window.location.href = 'search.php?quicksearch=' + caveName;
}

function decodeHtmlentities(string)
{
    var txt = document.createElement("textarea");
    txt.innerHTML = string;
    return txt.value;		
}


/* 
 * A quick function to read cookie by name
 * https://stackoverflow.com/questions/9056171/why-does-jquery-cookie-not-actually-set-a-cookie
 */
var readCookie = function(name) {
    var nameEQ = name + '=';
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
};


function isEmpty(value){
  return (value == null || value === '');
}
	
	
