<?php
require_once("./lib/proj4php/vendor/autoload.php");

use proj4php\Proj4php;
use proj4php\Proj;
use proj4php\Point;



/* 
 * Function convert2utm handle the process to convert long/lat in decimal
 * format to UTM notation
 * @param $coords is an array of coords
 *        $coords is defined : 
 *                            $coords = array( x, y, z)
  * @return an array containing converted data defined as :
 *  
 *              array(
 *                  'x' => x,
 *                  'y' => y,
 *                  'z' => z,
 *                  'zone' => 'zone',
 *                  'band' => 'band',
 *                  'string' => 'zoneband x y',
 *              )  
 * @return an array containing converted data defined as :
 *                              array( 
 *              array(
 *                  'x' => x,
 *                  'y' => y,
 *                  'zone' => 'zone',
 *                  'band' => 'band',
 *                  'string' => 'zoneband x y',
 *              ),
 *              array(
 *                  'x' => x1,
 *                  'y' => y1,
 *                  'zone' => 'zone1',
 *                  'band' => 'band1',
 *                  'string' => 'zone1band1 x1 y1',
 *              ),
 *          );
 */
    
    function convert2utm($coords){
        $convCoords  = array();
        $proj4 = new Proj4php();
        $projWGS84  = new Proj('EPSG:4326', $proj4);  //WGS84 (long/lat)
        
        //Find current zone
        $zone = long2UTMZone($coords[0]);
        
        //find band (letter)
        $band = getUTMLatBand($coords[1]);
        
        //build proj data for proj4
        //depending on zone ; may not be suitable for all zones like 32V or 31X
        $currUtmDefinition = '+proj=utm +zone=' . $zone . ' +datum=WGS84 +units=m +no_defs';
        $currUTM = new Proj($currUtmDefinition, $proj4);
        
        //create new point
        $pointSrc = new Point($coords[0], $coords[1], $projWGS84);
        
        //convert point to utm
        $pointDest = $proj4->transform($currUTM, $pointSrc);
        
        //build return data
        $x = floor( $pointDest->__get('x') );
        $y = floor( $pointDest->__get('y') );

        $convCoords = array( 
                                'zone' => $zone,
                                'band' => $band,
                                'x' => $x,
                                'y' => $y,
                                'z' => $coords[2],
                                'string' => $zone . $band. ' ' . $x . ' ' . $y,
                                );
        return $convCoords;
    }
    
    /*
     * this get a UTM zone for a know longitude. Take long as only arg.
     * It does not work for some area (ie : norway and Svalbard)
     * see https://stackoverflow.com/questions/9186496/determining-utm-zone-to-convert-from-longitude-latitude
     */
    function long2UTMZone($long)
    {
        return ( floor( ($long + 180)/6) % 60 + 1 );
    }
    
    
    /* this function get UTM latitude band for a given 
     * latitude in degrees
     * inspired from 
     * https://gis.stackexchange.com/questions/238931/utm-coordinates-and-knowing-how-to-get-the-grid-zone-letter
     */
    function getUTMLatBand($lat)
    {

        $bandLetters = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        //int latz = 0;//Latitude zone: A-B S of -80, C-W -80 to +72, X 72-84, Y,Z N of 84

        if ($lat > -80 && $lat < 72) {
            //= floor((lat + 80)/8)+2;
            return substr ( $bandLetters, floor( ($lat+80)/8) +2, 1);
            //return bandLetters.charAt(Math.floor( ($lat+80)/8) +2);
        }
        if ($lat > 72 && $lat < 84) {
            return substr ( $bandLetters,21, 1);
        }
        if ($lat > 84){
            return substr ( $bandLetters,23);
        }
        return false;
    }
?>