<?php
/********************
*  This function check if array is null or empty
*  @param array to test
*  @return false or true
*
*
*
*********************/

function IsNullOrEmptyArray($array)
{
	if ( ! is_array($array) )
	{
		//must be an array
		return true;
	}
	$fArray = array_filter($array);
	if ( empty($fArray) )
	{
		//'empry array';
		return true;
	}
	//'non empty array';
	return false;
}
	
function IsNullOrEmptyString($str)
{
	if ( ! is_string($str) )
	{
		//must be an array
		return true;
	}
	
	if ( empty($str) | is_null($str) )
	{
		//'empty str';
		return true;
	}
	//'non empty or null string';
	return false;
}
	
	
	
/**
* Returns a GUIDv4 string
*
* Uses the best cryptographically secure method
* for all supported pltforms with fallback to an older,
* less secure version.
*
* @param bool $trim
* @return string
*/
function GUIDv4 ($trim = true)
{
    // Windows
    if (function_exists('com_create_guid') === true) {
        if ($trim === true)
            return trim(com_create_guid(), '{}');
        else
            return com_create_guid();
    }

    // OSX/Linux
    if (function_exists('openssl_random_pseudo_bytes') === true) {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    // Fallback (PHP 4.2+)
    mt_srand((double)microtime() * 10000);
    $charid = strtolower(md5(uniqid(rand(), true)));
    $hyphen = chr(45);                  // "-"
    $lbrace = $trim ? "" : chr(123);    // "{"
    $rbrace = $trim ? "" : chr(125);    // "}"
    $guidv4 = $lbrace.
              substr($charid,  0,  8).$hyphen.
              substr($charid,  8,  4).$hyphen.
              substr($charid, 12,  4).$hyphen.
              substr($charid, 16,  4).$hyphen.
              substr($charid, 20, 12).
              $rbrace;
    return $guidv4;
}

/*
 * send back to enduser agent json info.
 * this is an ending function. No other code can run after.
 */
function jsonWrite($json, $httpStatusCode = 200, $httpStatusStr = 'OK')
{	
	
	header('HTTP/1.1 ' . $httpStatusCode . ' ' . $httpStatusStr);
    header('Content-Type: application/json; charset=UTF-8');
    echo $json;
    exit();
}

/*
 * find string from array in a string
 */
 function strstr_from_arr($needles, $haystack)
{
    foreach($needles as $needle){
        if (strpos($haystack, $needle) !== false) {
            return true;
        }
    }
    return false;
}

/*
 * Replace/remove special chars in filenames
 * to get usable filenames for file copy/move.
 */
function cleanStringFilename($filename, $charset = 'UTF-8')
{
    
    //$filename_raw = $filename;
    $newFile = '';
    $parts = explode('.', $filename);
    $partsCount = count($parts);
    $partsCount = $partsCount - 1;
    // Return if only one extension
    foreach ($parts as $key=>$value)
    {
        if ($key == $partsCount)
        {
            //on est à l'extension on rajoute un .
            $newFile .='.';
        }
        $value = str_replace(' ', '-', $value); // Replaces all spaces with hyphens.
        $value = htmlentities($value, ENT_NOQUOTES, $charset);
        $value = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $value);
        $value = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $value); // pour les ligatures e.g. '&oelig;'
        $value = preg_replace('#&[^;]+;#', '', $value); // remove all other chars
        $value = preg_replace('/[^A-Za-z0-9\-]/', '', $value); // Removes special chars.
        $newFile .= $value;
    
    }
    return preg_replace('/-+/', '-', $newFile); // Replaces multiple hyphens with single one.
}

//this function return the fa raw data for a specified file type
//to be later integrated into a class attribute in html_entity_decode
function getFaIcon($fileExt, $faStyle = 'fas')
{
	//bad arg
	if(empty($fileExt) )
	{
		return false;
	}
	
	$fileExt = strtolower($fileExt);
	
	switch($fileExt)
	{
		case 'pdf':
			return $faStyle . ' fa-file-pdf';
			break;
		
		case 'xlsx':
		case 'xls':
			return $faStyle . ' fa-file-excel';
			break;
								
		case 'docx':
		case 'doc':
			return $faStyle . ' fa-file-word';
			break;
		
		case 'txt':
			return $faStyle . ' fa-file-alt';
			break;
		
		case 'rar':
		case 'zip':
			return $faStyle . ' fa-file-archive';
			break;
			
		case 'csv':
			return $faStyle . ' fa-file-csv';
			break;
									
		case 'jpg':
			return $faStyle . ' fa-file-image';
			break;
            
        case 'gpx':
        case 'kml':
			return $faStyle . ' fa-map-marked-alt';
			break;
			
		default:
			return $faStyle . ' fa-file';
			break;
		
	}
}

// truncate a string to desired size, add at the end if specified
function truncateStr($string, $maxSize, $start = 0, $addDot = true )
{
    if(! is_string($string) )
    {
        return false;
    }
    
    if(strlen($string) < $maxSize)
    {
        return $string;
    }
    
    $str = substr( $string, $start,$maxSize);
    
    if($addDot)
    {
         $str .= '...';
    }
    
    return $str;
}


    /*
     * filtering an multi-dim array by pair key/value
     * ex : 
     *   $results = array(
     *      0 => array('key1' => '1', 'key2' => 2, 'key3' => 3),
     *      1 => array('key1' => '12', 'key2' => 22, 'key3' => 32)
     *   );
     *   $nResults = filter_by_value($results, 'key2', '2'); 
     *   
     *   will return  : array('key1' => '1', 'key2' => 2, 'key3' => 3)
     */
    function filter_by_value ($array, $index, $value){
        if(is_array($array) && count($array)>0) 
        {
            foreach(array_keys($array) as $key){
                $temp[$key] = $array[$key][$index];
                
                if ($temp[$key] == $value){
                    $newarray[$key] = $array[$key];
                }
            }
          }
      return $newarray;
    } 
    
    
    /*
     * Functions to read/write to ini file:
     * taken from https://stackoverflow.com/questions/5695145/how-to-read-and-write-to-an-ini-file-with-php
     */ 

    function write_php_ini($array, $file){
        $res = array();
        foreach($array as $key => $val){
            if(is_array($val)){
                $res[] = "[$key]";
                foreach($val as $skey => $sval) $res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
            }
            else{
                $res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
            }
        }
        safefilerewrite($file, implode("\r\n", $res));
    }

    function safefilerewrite($fileName, $dataToSave){    
        if ($fp = fopen($fileName, 'w')){
            $startTime = microtime(TRUE);
            do
            {  
                $canWrite = flock($fp, LOCK_EX);
                // If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
                if(!$canWrite) usleep(round(rand(0, 100)*1000));
            } while ((!$canWrite)and((microtime(TRUE)-$startTime) < 5));

            //file was locked so now we can store information
            if ($canWrite){           
                fwrite($fp, $dataToSave);
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }

    }

?>
