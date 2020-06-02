<?php

//using phpGPX namespace
use phpGPX\Models\GpxFile;
use phpGPX\Models\Link;
use phpGPX\Models\Metadata;
use phpGPX\Models\Point;
use phpGPX\Models\Segment;
use phpGPX\Models\Track;
use phpGPX\Models\Extensions;
use phpGPX\Models\Extensions\TrackPointExtension;

require_once (__DIR__ . '/varcave.class.php');
require_once (__DIR__ . '/varcaveAuth.class.php');
//include phpGPX extensions
require_once (__DIR__ . '/../phpGPX/vendor/autoload.php');

class VarcaveCave extends Varcave
{
    
	/**
	 * this function get the last n modified lines from changelog.
	 * @param $max : max number of row return
	 * @param $caveID : if true, look for change log for specific cave
	 * @param $getAllLog : select visible/hidden lines (0 = only hidden | 1= only visible | 2 = show visible and hidden)
	 * 
	 * @return on success : multidimentionnal associated array , else false (no data set return to false)
	 * ex :
	 * [0] => Array
        (
            [0] => indexid caves
            [1] => guid
            [2] => name
            [3] => changelog content
            [4] => date YYYY-MM-DD
            [5] => id
            [6] => visibility status
        )
	 **/
	function findLastModificationsLog(int $max=9999, $caveID = false, int $getAllLog = 1, $json = false)
	{
		$this->logger->debug('Finding last modification for cave : [' . $caveID . ']');
		try
        {
			$where_indexid = $where_changelog = $query = '';
			$results=array();
			
			$caveID = (int) $caveID;
			// building req
			if ($caveID) 
			{
				$where_indexid .= $this->dbtableprefix . ' caves.indexid=' . $caveID ; //no quote because int cast before
			}
			else 
			{
				$where_indexid .= $this->dbtableprefix . ' caves.indexid IS NOT NULL';
			}
			
			switch ($getAllLog) 
			{
				case 0:
				   $where_changelog .= $this->dbtableprefix . ' changelog.isVisible=0';
					break;
				case 1:
					$where_changelog .= $this->dbtableprefix . ' changelog.isVisible=1';
					break;
				case 2:
					$where_changelog .= '(' . $this->dbtableprefix . 'changelog.isVisible=1 OR ' . $this->dbtableprefix .  'changelog.isVisible=0)';
					break;
				default:
				throw new exception ("findlastModifiedCave: Method not supported: $getAllLog");
			 
			}
  
            $req = 'SELECT name,indexid_caves,chgLogTxt,guidv4,date,' . $this->dbtableprefix . 'changelog.indexid,isVisible  
                        FROM  ' . $this->dbtableprefix . 'caves join  ' . $this->dbtableprefix . 'changelog
                        WHERE ' . $this->dbtableprefix . 'caves.indexid=indexid_caves AND ' . $where_indexid . ' AND ' . $where_changelog . '
                        ORDER BY ' . $this->dbtableprefix . 'changelog.date DESC limit 0,' . $max ;
            
            $this->logger->debug('request : ' . $req);
            
            $pdoStatement = $this->PDO->query($req);
            $result = $pdoStatement->fetchall(PDO::FETCH_BOTH);
        }
        catch (Exception $e)
        {
            $this->setErrorMsg(__METHOD__,time(), L::varcaveCave_failToFetchChangelog );
			$this->logger->debug('Fail to find mods : ' . $e->getmessage());
			
			return false;
        }
		
		
		if( isNullOrEmptyArray($result) )
		{
			$this->logger->debug('No modifications found');
			return false;
		}
		
	
        foreach($result as $col)
        {
            $col['date']=substr($col['date'],0,10); 
            
            //on prépare les inbformation à renvoyer sous la forme d'un array double
            //array0 ( 0=>indexid 1=>numero 2=>nom 3=>description_modif 4=>date de modif 5=>indexid_commentaire 6=>statut du commentaire affiché ou non)

            $resultats[]=array(
				'indexid_caves' => 	$col['indexid_caves'],
				'guid' => 		$col['guidv4'],
				'name' => 			$col['name'],
				'chgLogTxt' => 		$col['chgLogTxt'],
				'date' => 			$col['date'],
				'indexid' => 		$col['indexid'],
				'isVisible' => 		$col['isVisible'],
				);
        }
		
		$this->logger->debug( count($resultats) . ' mods found.') ;
        
		if ($json)
        {
			return json_encode($resultats);
		}
		
        return 	$resultats; 
	}
	
	/*
	 * this function delete the specified id from changelog
	 * @param $id = the changelog.indexid to delete
	 *
	 * return on success true
	 *        on failure throw exception
	*/
	function delLastModificationsLog($id)
	{
		$this->logger->info('trying to remove row [' . $id . '] from changelog');
		try
		{
			$req = 'DELETE FROM ' . $this->dbtableprefix . 'changelog WHERE indexid=' . $this->PDO->quote($id);
			$pdoStatement = $this->PDO->query( $req );
			
			$this->logger->info('deletion succeed');
            return true;
		}
		catch (exception $e)
		{
			$this->logger->error('deletion failed');
			$this->logger->debug($e->getmessage() );
			throw new Exception(L::varcaveCave_failToRemoveChangeLogEntry);
		}
	}
	
	/*
	 * this function add a new entry to the cave changelog
	 * @param $caveguid = the current cave guidv4 to attach the new changelog
	 * @param $msg      = the associated message
	 * @param $isVisible = set if changelog si visible on website front page
	 *
	 * return on success true
	 *        on failure throw exception
	*/
	function AddLastModificationsLog($guid, $msg, $isVisible)
	{
		$this->logger->info('trying to add row [' . $guid . '] to changelog with visibility : [' . $isVisible . ']');
		try
		{
            $result = $this->selectByGUID($guid);
            $this->logger->debug('cave indexid is :' . $result['indexid']);
			$req = 'INSERT INTO  ' . $this->dbtableprefix . 'changelog ' .
                   '  (indexid_caves, chgLogTxt, isVisible) ' .
			       '  VALUES (' . 
                               $result['indexid']  . ',' . 
                               $this->PDO->quote($msg) . ',' . 
                               $this->PDO->quote($isVisible) . 
                             ')';
			$pdoStatement = $this->PDO->query( $req );
			$this->logger->debug ('request : ' . $req);
			$this->logger->info('Insert succeed');
            return $this->PDO->lastInsertId();
		}
		catch (exception $e)
		{
			$this->logger->error('Insert failed');
			$this->logger->debug($e->getmessage() );
			throw new Exception(L::varcaveCave_failToAddChangeLogEntry);
		}
	}
	
	
	
	/**
	* Search caves 
	* return a list of cave as an pdoStatement, data are :  `name`, `guidv4`
    * 
	* @param $searchInput : an array of associated element to build a request :
	*	ex : $search[0]
	*					[field] => 'name'
	*					[type] => 'LIKE%'
	*					[value] => 'my cave'
	*				[1]
	*					[field] => 'depth'
	*					[type] => '<'
	*					[value] => '-55'
	* @param $sortField : sorting field user 
	* @param $ascDesc : ascending or descending
	* @param $limitOffset : offset to select 
	* @param $limitMax max number of entry to search
	*
	* @return on error : false - on success  array of infos
	* ex :
	*	$return[0] = results as PDOstmt
	*	$return[1] = number of items found in search
	*	$return[2] => total depth sum for founded caves
	*	$return[3] => total length sum for founded caves
	*	$return[4] => list of col and their respective localized name
	* 
    */
	function search($searchInput, $sortField, $ascDesc = 'ASC', $limitOffset = 0,$limitMax = 9999999, $noSaveSearch = false)
	{
		$this->logger->debug('Start search for cave');
		$this->logger->debug('requested info :');
		$this->logger->debug(print_r($searchInput,true));
		$this->logger->debug('sortFields :');
		$this->logger->debug(print_r($sortField,true));
	
	
		if (IsNullOrEmptyString($sortField) | IsNullOrEmptyString($ascDesc) )
		{
			$this->logger->debug('fail : bad argument');
			return false;
		}
		
		if(IsNullOrEmptyArray($searchInput) )
		{
			$this->logger->debug('empty search array');
			return false;
		}
		
		$req = '';
		$betweenFound = false;
		foreach($searchInput as $key=>$value)
		{
			
			if ($value['field'] == 'CO2' | $value['field'] == 'airflow' | 
                    $value['field'] =='anchors'| $value['field'] =='zone_natura_2000'| 
                    $value['field'] =='noAccess'|$value['field'] =='PNR_SB')
            {
                $req .= ' ' . $value['field'] . ' = 1 ';
            }
            elseif ($value['type'] == '=')
            {
                $req .=  $value['field'] . ' = ' . $this->PDO->quote($value['value']) . ' ';
            }
            elseif ($value['type'] == 'LIKE') 
            {
                //contient = LIKE '%xxxxx%'
                $req .= $value['field'] . ' LIKE ' . $this->PDO->quote('%' . $value['value'] . '%') . ' ';
            }
            elseif ($value['type'] == '>') 
            {
                //ajouter 'valeur' > valeur_champ
                $req= ' ' . $value['field'] . ' > ' . $this->PDO->quote($value['value']) . ' ';
            }
            elseif ($value['type'] == '<')
            {
                //ajouter < %'valeur'%
                $req .= $value['field'] . ' < ' . $this->PDO->quote($value['value']) . ' ';
            }
			elseif ( $value['type'] == 'BETWEEN')  //
            {	
				if($betweenFound)
				{
					//should be the -BETWEEN2 field. Skipping
					continue;
				}
				$fieldName = substr ( $value['field'] , 0, -9 )  ; //truncate -BETWEENx
				
				$between1Idx = array_search($fieldName . '-BETWEEN1', array_column($searchInput, 'field') );
				$between2Idx = array_search($fieldName . '-BETWEEN2', array_column($searchInput, 'field') );
				//echo $between1Idx . '+' . $between2Idx;
				$req .= $fieldName . ' BETWEEN ' . $searchInput[$key]['value'] . '  AND ' . $searchInput[$between2Idx]['value'] . ' ';
				//prevent to use for later processing
				$betweenFound = true;
            	
            }
            elseif($value['type'] == '!=')
            {
                $req .=  $value['field'] . ' != ' . $this->PDO->quote($value['value']) . ' ';
            }
            else
            {
				$this->logger->debug(__METHOD__ . ' : ERROR invalid choice : ' . $value['field'] . ' type: ' . $value['type'] . ' value:  ' . $value['value'] );
				return false;
				throw new Exception($msg,0);
            }
            
            //adding separator for each searh element
            $req .= ' AND ';
			
		}
		
		$req .= ' 1 '; //reques will be malformed : SELECT .... AND 1
		$limit = ' LIMIT ' . $limitOffset . ',' . $limitMax;
        $colsReq = '`name`, `guidv4`';
		$reqSearch = 'SELECT ' . $colsReq .  ' FROM ' . $this->dbtableprefix .  'caves WHERE ' . $req . ' ORDER BY `' . $sortField . '` ' . $ascDesc . ' ' . $limit . ';';
	
		//prepare a list of cave for next/previs in display.php
		$qSearchNextPrevious = 'SELECT  indexid FROM ' . $this->dbtableprefix .  'caves WHERE ' . $req . ' ORDER BY `' . $sortField . '` ' . $ascDesc . ' ' . $limit . ';';
	
		//no LIMIT to get total item founds
		$reqCount = 'SELECT COUNT(*) FROM ' . $this->dbtableprefix  . 'caves WHERE ' . $req ;
		
		//get some info for selected search
		$reqSearchMetrics = 'SELECT (SELECT SUM( ABS( maxDepth ) ) ) as totalDepth,( SELECT SUM(length)) as totalLength FROM ' .  $this->dbtableprefix . 'caves WHERE ' . $req;   
		
		$this->logger->debug('search request : ' . $reqSearch);
		$this->logger->debug('count request : ' . $reqCount);
		$this->logger->debug('metrics request : ' . $reqSearchMetrics);
		try
		{
			//get cave search from db
			$reqPDOstmt = $this->PDO->query($reqSearch);
			$reqPDOstmtCols = $this->PDO->query($reqSearch);
			
			$PDOstmt = $this->PDO->query($qSearchNextPrevious);
			$nextPreviousList =array();
			while($row = $PDOstmt->fetch(PDO::FETCH_NUM ))
			{
				$nextPreviousList[] = $row[0];
			}
			
			
			//get number of item founded witout LIMIT
			$PDOStmt = $this->PDO->query($reqCount);
			$caveCount = $PDOStmt->fetch(PDO::FETCH_NUM );
		
			
			$PDOStmt = $this->PDO->query($reqSearchMetrics);
			$metrics = $PDOStmt->fetch(PDO::FETCH_ASSOC);
			
		}
		catch(Exception $e)
		{
			$this->setErrorMsg(__METHOD__, L::errors_ERROR, L::search_searchError);
			$this->logger->debug('fail to exec search : at line ' . $e->getLine() );
			$this->logger->debug($e->getMessage());
			$this->logger->debug('search request :' . $req);
			$this->logger->debug('search count request :' . $reqCount);
			$this->logger->debug('search metrics request :' . $reqSearchMetrics);
			return false;
		}
		
		/*
		 * To handle next/previous button in display.php
		 * adding som info to $_session.
		 *
		 */
		if( ! $noSaveSearch)
		{
			$_SESSION['lastSearch'] = $searchInput ;
			$_SESSION['nextPreviousCaveList'] = $nextPreviousList ;
		}
		
		$return =  array(
				$reqPDOstmt, //list of caves
				$caveCount[0], // number of item founds
				$metrics['totalDepth'], //metrics for total depth
				$metrics['totalLength'], //metrics for total length
				);

		return $return;
	}
	
	/**
	* this function get all info about table caves
	* then it can be used later to create a form to
	* show wich element should be presented to users for
	* advenced search
	*
	* @param none
	* @return an array on success, false on failure
	**/	
	protected function getCaveColumns()
	{
		try
		{
			$tableCol = 'EXPLAIN ' . $this->dbtableprefix . 'caves';
			$pdoStmtTableCol = $this->PDO->query($tableCol);
			return $pdoStmtTableCol->fetchall(PDO::FETCH_ASSOC);
		}
		catch(exception $e)
		{
			
			$this->logger->debug( __METHOD__ . ': error getting table info: ' . $e->getmessage() );
			return false;
		}
		
	}
	
	
	/**
	* handle update to searchField table to have a way to configure wich
	* field should be displayed to users
	*
	* @param none
	* @return true on success, false on failure
	**/
	function updateSearchFieldList()
	{
		$this->logger->debug('Start end_user_fields update...');
		if ( ! $colsDef = $this->getCaveColumns() )
		{
			return false;
		}
		$req1 = 'DELETE FROM ' . $this->dbtableprefix . 'end_user_fields WHERE 1;';
		$req2 = 'INSERT INTO ' . $this->dbtableprefix . 'end_user_fields (`indexid`,`field`, `type`, `display`) VALUES ';
		
		$first = true;
		foreach ($colsDef as $col)
		{
			if ($first)
			{
				$req2 .= '(NULL,\''. $col['Field'] . '\',\''. $col['Type'] . '\', 1 )';
				$first = false;
				continue;
			}
			
			$req2 .= ',(NULL,\''. $col['Field'] . '\',\''. $col['Type'] . '\', 1 )';
		}
		
		try
		{	
			$this->PDO->beginTransaction();
			$this->PDO->exec($req1);
			$this->PDO->exec($req2);
			$this->PDO->exec('DELETE FROM ' . $this->dbtableprefix . 'end_user_fields WHERE `field`=\'indexid\'');
			$this->PDO->commit();
			return true;
			
		}
		catch(Exception $e)
		{
			$this->setErrorMsg(__METHOD__, L::errors_ERROR, L::varcaveCave_updateFieldError);
			$this->logger->debug('fail to update searchField :' .$e->getmessage() );
			$this->logger->debug('request : ' . $req1 . "\n" . $req2);
			$this->PDO->rollback();
			return false;
		}
		
		
	}
	
	/**
	* Get localized names of columns names.
	* Thoses must be set by user.
	* 
	*
	* @param $filter = get info for a spécific content 
	* 					0 => get all fields
	* 					1 => get  search field only
	* 					2 => get display.php fields
	* @return on success : array (colName => localizedColName) 
	*            on failure false 
	**/
	function getI18nCaveFieldsName($filter)
	{
		$this->logger->debug( __METHOD__ . ' : Fetching search fields localized name from db');
		try 
		{
			switch ($filter)
			{
				case 'ALL':
					$query = 'SELECT *  FROM ' . $this->dbtableprefix . 'end_user_fields WHERE 1 ORDER BY field_group,sort_order,field ASC';
					break;
				case 'ONSEARCH':
					$query = 'SELECT field,type,field_group   FROM ' . $this->dbtableprefix . 'end_user_fields WHERE show_on_search=1 ORDER BY sort_order,field ASC';
					break;
				case 'ONDISPLAY':
					$query = 'SELECT field,type,field_group  FROM ' . $this->dbtableprefix . 'end_user_fields WHERE show_on_display=1 ORDER BY sort_order,field ASC';
					break;
                case 'ONEDIT':
					$query = 'SELECT field,type,field_group   FROM ' . $this->dbtableprefix . 'end_user_fields WHERE show_on_edit=1 ORDER BY sort_order,field ASC';
					break;
            }
				
				
			$pdoStmt = $this->PDO->query($query);
			 
			$cols =  array();
			$i18nFields = $pdoStmt->fetchall(PDO::FETCH_ASSOC);
			
			foreach($i18nFields as &$subArr)
			{
				//add display name to col definition
				if ( defined('L::table_cave_field_' . $subArr['field']) ){
					$subArr['display_name'] = constant('L::table_cave_field' . '_' . $subArr['field']);
				}else{
					$subArr['display_name'] = $subArr['field'];
				}
			}
            $this->logger->debug('Find  translated col names : ' . print_r($i18nFields,true));
			return $i18nFields;
		}
		catch (Exception $e)
		{
			$this->logger->debug( __METHOD__ . ' :FAILED1 ' . $e->getmessage() );
			return false;
		}
	}

      
    
    /**
	 * Get caves informations by using cave GUID
	 * 
	 * @param    $guidv4 : the cave guid
     * @param    $forceRandomCoords force obfuscation of cave coors
     * @param    $skipCoords disable processing of coords. Remove coords from result set
     *           
	 * @return on success  : assoc array
	 *         on failure to find cave : false
	 * 		   on error   : throw exception
	 **/
	function selectByGUID($guid, $forceRandomCoords = false, $skipCoords=true)
    {
		$this->logger->debug( __METHOD__ . ' : selecting cave  with guid [' . $guid . ']');
       
		//checking user input
		if ( strlen($guid) != 36)
		{			
			//arg type not good 
			$this->logger->debug( __METHOD__ . ' : GUID check failed');
			$this->setErrorMsg(__METHOD__,time(),  L::varcaveCave_badArgGuid );
			return false;
		}
		
        //exec query and validation
        try 
        {
			$req2 = 'SELECT * FROM ' . $this->dbtableprefix  . 'caves WHERE guidv4=' . $this->PDO->quote($guid);
			$PDOstmt2 = $this->PDO->query($req2);
			$result = $PDOstmt2->fetch(PDO::FETCH_ASSOC);
			
			if (!$result)
			{
				$this->logger->debug( __METHOD__ . ' : Cave ID :' . $guid . ' selection failed');
				return false;
			}
			
			//this part obfuscate cave coordinates from geoJson
			$auth =  new VarcaveAuth;
            $this->logger->debug('check for  coordinate obfuscation : ');
            if($skipCoords == false)
            {
                //!$auth->isMember('admin'))
                // disable coords obfuscation if user is admin
                if ( $auth->isMember('admin') || $auth->isMember('editors') )
                {
                    $this->logger->debug('  no obfuscation, user admin or editor');
                    $obfuscateCoords = false;
                }
                elseif (  ($result['random_coordinates']  ||  $forceRandomCoords ) )
                {
                    $this->logger->debug('  cave is set with random_coordinates or function called with `forceRandomCoords`');
                    $obfuscateCoords = true;
                }
                elseif( !isset($_SESSION['isauth']) )
                {
                    $this->logger->debug('  anon user, check if global config is set for obfuscation');
                    if( $this->getconfigelement('anon_get_obfsuc_coords') ) //verify if global config is set
                    {                                                       //to obfuscate coords to anonymous users
                        $obfuscateCoords = true ;
                    }
                    else
                    {
                        $obfuscateCoords = false ;
                    }
                }
                else
                {
                    $obfuscateCoords = false;
                }
                
                if( $obfuscateCoords )
                {
                    $this->logger->debug('*YES* Request coordinate obfuscation');
                    $coordsObj = json_decode($result['json_coords']);		
                    //geoJson store multipoint coord in this namespace
                    //Obj->features[0]->geometry->coordinates[0];
                    foreach($coordsObj->features[0]->geometry->coordinates	 as &$coordSet)
                    {
                        $this->logger->debug('source coords are : ' . $coordSet[1] . ' ' . $coordSet[0]);
                        $coordSet[0] = round($coordSet[0],2);
                        $coordSet[1] = round($coordSet[1],2);
                        $this->logger->debug('new coords are : ' . $coordSet[1] . ' ' . $coordSet[0]);
                    }
                    
                    //insert new coords in json object
                    $result['json_coords'] = json_encode((array)$coordsObj);
                }
            }
            else
            {
                $this->logger->debug('Erase coords data from result set');
                $result['json_coords'] = '';
            }
			return $result;			
        }
        catch (Exception $e) 
        {
			$this->logger->error( __METHOD__ . ' : cave Selection failed : ' . $e->getmessage() );
			$this->setErrorMsg(__METHOD__,time(),  L::varcaveCave_caveSelectFailed );
			throw new exception (L::errors_ERROR . ' : cave Selection failed');
			return false;
        }
        
    }
    
    /*
     * Update cave property with specified argument.
     * 
     * @param     guidv4 hex format like   dbe6f8e0a-2323-4986-b79d-d5ec5e46a1c3
	 *            $col : mysql col name to update 
     *            $value : the coresponding value
	 * @return on success  : true
	 *         on failure : false
	 * 		   on error  :  throw exception
     * 
     */
    function updateCaveProperty($guid, $colname, $value)
    {
         $this->logger->info(__METHOD__ . ': try to update cave : ' . $guid);
         if( $this->selectByGUID($guid) == false)
         {
             $this->logger->error('Update fail, cave not found');
             return false;
         }
        try
        {
            $this->logger->info('update `caves` field :[' . $colname  .']');
            $qUpdate = 'UPDATE ' . $this->getTablePrefix() . 'caves SET `' . $colname . '`=' . $this->PDO->quote($value) . ' WHERE guidv4=' . $this->PDO->quote($guid);
            $this->PDO->query($qUpdate);
            return true;
        }
        catch(exception $e)
        {
            $this->logger->error('cave update fail :' . $e->getmessage() );
            $this->logger->debug('Query : ' . $qUpdate);
            throw new exception('cave update fail');
        }
        return false;
     }
         
    
    /*
     * This method delete specified file,
     * 
     * @param     $filePath : filename to delete, relative or full path
	 * @return on success  : true
	 * 		   on error  :  throw exception
     */
    protected function deleteCaveFile($fullPath)
    {
        //$file = basename($fullPath);
        //$filePath = dirname($fullPath);
        $this->logger->info('cave::deleteCaveFile : Trying to delete file : ' . $fullPath);
        if( ! file_exists($fullPath) )
        {
            throw new exception('file does not exists : ' . $fullPath);                       
        }
        if( is_writable($fullPath) ) 
        {
            if ( !unlink($fullPath) )
            {
                throw new exception('unable to delete file : ' . $fullPath);
            }
             $this->logger->info('cave::deleteCaveFile : delete file success');
        }
        else
        {
            throw new exception (L::errors_readOnlyFile . ':[' . $fullPath . ']');
        }
        return true;
        
    }
    
    function updateCaveGeoJsonProperty($guid, $actionType, $coordSetIndex, $valueIdx = false, $value = false)
    {
        try
        {
            //CHECK cave existence
            $this->logger->info('caveClass::updateCaveGeoJsonProperty try to update geo json : ' . $guid);
            if( $this->selectByGUID($guid) == false)
            {
                 $this->logger->error('Update fail, cave not found');
                 return false;
            }
            
            $qItem = 'SELECT `json_coords` FROM ' . $this->getTablePrefix() . 'caves WHERE guidv4=' . $this->PDO->quote($guid);
            $itemPdoStmt = $this->PDO->query($qItem);
            $itemRes = $itemPdoStmt->fetch(PDO::FETCH_NUM);
            $resTxt = $itemRes[0];
            
            //check json object existence for add action
            //(for delete $jsonEmpty should stay to false !)
            $jsonEmpty = false;
            if( empty($itemRes[0]) )
            {
                $this->logger->debug('inexistant json object for column: json_coords');
                $jsonEmpty = true;
                //object will be created later
            }
            else
            {
                //check wich column is fetch, depending col,
                //final array is not the same.
                $this->logger->debug('decoding json object');
                $geoJsonObj = json_decode($resTxt);
                $this->logger->debug('json object : ' . print_r($geoJsonObj,true) );
                //count element in array of object
                $i = 0;
                $indexes = array();
                foreach($geoJsonObj->features[0]->geometry->coordinates as $key=>$data)
                {
                    $i++;
                    $indexes[] = $key; //build a list of obj indexes to get the max later when adding elements
                    
                }
                $elCount = $i;
                $this->logger->debug('Count : ' . $elCount . ' elements');
            }

            
            /*
             * depending user request we add item or remove elements
             * of the json object
             */
            if($actionType == 'add' )
            {
                $this->logger->info('Adding data [' . $value . '] to json object');
                //get last entry index of existing json data
                if ($jsonEmpty)
                {
                    $this->logger->debug('object do not contain any data, creating new one');
                    //$geoJsonObj->features[0]->geometry->coordinates[$idx][$valueIdx] = $value;
                    $geoJsonObj = new stdClass();
                    $geoJsonObj->features = array();
                    $geoJsonObj->features[0] = new stdClass();
					$geoJsonObj->features[0]->type = 'Feature';
					$geoJsonObj->features[0]->properties = new stdClass();
					$geoJsonObj->features[0]->properties->prop0 = '';
                    $geoJsonObj->features[0]->geometry = new stdClass();
                    $geoJsonObj->features[0]->geometry->coordinates = array();
					$nextCoordSetIndex = 0;
					$geoJsonObj->features[0]->geometry->coordinates[$nextCoordSetIndex] = array(
																		0=>'',
																		1=>'',
																		2=>'',
																		);
                    
                    $this->logger->debug('new json object content : ' . print_r($geoJsonObj, true) );
                }
                else
                {
                    $nextCoordSetIndex = max($indexes) + 1;
                    $this->logger->debug('Adding  data to existing object, current value will be :' . $nextCoordSetIndex);
                    $geoJsonObj->features[0]->geometry->coordinates[$nextCoordSetIndex] = ['', '', ''];
                    $this->logger->debug('new json object content : ' . print_r($geoJsonObj, true) );
                }
                
            }
            elseif($actionType == 'delete' )
            {
                $idx = intval($coordSetIndex);
            
                //Remove mysql data if needed (last element in list)
                if($elCount > 1)
                {
                    //not the last element of object
                    //remove index entry from json Array
                    unset( $geoJsonObj->features[0]->geometry->coordinates[$idx] );
					//renumber array to have keet json format and avoid an array to named array conversion
					$geoJsonObj->features[0]->geometry->coordinates = array_values($geoJsonObj->features[0]->geometry->coordinates);
                    $this->logger->debug('removing one of the array element:[' . $idx . ']');
                    $this->logger->debug('geoJson after deletion :' . print_r($geoJsonObj,true) );
                    
                }
                else
                {
                    //last element of json obj, delete the col content
                     $this->logger->debug('removing last array element');
                     //reseting geojsonObj to zero
                     $geoJsonObj = new stdClass();
                }
            }
            elseif($actionType == 'modify')
            {
                $idx = intval($coordSetIndex);
                $this->logger->info('Edit data [' . $idx . '][' . $valueIdx . '] with value:' . $value);
				$geoJsonObj->features[0]->geometry->coordinates[$idx][$valueIdx] = $value;
                $this->logger->debug('new json object content : ' . print_r($geoJsonObj, true) );
            }
            else
            {
                throw new exception('Add or delete action not supported : ' . $actionType);
            }
            
            try
            {
                $json = json_encode($geoJsonObj,  JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK );
                //clear content of json if empty object
                if($json == '{}')
                {
                    $json ='';
                }
                
                $qUpdate = 'UPDATE ' . $this->getTablePrefix() . 'caves SET json_coords = ' . $this->PDO->quote($json) . ' WHERE guidv4=' . $this->PDO->quote($guid);
                $this->logger->debug('update query : ' . $qUpdate);
                $this->PDO->beginTransaction();
                $this->PDO->query($qUpdate);
                $this->PDO->commit();
                if($actionType == 'add')
                {
					$this->logger->debug('info returned to browser nextCoordSetIndex : ' . $nextCoordSetIndex);
                    return $nextCoordSetIndex;
                }
                return true;
            }
            catch(exception $e)
            {
                $this->logger->error('fail to update cave : ' . $e->getmessage() );
                $this->logger->debug('update query : ' . $qUpdate);
                return false;                
            }
        }
        catch (Exception $e)
        {
            $this->logger->error('Update geo json fail : ' . $e->getmessage() );
            throw new exception('Error on cave geo json update: ' . $e->getmessage() );
        }
    } 
    
	 /*
     * This method get a list of files or other data from 
	 * the files cave field.
     * 
     * @param     guidv4 : given cave guid
	 * @param     varObject : the specific attribute we want to get from the global json object
	 * @return on success  : array of data
	 * 		   on error  :  throw exception
     */
    function getCaveFileList($guidv4, $varObject)
	{
		$this->logger->debug(__METHOD__ . ' : Enumerating files for json object content :['. $varObject . ']');
		$cave = $this->selectByGuid($guidv4);
		if($cave === false)
		{
			throw new exception(L::varcaveCave_badArgGuid);
		}
		
		if(empty($cave['files'] ) )
		{
			$this->logger->debug('Empty files list');
			return false;	
		}
		else
		{
			$jsonFiles = json_decode($cave['files']);
			$this->logger->debug('Object content : ' .print_r($jsonFiles,true) );
		}
		
		if( isset($jsonFiles->$varObject) )
		{
			return (array)$jsonFiles->$varObject;
		}
		else
		{
			$this->logger->debug('Requested object is empty');
			return array();
		}
	}
	
	/* 
	 * update 
     * This function update json `files->Element` properties
     * 
     * @param     guid = hex format like   dbe6f8e0a-2323-4986-b79d-d5ec5e46a1c3
     * @param     varObject : final json object name identifier
	 * @return on success : (int) item number in the list
	 * 		   on error  :  throw exception
     * 
     * @exemple addDataToCaveFileList(myguid, "picture_file", "path/to/pic" )
     */
	function addDataToCaveFileList($guidv4, $varObject, $data )
	{
		$this->logger->debug(__METHOD__ . 'Add data for field :['. $varObject . '] with data :[' . $data . '].');
		
		$cave = $this->selectByGuid($guidv4);
		if($cave === false)
		{
			throw new exception(L::varcaveCave_badArgGuid);
		}
		
		if(empty($cave['files'] ) )
		{
			$this->logger->debug('Empty files list, will create blank one');
			$files = new stdClass();
			$files->$varObject = new stdClass();
			$lastItemIndex = 1;
			$nextItemIndex =  1;
		}
		else
		{
			$files = json_decode($cave['files']);
			 foreach($files->$varObject as $key=>$_data)
			{
				//$i++;
				$indexes[] = $key; //build a list of obj indexes to get the max later when adding elements	
			}
			$lastItemIndex = max($indexes);
			$nextItemIndex = $lastItemIndex + 1;
			$this->logger->debug('Files list exist, contains ' . $lastItemIndex .  ' elements');
		}
		//appending data to existing object
		if ($varObject == "photos")
		{	
			//special case for photo it contains sub array
			$el = explode ( ",",$data);
			$files->$varObject->{$nextItemIndex} = $el;
			
		}
		else
		{
			$files->$varObject->{$nextItemIndex} = $data;
		}
		//reverting back to json
		$filesJson = json_encode($files, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
		
		//writing to db
		$this->logger->debug('Updating db with new elements.\n' . print_r($filesJson, true) );
		if (! $this->updateCaveProperty($guidv4, 'files', $filesJson) )
		{
			throw new exception(L::varcaveCave_failToAddFileList);
		}
		return $nextItemIndex;

	}
	
	
	/*  
     * This function delete an json `files->Element` properties
     * 
     * @param     guid = hex format like   dbe6f8e0a-2323-4986-b79d-d5ec5e46a1c3
     * @param     varObject : final json object element
	 * @param     itemNbr =  number in the list  
	 * @return on success : true
	 * 		   on error  :  throw exception
     * 
     */
	function delDataCaveFileList($guidv4, $varObject, $itemNbr )
	{
		$this->logger->debug(__METHOD__ . ' : delete data for field :['. $varObject . '] with index :[' . $itemNbr . '].');
		
		$cave = $this->selectByGuid($guidv4);
		if($cave === false)
		{
			throw new exception(L::varcaveCave_badArgGuid);
		}
		
		$files = json_decode($cave['files']);
		
		//deleting db data and file of existing object
		if ($varObject == "photos")
		{
			$this->deleteCaveFile($files->$varObject->$itemNbr[0]);
		}
		else{	
			$this->deleteCaveFile($files->$varObject->$itemNbr);
		}
		unset ($files->$varObject->$itemNbr);
		
		//reverting back to json
		$filesJson = json_encode($files, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
		
		//writing to db
		$this->logger->debug('Updating db with removed elements.\n' . print_r($filesJson, true) );
		if (! $this->updateCaveProperty($guidv4, 'files', $filesJson) )
		{
			throw new exception(L::varcaveCave_failToAddFileList);
		}
		return true;

	}
	
	/*  
     * This function edit an existing json `files->Element` properties
     * 
     * @param     guid = hex format like   dbe6f8e0a-2323-4986-b79d-d5ec5e46a1c3
     * @param     varObject : final json object element
	 * @param     value =   new value to update  
	 * @return on success : true
	 * 		   on error  :  throw exception
     * 
     */
	function editDataCaveFileList($guidv4, $varObject, $itemNbr, $value)
	{
		$this->logger->debug(__METHOD__ . ' : update cave data file list field :['. $varObject . '] with index :[' . $itemNbr . '] and value[.' . $value . ']');
		
		$cave = $this->selectByGuid($guidv4);
		if($cave === false)
		{
			throw new exception(L::varcaveCave_badArgGuid);
		}
		
		$files = json_decode($cave['files']);
		
		//deleting db data and file of existing object
		if ($varObject == "photos")
		{
			//update  comment of photo
			$files->$varObject->$itemNbr[1] = $value;
		}
		else
		{	
			$files->$varObject->$itemNbr = $value;
		}
		
		//reverting back to json
		$filesJson = json_encode($files, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
		
		//writing to db
		$this->logger->debug('Updating db with new elements.\n' . print_r($filesJson, true) );
		if (! $this->updateCaveProperty($guidv4, 'files', $filesJson) )
		{
			throw new exception(L::varcaveCave_failToAddFileList);
		}
		return true;

	}
	
	/*
	 * This function create a new empty cave
     * 
     * @param     name = string of cave name
	 *
	 * @return on success : new cave Guid
	 * 		   on error  :  throw exception
	 */
	function createNewCave($caveName)
	{
		$this->logger->info(__METHOD__ . ' : Adding a new cave');
		
		if( empty($caveName) )
		{
			$this->logger->error('No cave name specified');
			throw new exception (L::varcaveCaveemptycavename);			
		}
			
		try
		{
			//creating unique informations for cave
			$newGuid = GUIDv4();
			
            $this->logger->debug('Generate new guid : [' . $newGuid .']');
            
			//building mysql query insert
			$req = 'INSERT INTO ' . $this->dbtableprefix . 'caves ';
			$req .= '(name,guidv4)';
			$req .= 'VALUES(' . $this->PDO->quote($caveName) . ',' . $this->PDO->quote($newGuid) . ')';
			
			$this->logger->debug('request : ' . $req);
			
			$this->PDO->query($req);
			
			$this->logger->info('creating new cave sucessfull');
			return $newGuid;
			
		}
		catch (exception $e)
		{
			$this->logger->error('cave creation failed : ' . $e->getmessage() );
			throw new exception (L::varcaveCave_caveCreationFailed);
			
		}
	}
	
	
	/* 
	 * this function check if stats already exist for a given cave
	 * @param : $caveid = the cave indexid to check if stats exists
	 *          $create = create stats if not exists (default)
	 * @return : true if exist, false in other cases
	 */
	function stats_exists($caveid, $create = true)
	{
		$this->logger->debug(__METHOD__ . ' : check if stats exist for cave : ' . $caveid);
		try
		{
			$q = 'SELECT indexid FROM ' . $this->dbtableprefix . 'stats WHERE cave_id=' . $this->PDO->quote($caveid); 
			$statsPdoStmt = $this->PDO->query($q);
			$stats = $statsPdoStmt->fetch(PDO::FETCH_ASSOC);
			if( empty($stats) && $create == false )
			{
				$this->logger->debug('no stats exists, no creation');
				return false;
			}
			elseif ( empty($stats) && $create == true )
			{
				//create stats
				$this->logger->debug('no stats exists, create new one');
				$q = 'INSERT INTO ' . $this->dbtableprefix . 'stats 
				(cave_id,view_count,lastupdate) 
				VALUES (' . $this->PDO->quote($caveid) . ',0,0
				)';
				$statsPdoStmt = $this->PDO->query($q);
				return true;
			}
			$this->logger->debug('stats already exists');
			return true;
		}
		catch(exception $e)
		{
			$this->logger->error('Fail to fetch stats for cave indexid:[' . $caveid . ']' . $e->getMessage() );
			$this->logger->debug('query:'.$q);
			return false;
		}
		
	}
	
	/*
	 * This function update view stats except if known ipaddress is used (ie admin computer)
	 * or if user is member of admin group
	 * @param : $caveid : the cave indexid
     * @return false if no stats are added, true in other cases
	 */ 
	function updateStats($caveid)
	{
		$this->logger->debug(__METHOD__ . ': Updating stats for cave : ' . $caveid );
		$auth = new varcaveAuth();
		
		//set an array from the ip listing located in ocal config
		$adminIpList  = array_map('trim', explode(',', $this->getConfigElement('adminIP') ));
		
		//a very simple way to check if remote administrative ip address is used
		$noStatsAdded = 'No stats added';
		if ( in_array($_SERVER['REMOTE_ADDR'], $adminIpList) ) {
			$this->logger->debug($noStatsAdded . ': special IP detected');
			return false;
		}
		elseif( $auth->isMember('admin') ){
			$this->logger->debug($noStatsAdded. ': user admin') ;
			return false;
		}
		else {
			try
			{
				$q = 'UPDATE ' . $this->dbtableprefix . 'stats 
					  SET view_count = view_count + 1
					  WHERE cave_id = ' . $this->PDO->quote($caveid);
				$this->PDO->query($q);
				return true;
			}
			catch (exception $e)
			{
				$this->logger->error('Fail to update stats for cave : ' .$caveid . $e->getMessage() );
				$this->logger->debug('full query : ' . $q);
				return false;
			}
		}
	}
	
	/*
	 * Get data from table stats to get info about
	 * most viewed caves
	 * @param  $limit max line to get from db
	 * @return array on success, false on error
	           
	 */
	function getStats()
	{
		try
		{
			$q = 'SELECT stats.indexid as indexid ,view_count,caves.name as name FROM ' . $this->dbtableprefix . 'stats,' . $this->dbtableprefix . 'caves WHERE caves.indexid = cave_id ORDER BY view_count DESC LIMIT ' . (int) $this->config['displayedStats'] ;
			$statsPdoStmt = $this->PDO->query($q);
			return $statsPdoStmt->fetchall(PDO::FETCH_ASSOC);
		}
		catch (exception $e)
		{
			$this->logger->error(__METHOD__ . ' : fail to fetch stats');
			$this->logger->debug('Query : ' .  $q . ' Error :'. $e->getmessage() );
			return false;
		}
	}
	
	/**
	 * Get a GPX data
	 * 
	 * Get coordinates and build GPX data from a designated cave.
	 * 
	 * @param int $caveid  cave indexid to obtain gpx file
	 * @param bool $PointNameAsRef  use the cave reference or number 
	 * as points names instead of cave name
	 * @param bool $outputAsFile a flag to force a file download 
	 * @return string or false
	 */
	public function createGPX($caveid, $PointRefAsName = false, $outputAsFile = false)
	{
		


		$this->logger->debug(__METHOD__ . ' : build gpx data from :' . $caveid);
		$cavedata = $this->selectByGUID($caveid,false,false);
		
		$coords = json_decode($cavedata['json_coords']);
		$this->logger->debug('json data :  '. print_r($coords, true) );
		$coordsList = $coords->features[0]->geometry->coordinates;
		
		if(empty($coordsList) )
		{
			$this->logger->debug('no coords for cave');
			return false;
		}
		
		$gpx_file = new GpxFile();
		
		// Creating Metadata
		$gpx_file->metadata 			= new  Metadata();
		$description = L::cave . ' : ' . $cavedata['name'] . "\n";
		$description .= $this->config['httpdomain'] . '/' . 	
						$this->config['httpwebroot'] .  '/display.php?guid=' . $caveid;
		$gpx_file->metadata->description =  $description;
		
		//add website link
		$link 							= new Link();
		$link->href 					= $this->config['httpdomain'] . '/' . 	
						$this->config['httpwebroot'] .  '/display.php?guid=' . $caveid;
		$gpx_file->metadata->links[] 	= $link;
		
		
		$namePrefix = $cavedata['name'];
		if($PointRefAsName)
		{
			$this->logger->debug('using cave ref as point name');
			$namePrefix = $cavedata['caveRef'];
		}
		
		// Creating points
		
        if( count($coordsList) > 1)
        {
            $i=0;
            foreach($coordsList as $key=>$value)
            {
                $long = $value[0];
                $lat = $value[1];
                $elev = $value[2];
                
                $this->logger->debug('add point with : lat:'. substr_replace($lat ,"*",-5).' long:'.substr_replace($long ,"*",-5).'elev:'.$elev );
                $point                 = new Point(Point::WAYPOINT);
                $point->name           = $namePrefix . '_' . $i ;
                $point->latitude       = $lat;
                $point->longitude      = $long;
                $point->elevation      = $elev;
                $gpx_file->waypoints[] = $point;
                
                $i++;
            }
        }
        else{
            $long = $coordsList[0][0];
            $lat = $coordsList[0][1];
            $elev = $coordsList[0][2];
            
            $this->logger->debug('add point with : lat:'. substr_replace($lat ,"*",-5).' long:'.substr_replace($long ,"*",-5).'elev:'.$elev );
            $point                 = new Point(Point::WAYPOINT);
            $point->name           = $namePrefix;
            $point->latitude       = $lat;
            $point->longitude      = $long;
            $point->elevation      = $elev;
            $gpx_file->waypoints[] = $point;
            
        }
		
		$this->logger->debug('gpx content : ' . print_r($gpx_file, true) );
		return $gpx_file->toXML()->saveXML();
		
	}
	
    /**
	 * export all GPX data. This export keeps obfuscation of coords
	 * 
	 * 
	 * @param int $type "gpx" or "kml"
	 * @param bool $PointNameAsRef  use the cave reference or number 
	 * as points names instead of cave name
     * @param $asUser generate file as user thereby coords *ARE* by default obfuscated if needed
	 * @return string or false
	 */
	public function createAllGPXKML($type, $PointRefAsName = false, $asUser = true )
	{
		$this->logger->debug(__METHOD__ . ' : build gpx and kml for all caves');
        $searchInput[] = array(
                        'field' => 'indexid',
                        'type' => '!=',
                        'value' => '',
                );
        $cols = array('indexid', 'name', 'caveRef', 'guidv4');
		try{
            $caves = $this->search($searchInput, 'name', 'ASC', 0, 99999, true);
            $allCaves = $caves[0]->fetchall(PDO::FETCH_ASSOC);
        }
        catch(Exception $e){
            $this->logger->error('Fail to search caves from db' . $e->getMessage() );
        }
		
        $this->logger->debug('Found : ' . count($allCaves) );
        
        $gpx_file = new GpxFile();
            
        // Creating Metadata
        $gpx_file->metadata 			= new  Metadata();
        $gpx_file->metadata->description =  'List of caves';
        
        $siteLink 							= new Link();
        $siteLink->href 					= $this->config['httpdomain'] . '/' . $this->config['httpwebroot'] ;
        $gpx_file->metadata->links[] 	= $siteLink;
        
        //impersonnate as simple user if required
        if( $asUser == true ){
            $admin_sessionid = session_id();
            $admin_SESSION = $_SESSION;
            $this->logger->debug('saved data :' . $admin_sessionid  . '$_SESSION:'. print_r($admin_SESSION,true) );
            session_write_close(); //end admin sess

            //Create a dummy session to obfuscate coords for end users
            session_id('system-session');
            session_start();
            $_SESSION = array();
            $_SESSION['groups'] = 'users';
            $_SESSION['isauth'] = 1;
            $this->logger->debug('System $_SESSION ('. session_id() .') info :' . print_r($_SESSION,true) );
        }
        
        foreach($allCaves as $key => $cave)
        {
            $cavedata = $this->selectByGUID($cave['guidv4'],false,false);
            
            $coords = json_decode($cavedata['json_coords']);
            $this->logger->debug('json data :  '. print_r($coords, true) );
            $coordsList = $coords->features[0]->geometry->coordinates;
            
            if(empty( (array)$coordsList ) )
            {
                $this->logger->debug('no coords for cave: ' . $cavedata['guidv4']);
                continue;
            }
            
            
            
            //add website link
            $caveLink 							= new Link();
            $caveLink->href 					= $this->config['httpdomain'] . '/' . 	
                            $this->config['httpwebroot'] .  '/display.php?guid=' . $cavedata['guidv4'];
            
            
            $pointName = $cavedata['name'];
            if($PointRefAsName)
            {
                $this->logger->debug('using cave ref as point name');
                $pointName = $cavedata['caveRef'];
            }
            
            // Creating points
            if( count($coordsList) > 1)
            {
                $i=0;
                foreach($coordsList as $key => $value)
                {
                    $long = $value[0];
                    $lat = $value[1];
                    $elev = $value[2];
                    
                    $this->logger->debug('add point with : lat:'. substr_replace($lat ,"*",-5).' long:'.substr_replace($long ,"*",-5).'elev:'.$elev );
                    $point                 = new Point(Point::WAYPOINT);
                    $point->name           = $pointName . ' [' . $i . ']' ;
                    $point->latitude       = $lat;
                    $point->longitude      = $long;
                    $point->elevation      = $elev;
                    $point->links[]          = $caveLink;
                    $gpx_file->waypoints[] = $point;
                    
                    $i++;
                }   
            }
            else
            {
                $long = $coordsList[0][0];
                $lat = $coordsList[0][1];
                $elev = $coordsList[0][2];
                
                $this->logger->debug('add point with : lat:'. substr_replace($lat ,"*",-5).' long:'.substr_replace($long ,"*",-5).'elev:'.$elev );
                $point                 = new Point(Point::WAYPOINT);
                $point->name           = $pointName ;
                $point->latitude       = $lat;
                $point->longitude      = $long;
                $point->elevation      = $elev;
                $point->links[]          = $caveLink;
                $gpx_file->waypoints[] = $point;
                
            }
		}
        
        //clean impersonation
        if( $asUser ){
            //delete dummy session
            session_destroy();
            //restore admin session settings
            session_id($admin_sessionid);
            session_start();
            $_SESSION = $admin_SESSION;
            $this->logger->debug('reload admin session :' . session_id(). ' $_SESSION:' . print_r($_SESSION,true) );
        }

        $this->logger->debug('gpx build complete');
        return $gpx_file->toXML()->saveXML();
	}
    
    /*
     * documentExists
     * Check onto a cave if some document(s) are registered. Test existance of any
     * information into `files`.docType column 
     * @param caveDocs cave "files" as an json string fetched from selectByGuid()
     * @param docType document type for existence check (can be biodoc, documents, etc.).
     * 
     * @result true if some document type are registered, false if nothing is registered
     */
    public function documentExists($caveDocs, $docType){
        $this->logger->debug(__METHOD__ . ': check if documents are registered for cave');
        
        $filesObj = json_decode($caveDocs);
        if( ! isset($filesObj->$docType) ){
                $this->logger->debug('Files document group DO NOT exists');
                return false;
        }
        elseif( empty( (array)$filesObj->$docType) ){  //cast to array stdObject
            $this->logger->debug('Files document group EMPTY');
            return false;
        }
        else{
            $this->logger->debug('Files document exists');
            return true;
        }
            
    }
	
}    
?>
