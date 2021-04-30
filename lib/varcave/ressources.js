$(document).ready(function()
{
    console.log('togle state');
		console.log( $('.ressources-toggleform').hasClass('fas') );
    
    if(debuglevel != null && debuglevel == "debug"){
        var ret = {
            'responseText' : '{"title" : "Warning", "stateStr" : "Debug mode on, gpx build may fail"}'
                };
        updateError(ret, null, null);
    };
    
	//show form to upload a file
	$('#ressources-upload > span, #ressources-form-title, .ressources-toggleform-hint').click(function(e){
		$('.ressources-toggleform-hint').toggle();
		$('#ressources-uploadform').delay(100).toggle('fold');
		
        $('.ressources-toggleform')
        .find('[data-fa-i2svg]')
        .toggleClass('fa-caret-down')
        .toggleClass('fa-caret-right');
	});
	
	// permit use of form validation button if all required fields are populated
	$('#ressources-uploadform > input').change(function() {
		var empty = false;
		var emptyfile = false;
        
		//check content of input text and textarea
		$('#ressources-uploadform > input, #file').each(function() {
            if ($(this).val().length == 0) {
                empty = true;
				console.log( $(this).attr('id') + 'is empty' );
            }
			
	
			if (empty || emptyfile)
			{
				$('#ressources-savefile').attr('disabled', 'disabled');
			}
			else
			{
				$('#ressources-savefile').removeAttr('disabled');
			}
		});


		
	});
	
	//send data to server
	$('#ressources-savefile').on('click', function (e){
		console.log('saving...')
		updateFile('add', false, $(this) )
	});
	
	//delete file
	$('#available-ressources').on('click', '.ressources-deletefile', function(e){
		var id = $(this).attr('data-id')
		console.log('delete ' + id );
		updateFile('delete', id , $(this) );
	});
	
	// download file
	$('.ressources-download').on('click', function(e){
		var url = $(this).attr('data-url');
		location.replace(url);
		return true;
		
	});
	
    //start GPX file build
    $('#ressources-genGPX').click(function () {
        console.log('genGPX action started');
        updateFile('buildgpx', '0' , '0');
    });
    
});


var loadingSpiner = $('.loadingSpiner');

//update files ressources on server
// action = 'add' | 'delete' | 'update'
// id = id of edited file
function updateFile(action, id , jqEl){
	loadingSpiner.toggle();
	
	var form_data = new FormData();
		
	console.log('update files action is  : ' + action );
	form_data.append('action', action);
	
	if (action == 'add'){
		var display_group = $('#display_group').val();
       
		var display_name = $('#display_name').val();
		var description = $('#description').val();
		
		form_data.append('display_group', display_group );
		form_data.append('display_name', display_name );
		form_data.append('description', description );
		form_data.append('access_rights', '');
		
		
		//preparing files to send
		var fileInput = $('#file').get(0); 
		var file = fileInput.files[0];
        var filename = fileInput.files[0].name;
		form_data.append('file', file, filename);
		
	}
	else if (action == 'delete'){
		form_data.append('id', id );
	}	
	else if (action == 'update'){
		form_data.append('id', jqel.attr('data-id') );
		form_data.append('item', jqel.attr('id') );
		form_data.append('itemvalue', jqel.attr('id') );
		
		
	}
    else if(action == 'buildgpx'){
        form_data.append('action','buildgpx');
    }
	else
	{
		console.log('action is not supported');
		return false;
	}
	
	$.ajax(
	{
		type: 'post',
		url: 'ressources.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success:function(data, textStatus, jqXHR)
				{
                    console.log(data);
					console.log('update succeed.');
					if(action == 'add' || action == 'buildgpx')
					{

                        var description = data.description;
                        var display_name = data.display_name;
                        var display_group = data.display_group;
                        
						var newItem = '\
						<div class="ressources-fileitem">\
							<div id="ressources-filelink-' + data.newid +'">\
								<span class="' + data.faIcon + ' fa-4x"></span>\
							</div>\
							<div class="ressources-item"><a href=" ' + data.newfile + '">' + display_name + ' </a></div>\
							<div class="ressources-item">' + display_name + '</div> \
							<div class="ressources-item italic">' + description +  '</div>\
							<div class="ressources-item center-txt">\
								<span class="fas fa-trash-alt fa-lg ressources-deletefile" data-id="' + data.newid + '"></span>\
							</div>\
						</div>';
					  
						//check if display group already exist, if not createDocumentFragment
						//a new 'segment' in page
						
						console.log('append to new element' + display_group );
						var appendEl = $('#ressources-displayGroup-'+display_group.toLowerCase()+' > .ressources-displayGroup' );
						if ( $( appendEl ).length )
						{
							console.log('display group exists');
							$( appendEl ).append(newItem);
						}
						else
						{
							console.log('inexistant display group, adding new one')
							var newData = '	<div id="ressources-displayGroup-' + display_group.toLowerCase() + '">\
											   <h4 class="ressources-displayGroup-title">' +display_group + '</h4>\
											   <div class="ressources-displayGroup">' +
											   newItem + 
											   '</div>\
											</div>';
							$('#available-ressources').append(newData);	
						}
						
						//clear fields
						$('#display_group').val('');
						$('#display_name').val('');
						$('#description').val('');
						$('#file').val('');
						$('#ressources-savefile').attr('disabled','disabled');
						
					}
					else if(action == 'delete')
					{
						console.log('deleting elements');
						//remove corresponding div from DOM
						console.log( jqEl );
						jqEl.closest('.ressources-fileitem').hide('slow', function ()
							{
								jqEl.closest('.ressources-fileitem').remove();
							});
						
						
						
					}
                    loadingSpiner.delay(500).hide('slow');
				},
		error: updateError,
	});

    function updateError(jqXHR, error, errorThrown){
        loadingSpiner.delay(500).hide('slow');
        loadingSpiner.hide();
        var msg = JSON.parse(jqXHR.responseText);
        showDialog(msg.title, msg.stateStr);
        return false;
        
        
    }
		
}