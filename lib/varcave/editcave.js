$(document).ready(function ()
{
	$("input[type='text'], textarea, input[type='checkbox']").not('#quickSearchInput, .coords, .edit-filenote, .changeloginput, .file-upload-selector').on("change", function() 
	{
		console.log('Changing: ' + this.name);
		console.log('with new value: ' + this.value);
		console.log('cave guid: ' + guid);
		jqEl = $(this);
		updateCave(this.name, this.value, jqEl);
		
	});
    
	
	$('body').on('change', 'input[class="edit-filenote"]', function() 
	{
		jqEl = $(this);
		var name = jqEl.parent('div').attr('id');
        var itemid = jqEl.attr('data-id');
        var value = jqEl.val();
		
        console.log('Changing : filenote' + name);
		console.log('with new value: ' + value);
		
        updateCaveFiles(guid, 'edit', '', itemid, value, jqEl);		
	});

	$('body').on('click', 'span[class|="edit-file-delete"]',  function() {
		console.log('try to delete: ' + $(this).attr('data-filetype') );
		var jqEl = $(this);
		var itemid = jqEl.attr('data-id');
		console.log('itemid:'+itemid);
        //run update
        updateCaveFiles(guid, 'del', 'none', itemid, 'none', jqEl)        
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
        filetype = fileForm.attr('data-filetype')
		console.log('try to send form file via ajax : ' + fileForm.attr('id')+' filetype = ' + filetype );
		updateCaveFiles(guid, 'add', filetype, '', '', jqEl)
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
        console.log('disable cooridnate system changes');
        $('#coordSystem').attr('disabled',true);
        return true;
	});
    
    /*
     * event to check change of coordinates fields
     */
    $('body').on('change', 'input[class="coords"]', function(){
        jqEl = $(this);

        //check if consistent data are present to add new data
        var isNewCoordSet = jqEl.parent('.editCoords').parent('#edit-add-coordset').attr('data-isnewcoordset');
        var coordSetIdx = jqEl.parent('.editCoords').attr('data-coordset');
        
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
        console.log('current coordSystem : '+coordSystem);
        //convert data on the fly if coord system is not geographical
        if (coordSystem == 'GEOGRAPHIC'){
            var values =  JSON.stringify({
                    'long': jqEl.parent('.editCoords').children('input[data-coord="long"]').val(),
                    'lat': jqEl.parent('.editCoords').children('input[data-coord="lat"]').val(),
                    'z': jqEl.parent('.editCoords').children('input[data-coord="z"]').val()
            }); 
        }else{
            
            var newdata = window[coordSystem+"_2longlat"](coordSetIdx);
            console.log('Coords conversion to be uploaded:');
            console.log(newdata);
            var values =  JSON.stringify({
                    'long': newdata.y,
                    'lat': newdata.x,
                    'z': newdata.z
             });
        }   
        
        
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
		var changelogVisible = +$("#changelogEntryVisibility").is(":checked"); //'+' set bool equal 0/1 not true/false(string)
		console.log('Adding to log : ' + changelogTxt + ' with visibility: '+ changelogVisible);
		updateCave('none', changelogTxt, jqEl, changelogVisible, 'changelog', 'add');
	});
	
    /*
     * Delete log entry
     */
    $('body').on('click', '.edit-trash-changelog', function()
	{
		var jqEl = $(this);
		var changelogid = jqEl.attr('data-elnumber');
		console.log('Deleting log : [' + changelogid + ']');
		updateCave(false, changelogid, jqEl, false, 'changelog', 'delete');
	});
    
    /*
     * Change coordinate system inputs when user select another one
     */
    //$('#coordSystem').on('change', function(){
     $('body').on('change', '#coordSystem', function(){
        if( $('#coordSystem').val() == 'GEOGRAPHIC' ){
            console.log('show coord add button;');
            $('#edit-addItem-json_coords').show();
            //show back default coordinates form
        }
        else{
            console.log('hide delete coord tools');
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
function updateCave(item, value, jqEl, visiblity = false, target = 'normal', actionType = false)
{
	console.log ('update cave : item :'+item+' | value:'+value+' | target:'+target+' | visiblity:'+visiblity);
	var form_data = new FormData();
	form_data.append('update', 'true');
	form_data.append('item', item);
	form_data.append('value', value);
	form_data.append('guid', guid);
	form_data.append('visility', visiblity)
	form_data.append('target', target)
	form_data.append('actionType', actionType)
    
    
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
						if(target == 'changelog')
						{
							console.log('changelog operation.');

							$('#changelogEntry').val(''); //reset input form to empty 
							var logData = JSON.parse(data.logData);
							logData = logData[0];

							//displaying data to changelog
							var newHtml ='';
							newHtml +=  '<li>';
							newHtml +=    '<i class="fas fa-edit fa-lg"></i>' + logData.date + ' » ' + logData.chgLogTxt;
							newHtml +=    ' <span name="changelog" data-elNumber="' + logData.indexid + '" class="edit-trash-changelog fas fa-trash-alt"></span>';
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
                        
                        //remove data from coordList Array. THis array is used for coord conversion in other coordinates systems
                        var longitude = parseFloat( jqEl.parent('.editCoords').children('input[data-coord="long"]').val() );
                        var latitude = parseFloat( jqEl.parent('.editCoords').children('input[data-coord="lat"]').val() );
                        
                        coordList.features.forEach( function(item,i) {
                            console.log(item.geometry.coordinates+' search : '+longitude+'|'+latitude);
                            if( item.geometry.coordinates.includes(longitude) && item.geometry.coordinates.includes(latitude) ){  //indexof return -1 if not found
                                console.log('array found, index is :['+i+']');
                                delete coordList.features[i] == null;
                            }
                            console.log('not found in array');
                        }, longitude, latitude);

                        //hide and delete from DOM element
                        $(jqEl).parent().hide('slow', function () {
                            $(this).remove();
                        });						
					}
                    
                    if(data.actionType == 'add')
                    {
                        //clear and hide new coord set
                        console.log('new data saved, clear form and add coord line:');
                        console.log(data.insertIndex);
                        values = JSON.parse(values); //restore back native json object
                        //restore coordinates system change 
                        $('#coordSystem').removeAttr('disabled');
						$('#edit-add-coordset').hide('slow');
                        
                        jqEl.parent('.editCoords').children('input[data-coord="long"]').val(''); 
                        jqEl.parent('.editCoords').children('input[data-coord="lat"]').val('');
                        jqEl.parent('.editCoords').children('input[data-coord="z"]').val('');
                        
                        var html ='';
                        html +=  '<div class="editCoords" data-coordset="' + data.insertIndex + '">'; 
                        html += '   X:<input type="text" class="coords" data-coord="lat"  value="' + values.long +  '" />';
                        html += '   Y:<input type="text" class="coords" data-coord="long" value="' + values.lat +  '" />';
                        html += '   Z:<input type="text" class="coords" data-coord="z"    value="' + values.z +  '" />';
                        html += '  &nbsp<span class="fas fa-trash-alt fa-lg" data-coordset="' + data.insertIndex + '" id="edit-delCoordSet-' + data.insertIndex + '"></span>';
                        html += '</div>';
                        
                        $('#edit-json_coords').append(html);
                        
                        //update coordList to reflect new adde row
                        coordList.features.push (
                            {
                                "type":"Feature",
                                "geometry": {
                                    "type": "Point",
                                    "coordinates":  [parseFloat(values.long), parseFloat(values.lat) , parseFloat(values.z)] 
                               },
                                "properties":{
                                    "id": data.insertIndex
                               }
                            });
                    }
                    
                    if(data.actionType == 'edit'){
                        //update coordList to reflect changes
                        coordList.features.forEach( function(item,i) {
                            console.log(item.properties.id +' search : '+coordSetIdx);
                            if( item.properties.id == coordSetIdx ){ 
                                console.log('array found, index is :['+i+'] updating coordlist');
                                values = JSON.parse(values);  //restore a native json object not a string
                                coordList.features[i].geometry.coordinates = [ parseFloat(values.lat), parseFloat(values.long), parseFloat(values.z) ];
                            }else{
                                console.log('not found in array');
                            }
                        }, longitude, latitude);
                        
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
    var newdata = window["getCoordEditBoxes_"+coordSystem](coordList);
    //debug : console.log(newdata);
    
    rootCoordNode.append(newdata);
}

/*
 * update data, add or delete cave files
 */
function updateCaveFiles(guid, actiontype, filetype, itemid, values, jqEl)
{
	console.log('update cave files'); 
    console.log('action type :'+actiontype+' on:'+filetype+'|'+itemid);
    
    // start building formdata
    var form_data = new FormData();
    //general options
    form_data.append('target', 'files');
    form_data.append('update', 'true');
    
    //user input options
    form_data.append('guid', guid);
    form_data.append('actiontype', actiontype);
    form_data.append('filetype', filetype);
    form_data.append('itemid', itemid);
    form_data.append('filenote', values);
    
    
    
    //specific action and settings 
    if(actiontype == 'add'){
        var currentForm = jqEl.parent('form');
        //current added files
        var currentFormInput = currentForm.children('input').get(0);
        var file = currentFormInput.files[0];
        var filename = currentFormInput.files[0].name;
        console.log("filename : "+filename);
        form_data.append('file', file, filename);
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
                    /*
                     * return is 
                     * data.insertIndex
                     * data.filename
                     * data.extension
                     * data.faicon
                     */
					if(actiontype == 'add'){
                        //add new file to form
                        
                        var newfilehtml = '<div id="edit-' + filetype + '-id-' + data.insertIndex + '" data-id="' + data.insertIndex  +'" class="edit-fileentry">';
                        
                        if (data.extension  != 'jpg')
                        {
                                newfilehtml += '<i class="'+ data.faicon + ' fa-2x"></i> ' + data.filename ;
                        }
						else
                        {
                                newfilehtml += '<i id="edit-rotLeft-' + filetype + '-' + data.insertIndex + '" class="fas fa-undo fa-lg"></i>';
                                newfilehtml += '<img class="edit-CaveMini" src="' + data.fullpath + '" />';
                                newfilehtml += '<i id="edit-rotRight-' + filetype + '-' + data.insertIndex + '" class="fas fa-undo fa-flip-horizontal fa-lg"></i>';
                                if(filetype == 'photos')
								{
									 newfilehtml += '<input type="text" class="edit-filenote" value="" data-id="' + data.insertIndex + '"/>';
								}
                        }
                       
                        newfilehtml += '  &nbsp;&nbsp;&nbsp;&nbsp;<span class="fas fa-trash-alt fa-lg edit-file-delete" data-id="' + data.insertIndex + '" data-filetype="' + filetype + '"></span> ';
                        newfilehtml += '</div>'; 
                        
                        //append to existing section
                        $('#edit-'+filetype).append(newfilehtml);
                        
                        //clear form content
                        $('#fileSelector-'+filetype).val('');
                        
                    }
                    else if (actiontype == 'del'){
                        //remove div containing file data
                        console.log("remove div file:")
                        console.log($(jqEl).parent('div'));
                        $(jqEl).parent('div').hide('slow', function ()
                        {
                            $(this).remove();
                        });
                    }
                    

				},
		error: updateError,
	});
	
	loadingSpiner.delay(500).hide('slow');
}
