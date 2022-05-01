<?php
    /*
     * This sample script show how to register a new openlayer Layer type plugin for varcave
    */
    require_once(__DIR__ . '/../varcave.class.php');

    $pluginName = 'openTopoMap';
    $pluginShortName = 'OPENTOPOMAP';  //no space. All locale function *MUST* start by this identifier followed by _

    $OPENTOPOMAP_config_register = array( 
                    array(
                        'configItem'=> null, 
                        'pluginGUID' => '48563c2a-7231-8214-bfec-a1f778c768c9',
                        'pluginShortName' => $pluginShortName,
                        'pluginName' => $pluginName,
                    ),
            );

    $varcave = new varcave();
    $key = $varcave->getConfigElement("OPENTOPOMAP_TDF_API_KEY");
    $OPENTOPOMAP_jsdata = <<<EOT
    var OPENTOPOMAP = new ol.layer.Tile({
            title: "{$pluginName}",
			name: "{$pluginShortName}",
            source: new ol.source.OSM({
                url: "https://b.tile.opentopomap.org/{z}/{x}/{y}.png"
            })
	});
EOT;

    function OPENTOPOMAP_getJS($asString = true )
    {
        if ($asString == true)
        {
            return $OPENTOPOMAP_jsdata;
        }
        else
        {
            header('Content-Type: application/javascript');
            echo $OPENTOPOMAP_jsdata;
        }
    }

?>