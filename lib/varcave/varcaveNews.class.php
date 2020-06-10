<?php

require_once (__DIR__ . '/varcave.class.php');
require_once (__DIR__ . '/varcaveAuth.class.php');
 
 /* 
  * This class handle news management for homepage
  *   add
  *   deletion
  *   modification
  */
 
 

class varcaveNews extends Varcave
{     
    function __construct() 
    {
        parent::__construct(); 
    }

    
	/**
	* getNews provide a simple way to get a list of news article 
    * with their respective data.
	* @input $_start : limit query row return
	* @input $_end : limit query  row return
    * @param $allNews get all news even thoses marked as deleted
    * @param $asNamedCol if set ouput is a named array
	*
	* @return an array of news and their content.
	* 
	* NOTE : on return false
	**/
	function getNews ($_start=0, $_end = 100, $allNews = false, $asNamedCol = false)
    {
        $this->logger->debug(__METHOD__ . ' : fetching list of news from db');
		try
        {
            if ($allNews)
            {
                $where = ' 1 ';
            }
            else
            {
                $where = ' deleted = 0 ';
            }
			
            $q = 'SELECT news.indexid,  ' .
                        'news.deleted, '.
                        'news.content, '.
                        'news.title, '.
                        'news.creation_date, '.
                        'news.edit_date, '.
                        'u2.username as editor_username, '.
                        'u2.firstname as editor_firstname, '.
                        'u2.lastname as editor_lastname, '.
                        'u.username, '.
                        'u.firstname, '.
                        'u.lastname ' .
                ' FROM ' . $this->dbtableprefix . 'news as news' .
                ' LEFT JOIN ' . $this->dbtableprefix . 'users as u ' .
                '   ON news.creator = u.indexid ' .
                ' LEFT JOIN ' . $this->dbtableprefix . 'users as u2 ' .
                '  ON news.last_editor = u2.indexid ' .
                'WHERE ' . $where . ' ' .
                'ORDER BY creation_date DESC ' .
                'LIMIT '. $_start . ',' . $_end ;
            
            $this->logger->debug('Query : ' . $q);
            
			$pdoStatement = $this->PDO->query($q);
            if($asNamedCol)
            {
                $result = $pdoStatement->fetchall(PDO::FETCH_ASSOC);
            }
            else
            {
                $result = $pdoStatement->fetchall(PDO::FETCH_NUM);
            }
        }
        catch (Exception $e)
        {
            $this->logger->error('Fail to get news' . $e->getmessage());
            $this->logger->debug('Query : ' . $q);
            return false;
        }
        $this->logger->debug('Found :' . count($result) . ' News to display' );
        $this->logger->debug('query : ' . $q);
        //renvoi du tableau
        return $result;
        
    } 
    
	/**
	* getNews provide a simple way to get one news content by this ID
	* @input $_newsIndexid : new db indexid
	*
	* @return an array with news  content. Or false news is not found
	**/
    function getNewsbyID($_newsIndexid)
    {
        $this->logger->debug(__METHOD__ . ' : try to get news with ID:[' . $_newsIndexid .']');
		$q = 'SELECT '.
                  $this->dbtableprefix . 'news.*,' .
                  $this->dbtableprefix . 'users.username '.
                'FROM ' .
                  $this->dbtableprefix . 'news,users '.
                'WHERE ' .
                  $this->dbtableprefix . 'users.indexid = news.creator '.
                'AND ' .
                  $this->dbtableprefix . 'news.indexid = ' .  $this->PDO->quote($_newsIndexid) ;
		try
		{
            $this->logger->debug('query:'.$q);
		    $pdoStmtListeNews = $this->PDO->query($q);
		}
        catch (Exception $e)
        {
            $this->logger->error('Fail to fetch news by ID' . $e->getmessage() );
        }
		
        $result = $pdoStmtListeNews->fetch(PDO::FETCH_ASSOC);
        $this->logger->debug('query success, results :' . print_r($result,true) );
        
		if( empty($result) )
        {
            return false;
        }
        else
        {
            return $result;
        }

    } 

	/*
	 * this function insert a new News into database.
	 * @param $creator : integer identifying the user uid that insert the new data
	 * @param $title News title
	 * @param $content html content of the News
	 * @return : int on success representing the new indexid inserted, false on failure
	 */
	function addNews($title, $content) 
    {
		$this->logger->info(__METHOD__ . ' : Add new News to db');
        try
        {	
			//check if user authenticated because of use of $_session var
			if( !isset($_SESSION['uid']) && $_SESSION['isauth'] != 1 )
			{
				$q = '';
				throw new exception('Trying to update data with unauthenticated user');
			}
			
			$q = 'INSERT INTO ' .  $this->dbtableprefix . 'news'. 
			'(indexid,creator,last_editor,deleted,content,title,creation_date,edit_date)'.
			'VALUES (
			   NULL, '.                                     //indexid
			   $this->PDO->quote($_SESSION['uid']).','.     //Creator*
			   'NULL,'.                                     //last_editor
			   '0,'.                                        //deleted
			    $this->PDO->quote($content) . ', '.         //content
			    $this->PDO->quote($title) . ','.            //title
			   '0,'.                                     //creation_date
			   '0'.                                      //edit_date
			')';
			$this->PDO->query($q);
			$lastid = $this->PDO->lastinsertid();
			$this->logger->error('Update sucess, last id is:[' . $lastid . ']');
			$this->logger->debug('Query:' . $q);
			return $lastid;
		}
        catch (Exception $e)
        {
            $this->logger->error('Fail to update database: '.$e->getmessage() );
			$this->logger->error('Query:' . $q);
			return false;
        }
    }
	
	/*
	 * Function updateNews update an existing news with new data
	 * @param $_newsIndexid : news DB indexid
	 * @param $_newsData : a json object with assoc values: title, content, deleted
	 * @return true on success, false on error
	 */ 
	function updateNews ($_newsIndexid, $_newsData ) 
    {
		$this->logger->debug(__METHOD__ . ' : try to edit news nbr : ['.$_newsIndexid.']');
		
		//$_newsData .='}.\///,;:';
		$_newsData = json_decode ($_newsData, true);
		
		if( $_newsData == false )
		{
			$this->logger->error(__METHOD__ . ' : Data arg is not an valid json object');
			return false;
		}
		$this->logger->debug(print_r($_newsData,true));
		//small `transtype` for database compliance
		if ($_newsData['deleted'] == '')
		{
			$_newsData['deleted'] = 0;
		}
		else
		{
			$_newsData['deleted'] = 1;
		}
		
		try
        {	
			//check if user authenticated because of use of $_session var
			if( !isset($_SESSION['uid']) && $_SESSION['isauth'] != 1 )
			{
				throw new exception('Trying to update data with unauthenticated user');
			}
			$q = 'UPDATE ' . $this->dbtableprefix . 'news 
		      SET title =' . $this->PDO->quote($_newsData['title']) . ',
			      content = ' . $this->PDO->quote($_newsData['content']) .',
				  deleted = ' . $_newsData['deleted'] . ',
				  last_editor = ' . $_SESSION['uid']. '
			  WHERE indexid=' . $this->PDO->quote($_newsIndexid);
			  
			$this->PDO->query($q);
			$this->logger->debug('Query : ' . $q);
			
		}
        catch (Exception $e)
        {
            $this->logger->error('Fail to update News ' . $e->getmessage());
			$this->logger->debug('Query : ' . $q);
			return false;
        }
		return true;
    }

    function deleteNews () 
    {
       /*
        News is not deleted, just hidden !
        */
        $mysqliQueryDelete='UPDATE news SET visible=0 WHERE indexid=' . $this->newsIndexid;
        try
        {		
			$this->PDO->query($mysqliQueryDelete);
		}
        catch (Exception $e)
        {
            exit('Erreur de la suppression de la news : ' . "\n" . $mysqliQueryDelete . "\n" . $e->getmessage());
        }
        return 0;
	}
    
}
?>
