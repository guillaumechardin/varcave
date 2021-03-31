$(document).ready(function ()
{
	$("input[type='text'], textarea, input[type='checkbox']").not('#quickSearchInput, .coords, .edit-photoComment, .changeloginput').on("change", function() 
	{
		console.log('Changing: ' + this.name);
		console.log('with new value: ' + this.value);
		console.log('cave guid: ' + guid);
		jqEl = $(this);
        if(jqEl.attr("type") == "file" )
        {
            console.log('skip file type input');
            return false;
        }
		updateCave(this.name, this.value, jqEl);
		
	});
    
	
	$('body').on('change', 'input[class="edit-photoComment"]', function() 
	{
		name = $(this).attr('class');
		jqEl = $(this);
		value = this.value;
		elnumber = $(this).attr('data-elNumber');
		
		console.log('Changing: ' + name);
		console.log('index number : ' + elnumber);
		console.log('with new value: ' + value);
		console.log('cave guid: ' + guid);
		
		updateCave('photos', value , jqEl, 'edit', false, true, elnumber)
		
	});

	
	
	$('body').on('click', 'span[id|="edit-trash"]',  function() {
		console.log('try to delete: ' + $(this).attr('name') );
		var item = $(this).attr('name');
		var value = $(this).attr('data-elNumber');
		console.log('array id:'+value);
		jqEl = $(this);
		console.log('html element ' + jqEl);
		//updateCave(item, value, jqEl, actionType, fileInputProcess = false, json = false, elnumber = false, visiblity = false)
		updateCave(item, value, jqEl, 'delete', false, true);
	});
    
    
    
    $('span[id|="edit-addItem"]').on('click', function() {
        jqEl = $(this);
		console.log('try to add item to: ' + jqEl.attr('id') );
		var item = jqEl.attr('name');
        jqEl.siblings("form").show(500);
	});
    
    $('span[id|="sendFile"]').on('click', function() {
        jqEl = $(this);
        fileForm = jqEl.parent('form');
		console.log('try to send form file via ajax : ' + fileForm.attr('id')+' name='+fileForm.attr('name') );
		updateCave(fileForm.attr('name'), '', jqEl, 'add', true, true)
		//model updateCave(item, value, jqEl, actionType, fileInputProcess, json)
        fileForm.hide(1000);
	});
	
    $('body').on('click', 'i[id|="edit-rotLeft"]', function (){
        console.log('Click on rotate left');
        var imgPath = $(this).siblings('img').attr('src')
        console.log('try to rotate: '+imgPath);
        rotateImg('left', imgPath , $(this) );
        //update img src to force browser update
        
    });
        
    $('body').on('click', 'i[id|="edit-rotRight"]', function (){
        console.log('Click on rotate right');
        var imgPath = $(this).siblings('img').attr('src')
        console.log('try to rotate: '+imgPath);
        rotateImg('right', imgPath , $(this) );
        //update img src to force browser update
        
    });
    
    $('body').on('click', 'span[id|="edit-delCoordSet"]',  function() {
        var coordsSetIdx = $(this).attr('data-coordset')
        console.log('try to delete: ' + coordsSetIdx );
		jqEl = $(this);
        updateCaveCoords(coordsSetIdx, 'null', jqEl, 'del' )
	});
	
    
    /*
     * show form to add new coords 
     */
	$('body').on('click', 'span[id="edit-addItem-json_coords"]',  function() {
        console.log('try add new set of coords to #edit-json_coords' );
        $('#edit-add-coordset').show('slow');
        return true;
	});
    
    /*
     * event to check change of coordinates fields
     */
    $('body').on('change', 'input[class="coords"]', function(){
        jqEl = $(this);

        //check if consistent data are present to add new data
        var isNewCoordSet = jqEl.parent('.editCoords').parent('#edit-add-coordset').attr('data-isnewcoordset');
        if(isNewCoordSet == 1 && (jqEl.parent('.editCoords').children('input[data-coord="long"]').val() == '' || 
                                  jqEl.parent('.editCoords').children('input[data-coord="lat"]').val()  == '' ||
                                  jqEl.parent('.editCoords').children('input[data-coord="z"]').val()    == '' )
        ){
            console.log('all required fields not set');
            return false;
        }
        
        //add new data to dd
        var actionType = 'edit'; //default action type
        if(isNewCoordSet == 1){
            actionType = 'add';
        }
        
        var coordSystem = $('#coordSystem').val();
        var coordSetIdx = jqEl.parent('.editCoords').attr('data-coordSet');
        
        var values =  JSON.stringify({
                'long': jqEl.parent('.editCoords').children('input[data-coord="long"]').val(),
                'lat': jqEl.parent('.editCoords').children('input[data-coord="lat"]').val(),
                'z': jqEl.parent('.editCoords').children('input[data-coord="z"]').val()
        }); 

		console.log('cave guid: ' + guid);
		console.log('Changing coords index: ' + coordSetIdx);
		console.log('with new value: ' + values);
		updateCaveCoords(coordSetIdx, values, jqEl, actionType )
	});
	
	$('body').on('click', '.edit-add-changelog', function()
	{
		var jqEl = $(this);
		console.log(jqEl);
		var changelogTxt = $("#changelogEntry").val();
		var changelogVisibile = +$("#changelogEntryVisibility").is(":checked"); //'+' set bool equal 0/1 not true/false(string)
		console.log('Adding to log : ' + changelogTxt + ' with visibility: '+ changelogVisibile);
		updateCave('changelog', changelogTxt, jqEl, 'add', false, false,false,changelogVisibile)
	});
	
    /*
     * Change coordinate system inputs when user select another one
     */
    $('#coordSystem').on('change', function(){
        if( $('#coordSystem').val() == 'GEOGRAPHIC' ){
            console.log('show coord add button;');
            $('#edit-addItem-json_coords').show();
            //show back default coordinates form
        }
        else{
            //hide + tool to add coords list
            $('#edit-addItem-json_coords').hide();
        }
        var coordSystem = $(this).val();
        updateCoordsInputs(coordSystem);
    });
});



var loadingSpiner = $(".loadingSpiner");
/*
 *item is the target element to modify in db
 *value is the content to update
 *jqEl is the current jqElement calling the function
 * actionType is sting : either delete,update or add
 * fileInputProcess true or false, use this to specify if target operation is file update. form data 
    will be automatically identified
 * json specify if the target updated data in db is json
 * elnumber is the numbering in a numbered listStyleType
 * visibility is bool, and set the visibility on modification cave log
 */
function updateCave(item, value, jqEl, actionType, fileInputProcess = false, json = false, elnumber = false, visiblity = false)
{
	console.log ('update cave : item :'+item+' | value:'+value+' | actionType:'+actionType+' | fileInputProcess:'+fileInputProcess+' | json:'+json+'| elNumber:'+elnumber + '|visiblity:' + visiblity);
	var form_data = new FormData();
	form_data.append('update', 'true');
	form_data.append('item', item);
	form_data.append('value', value);
	form_data.append('guid', guid);
	form_data.append('elNumber', elnumber);
	form_data.append('actionType', actionType);
	form_data.append('visility', visiblity)
	form_data.append('target', 'normal')
    
	
	console.log('isjson:['+json+"]");
    if( jqEl.attr("type") == 'checkbox' )
    {
        console.log('checkbox value:' + jqEl.is(':checked') );
        form_data.append('checkbox', 1);
        
        if ( jqEl.is(':checked') )
        {
            form_data.append('checkboxValue', 1);
        }
        else
        {
            form_data.append('checkboxValue', 0);
        }
        
    }
    
	if(json == true)
	{
		form_data.append('json', 'true');
		form_data.append('actionType', actionType);
	}
	
    
	
	
    if(fileInputProcess == true)
    {
        var currentForm = jqEl.parent('form');
        var currentFormInput = currentForm.children('input').get(0);
        console.log('form input content : ');
        console.log(currentFormInput);
        var file = currentFormInput.files[0];
        var filename = currentFormInput.files[0].name;
        //var name = $(currentFormInput).attr("id");
        console.log("filename : "+filename);
        form_data.append('file', file, filename);
        //form_data = new FormData(currentForm);
        
    }
	
	
	loadingSpiner.toggle();
	$.ajax({
		type: 'post',
		url: 'editcave.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(data, textStatus, jqXHR)
				{
					updateSuccess(data, textStatus, jqXHR);
					if(data.actionType == 'delete')
					{
						console.log('remove element : ' + jqEl.name);
                        //hide and delete from DOM element
						$(jqEl).parent().hide('slow', function ()
							{
								$(this).remove();
							});
					}
                    if(data.actionType == 'add')
                    {
						if(item == 'changelog')
						{
							console.log('changelog operation.');

							$(changelogEntry).val(''); //reset input form to empty 
							var logData = JSON.parse(data.logData);
							logData = logData[0];

							//displaying data to changelog
							var newHtml ='';
							newHtml +=  '<li>';
							newHtml +=    '<i class="fas fa-edit fa-lg"></i>' + logData.date + ' » ' + logData.chgLogTxt;
							newHtml +=    ' <span name="changelog" data-elNumber="' + logData.indexid + '" id="edit-trash-changelog" class="fas fa-trash-alt"></span>';
							newHtml +=  '</li>';
							
							$('.displayChangeLog > ul').prepend(newHtml);
							
							return true;
						}
                        var name =  $(jqEl).parent('form').attr('name');
                        if (data.extension == 'jpg')
                        {
							if (name == 'photos')
							//handling special case with photo by adding rotate options
							{
								var newHtml =''; 
								newHtml += '<div id="edit-photos-elNumber-' + data.insertIndex + '">';
								newHtml += '  <i id="edit-rotLeft-photos-' + data.insertIndex + '" class="fas fa-undo fa-lg"></i>';
								newHtml += '  <img class="edit-CaveMini" src="' + data.newVal + '" />';
								//$count = strlen($value->comment) + 10;
								
								newHtml += '  <i id="edit-rotRight-photos-' + data.insertIndex + '" class="fas fa-undo fa-flip-horizontal fa-lg"></i>';
								newHtml += '  <input type="text" class="edit-photoComment" value="" data-elNumber="' + data.insertIndex + '"/>';
								newHtml += '    &nbsp;&nbsp;&nbsp;&nbsp';
								newHtml += '    <span class="fas fa-trash-alt fa-lg" name="photos" id="edit-trash-photos" data-elNumber="' + data.insertIndex + '">';
								newHtml += '    </span> ';
								newHtml += '</div>'; //edit-photos-elNumber-
								
								//console.log('add new added element to : #edit-photos');

							}
							else{
								var newHtml = '<div id="edit-imgEl-'+data.insertIndex+'"><i id="edit-rotLeft-'+name+'-'+data.insertIndex+'" class="fas fa-undo fa-lg"></i><img class="edit-CaveMini" src="'+data.newVal+'"><i id="edit-rotRight-'+name+'-'+data.insertIndex+'" class="fas fa-undo fa-flip-horizontal fa-lg"></i>  &nbsp;&nbsp;&nbsp;&nbsp;<span class="fas fa-trash-alt fa-lg" name="'+name+'" id="edit-trash-'+name+'" data-elNumber="'+data.insertIndex+'"></span> </div>'   
							}
						}
                        else
                        {
                            var newHtml = '<div id="edit-imgEl-'+data.insertIndex+'"><i class="far fa-file-alt fa-2x"> </i> '+data.filename+'&nbsp;&nbsp;&nbsp;&nbsp;<span class="fas fa-trash-alt fa-lg" name="'+name+'" id="edit-trash-'+name+'" data-elNumber="'+data.insertIndex+'"></span> </div>'
                        }
                        var appendEl = '#edit-'+name;
                        $(appendEl).append(newHtml);
                        console.log('add new added element to :' + appendEl);
                        $(jqEl).parent('form')[0].reset();
                    }
				},
		error: updateError,
	});
	
	loadingSpiner.delay(500).hide('slow');
	
	
}



function updateSuccess(json,state,jqXHR )
{
	//showDialog(json.title,json.stateStr);
	
	return true;
}

function updateError(jqXHR, error, errorThrown)
{
	loadingSpiner.hide();
	var msg = JSON.parse(jqXHR.responseText);
	showDialog(msg.title, msg.stateStr);
	return false;
}

function rotateImg(direction, path , el)
{
    var form_data = new FormData();
    form_data.append('guid', guid);
	form_data.append('update', 'true');
    form_data.append('rotate', direction);
    form_data.append('imgPath', path);
    //append dummy info
	form_data.append('item', 'none(rotate)');
	form_data.append('value', 'none(rotate)');
    
    $.ajax({
		type: 'post',
		url: 'editcave.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(data, textStatus, jqXHR){
            //update src of img to force browser update
            el.siblings('img').attr('src',data.newPath);
//            el.siblings('img').hide('slow');
            //el.siblings('img').attr('src',path);
            //el.siblings('img').show('slow');
            
        },
		error: updateError,
	});
    
}

/*
 * ask for update cave coordinates
 * coordSetIdx = id of coords in caves_coordinates table
 * values long/lat/z values to update as js array. ie : [4.255544,43.256814,552]
 * jqEl : current jquery input element that trigger update
 * action : add | edit | del depending user action
 */
function updateCaveCoords(coordSetIdx, values, jqEl, actionType )
{
	console.log('update cave coordinates'); 
    console.log('action type :'+actionType);
    
	var form_data = new FormData();
    form_data.append('guid', guid);
	form_data.append('coordSetIndex', coordSetIdx);
	form_data.append('values', values);
    form_data.append('actionType', actionType);
    form_data.append('target', 'coords');
    form_data.append('update', 'true');
    
    
    loadingSpiner.toggle();
	$.ajax({
		type: 'post',
		url: 'editcave.php',
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(data, textStatus, jqXHR)
				{
					//console.log('server response:')
					//console.log(data);
					updateSuccess(data, textStatus, jqXHR);
					if(data.actionType == 'del')
					{
						//remove element
						console.log('remove element : ' + coordSetIdx);
                        console.log(coordSetIdx);
                        //delete data from coordinatesList var 
                        //hide and delete from DOM element
						$(jqEl).parent().hide('slow', function ()
							{
								$(this).remove();
							});						
					}
                    
                    if(data.actionType == 'add')
                    {
                        //clear and hide new coord set
                        console.log('new data saved, clear form and add coord line');
                        values = JSON.parse(values); //restore back native json object
						$('#edit-add-coordset').hide('slow');
                        jqEl.parent('.editCoords').children('input[data-coord="long"]').val(''); 
                        jqEl.parent('.editCoords').children('input[data-coord="lat"]').val('');
                        jqEl.parent('.editCoords').children('input[data-coord="z"]').val('');
                        
                        var html ='';
                        html +=  '<div class="editCoords" data-coordSet="' + data.insertIndex + '">'; 
                        html += '   X:<input type="text" class="coords" data-coord="lat"  value="' + values.long +  '" />';
                        html += '   Y:<input type="text" class="coords" data-coord="long" value="' + values.lat +  '" />';
                        html += '   Z:<input type="text" class="coords" data-coord="z"    value="' + values.z +  '" />';
                        html += '  &nbsp<span class="fas fa-trash-alt fa-lg" data-coordSet="' + data.insertIndex + '" id="edit-delCoordSet-' + data.insertIndex + '"></span>';
                        html += '</div>';
                        
                        $('#edit-json_coords').append(html);
                        
                        
                    }
                    
                    if(data.actionType == 'modify'){
                    //nothing to do update has been done server side
                    }

				},
		error: updateError,
	});
	
	loadingSpiner.delay(500).hide('slow');
}

function updateCoordsInputs(coordSystem){
    //get a node list of available inputs
    console.log('target coordSystem: '+coordSystem);
    
    //flush content :
    var rootCoordNode = $('#edit-json_coords');
    rootCoordNode.html('');
    
    //add convert coords to current system and get back html content
    var newdata = window["getCoordEditBoxes_"+coordSystem](coordinatesList);
    //debug : console.log(newdata);
    
    rootCoordNode.append(newdata);
}


