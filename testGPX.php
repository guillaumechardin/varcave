<?php

include_once('lib/varcave/varcaveCave.class.php');

$cave = new varcaveCave();

$gpxdata = $cave->createAllGPXKML('gpx', FALSE, false);

echo $gpxdata;



?>