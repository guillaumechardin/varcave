<?php
    /*
     * This sample script show how to register a new openlayer Layer type plugin for varcave
    */
    require_once(__DIR__ . '/../varcave.class.php');

    $pluginName = 'Landscape OSM thunderforest';
    $pluginShortName = 'TDF_LANDSCAPE';  //no space. All locale function *MUST* start by this identifier followed by _

    $TDF_LANDSCAPE_config_register = array( 
                    array(
                        'configItem'=> $pluginShortName . '_TDF_API_KEY', 
                        'configItemValue'=> 'yourapikey', 
                        'configItemType'=> 'text', 
                        'configItemGroup'=> 'geoAPI', 
                        'adminOnly' => 0,
                        'configItem_dsp' => 'Clef API thunderforest (' . $pluginShortName .')',
                        'configItem_hlp' => 'C\'est une aide formidable',
                        'pluginGUID' => 'e98e16d2-3eca-46c6-a6e0-932a39a4b3ec',
                        'pluginShortName' => $pluginShortName,
                        'pluginName' => $pluginName,
                    ),
            );

    $varcave = new varcave();
    $key = $varcave->getConfigElement("TDF_LANDSCAPE_TDF_API_KEY");
    $TDF_LANDSCAPE_jsdata = <<<EOT
    var TDF_LANDSCAPE = new ol.layer.Tile({
            title: "{$pluginName}",
			name: "{$pluginShortName}",
            source: new ol.source.OSM({
                url: "https://tile.thunderforest.com/landscape/{z}/{x}/{y}.png?apikey={$key}",
            })
	});
EOT;

    function TDF_LANDSCAPE_getJS($asString = true )
    {
        if ($asString == true)
        {
            return $TDF_LANDSCAPE_jsdata;
        }
        else
        {
            header('Content-Type: application/javascript');
            echo $TDF_LANDSCAPE_jsdata;
        }
    }

?>