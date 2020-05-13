<?php
	
header('Content-Type: application/javascript');
require_once(__DIR__ . '/varcaveCave.class.php');
//require_once(__DIR__ . '/functions.php');
//require_once(__DIR__ . '/../Klogger/logger.php');

$cave = new varcaveCave();


try
{
	
	//check if user set geoapi and if caveguid is set
	if (!isset($_GET['caveguid']) && !isset($_GET['api']) && $_GET['caveguid'] != ''  && $_GET['api'] != '' )
	{
		echo 'alert("No api or uid")';
		exit();
	}
	
	$caveData = $cave->selectByGUID($_GET['caveguid'], 0, false);
	if ( ! $caveData)
	{
		echo 'alert("getjsgeoapi: no cave found")';
		exit();
	}
	
	$coordsObj = json_decode($caveData['json_coords']);
    $coordList = $coordsObj->features[0]->geometry->coordinates;
		
}
catch (Exception $e)
{
	$cave->logger->error('Cannot select cave by GUID');
	echo 'alert("getjsgeoapi: Cave selection error")';
	exit();
}


//if using googlemaps or geoportail data, 
if($_GET['api'] == 'googlemaps' || $_GET['api'] == 'geoportail')
{
    $cave->logger->debug('getjsgeoapi.js : selected Geoapi is :' . $_GET['api']);
    $cave->logger->debug('getjsgeoapi.js :  coord list : ' . print_r($coordList,true) );
    
	$js = 'var map = "";';
	
	$js .= 'function initMap()';
	$js .= '{';
	$js .= '	map = new google.maps.Map(document.getElementById("miniMap"), ';
	$js .= '	{';
	$js .= '		zoom: ' . $cave->getConfigElement('gApi_zoom_lvl') . ',';
	$js .= '		center: {lat:' . $coordList[0][1] . ',lng:' . $coordList [0][0] . '},';
	$js .= '        mapTypeControlOptions : {
	                                       position: google.maps.ControlPosition.TOP_RIGHT, 
	                                       style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
	                                       mapTypeIds: ["satellite","terrain"]
	                                       },'; // terrain, roadmap and satellite are a default bundle of GM
	$js .= '        mapTypeControl: false,';

		
	$js .= '		disableDefaultUI: true,';
	$js .= '		mapTypeId: "satellite",';
	$js .= '		zoomControl: false,';
	$js .= '		gestureHandling: "none",';

	$js .= '	});';

	$i = 1;
	foreach($coordList as $coord)
	{
		$js .= 'var marker = new google.maps.Marker(';
		$js .= '{';
		$js .= '	position: {lat:' . $coord[1]. ', lng:' . $coord[0] . '},' ;
		$js .= '	map: map,';
		$js .= '	title: "' . $caveData['name'] . '",';
		$js .= '	label: ';
		$js .= '	{';
		$js .= '		color: "white",';
		$js .= '		fontSize: "2em",';
		$js .= '		fontWeight: "bold",';
		$js .= '		text: "' . $i . '",';
		$js .= '	},';
		$js .= '});';
		$i++;
	}
	if ($_GET['api'] == 'geoportail')
	{
		/*
		 * photos IGN
		 */
		$js .= 'var photosIgn=new google.maps.ImageMapType(';
		$js .= '{';
		$js .=  '    getTileUrl: function(tileCoord,zoom)';
		$js .=  '    {';
		$js .=  '        url="https://wxs.ign.fr/' . $cave->getConfigElement('geoportail_api_key') . '/geoportail/wmts?"+
                                "LAYER=ORTHOIMAGERY.ORTHOPHOTOS"+
                                "&FORMAT=image/jpeg&SERVICE=WMTS&VERSION=1.0.0"+
                                "&REQUEST=GetTile&STYLE=normal&TILEMATRIXSET=PM"+
                                "&TILEMATRIX="+zoom+"&TILEROW="+tileCoord.y+
                                "&TILECOL="+tileCoord.x;';
		$js .=  '        return url; ';                    
		$js .=  '     },';
		$js .=  '     tileSize: new google.maps.Size(256,256),';
		$js .=  '     name: "Photos IGN",';
		$js .=  '     maxZoom: 18';
		$js .=  '});';
		$js .= 'map.mapTypes.set("IGNLayer",photosIgn);';
		$js .= 'map.mapTypeControlOptions.mapTypeIds.push("IGNLayer");';
		$js .= 'map.mapTypeId = "IGNLayer";';
		
		
		// end photos IGN
		
		/*
		 * source cartes IGN
		 */
		$js .= 'var cartesIgn=new google.maps.ImageMapType(';
		$js .= '{';
		$js .=  '    getTileUrl: function(tileCoord,zoom)';
		$js .=  '    {';
		$js .=  '        url="https://wxs.ign.fr/' . $cave->getConfigElement('geoportail_api_key') . '/geoportail/wmts?"+
                                "LAYER=GEOGRAPHICALGRIDSYSTEMS.MAPS"+
                                "&FORMAT=image/jpeg&SERVICE=WMTS&VERSION=1.0.0"+
                                "&REQUEST=GetTile&STYLE=normal&TILEMATRIXSET=PM"+
                                "&TILEMATRIX="+zoom+"&TILEROW="+tileCoord.y+
                                "&TILECOL="+tileCoord.x;';
		$js .=  '        return url; ';                    
		$js .=  '     },';
		$js .=  '     tileSize: new google.maps.Size(256,256),';
		$js .=  '     name: "Cartes IGN",';
		$js .=  '     maxZoom: 18';
		$js .=  '});';
		 
		$js .= 'map.mapTypeControlOptions.mapTypeIds.push("IGNMapLayer");';
		$js .= 'map.mapTypes.set("IGNMapLayer",cartesIgn);';
		//$js .= 'map.mapTypeId = "IGNMapLayer";';
		
		
		$js .= 'map.setZoom(18);';
		$js .= 'console.log(map);';


		
	}
	$js .= '}';
	
	echo $js;
}
elseif ($_GET['api'] == 'none')
{


}
else
{
	echo 'alert("API not supported")';
}


?>
