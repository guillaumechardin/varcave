<?php

require_once (__DIR__ . '/lib/varcave/varcaveCave.class.php');
require_once (__DIR__ . '/lib/varcave/functions.php');
require_once (__DIR__ . '/lib/varcave/varcaveAuth.class.php');
require_once (__DIR__ . '/lib/varcave/varcaveHtml.class.php');

$auth = new VarcaveAuth();


$acl = $auth->getacl('52f41225-92c9-4926-a24f-d62f1e824f8d');
if ( !$auth->isSessionValid() ||  !$auth->isMember( $acl[0]) )
{
    $auth->logger->error('getpdf.php : user try to access unauthentified' . 'IP : '. $_SERVER['REMOTE_ADDR'] );
    $html = new VarcaveHtml(L::errors_ERROR);
    $html->stopWithMessage(L::errors_ERROR, L::errors_pageAccessDenied, 401, 'Unauthorized ');
}

$cave = new varcaveCave();
switch($_GET['action'])
{
    case 'cave':
        if(isset($_GET['guid']) && !empty($_GET['guid']) ){
            $cave->logger->debug('getgpxkml.php : start collecting data for file creation');
            
            //fetch and start download of gpx file
            $GPXdata = $cave->createGPX($_GET['guid']);
            $cave->logger->debug('GPXdata :' . print_r($GPXdata, true) );
            $caveData = $cave->selectByGUID( $_GET['guid'], false, false );
            if ($caveData == false)
            {
                $cave->logger->error('  No data or fail to get data');
                header('HTTP/1.1 400' . L::errors_badArgs);
                echo L::errors_ERROR . ' : ' . L::errors_badArgs;
                exit();
            }

            header('Content-Type: application/gpx+xml');
            //clean cav name susbtr is here to remove the 6last letter of dummy file extension and makes cleanStringFilename works
            $filename = substr(cleanStringFilename($caveData['name'].'.dummy'), 0,-6);
            header('Content-Disposition: attachment; filename=' . $filename . '.gpx');
            echo $GPXdata ;
            exit();
        }
        else 
        {
            header('HTTP/1.1 400' . L::errors_badArgs);
            echo L::errors_ERROR . ' : ' . L::errors_badArgs;
            exit();
        }
        break;
    
    //start downloading gpx data from a search
    case 'searchgpxdownload':
        $cave->logger->debug( basename(__FILE__) . ' start collecting data for collection creation');
        if( (isset($_GET['searchid']) && !empty($_GET['searchid'])) && isset($_SESSION['searchid']) && $_SESSION['searchid'] == $_GET['searchid'] )
        {
            $GPXdata = $cave->createGPX($_SESSION['nextPreviousCaveList']);
            //$cave->logger->debug('  GPXdata :' . print_r($GPXdata, true) );
            /*$caveData = $cave->selectByGUID( $_GET['guid'], false, false );
            if ($caveData == false)
            {
                $cave->logger->error('  No data or fail to get data');
                header('HTTP/1.1 400' . L::errors_badArgs);
                echo L::errors_ERROR . ' : ' . L::errors_badArgs;
                exit();
            }*/

            header('Content-Type: application/gpx+xml');
            $date = new DateTime('now');
            $timef =  $date->format('Y-m-d_H-i-s');
            $filename = L::search_last_search_filename . '_' . $timef;
            header('Content-Disposition: attachment; filename=' . $filename . '.gpx');
            echo $GPXdata ;
            exit();
        }
        else
        {
            $html = new VarcaveHtml(L::errors_ERROR);
            $html->stopWithMessage(L::errors_ERROR, L::getgpxkml_search_origin_error, 400, 'BAD REQUEST');
        }
        break;
    
    default:
        header('HTTP/1.1 400' . L::errors_badArgs);
        echo L::errors_ERROR . ' : ' . L::errors_badArgs;
        exit();
}

?>