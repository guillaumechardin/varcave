<?php
require_once("./lib/proj4php/vendor/autoload.php");
//require_once("./lib/proj4php/src/Proj4php.php");
//require_once("./lib/proj4php/src/Proj.php");
//require_once("./lib/proj4php/src/Point.php");

use proj4php\Proj4php;
use proj4php\Proj;
use proj4php\Point;

/*$projL3    = new Proj('EPSG:27573', $proj4); //lambertIII carto 
$projWGS84  = new Proj('EPSG:4326', $proj4);  //WGS84
$projUTM31  = new Proj('EPSG:32631', $proj4); // Zone UTM31
$projUTM32  = new Proj('EPSG:32632', $proj4); // Zone UTM32
*/


    $proj4     = new Proj4php();

    /*$projL93   = new Proj('EPSG:2154', $proj4);
    
    $projLI    = new Proj('EPSG:27571', $proj4);
    $projLSud  = new Proj('EPSG:27563', $proj4);
    $projL72   = new Proj('EPSG:31370', $proj4);
    $proj25833 = new Proj('EPSG:25833', $proj4);
    $proj31468 = new Proj('EPSG:31468', $proj4);
    $proj5514  = new Proj('EPSG:5514', $proj4);
    $proj28992 = new Proj('EPSG:28992', $proj4);*/
    
    $projWGS84 = new Proj('EPSG:4326', $proj4);
    $projUTM31  = new Proj('EPSG:32631', $proj4); // Zone UTM31
    $projUTM32  = new Proj('EPSG:32632', $proj4);
    
    //X:5.9630875 Y:43.1988465 Z:670m
    $pointSrc = new Point(5.9630875, 43.1988465, $projWGS84);
    $pointDst = $proj4->transform($projUTM31, $pointSrc);
    
    $newCoords[] = array( 'x'=>$pointDst->__get('x'), 'y' => $pointDst->__get('y') );
    
    print_r($pointDst);
    print_r($newCoords);
    
    exit();
?>