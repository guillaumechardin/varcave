<?php
require_once("./lib/proj4php/vendor/autoload.php");

use proj4php\Proj4php;
use proj4php\Proj;
use proj4php\Point;



/* 
 * Function convert2utm handle the process to convert long/lat in decimal
 * format to UTM notation
 * @param $coord is an array of coords
 *        $coord is defined : 
 *                            $coord = array( x, y, z)
 *                              
 * @return an array containing converted data defined as :
 *  
 *              array(
 *                  'x' => x,
 *                  'y' => y,
 *                  'string' => 'x y z',
 *              )
 */
    
function convert2lambert3($coord){
	$proj4 = new Proj4php();
	$projWGS84  = new Proj('EPSG:4326', $proj4);  //WGS84 (long/lat)
	
	//build proj data for proj4
	$lambert3 = '+proj=lcc +lat_1=44.10000000000001 +lat_0=44.10000000000001 +lon_0=0 +k_0=0.999877499 +x_0=600000 +y_0=3200000 +a=6378249.2 +b=6356515 +towgs84=-168,-60,320,0,0,0,0 +pm=paris +units=m +no_defs';
	$lambert3 = new Proj($lambert3, $proj4);
		//create new point
		$pointSrc = new Point($coord[1], $coord[0], $projWGS84);
		
		//convert point to utm
		$pointDest = $proj4->transform($lambert3, $pointSrc);
		
		//build return data
		$x = floor( $pointDest->__get('x') );
		$y = floor( $pointDest->__get('y') );

		$convCoords = array( 
								'x' => $x,
								'y' => $y,
								'z' => $coord[2],
								'string' => 'X:' . $x/1000 . '   Y:' . $y/1000 . ' ' . $coord[2] .'m',
								);
	return $convCoords;
}

?>