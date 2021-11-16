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
				$where_indexid .= 'caves.indexid=' . $caveID ; //no quote because int cast before
			}
			else 
			{
				$where_indexid .= 'caves.indexid IS NOT NULL';
			}
			
			switch ($getAllLog) 
			{
				case 0:
				   $where_changelog .= ' changelog.isVisible=0';
					break;
				case 1:
					$where_changelog .= 'changelog.isVisible=1';
					break;
				case 2:
					$where_changelog .= '(changelog.isVisible=1 OR changelog.isVisible=0)';
					break;
				default:
				throw new exception ("findlastModifiedCave: Method not supported: $getAllLog");
			 
			}
  
            $req = 'SELECT name,indexid_caves,chgLogTxt,guidv4,date,changelog.indexid,isVisible  
                        FROM  ' . $this->dbtableprefix . 'caves as caves join  ' . $this->dbtableprefix . 'changelog as changelog
                        WHERE caves.indexid=indexid_caves AND ' . $where_indexid . ' AND ' . $where_changelog . '
                        ORDER BY changelog.date DESC limit 0,' . $max ;
            
            //$this->logger->debug('request : ' . $req);
            
            $pdoStatement = $this->PDO->query($req);
            $result = $pdoStatement->fetchall(PDO::FETCH_BOTH);
        }
        catch (Exception $e)
        {
            $this->setErrorMsg(__METHOD__,time(), L::varcaveCave_failToFetchChangelog );
			$this->logger->error('Fail to find mods : ' . $e->getmessage());
			$this->logger->debug('Full query : '. $req);
            
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
    * @param $noSaveSearch do not save user search for later use
    * @param $reqFields a csv value with requested fields. Beware that this function do not obfucate coords and generaly do not alter source data !
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
	function search($searchInput, $sortField = 'name', $ascDesc = 'ASC', $limitOffset = 0,$limitMax = 9999999, $noSaveSearch = false, $reqFields = false)
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
        $join = '';
        $groupby = ' GROUP BY caves.indexid ';
		$betweenFound = false;
		foreach($searchInput as $key=>$value)
		{
			
			if ( in_array( $value['field'], $this->getBooleanEndUserType() ) ){
                //depending field type, adapt query
                if( $value['type'] == '=' ){
                        $req .= ' ' . $value['field'] . ' = 1 ';
                }
                elseif( $value['type'] == 'NOTEQUAL'){
                    $req .= ' ' . $value['field'] . ' != 1 ';
                }
                else{
                    //not supported req
                    $this->logger->error(__METHOD__ . ' : ERROR invalid choice on boolean value : ' . $value['field'] . ' type: ' . $value['type'] . ' value:  ' . $value['value'] );
                    return false;
                    throw new Exception($msg,0);
                }
                
            }
            elseif ( in_array( $value['field'], $this->getFileEndUserType() ) ){
                //depending field type, adapt query
                $join = ' INNER JOIN  caves_files ON caves.indexid = caves_files.caveid ';
                if( $value['type'] == '=' ){
                    $req .= ' caves_files.file_type LIKE ' . $this->PDO->quote('%' . $value['field'] . '%');
                }
                else{
                    //not supported req
                    $this->logger->error(__METHOD__ . ' : ERROR invalid choice on file type value : ' . $value['field'] . ' type: ' . $value['type'] . ' value:  ' . $value['value'] );
                    return false;
                    throw new Exception($msg,0);
                }
                
            }
            elseif ($value['type'] == '='){
                $req .=  $value['field'] . ' = ' . $this->PDO->quote($value['value']) . ' ';
            }
            elseif ($value['type'] == 'LIKE'){
                //contient = LIKE '%xxxxx%'
                $req .= $value['field'] . ' LIKE ' . $this->PDO->quote('%' . $value['value'] . '%') . ' ';
            }
            elseif ($value['type'] == '>'){
                //ajouter 'valeur' > valeur_champ
                $req .= ' ' . $value['field'] . ' > ' . $this->PDO->quote($value['value']) . ' ';
            }
            elseif ($value['type'] == '<'){
                //ajouter < %'valeur'%
                $req .= $value['field'] . ' < ' . $this->PDO->quote($value['value']) . ' ';
            }
            elseif( $value['type'] == 'NOTEQUAL'){
                //
                $req .=  $value['field'] . ' != ' . $this->PDO->quote($value['value']) . ' ';
            }
			elseif ( $value['type'] == 'BETWEEN'){	
				if($betweenFound){
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
            elseif($value['type'] == '!='){
                $req .=  $value['field'] . ' != ' . $this->PDO->quote($value['value']) . ' ';
            }
            else{
				$this->logger->debug(__METHOD__ . ' : ERROR invalid choice : ' . $value['field'] . ' type: ' . $value['type'] . ' value:  ' . $value['value'] );
				return false;
				throw new Exception($msg,0);
            }
            
            //adding separator for each search elements
            $req .= ' AND ';
			
		}
		
		$req .= ' 1 '; //reques will be malformed : SELECT .... AND 1
		$limit = ' LIMIT ' . $limitOffset . ',' . $limitMax;
        
        //get search field that will be returned back to user
        if (!$reqFields){
            $colsReq = trim( $this->getConfigElement('returnSearchFields') );
        }
        else{
            $colsReq = $reqFields;
        }
    
		$reqSearch = 'SELECT ' . $colsReq .  ' FROM ' . $this->dbtableprefix .  'caves ' . $join . ' WHERE ' . $req . ' ' . $groupby . ' ORDER BY `' . $sortField . '` ' . $ascDesc . ' ' . $limit . ';';
	
		//prepare a list of cave for next/previs in display.php
		$qSearchNextPrevious = 'SELECT  indexid FROM ' . $this->dbtableprefix .  'caves ' . $join . ' WHERE ' . $req . ' ' . $groupby . ' ORDER BY `' . $sortField . '` ' . $ascDesc . ' ' . $limit . ';';
	
		//no LIMIT to get total item founds
		$reqCount = 'SELECT COUNT(*) FROM ' . $this->dbtableprefix  . 'caves ' . $join . ' WHERE ' . $req ;
		
		//get some info for selected search
		$reqSearchMetrics = 'SELECT (SELECT SUM( ABS( maxDepth ) ) ) as totalDepth,( SELECT SUM(length)) as totalLength FROM ' .  $this->dbtableprefix . 'caves ' . $join . ' WHERE ' . $req;   
		
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
    *    THIS SHOULD NOT BE USED UNLESS YO KNOW EXACTLY WHAT IT DOES !!!
	* handle rebuild of  end_user_fields table to have a way to configure wich
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
            //cleanup some fields from the tabe
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
					$query = 'SELECT *  FROM ' . $this->dbtableprefix . 'end_user_fields WHERE 1 ';
					break;
				case 'ONSEARCH':
					$query = 'SELECT field,type,field_group   FROM ' . $this->dbtableprefix . 'end_user_fields WHERE show_on_search=1 ';
					break;
				case 'ONDISPLAY':
					$query = 'SELECT field,type,field_group  FROM ' . $this->dbtableprefix . 'end_user_fields WHERE show_on_display=1 ';
					break;
                case 'ONPDF':
					$query = 'SELECT field,type,field_group  FROM ' . $this->dbtableprefix . 'end_user_fields WHERE show_on_pdf=1 ';
					break;
                case 'ONEDIT':
					$query = 'SELECT field,type,field_group   FROM ' . $this->dbtableprefix . 'end_user_fields WHERE show_on_edit=1 ';
					break;
            }
            $ORDERBY = ' ORDER BY field_group ASC, sort_order, field ASC';
				
			$pdoStmt = $this->PDO->query($query . $ORDERBY);
			 
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
			$reqCave = 'SELECT * FROM ' . $this->dbtableprefix  . 'caves WHERE guidv4=' . $this->PDO->quote($guid);
			$PDOstmtCave = $this->PDO->query($reqCave);
			$result = $PDOstmtCave->fetch(PDO::FETCH_ASSOC);
            
            
            $reqCoords = 'SELECT id,guidv4,name,ST_X(location) lat,ST_Y(location) "long", z FROM ' . $this->dbtableprefix  . 'caves ' . 
                         ' INNER JOIN caves_coordinates ' .
                         ' ON ' .  $this->dbtableprefix . 'caves.indexid = ' .  $this->dbtableprefix . 'caves_coordinates.caveid ' .  
                         ' WHERE guidv4=' . $this->PDO->quote($guid);
            $PDOstmtCoords = $this->PDO->query($reqCoords);
            $resultCoords = $PDOstmtCoords->fetchall(PDO::FETCH_ASSOC);
            
            //define an empty geoJson objet
            $geoJsonObj = new stdClass();
            $geoJsonObj->type = "FeatureCollection";
            $geoJsonObj->features = array();
            
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

                    foreach($resultCoords as &$coordSet)
                    {
                        $this->logger->debug('source coords are : ' . $coordSet['long'] . ' ' . $coordSet['lat']);
                        $coordSet['long'] = round( $coordSet['long'], 2);
                        $coordSet['lat'] = round( $coordSet['lat'], 2);
                        $this->logger->debug('new coords are : ' . $coordSet['long'] . ' ' . $coordSet['lat']);
                    }
                }
            }
            else
            {
                $this->logger->debug('Yes : Erase coords data from result set');
                $resultCoords = array(); //build an empty array to avoid typeset errors
            }

            //insert a new geoJson object in cavedata
            foreach($resultCoords as $key => $coordSet){
                $geoJsonObj->features[$key] = new stdClass();
                $geoJsonObj->features[$key]->type = "Feature";
                $geoJsonObj->features[$key]->geometry = new stdClass();
                $geoJsonObj->features[$key]->geometry->type = 'Point';
                $geoJsonObj->features[$key]->geometry->coordinates = array(
                                                0 => (float)$coordSet['lat'],
                                                1 => (float)$coordSet['long'],
                                                2 => (float)$coordSet['z'],
                                                );
                $geoJsonObj->features[$key]->properties = new stdClass();
                $geoJsonObj->features[$key]->properties->id = $coordSet['id'];                            
            }
            //store original coords php formated data 
            $result['caveCoords'] = $resultCoords;
            unset($resultCoords);
            //store data for users in json_coords "data field"
            $result['json_coords'] = json_encode($geoJsonObj);
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
            $this->logger->debug('Query : ' . $qUpdate);
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
    
    /*
     * updateCaveCoords update coordinates data for a specific cave.
     * 
     * @param     guidv4 : target cave guid
	 * @param     actionType : Action to run add | delete | edit
     * @param     targetCoordsetId id of coord set to edit/delete if set
     * @param     new values as a named array(long, lat, z)
     * 
	 * @return on success  : new id on add or true
	 * 		   on error  :  return false  or throw exception
     */
    function updateCaveCoords($guidv4, $actionType, $targetCoordsetId = false, $values = false){
         //CHECK cave existence
        $this->logger->info('caveClass::updateCaveCoords try to update coords of : ' . $guidv4);
        $cavedata = $this->selectByGUID($guidv4);
        if(  $cavedata == false)
        {
             $this->logger->error('Update fail, cave not found');
             return false;
        }
        
        switch ($actionType) {
            case 'add':
                $values = json_decode($values,true); //convert js string to php array
                $q = 'INSERT INTO ' . $this->dbtableprefix . 'caves_coordinates (id,caveid,z,location) '.
                     ' VALUES( null,' . (int)$cavedata['indexid'] . ', ' . (int)$values['z'] . ', ST_PointFromText("POINT(' . (float)$values['lat'] . ' ' . (float)$values['long'] .')", 4326) )';
                break;
            case 'edit':
                $values = json_decode($values,true); //convert js string to php array
                $q = 'UPDATE ' . $this->dbtableprefix . 'caves_coordinates SET ' .
                     '  location = ST_PointFromText("POINT(' . (float)$values['lat'] . ' ' . (float)$values['long'] .')",4326),'.
                     '  z = '. (int)$values['z'] . 
                     '  WHERE `caves_coordinates`.`id` = ' . (int)$targetCoordsetId;
                break;
            case 'del':
                $q = 'DELETE FROM ' . $this->dbtableprefix . 'caves_coordinates WHERE id =' . (int)$targetCoordsetId;
                break;
            default:
                $this->logger->error('Unsupported action : [' . $actionType . ']');
                throw new exception('Unsupported action : [' . $actionType . ']');            
        }
        
        //update DB
        try{
            $this->PDO->beginTransaction();
            $this->logger->debug('update coords query:' . $q);
            $this->PDO->query($q);
            if ($actionType == 'add'){
                $lastid = $this->PDO->lastinsertid();
                $this->PDO->commit();
                $this->logger->debug('update coords success. Last insertid :[' . $lastid . ']' );
                return $lastid;
            }
            $this->PDO->commit();
            $this->logger->debug('update coords success');
            return true;
        }catch(Exception $e)
        {
            $this->logger->error('Updating coordinates fail : ' . $e->getmessage() );
            $this->logger->debug('query : ' . $q );
            throw new exception('Error while updating cave coordinate: ' . $e->getmessage() );
        }
        
    }
    
	 /*
     * This method get a list of files or other data from 
	 * the files cave field.
     * 
     * @param     guidv4 : given cave guid
	 * @param     fileType : the specific attribute we want to get from the global json object
	 * @return on success  : array of data
	 * 		   on error or failure or not docs:  false
     */
    function getCaveFileList($guidv4, $filetype = 'all')
	{
		$this->logger->debug(__METHOD__ . ' : Enumerating files for a cave :['. $filetype . ']');
		$cave = $this->selectByGuid($guidv4);
		if($cave === false)
		{
			throw new exception(L::varcaveCave_badArgGuid);
		}
		
        if($filetype == 'all'){
            //fetch all files_type
            $q = 'SELECT * FROM ' . $this->dbtableprefix . 'caves_files WHERE caveid = ' . $cave['indexid'] ;    
        }
        else{
            //fetch only one specific files_type
            $q = 'SELECT * FROM ' . $this->dbtableprefix . 'caves_files WHERE caveid = ' . $cave['indexid'] . ' AND file_type = ' .  $this->PDO->quote($filetype) ;
        }
        
        try{
            $this->logger->debug('get cave file list query : ' . $q);
            $fileStmt = $this->PDO->query($q);
            $fileList = $fileStmt->fetchall(PDO::FETCH_ASSOC);
            $this->logger->debug('Get :' . count($fileList) . ' documents for this cave');
            
            //organise data presentation
            if($filetype != 'all'){
                if( count($fileList) >= 1 )
                {
                    $returnData[$filetype] = array(); ;
                    foreach($fileList as $file){
                        $returnData[$filetype][] = $file;
                    }
                    return $returnData;
                }
                else{
                    $this->logger->debug('no file data found');
                    return false;
                }
            }
            else{
                //request all file_type, organise data...
                if( count($fileList) >= 1 )
                {
                    foreach($fileList as $key => $files)
                    {
                        //parse multidim array
                        $returnData[ $files['file_type'] ][] = $files;
                    }
                    return $returnData;
                }
                return false;
            }
        }catch(Exception $e){
            $this->logger->error('Fail to fetch list of files');
            $this->logger->debug( $e->getMessage() );
            return false;
        }
	}

	
	/*  
     * This function to update a linked cave file
     * 
     * @param     guid = hex format like   dbe6f8e0a-2323-4986-b79d-d5ec5e46a1c3
     * @param     actionType can be add | edit | delete
     * @param     varObject : final json object element
	 * @param     values =   array containin [0]file_path [1]:file_note  
	 * @return on success : current id of edited/added value
	 * 		   on error  :  throw exception
     * 
     */
	function updateCaveFileList($guidv4, $actionType, $filetype, $itemid, $values)
	{
		$this->logger->debug(__METHOD__ . ' : action [' . $actionType . '] on file list. Field :['. $filetype . '] with index :[' . $itemid . '] and values[.' . substr($values[0],0,10) .','.substr($values[1],0,10) . ']...');
		
		$cave = $this->selectByGuid($guidv4);
		if($cave === false)
		{
			throw new exception(L::varcaveCave_badArgGuid);
		}
		
       
        switch($actionType){
            case 'add':
                //normaly on acrion `add`, no file_note so should be empty string
                $q = 'INSERT INTO ' . $this->dbtableprefix . 'caves_files (id,caveid,file_type,file_path,file_note) ' .
                       ' VALUES('.
                       'null,'.
                       $cave['indexid'] .','.
                       $this->PDO->quote($filetype) .','.
                       $this->PDO->quote($values[0]) .','.
                       $this->PDO->quote($values[1]) .
                    ')';                           
                break;
            
            case 'edit':
                //update only file_note
                $q = 'UPDATE ' . $this->dbtableprefix . 'caves_files SET file_note= ' . $this->PDO->quote($values[1]) .
                     ' WHERE id=' . $this->PDO->quote($itemid);
                break;
            
            case 'del':
                $q = 'DELETE FROM ' . $this->dbtableprefix . 'caves_files WHERE id=' . $this->PDO->quote($itemid);
                break;
            
            default:
                $this->logger->error('Unsupported action : [' . $actionType . ']');
                throw new exception('Unsupported action : [' . $actionType . ']'); 
        }
        
        try {
            $this->logger->debug( 'query : ' . $q );
            $this->PDO->query($q);
            if($actionType == 'add'){  //return id of new row
                return $this->PDO->lastinsertid(); 
            }
            return (int)$itemid;
        }
        catch(exception$e){
            $this->logger->error('Fail to update file list');
            $this->logger->debug( $e->getMessage() );
            $this->logger->debug( 'query : ' . $q );
            throw new exception(L::varcaveCave_failToUpdateFileList);         
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
			$q = 'SELECT stats.indexid as indexid ,view_count,caves.name as name ' .
                 'FROM ' . $this->dbtableprefix . 'stats as stats,' . $this->dbtableprefix . 'caves as caves ' .
                 'WHERE caves.indexid = cave_id ORDER BY view_count DESC LIMIT ' . (int) $this->config['displayedStats'] ;
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
		$coordsList = $coords->features;
		
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
            foreach($coordsList as $key => $value)
            {
                $long = $value->geometry->coordinates[0];
                $lat = $value->geometry->coordinates[1];
                $elev = $value->geometry->coordinates[2];
                
                $this->logger->debug('add point with : lat:'. substr_replace($lat ,"*",-5).' long:'.substr_replace($long ,"*",-5).'elev:'.$elev );
                $point                 = new Point(Point::WAYPOINT);
                $point->name           = $namePrefix . '_' . $i ;
                $point->latitude       = $lat;
                $point->longitude      = $long;
                $point->elevation      = $elev;
                $gpx_file->waypoints[] = $point;
                
                $i++;
            }
        }else {
            $long = $coordsList[0]->geometry->coordinates[0];
            $lat = $coordsList[0]->geometry->coordinates[1];
            $elev = $coordsList[0]->geometry->coordinates[2];
            
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
            session_name($this->cookieSessionName);
            session_id('system-session');
            session_start();
            $_SESSION = array();
            $_SESSION['groups'] = 'users';
            $_SESSION['isauth'] = 1;
            $_SESSION['username'] = 'gpx-kml_system-session';
            $this->logger->debug('System $_SESSION ('. session_id() .') info :' . print_r($_SESSION,true) );
        }
        
        foreach($allCaves as $key => $cave)
        {
            $cavedata = $this->selectByGUID($cave['guidv4'],false,false);
            
            $coords = json_decode($cavedata['json_coords']);
            $this->logger->debug('json data :  '. print_r($coords, true) );
            $coordsList = $coords->features;
            
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
                    $long = $value->geometry->coordinates[0];
                    $lat = $value->geometry->coordinates[1];
                    $elev = $value->geometry->coordinates[2];
                    
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
                $long = $coordsList[0]->geometry->coordinates[0];
                $lat = $coordsList[0]->geometry->coordinates[1];
                $elev = $coordsList[0]->geometry->coordinates[2];
                
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
            session_name($this->cookieSessionName);
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
     * Check onto a cave if some document(s) are registered
     * @param caveDocs target cave guid()
     * @param docType document type for existence check (can be biodoc, documents, etc.).
     * 
     * @result true if some document type are registered, false if nothing is registered
     */
    public function documentExists($guid, $docType){
        $this->logger->debug(__METHOD__ . ': check if documents [' . $docType . '] are registered for cave');
        
        $caveData = $this->selectByGUID($guid);
        try{
            $q = 'SELECT count(*) as docNbr FROM ' . $this->dbtableprefix . 'caves_files WHERE caveid = ' . (int)$caveData['indexid'] . ' AND file_type = '. $this->PDO->quote($docType) ;
            $countStmt = $this->PDO->query($q);
            $docsCount = $countStmt->fetch(PDO::FETCH_ASSOC);
            if( $docsCount['docNbr'] > 0){
                $this->logger->debug('Document of this type exists. Found : ' . $docsCount['docNbr']);
                return true;
            }
            else{
                 $this->logger->debug('no file document of this type present');
                 return false;
            }
        }
        catch(exception $e){
            $this->logger->error('File existence check failed');
            $this->logger->debug('Count query : ' . $q);
            return false;
        }
    }
    
    /*
     * getFilesFieldList
     * get an available list of type of document that are registered in table end_user_fields
     * @return 1dim array of elements, false on error
     */
     public function getFilesFieldList(){
        $this->logger->debug(__METHOD__ . ': get registered documents type from  `end_user_fields`');

        try{
            $q = 'SELECT field FROM ' .  $this->dbtableprefix . 'end_user_fields WHERE field_group="files"';
            $pdoRes = $this->PDO->query($q);
            
            while($res = $pdoRes->fetch(PDO::FETCH_NUM)){
                $r[] = $res[0];
            }
            return $r;
        }catch(exeption $e){
            $this->logger->error('Failed to get document list' . $e->getmessage() );
            $this->logger->debug('Full query :' . $q);
            return false;
        }
    }
    
    /*
     * getBooleanEndUserType
     * get a list of type of end user fields from `end_user_fields` where type is bool
     * @return 1dim array of elements, false on error
     */
     public function getBooleanEndUserType(){
        $this->logger->debug(__METHOD__ . ': get registered boolean elements from  `end_user_fields`');

        try{
            $q = 'SELECT field FROM ' .  $this->dbtableprefix . 'end_user_fields WHERE type="bool"';
            $pdoRes = $this->PDO->query($q);
            
            while($res = $pdoRes->fetch(PDO::FETCH_NUM)){
                $r[] = $res[0];
            }
            return $r;
        }catch(exeption $e){
            $this->logger->error('Failed to get boolean list' . $e->getmessage() );
            $this->logger->debug('Full query :' . $q);
            return false;
        }
    }
    
        /*
     * getFileEndUserType
     * get a list of type of end user fields from `end_user_fields` where type is bool
     * @return 1dim array of elements, false on error
     */
     public function getFileEndUserType(){
        $this->logger->debug(__METHOD__ . ': get registered files elements type from  `end_user_fields`');

        try{
            $q = 'SELECT field FROM ' .  $this->dbtableprefix . 'end_user_fields WHERE field_group="files"';
            $pdoRes = $this->PDO->query($q);
            
            while($res = $pdoRes->fetch(PDO::FETCH_NUM)){
                $r[] = $res[0];
            }
            return $r;
        }catch(exeption $e){
            $this->logger->error('Failed to get files list' . $e->getmessage() );
            $this->logger->debug('Full query :' . $q);
            return false;
        }
    }
    
    
    
    /*
     * deleteCave
     * Delete specified cave and remove all data in caves/<guidv4> folder this include at least files/photos/docs.
     * Remove changelog and statistic data. This process is non recoverable (except with backup).
     * 
     * @param string $guidv4 target cave guid to delete
     * @return $mixed true on success, array of undeleted element, false critical error
     */
     
     public function deleteCave($guidv4){
         $this->logger->info(__METHOD__ . ': request cave deletion');
         try{
            if( strlen($guidv4) != 36 ){
                 $this->logger->error('Bad cave guid format');
                 return false;
            }

            
            $cavePath[0] = './caves/' . $guidv4;
            
            //get content of cave directory as an array
            $dirListingArr = dirListingArr($cavePath[0]) ;
            
            //add root dir of dirpath
            $dirListingArr = array_merge($cavePath, $dirListingArr );
            
            //reverse to have deletion in right order
            $dirListingArr = array_reverse($dirListingArr);
        
            $this->PDO->beginTransaction();
            
            //get cave info to get cave indexid
            $caveData = $this->selectByGUID($guidv4);
            if($caveData == false){
                $this->logger->error('Cave not found in database');
                return false;
            }
            $caveID = $caveData['indexid'];
            //purge changelog data
            $q  = 'DELETE FROM ' . $this->dbtableprefix . 'changelog WHERE  indexid_caves = "' . $caveID . '";';
            $error = $this->PDO->query($q);
            
            //purge statistics
            $q  = 'DELETE FROM ' . $this->dbtableprefix . 'stats WHERE  cave_id = "' . $caveID . '";';
            $error = $this->PDO->query($q);
            
            //purge coordinates
            $q  = 'DELETE FROM ' . $this->dbtableprefix . 'caves_coordinates WHERE  caveid = "' . $caveID . '";';
            $error = $this->PDO->query($q);
            
            //purge files
            $q  = 'DELETE FROM ' . $this->dbtableprefix . 'caves_files WHERE  caveid = "' . $caveID . '";';
            $error = $this->PDO->query($q);
            
            // ***  finaly remove cave *** 
            $q  = 'DELETE FROM ' . $this->dbtableprefix . 'caves WHERE  indexid = "' . $caveID . '";';
            $error = $this->PDO->query($q);


            //check if deletion possible 
            $unWrFiles = false;
            
            foreach($dirListingArr as $key => $path)
            {
                if(filetype($path) == 'dir')
                {
                    $this->logger->debug('check dir rw mode: [' . $path .']');
                    if( is_writable($path) == false ){
                                $unWrFiles[] = $path;
                    }
                    else{
                        $this->logger->info('dir deleted [' . $path .']');
                        rmdir($path);
                    }
                }
                else{ //is a file
                    if( is_writable($path) == false ){
                            $unWrFiles[] = $path;
                    }
                    else{
                        $this->logger->info('file deleted [' . $path .']');
                        unlink($path);
                    }
                }
            }
            
            //commit db changes
            $var = $this->PDO->commit();
            
            
            $this->logger->info('*** cave deleted ***');
            //send back to user success status
            if($unWrFiles === false){
                return true;
            }
            else{
                $this->logger->info('Some files where not deleted');
                return $unWrFiles;
            }
         }
         catch(exception $ee){
            $this->logger->error('Unable to delete cave: ' . $e->getmessage() );
            $this->logger->debug('Full query :' . $q);
            if(!empty($unWrFiles) ){
                $this->logger->error('Some files could not be deleted, check access rights: ' . print_r($unWrFiles,true) );
            }
            $this->PDO->rollBack();
            return false;    
         }
     }
     
     /*
      * Find location of caves located near a point
      * 
      * @param int $origin origin coordinate for radius calculation
      * @param int $maxRadius max radius around a point to locate caves
      * @param int $maxCavesToFind limit result set to a number of caves
      * @return mixed array on success, false on other cases
      */
     public function findNearCaves($origin, $maxRadius, $maxCavesToFind, $excludecaveid, $jsarray = false){
        $this->logger->debug(__METHOD__ . ': try to locate caves around a point : [' . $origin . '] Radius : [' . $maxRadius . ']');
        $q = 'SELECT ' . 
               '  caves.guidv4,caves.name, ST_astext(location) as coords,ST_X(location) as X, ST_Y(location) as Y, ST_Distance_Sphere(Point(' . $origin . '), location) as distance ' .
               'FROM ' .
               '   caves_coordinates ' .
               'INNER JOIN ' .
               ' caves ' .
               'ON caveid=indexid ' .
               'WHERE ' . 
               '   ST_Distance_Sphere(Point(' . $origin . '), location) <' . (int)$maxRadius . ' AND indexid != ' . (int)$excludecaveid . ' ' .
               'ORDER BY distance ASC, name ASC ' .
               'LIMIT ' . $maxCavesToFind;
            
        try{
            $pdostm = $this->PDO->query($q);
                          
            if($pdostm->rowCount() <= 0){
                $this->logger->debug('No caves found!');
                return false;
            }
            
            //$coordsSet = $pdostm->fetchall(PDO::FETCH_ASSOC);
            $caveSet = $pdostm->fetchall(PDO::FETCH_ASSOC);
            
            $returnData = array();

            foreach($caveSet as $cave => $values)
            {
                $caveData = $this->selectByGUID($values['guidv4'], false, false);
                $this->logger->debug('cavedata: ' . print_r($caveData, true));
                // old version $jsreturn[]= array( $caveData['name'], $caveData['guidv4'], array($caveData['X'], $caveData['Y']) ) ;
                $returnData[] = array( 
                                    'name' => $caveData['name'], 
                                    'guidv4' => $caveData['guidv4'],
                                    'coords' => 'POINT(' . $caveData['caveCoords'][0]['lat'] . ',' . $caveData['caveCoords'][0]['long'] . ')',
                                    'X' => $caveData['caveCoords'][0]['lat'],
                                    'Y' => $caveData['caveCoords'][0]['long'],
                                    'distance' => $values['distance'],
                                    );
            }
            if($jsarray){
                $this->logger->debug('return data json :' . print_r($returnData, true) );
                return json_encode($returnData);
            }
            $this->logger->debug('return data :' . print_r($returnData, true) );
            return $returnData;
        }
        catch(exception $e){
            $this->logger->error('ERROR while searching cave around');
            $this->logger->error('error : ' . $e->getmessage());
            $this->logger->debug('query : '. $q);
            return false;
        }
     }
}    
?>
