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
    
function convert2lambert93($coord){
	$proj4 = new Proj4php();
	$projWGS84  = new Proj('EPSG:4326', $proj4);  //WGS84 (long/lat)
	
	//build proj data for proj4
	$lambert93 = '+proj=lcc +lat_0=46.5 +lon_0=3 +lat_1=49 +lat_2=44 +x_0=700000 +y_0=6600000 +ellps=GRS80 +towgs84=0,0,0,0,0,0,0 +units=m +no_defs +type=crs';
	$lambert93 = new Proj($lambert93, $proj4);
		//create new point
		$pointSrc = new Point($coord[1], $coord[0], $projWGS84);
		
		//convert point to utm
		$pointDest = $proj4->transform($lambert93, $pointSrc);
		
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