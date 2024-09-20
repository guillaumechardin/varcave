<?php
	
header('Content-Type: application/javascript');
require_once(__DIR__ . '/varcaveCave.class.php');
require_once(__DIR__ . '/varcaveHtml.class.php');
//require_once(__DIR__ . '/functions.php');
//require_once(__DIR__ . '/../Klogger/logger.php');

$cave = new varcaveCave();
$varcaveHTML = new varcaveHTML('dummy');

try
{
	
	//check if user set geoapi and if caveguid is set
	if (!isset($_GET['caveguid']) && $_GET['caveguid'] != '' )
	{
		$varcaveHTML->stopWithMessage(L::errors_error, L::errors_badGuid, 400, 'BAD REQUEST');
	}
	
	$caveData = $cave->selectByGUID($_GET['caveguid'], 0, false);
	if ( ! $caveData)
	{
		$varcaveHTML->stopWithMessage(L::errors_error, L::varcaveCave_caveSelectFailed, 400, 'BAD REQUEST');
	}
	
	$coordsObj = json_decode($caveData['json_coords']);
    $coordList = $coordsObj->features;
	
}
catch (Exception $e)
{
	$cave->logger->error(__FILE__ . ': Cannot select cave by GUID : ' . $e->getmessage() );
	$varcaveHTML->stopWithMessage(L::errors_error, __FILE__ . ': getjsgeoapi: Cave selection error', 400, 'BAD REQUEST');
}

$plugins = $varcaveHTML->getOlRegisteredPlugins();
$actives = array();
$JSLayersObj ='';
foreach($plugins as $key => $plugin)
{
    if(boolval($plugin['is_active']) == false)
    {
        //$actives[] = $plugin;
        continue;
    }
    require_once(__DIR__ . '/ol-plugins/' . basename($plugin['path']));

    //build some JS array with all map (map_name is same syntax as JS var handling js ol object)
    $JSLayersObj .= $plugin['map_name'] . ', ';
    $tmpVar = $plugin['map_name'] . '_jsdata';
    echo ($$tmpVar);

}

$JSdata = 'var map = false;';
$JSdata .= 'var lsControl = false;';

$JSdata .= 'var map = new ol.Map({';
$JSdata .= '    layers: ['.  $JSLayersObj . '],';
$JSdata .= '    target: "miniMap", /*HTML id target where map is render*/';
$JSdata .= '    view: new ol.View({';
$JSdata .= '        /*projection: "EPSG:4326",*/';
$JSdata .= '        center: new ol.proj.fromLonLat(';
$JSdata .= '            ['. $coordList[0]->geometry->coordinates[1] . ','. $coordList[0]->geometry->coordinates[0] . '],'; //first cave coordinate to center view
$JSdata .= '            "EPSG:3857"';
$JSdata .= '        ),';
$JSdata .= '    zoom: ' . $varcaveHTML->getConfigElement('ol_zoom_map_lvl') . ',';
$JSdata .= '    })';
$JSdata .= '});';

//start POI
$poiNbr = 0;
$JSpoi = '';
$JSdata .= 'var poi = [';
    //add all points for current cave 
    foreach($coordList as $key => $coord)
    {
        $JSdata .= 'new ol.Feature({';
        $JSdata .= '     name: ' . json_encode($caveData['name']) .',';
        $JSdata .= '     main: "main",';
        $JSdata .= '     geometry: new ol.geom.Point(';
        $JSdata .= '         ol.proj.fromLonLat(';
        $JSdata .= '             [' .  $coord->geometry->coordinates[1] . ',' .  $coord->geometry->coordinates[0] . ']';
        $JSdata .= '         )';
        $JSdata .= '     ),';
        $JSdata .= '}),';

        //add point style for current cave point
        $JSpoi .= <<<EOT
        poi[{$poiNbr}].setStyle(
            new ol.style.Style({
                           
                           image: new ol.style.Icon({
                              src: "img/marker_green_64.png",
                              anchor: [0.5, 1],
                              scale: 0.5,
                           }),
                           text: new ol.style.Text({
                              /*text: "{$poiNbr}",*/
                              scale: 1.5,
                              fill: new ol.style.Fill({
                                color: "#111"
                              }),
                              stroke: new ol.style.Stroke({
                                color: "0",
                                width: 0.5
                              }),
                              backgroundFill: new ol.style.Fill({
                                color: "#FFF"
                              }),
                              padding: [1,1,1,1],
                              offsetY: -40,
                           }) 
        
            })
        );
EOT;
        $poiNbr++;
    }
$JSdata .= '];';//end of POI

/* 
 *** start nearCavesPoi ***
 */
$coordOrigin =  $coordList[0]->geometry->coordinates[0] . ',' . $coordList[0]->geometry->coordinates[1];
$nearCaves = $cave->findNearCaves($coordOrigin, $cave->getConfigElement('near_caves_max_radius'), $cave->getConfigElement('near_caves_max_number'), $caveData['indexid'], false );

if($nearCaves != false){
    $nearPoiNbr = 0;
    $JSdata .= 'var nearCavesPoi = [';
    //add all points for near caves 
    foreach($nearCaves as $key => $coord)
    {
        $JSdata .= 'new ol.Feature({';
        $JSdata .= '     name: ' . json_encode($coord['name']) .',';
        $JSdata .= ' url: "' . $cave->getConfigElement('httpdomain') . '/display.php?guid=' . $coord['guidv4'] . '",';
        $JSdata .= '     geometry: new ol.geom.Point(';
        $JSdata .= '         ol.proj.fromLonLat(';
        $JSdata .= '             [' .  $coord['lat'] . ',' .  $coord['long'] . ']';
        $JSdata .= '         )';
        $JSdata .= '     ),';
        $JSdata .= '}),';

        //add point style for current cave point
        $JSpoi .= <<<EOT
        nearCavesPoi[{$nearPoiNbr}].setStyle(
            new ol.style.Style({
                           
                           image: new ol.style.Icon({
                              src: "img/marker_red_64.png",
                              anchor: [0.5, 1],
                              scale: 0.5,
                           }),
                           text: new ol.style.Text({
                              text: "{$coord['name']}",
                              scale: 1.5,
                              fill: new ol.style.Fill({
                                color: "#fff"
                              }),
                              stroke: new ol.style.Stroke({
                                color: "#fff",
                                width: 0.5,
                              }),
                              backgroundFill: new ol.style.Fill({
                                color: "red"
                              }),
                              padding: [3,1,1,1],
                              offsetY: -20,
                           }) 
        
            })
        );
EOT;
        $nearPoiNbr++;
    }

$JSdata .= '];';//end of nearCavesPoi



}
else{
    //create an issue with cave poi not show if not set to false
    $JSdata .= 'var nearCavesPoi = false;';

}

$JSdata .= $JSpoi;
$caveMarkers = L::olconfigplugins_cave_markers;
$nearCaves = L::olconfigplugins_near_caves;
$JSdata .= <<<EOT
var caveMarkers = new ol.layer.Vector({
    title: "{$caveMarkers}",
    name: "cavemarkers",
     source: new ol.source.Vector({
         features: poi,
     })
 });

 var nearCavesMarkers = new ol.layer.Vector({
    title: "{$nearCaves}",
    name: "nearcaves",
     source: new ol.source.Vector({
         features: nearCavesPoi,
     })
 });


map.addLayer(caveMarkers);
map.addLayer(nearCavesMarkers);


var layersSw = [];
map.getLayers().forEach( function (element,id,array)
{
    layersSw.push({
            layer: element,
            config: {
                title: element.get("title"),
                description: "none",
            }
    });
});

lsControl = new ol.control.LayerSwitcher({
        layers : layersSw, 
        options : {
            collapsed: true
        }
    });
map.addControl(lsControl);

map.on("click", function (evt) {
    const feature = map.forEachFeatureAtPixel(evt.pixel, function (feature) 
    {
        return feature;
    });
    if (feature.get("url") != undefined) 
    {
        url = feature.get("url");
        console.log(url);
        window.open(url, "_blank");
    }
});

// change mouse cursor when over marker
map.on("pointermove", function (evt) {
    var feat="";
    var hit = this.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
        feat = feature;
        return true;
    }); 
    if (hit && feat.get("main") != "main" ) {
        this.getTargetElement().style.cursor = "pointer";
    } else {
        this.getTargetElement().style.cursor = "";
    }
});
EOT;

/*
 * set default layer
 * set default map type, is set on varcaveconstruct for anonymous
 * or via account mgmt for users
 */
$varcaveHTML->logger->debug(basename(__FILE__) . ' set Openlayers default map type to ' . $_SESSION["geo_api"]);
$JSdata .= <<<EOF
map.getLayers().forEach( function (layer,id,array){
    if(layer.get("name") == "{$_SESSION["geo_api"]}")
    {
        layer.setZIndex(1);
    }
    else if(layer.get("name") == "cavemarkers" || layer.get("name") == "nearcaves")
    {
        layer.setZIndex(3);
    }
    else
    {
        layer.setZIndex(0);
    }
})
EOF;

echo $JSdata;
?>
