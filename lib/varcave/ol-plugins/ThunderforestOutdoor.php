<?php
    /*
     * This sample script show how to register a new openlayer Layer type plugin for varcave
    */
    require_once(__DIR__ . '/../varcave.class.php');

    $pluginName = 'Outdoor OSM thunderforest';
    $pluginShortName = 'TDF_OUTDOOR';  //no space. All locale function *MUST* start by this identifier followed by _

    $TDF_OUTDOOR_config_register = array( 
                    array(
                        'configItem'=> $pluginShortName . '_TDF_API_KEY', 
                        'configItemValue'=> 'yourapikey', 
                        'configItemType'=> 'text', 
                        'configItemGroup'=> 'geoAPI', 
                        'adminOnly' => 0,
                        'configItem_dsp' => 'Clef API thunderforest (' . $pluginShortName .')',
                        'configItem_hlp' => 'C\'est une aide formidable',
                        'pluginGUID' => 'f06c3c2a-8229-4211-af0f-f5f888b779a3',
                        'pluginShortName' => $pluginShortName,
                        'pluginName' => $pluginName,
                    ),
            );

    $varcave = new varcave();
    $key = $varcave->getConfigElement("TDF_OUTDOOR_TDF_API_KEY");
    $TDF_OUTDOOR_jsdata = <<<EOT
    var TDF_OUTDOOR = new ol.layer.Tile({
            title: "{$pluginName}",
			name: "{$pluginShortName}",
            source: new ol.source.OSM({
                url: "https://tile.thunderforest.com/outdoors/{z}/{x}/{y}.png?apikey=${key}"
            })
	});
EOT;

    function TDF_OUTDOOR_getJS($asString = true )
    {
        if ($asString == true)
        {
            return $TDF_OUTDOOR_jsdata;
        }
        else
        {
            header('Content-Type: application/javascript');
            echo $TDF_OUTDOOR_jsdata;
        }
    }

?>