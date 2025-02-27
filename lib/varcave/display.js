$(document).ready(function ()
{
	$("#previous,#next").on('click', function (){
		var caveGuid = $(this).attr('data-caveGuid');
		var arrowType = $(this).attr('id');
		
		var form_data = new FormData();
		form_data.append('nextPrev', caveGuid)
		
		$.ajax(
		{
			type: 'post',
			url: 'display.php',
			processData: false,
			contentType: false,
			data: form_data,
			dataType: "json",
			success: updateSuccess,
			error: updateError,
		});

		function updateSuccess(json,state)
		{
			
			if (json[0] == -1)
			{
				//no more than 1 cave in search 
				//remove arrow cave navbar
				$("span[id='previous'], span[id='next']").hide(700);
				
				setTimeout(function()
				{
					$("span[id='previous'], span[id='next']").remove();
				}, 2000);
				return true;
			}
			
			//console.log(json);
			//console.log(json.previous.guid);
			var targetGuid = null;
			if (arrowType == 'next')
			{
				console.log('click next')
				targetGuid = json.next.guid;
			}
			else
			{
				console.log('click previous')
				targetGuid = json.previous.guid;
			}
			//console.log
			window.location.href = "display.php?guid="+targetGuid;
			return false;
		}

		function updateError(jqXHR, error, errorThrown)
		{
			var msg = JSON.parse(jqXHR.responseText);			
			showDialog(msg.title, msg.stateStr);
			return false;
		}
	});
	
	$("#displayOpenMap").on('click', function(){
		openBigMap();
		
	});

	$('#coordSystem').on('change', function(){
		var coordSystem = $(this).val();
		updateCoords(coordSystem);
	});
	
	$('.display-gpx-dwnld').on('click', function(){
		var guid = $(this).attr('data-guid');
		window.open('getgpxkml.php?action=cave&gpx=1&guid='+guid);
		
	});
    
    $('.display-pdf-dwnld').on('click', function(){
		var guid = $(this).attr('data-guid');
		window.open('getpdf.php?guid='+guid);
		
	});
    
    //goto files section click 
    $('.display-files-dwnld').on('click', function(){
        var borderW  = $('.displaySciData').css('background-color');
		for(var i=0 ; i < 2 ; i++){
        window.location.href = '#display-files-section';
               $(".displaySciData").animate({backgroundColor:'gray'}, 400)
               .delay(400).animate({backgroundColor:borderW}, 400);
        }
        
    });
    
    
    $('.display-send-msg').on('click', function(){
        var guid = $(this).attr('data-guid');
        
        showDialog(newmessage,'');
        showDialog(newmessage,'');
        // define Dialog behavior
        $('#jqUiDialog').dialog("option", "width", "30%");
        $('#jqUiDialog').dialog("option", "buttons",
            [
                {
                    id: 'contact-sendemail',
                    text: send,
                    click: function()
                    {
                        // no action                        
                    },
                },			
        ]);
         
        //populate data into <input>'s form
        $('#jqUiDialogContent').append(sendEmailForm);
        
        /* 
         * set mail subject because this field can change from pages to pages.
         * var subject is set in display.php
         */
        var decoded = $('<div/>').html(subject).text();  //prenvent html entities into 
        $('#mail-subject').val(decoded);
		var decoded = $('<div/>').html(mailBody).text();  //prenvent html entities into 
		$('#mail-content').val(decoded);
	
	
        
        $('#jqUiDialog').dialog("option", "position", 
		{
			my: "center",
			at: "center",
			of: "body",
			collision: "none"
		});
    });
	
    $('#delete-cave').on('click', function(event){
        event.preventDefault();
        var acceptDel = '<div class="display-acceptDelWrap"><br/><label for="delete">' + iAccept + '</label><input name="delete" type="checkbox" id="display-acceptDel"/></div>';
        showDialog(infoTitle, deleteCaveMsg);
        $('#jqUiDialogContent').append(acceptDel);
        
        $('#jqUiDialog').dialog("option", "buttons", 
            [
                {
                    text: "cancel",
                    click: function() {
                        $('#jqUiDialogContent').html('');
                        $( this ).dialog( "close" );
                    },
                },
                {
                    text: "OK",
                    click: function() {
                        var guid = $('#delete-cave').data('guid');
                        deleteCave(guid);
                        $( this ).dialog( "close" );

                    },
                }
            ]
        );
        $(".ui-dialog-buttonpane button:contains('OK')").attr("disabled", true).addClass("ui-state-disabled");
     });
     
     $('#jqUiDialog').on('change', '#display-acceptDel', function(){
         if( $(this).is(':checked') ){
            $(".ui-dialog-buttonpane button:contains('OK')").attr("disabled", false).removeClass("ui-state-disabled");
         } else{
             $(".ui-dialog-buttonpane button:contains('OK')").attr("disabled", true).addClass("ui-state-disabled");
         }
         
     });
    
    //activate zoom-master on cave maps
    $(".displayCaveMapsImg").elevateZoom({
        scrollZoom: true,
        tint:true,
        tintColour:'#F90',
        tintOpacity:0.5,
        zoomWindowOffetx: 10,
    });
     
	//request server to toggle cave favorite
	$('.display-set-favorite').on('click', function(evt)
	{
		console.log('request toggle favorites');
    	var form_data = new FormData();
	
		form_data.append('guid', $(this).data('guid') );
		form_data.append('action', 'toggleCaveToFav');
		
		var starIcon = $(this).find('[data-fa-i2svg]');
		console.log(starIcon);

		$.ajax(
		{
			type: 'post',
			url: 'myaccount.php',
			processData: false,
			contentType: false,
			data: form_data,
			dataType: "json",
			success:function(data, textStatus, jqXHR)
					{
						console.log('cave added to fav');
						if(data.state=='saved')
						{
							console.log('set solid class');
							starIcon.removeClass().addClass('fas fa-star');
						}
						else
						{
							console.log('set regularclass');
							starIcon.removeClass().addClass('far fa-star');

						}
						
						//starIcon.remove();

						
						
						
					},
			error: function(data, textStatus, jqXHR){return false},
		});

		return true;		
	});
        
});

var loadingSpiner = $(".loadingSpiner");


function openBigMap()
{
	console.log('Cloning map');
	console.log( $('#miniMap').height() );
	/*
	 * get current display size and compute a value of 45% to
	 * display the bigMap
	 */
	 var windowsSize = $(window).height();
	 var mapSize =  windowsSize * 0.85; //70%
	 console.log('window size :' + windowsSize);
	 console.log('computed map size height :' + mapSize); 

	
	//removing headers of dialog() around 140px
	$('#miniMap').css('height', mapSize - 140); 
	
	//if exist a small sketch to access map, minimap is display:none.
	if(miniMapHidden)
	{
		$('#miniMap').show();
	}
		
	$("#jqUiDialogContent").dialog(
		{
			title: "MINIMAP",
			resizable: false,
			height: mapSize,
			width: "70%",
			modal: true,
			closeOnEscape: false,
			open: function(event, ui) 
				{
					$(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                    var mapNode = map.getTarget();
                    mapNode = document.getElementById(mapNode);
                    $('#jqUiDialogContent').append(mapNode);
                    map.addControl(new ol.control.Zoom() );
                    map.updateSize();
				},
			buttons: {
				Ok: function()
				{
					$( this ).dialog( "close" );
					restoreMiniMap();
				}
			}
		});
    
		
	
}

function restoreMiniMap()
{
    //show minimap as small img in main page.
	if(miniMapHidden)
	{
		$('#miniMap').hide();
	}
	console.log('restore minimap');
	//original height is 250px
	$('#miniMap').css('height', 250); 
	var mapNode = map.getTarget();
    mapNode = document.getElementById(mapNode);
    
    //remove minimap controls
    map.getControls().forEach(function(control) {
         console.log(control);
      if (control instanceof ol.control.Zoom) {
        map.removeControl(control)
      }
    }, this);

    
	$('.displayCaveAccessImg').prepend(mapNode);
    map.updateSize();
}

	
function updateCoords(coordSystem)
{
	// for each coords set, get value and convert to 
	// a new coordinate system.
    
    // call canonical function
    // coordinatesList is a global var as a geojson obj
    var newdata = window["convert2"+coordSystem](coordinatesList);
    console.log(newdata);
    
    //delete the current coord list
    $('#coordList > li').hide('slow', function(){ 
        $(this).remove(); 
        });
    
    //add new data.
    newdata.forEach(function(coord) {
        $("#coordList").append(
            '<li>' + coord.string + '</li>'
            );
    });
}

/*
 * ask server to delete cave if permited
 */
function deleteCave(guid){
    console.log('Exec cave deletion');
    var form_data = new FormData();
	
	form_data.append('guid', guid);
	form_data.append('delete', true);
	loadingSpiner.toggle();
	
	$.ajax(
	{
		type: 'post',
		url: 'editcave.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success:function(data, textStatus, jqXHR)
				{
					console.log('delete succeed.');
                    console.log(data);
                    if(data.leftToDel != ''){
                        var msg = data.stateStr + ' ' + notDeletedItems + ': '  + data.leftToDel;
                    }
                    else{
                        var msg = data.stateStr;
                    }
                    showDialog(data.title, msg);
                    $('.userPageContent').children('*').not('#jqUiDialog').hide('slow');
                    
				},
		error: updateError,
	});
	
	loadingSpiner.delay(500).hide('slow');
	return true
}



	
	
