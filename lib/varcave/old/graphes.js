
var csvdata = "";
var graphes = [];




$( function() {
	if (glog_data == false)
	{
		//on ne construit pas les graphes.
		console.log("pas de graphes/donnees");
		return false;
	};
	
		
    from = $( "#from" )
    .datepicker({
        dateFormat: "dd/mm/yy",
        changeMonth: true,
        changeMonth: true,
        changeYear: true,
        
    })
    .on( "change", function() {
       to.datepicker( "option", "minDate", getDate( this ) );
    }),
    to = $( "#to" ).datepicker({
        dateFormat: "dd/mm/yy",
        changeMonth: true,
        changeMonth: true,
        changeYear: true,
        
    })
    .on( "change", function() {
        from.datepicker( "option", "maxDate", getDate( this ) );
    });
    
    //initialiastion des onglets
    $( "#graphes_data_glog" ).tabs();
	
    
    //changement du lien du CSV lors de la selection de la date.
    $("#from, #to").on("change", changeCsvDLLink );
	
	//a l'init seulement le graphe 0
	createGraph(0);
	$('#g-0').hide();
	
	//$( "#from" ).datepicker( "setDate", minDatems );
	//$( "#to" ).datepicker( "setDate", maxDatems );
	
    /*
	
	
	$( "[id^='list-graphe-']").click( function ()
	{
		graphNum = $(this).attr( "num" );
		graphes[graphNum].resize();
	});
	
    affichage des graphes

	*/
}); 
	
function createGraph(tabid)
{
	console.log("Création graphe : g-" + tabid);
	graphDivName="g-"+ tabid;
	graphes[tabid] = new Dygraph(
		document.getElementById(graphDivName),
		"\n", {
			//rollPeriod: 2,
			//showRoller: true,
			legend: "always",
			labelsDiv : "legende-" + tabid,
			labelsSeparateLines: true,
			xlabel: "date",
			stackedGraph: false,
			hideOverlayOnMouseOut: false,
			connectSeparatedPoints: false,
		}); // options
		//resizeGraphPageLoad(graphes[tabid],$("#corps_page_contenu_glog_data"),$('#g-' + tabid) );
		console.log("Graphe[" + tabid + "] créé");
}

//cette fonction rafraichi les données du graphes de l'onglet courant
$("#btn-refresh-graph").click( function refreshcsvdata()
{
    //recuperation date au format dd/mm/yy
    var fromDate = toMysqlDate( $("#from").val() );
    var toDate = toMysqlDate( $("#to").val() );
    var releve = $('#graphSelectRange > select[name="selectSite"]').val();
	//récuperation n° tab courant
	var tabActif = $( "#graphes_data_glog" ).tabs( "option", "active" );
	console.log("graphe actif:"+tabActif);
	
    if (fromDate == null | toDate == null)
    {
        console.log("from or to : empty");
    }
    
	geturl = 'getcsv.php?'
	data = { 
			'from' : fromDate,
			'to' : toDate,
			'cavite' :  cavite,
			'releve' : releve,
			'download': 0,
		};
	$.each(data, function(index, value) {
		geturl += index +'='+value+'&';
	}); 
	$(".loadingGif").show("slow");
    $.ajax({
        type: "GET",
        url: geturl,
        graph:tabActif,
        //data: data,
        cache: false,
        success: function(data){
				refreshgraph(data,this.graph,graphes);
				changeGraphSelectors(this.graph);
				resizeGraphtoDiv(graphes[this.graph], $('#tab-'+this.graph) , $('#g-'+this.graph) );
		},
        error: function(jqXHR, error, errorThrown) {
					var ret = JSON.parse(jqXHR.responseText);
					var msg = ret.msg;
					$(".loadingGif").hide(1);
                    alert("Erreur du traitement :\n" + errorThrown + "\n" + msg + "\n" );
                    //location.reload(true);
                    return false;
                },
    });
});

function changeCsvDLLink()
{
	var fromDate = toMysqlDate( $("#from").val() );
    var toDate = toMysqlDate( $("#to").val() );
    var releve = $('#graphSelectRange > select[name="selectSite"]').val();
	var a = $("#dl_fichier_csv");
	var getobj = { 
			'from' : fromDate,
			'to' : toDate,
			'cavite' :  cavite,
			'releve' : releve,
			'download': 1,
		};
	var geturl = 'getcsv.php?'
	$.each(getobj, function(index, value) {
		geturl += index +'='+value+'&';
	});
	
	a.attr("href", geturl);
	return true;
	
}

function refreshgraph(datacsv, graphNum,graphes) {
	console.log('mise a jour du graphe:[' + graphNum +']');
    graphes[graphNum].updateOptions({
        'file': datacsv, 
    });
	graphes[graphNum].resetZoom();
    $(".loadingGif").delay(200).hide("slow");
	$('#g-'+graphNum).show();
};


function resizeGraphtoDiv(g, parentdiv, graphdiv)
{
	var size =  parentdiv.width() * 0.95 ; //reduction de 10% pour rentrer dans la div
	console.log("new graph size="+size);
	graphdiv.width(size);
	g.resize();
}

function toMysqlDate(date) {
	var dateArr = date.split("/")

	var mysqlDate = dateArr[2] + '-' + dateArr[1] + '-' + dateArr[0] + ' 00:00:00';
	console.log('mysql date : ' + mysqlDate);
	return mysqlDate;
}

function changeGraphSelectors(graphNum)
{
	var labels = graphes[graphNum].getLabels() ;
	var colsNum = graphes[graphNum].numColumns() -1 ; // l' axe x compte pour 1 col on ne peut/souhaite pas l'enlever
	console.log('nombre de series : ' + colsNum );

	var html = '';
	for  (var i = 0; i < colsNum ; i++) {
		
		html += "<input type=checkbox id=\"" + i + "\" checked onClick=\"changeVisibility(this,graphes[" + graphNum + "])\">"
		html += "<label for=\"" + i + "\">" +  labels[i+1] + "</label><br/>";
		//on force l'affichage de tous les series
		console.log('force affichage serie : ' + i );
		graphes[graphNum].setVisibility(i, true);
	}
	//console.log(html);
	$("#selectors-"+graphNum).html( html );
}

function changeVisibility(el,g)
{
        g.setVisibility(parseInt(el.id), el.checked);
}

function getDate( element ) {
	var date;
	try 
	{
		date = $.datepicker.parseDate( dateFormat, element.value );
	} catch( error ) 
	{
		date = null;
	}

	return date;
}	  



$('#graphSelectRange > select[name="selectSite"]').on("change", function()
{
	//console.log($(this).val());

	$("#datepicker").attr("disabled",false);
	$('#graphSelectRange select[name="selectSite"] option[value="0"]').remove();
	
	var url = 'cavite=' + cavite + '&releve=' + $(this).val();
	var tabActif = $("#graphes_data_glog").tabs( "option", "active" );
	
	$.ajax({
        type: "GET",
        url: "getglogmeta.php",
        tabid: tabActif,
        data: url,
        cache: false,
        success: function(data){
				showGrapheMeta(data,this.tabid);
				setDatePicker(data);
		},
        error: function(jqXHR, error, errorThrown) {
					var ret = JSON.parse(jqXHR.responseText);
					showDialog("#modalmsg p[name='msgcontent']",'ERREUR',ret.msg);
                    return false;
                },
    });
})

function showDialog(dialogSel,titre,msg)
{

	$( dialogSel ).html(msg);
	$( dialogSel ).dialog(
	{
		title: titre,
		modal: true,
		buttons: {
			Ok: function()
			{
				$( this ).dialog( "close" );
				$( dialogSel ).html("");
			},
		},
	});
}

function showGrapheMeta(json,tabid)
{
	console.log("active tab :" + tabid);
	var dateMin = json.dateMin;
	var dateMax = json.dateMax;
	
	var tabinfozone = $("#graphmeta-" + tabid);
	var html = "";
	html += '<div>Période de mesure disponible :';
	html += '<ul> \
				<li> Du '  + dateMin + '</li> \
				<li>Au ' + dateMax + '</li>\
				<li>Infos : ' + json.info + '</li>\
			</ul></div>';
	tabinfozone.html(html);
}

function setDatePicker(json)
{
	console.log(json);
	minDate = new Date(json.minDateUnixTime);
	maxDate  = new Date(json.maxDateUnixTime);
	 
	$( "#from" ).datepicker( "option", "minDate", minDate);
	$( "#from" ).datepicker( "option", "maxDate", maxDate);
	$( "#from" ).datepicker( "setDate", minDate);
	
	$( "#to" ).datepicker  ( "option", "minDate", minDate); 
	$( "#to" ).datepicker  ( "option", "maxDate", maxDate);
	$( "#to" ).datepicker  ( "setDate", maxDate);
	
}
