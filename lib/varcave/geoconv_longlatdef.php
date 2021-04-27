<?php
/*
 * This send back the default coords value in long/lat format. This is the default storage 
 * method in json_coords from cave db.
 */
 
function convert2GEOGRAPHIC($coord)
{
    $convCoords = array( 
                'x' => $coord[0],
                'y' => $coord[1],
                'z' => $coord[2],
                'string' => 'X: ' . $coord[0] . ' Y: ' . $coord[0] . ' ' . $coord[2] . 'm',
                );
    return $convCoords;
}
    
 
?>