$(document).ready(function ()
{
	$("#previous,#next").on('click', function ()
	{
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
			console.log
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
	
	$("#displayOpenMap").on('click', function()
	{
		openBigMap();
		
	});

	$('#coordSystem').on('change', function()
	{
		var coordSystem = $(this).val();
		updateCoords(coordSystem);
	});
	
	$('.display-gpx-dwnld').on('click', function()
	{
		var guid = $(this).attr('data-guid');
		window.open('getgpxkml.php?gpx=1&guid='+guid);
		
	});
    
    $('.display-pdf-dwnld').on('click', function()
	{
		var guid = $(this).attr('data-guid');
		window.open('getpdf.php?guid='+guid);
		
	});
    
    $('.display-files-dwnld').on('click', function()
	{
		window.location.href = '#display-files-section';
	});
    
    
    $('.display-send-msg').on('click', function()
    {
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
        
        $('#jqUiDialog').dialog("option", "position", 
		{
			my: "center",
			at: "center",
			of: "body",
			collision: "none"
		});
    });
	
});


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
	//restore visibility to true !
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
				},
			buttons: {
				Ok: function()
				{
					$( this ).dialog( "close" );
					restoreMiniMap();
				}
			}
		});
		
		var mapNode = map.getDiv();
		$('#jqUiDialogContent').append(mapNode);
		
		map.zoomControl = true;
		map.gestureHandling = true;
		map.mapTypeControl = true;
		
		google.maps.event.trigger(map, "resize");



	
}

function restoreMiniMap()
{
	if(miniMapHidden)
	{
		$('#miniMap').hide();
	}
	console.log('restore minimap');
	//original height is 250px
	$('#miniMap').css('height', 250); 
	var mapNode = map.getDiv();
	$('#displayOpenMap').prepend(mapNode);
	
	map.zoomControl = false;
	map.gestureHandling = "none";
	map.mapTypeControl = false;
	google.maps.event.trigger(map, "resize");
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
    $('#coordList >li').hide('slow', function(){ 
        $(this).remove(); 
        });
    
    //add new data.
    newdata.forEach(function(coord) {
        $("#coordList").append(
            '<li>' + coord.string + '</li>'
            );
    });
    
    /*
	$('#coordList >li').each(function (index)
	{
		var id = $(this).attr('data-id');
		var defaultx = $(this).attr('data-defaultx');
		var defaulty = $(this).attr('data-defaulty');
		console.log( 'Process coord set' + id) ;
		//get current x/y values
		var x = $('#x-'+id).text();
		var y = $('#y-'+id).text();
		var coordsArr = {'x':parseFloat(x),'y':parseFloat(y)};
		
		switch(coordSystem)
		{
			case 'UTM':
				//get current utm zone
				var utmZone = long2UTMZone( coordsArr.x );
				var zoneBand = getUTMLatBand( coordsArr.y );
				console.log('UTM zone: ' + utmZone + 'letter :' + zoneBand );
				
				//prepare convert
				var dest = ('+proj=utm +zone=' + utmZone +' +units=m');
				var source = ('+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs');
				
				//toogle UTM zone display zone
				$('#display-utmzone-'+id).text(utmZone+''+zoneBand);
				$('#display-utmzone-'+id).hide().toggle(100);
				var converted = proj4(source,dest, coordsArr);
				console.log( 'converted : X:'+converted.x + ' Y:' +converted.y );
				
				// update value fields
				$('#x-'+id).text( Math.round(converted.x) );
				$('#y-'+id).text( Math.round(converted.y) );
				
				
				break;
				
			case 'GEOGRAPHIC':
				//hide utm zone 
				$('#display-utmzone-'+id).toggle(100);
				
				//restore default values
				$('#x-'+id).text( defaultx );
				$('#y-'+id).text( defaulty );
				break;
			default:
				console.log('Unsupported coordinate system');
		}
	});
     * */

}
	


	
	
