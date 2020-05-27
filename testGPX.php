<?php

include_once('lib/varcave/varcaveCave.class.php');

$cave = new varcaveCave();

$gpxdata = $cave->createAllGPXKML('gpx', FALSE, false);

header('Content-Type: application/gpx+xml');
//clean cav name susbtr is here to remove the 6last letter of dummy file extension and makes cleanStringFilename works
header('Content-Disposition: attachment; filename=gpx-export.gpx');
echo $gpxdata ;



?>