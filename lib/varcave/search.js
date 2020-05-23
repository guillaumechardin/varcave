$(document).ready(function()
{	
	$("#caveSearchForm").on('submit', function(e) 
	{
		console.log("starting search request");
		e.preventDefault();
        $(".loadingSpiner").show(200);
		doSearch();
		
	});
	
    /*
     * Display or hide the betweend x and y field 
     * if the user choosen type is between or other 
     */
	$("select").change(function()
	{
		//console.log( $(this).attr('name') );
		//console.log( $(this).val() );
		
		if($(this).val() == 'BETWEEN')
		{
			var selectName =  $(this).attr('name');
			
			var shortName  = selectName.replace('type_','');
			console.log('input[name=\'value_'+shortName+'\']');
			$('input[name=\'value_'+shortName+'\']').val('');
			$('*[name|=\'value_'+shortName+'\']').show(); //ALL BETWEEN FIELDS
			$('*[name=\'value_'+shortName+'\']').hide(); //"STANDARD" input field
		}
		else
		
		{
			
			var selectName =  $(this).attr('name');
			var shortName  = selectName.replace('type_','');
			$('*[name|=\'value_'+shortName+'\']').hide();
			$('*[name|=\'value_'+shortName+'\']').val('');
			$('*[name=\'value_'+shortName+'\']').show();
			
			
		}
		
	});
	
	

	
	
	
});

/*
 * call search process
 * Set datatable page number in get request to get it back later
 */
function doSearch(formData)
{
	if( formData != null)
	{
		console.log('get request from post array:');
		var myForm = formData;
		var mycontentType = 'multipart/form-data';
	}
	else
	{
		console.log('get request from form:');
		var myForm = $("#caveSearchForm").serialize();
		var mycontentType = 'application/x-www-form-urlencoded'
	}
	
	$.ajax(
	{
				type: 'POST',
				url: 'search.php',
				processData: false,
				contentType: mycontentType,
				data: myForm,
				dataType: "json",
				success: searchSuccess,
				error: searchError,
	});
	
	return true;
		
}


// outside scope of function to get easilyaccess from other func.
var table = null;
function searchSuccess (json,state)
{
    $(".loadingSpiner").hide(); 
	//remove content of search page and replace with table and results
	$("#caveSearchForm").remove();
	
	//populate table body
	var datatableJson = [];
	$.each(json.caves, function(index, caveData) 
	{
		datatableJson.push(caveData);
	});
	
	
	var colDefs = [];
	$.each(json.colsName, function(colName, coldisplayName) 
	{
		//hide guid columns from form.
		if (colName == 'guidv4')
		{
			console.log('hidding col guidv4');
			colDefs.push({"data":'guidv4', 'visible':false});
		}
		else
		{
			colDefs.push({"data":colName, 'title':coldisplayName});
		}
		
	});
	
	console.log('Json return from $ajax');
	console.log(json);
	console.log('Columns definitions');
	console.log(colDefs);
	
	
	//add sufficient table row footer
	var tCols = colDefs.length;
	var trContent = '<tr>';
	var numCOl = tCols-1; //minus 1 to have better to not include the Action col.
	trContent += '<th class="searchStatFooter" colspan="'+numCOl+'" > ' + search_totalDepth + ' : ' + json.totalDepth + 'm | ' + search_totalLength + ': ' + json.totalLength + 'm</th>';
		
	trContent += '</tr>'
	$("#tableSearch > tfoot").append(trContent);	
	
	
	table =  $("#tableSearch").DataTable(
	{
		"rowId": 0,
		"pageLength": maxSearchResults,
		"language":   i18nMenus,
		//"jQueryUI":   true,
		"data":       datatableJson,
		"columns":    colDefs,
		"order": [], //ordering by data read order
		select: {
            //style:    'single', //disable row selection
            blurable: true,
        },
        "footerCallback": function ( tfoot, data, start, end, display )
        {
			
			var api = this.api();
			console.log(api.column( 4 ).data() );
			$( api.column( 4 ).footer() ).html('<a href="#">OK<');
		},
	});
	
	//$("#tableSearch").DataTable().columns( [0] ).visible( false );
	
	
	
	
	table.on( 'select', function ( e, dt, type, idx )
	{
		/*if (type=='cell')
		{
			
		}*/
		if ( type === 'row' )
		{
			//var RowId = table.row( this ).index();
			console.log ('table id :');
			console.log(idx);	
			console.log('guid Cavité:');
			console.log(dt.row(idx).data().guidv4);
			
		
		}
    });
	
};
	
$('.showCaveBtn').on("click", function(e)
{
    e.preventDefault();
    var targetLnk = ($this).attr('href');
    console.log('target is ' + targetLnk);
    
});


function searchError(jqXHR, error, errorThrown)
{
	var msg = JSON.parse(jqXHR.responseText);
	showDialog(msg.title, msg.stateStr);
    $(".loadingSpiner").hide();
	return false;
};

$('#tableSearch').on( 'page.dt', function () {
    $('html, body').animate({
        scrollTop: 0
    }, 300);
} );

