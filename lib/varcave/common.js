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
	
	//this get a UTM zone for a know longitude. Take long as only arg.
	// It does not work for some area (ie : norway and Svalbard)
	//see https://stackoverflow.com/questions/9186496/determining-utm-zone-to-convert-from-longitude-latitude
	function long2UTMZone(long)
	{
		return (Math.floor( (long + 180)/6) % 60) + 1;
	}


	// this function get UTM latitude band for a given 
	// latitude in degrees
	// inspired from 
	// https://gis.stackexchange.com/questions/238931/utm-coordinates-and-knowing-how-to-get-the-grid-zone-letter
	function getUTMLatBand(lat)
	{

		var bandLetters = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
		//int latz = 0;//Latitude zone: A-B S of -80, C-W -80 to +72, X 72-84, Y,Z N of 84

		if (lat > -80 && lat < 72) {
			//= floor((lat + 80)/8)+2;
			return bandLetters.charAt(Math.floor( (lat+80)/8) +2);
		}
		if (lat > 72 && lat < 84) {
			return bandLetters.charAt(21);
		}
		if (lat > 84){
			return bandLetters.charAt(23)
		}
	}
