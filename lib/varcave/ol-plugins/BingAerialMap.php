<?php
    /*
     * This sample script show how to register a new openlayer Layer type plugin for varcave
    */
    require_once(__DIR__ . '/../varcave.class.php');

    $pluginName = 'Carte bing photos aeriennes';
    $pluginShortName = 'BING_AERIAL';  //no space. All locale function *MUST* start by this identifier followed by _

    $BING_AERIAL_config_register = array( 
                    array(
                        'configItem'=> $pluginShortName . '_API_KEY', 
                        'configItemValue'=> 'YOURAPIKEY', 
                        'configItemType'=> 'text', 
                        'configItemGroup'=> 'geoAPI', 
                        'adminOnly' => 0,
                        'configItem_dsp' => 'Clef API bing (' . $pluginShortName .')',
                        'configItem_hlp' => 'C\'est une aide formidable',
                        'pluginGUID' => '57b52f1d-1442-48b4-a1a3-e0a69730d9e7',
                        'pluginShortName' => $pluginShortName,
                        'pluginName' => $pluginName,
                    ),
            );

    $varcave = new varcave();
    $key = $varcave->getConfigElement("BING_AERIAL_API_KEY");
    $BING_AERIAL_jsdata = <<<EOT
    var BING_AERIAL = new ol.layer.Tile({
      title: "{$pluginName}",
	  name: "{$pluginShortName}",
      preload: Infinity,
      source: new ol.source.BingMaps({
        key: "{$key}",
        imagerySet: 'aerial',
        maxZoom: 19
      })
    });
EOT;

    function BING_AERIAL_getJS($asString = true )
    {
        if ($asString == true)
        {
            return $BING_AERIAL_jsdata;
        }
        else
        {
            header('Content-Type: application/javascript');
            echo $BING_AERIAL_jsdata;
        }
    }

?>