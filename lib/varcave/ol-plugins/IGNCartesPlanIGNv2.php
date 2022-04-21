<?php
    /*
     * This sample script show how to register a new openlayer Layer type plugin for varcave
    */
    require_once(__DIR__ . '/../varcave.class.php');

    $pluginName = 'IGN  plan v2';
    $pluginShortName = 'IGN_PLAN';  //no space. All locale function *MUST* start by this identifier followed by _

    $IGN_PLAN_config_register = array( 
                    array(
                        'configItem'=> $pluginShortName . '_IGN_API_KEY', 
                        'configItemValue'=> 'essentiels', 
                        'configItemType'=> 'text', 
                        'configItemGroup'=> 'geoAPI', 
                        'adminOnly' => 0,
                        'configItem_dsp' => 'Clef API geoportail (' . $pluginShortName .')',
                        'configItem_hlp' => 'C\'est une aide formidable',
                        'pluginGUID' => '25774c3c-c883-4114-9349-bd1e655e37e3',
                        'pluginShortName' => $pluginShortName,
                        'pluginName' => $pluginName,
                    ),
            );

    $varcave = new varcave();
    $key = $varcave->getConfigElement("IGN_PLAN_IGN_API_KEY");
    $IGN_PLAN_jsdata = <<<EOT
    var IGN_PLAN = new ol.layer.Tile({
                title: "${pluginName}",
				name: "{$pluginShortName}",
                //type: 'base',
                source : new ol.source.WMTS({
                    url: "https://wxs.ign.fr/${key}/geoportail/wmts",
                    layer: "GEOGRAPHICALGRIDSYSTEMS.PLANIGNV2",
                    matrixSet: "PM",
                    format: "image/png",
                    style: "normal",
                    tileGrid : new ol.tilegrid.WMTS({
                        origin: [-20037508,20037508], // topLeftCorner
                        resolutions: [156543.03392804103,78271.5169640205,39135.75848201024,19567.879241005125,9783.939620502562,4891.969810251281,2445.9849051256406,1222.9924525628203,611.4962262814101,305.74811314070485,152.87405657035254,76.43702828517625,38.218514142588134,19.109257071294063,9.554628535647034,4.777314267823517,2.3886571339117584,1.1943285669558792,0.5971642834779396,0.29858214173896974,0.14929107086948493,0.07464553543474241], //resolutions
                        matrixIds: ["0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19"] // ids des TileMatrix
                    })
                })
            });
EOT;

    function IGN_PLAN_getJS($asString = true )
    {
        if ($asString == true)
        {
            return $IGN_PLAN_jsdata;
        }
        else
        {
            header('Content-Type: application/javascript');
            echo $IGN_PLAN_jsdata;
        }
    }

?>