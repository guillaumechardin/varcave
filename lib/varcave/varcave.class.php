<?php 
require_once (__DIR__ . '/varcaveI18n.class.php');
require_once(__DIR__ . '/../Klogger/logger.php');
require_once('varcaveAuth.class.php');
require_once('functions.php');

try
{
	$i18n = new varcavei18n(__DIR__ . '/../../lang/lang_{LANGUAGE}.ini',  __DIR__ . '/../../langcache/');
	$i18n->setFallbackLang(); //set fallback lang (by using no args) to default one specified in config
	$i18n->setMergeFallback(true);
	$i18n->init();
    
    $i18nAppliedLang = $i18n->getAppliedLang();
}
catch (Exception $e)
{
    //capture error messagest
    echo '<strong>Varcave initiating fail in ' . $e->getFile() . ' at line : '. $e->getline() . '</strong><br>';
    echo $e->getmessage();
}

class Varcave {
	//engine version
	public const version = '3.4.1';
	
    //logger interface
    public $logger;
    
    //PDO object to run queries...
	public $PDO;
	
	//end user config file to store some local info like DB users and passwd
	private $configFile = __DIR__ . '/../../include/localconfig.php' ;
    
	protected $engine;
	protected $dbhost;
	protected $dbname;
	protected $dbtableprefix;
	protected $dbuser;
	protected $dbpasswd;
	
	protected $config = false;
	protected $allConfigInfo = false;
	//realpath of website
	protected $ROOT_DIR ='';
	
	//protected $execError = false;
	// standard var to handle message to user.
	// Must not be used to return sensitive informations
	protected $errorMsg = array();
	protected $errorMsgCntr = 0;
	
    //initialize  timestamp to compute some stats
    protected $startInvoke = 0;
    
    //langcode the currently applied i18n language
    protected $langcode = '';
    
    //cookie session name 
    protected $cookieSessionName = 'VARCAVEID';
    
    function __construct(){
        $this->startInvoke = microtime(true);
        #loading local configuration
		//setting temporary logger interface because defined bd loglevel
		//is not yet retreive
		$this->logger = new Katzgrau\KLogger\Logger(__DIR__ . '/../../logs', Psr\Log\LogLevel::INFO );
		$this->retreiveConfig();
        
		/* realpath of website
		 * 2 levels strip "/lib/varcave" from real path
		 * NO TRAILING SLASH
		 */
		$this->ROOT_DIR = dirname(realpath(__DIR__), 2); 
		
        #initiate logging to file
		$this->logger = new Katzgrau\KLogger\Logger(__DIR__ . '/../../logs', $this->getLogLevel() );
		
        //set langcode 
        global $i18nAppliedLang;
        $this->langcode = $i18nAppliedLang;
        
        
		//set fallback language for localization.
		// ==> if user prefered lang is not found, i18n will use this translations.
		
		//setting up translation (THIS IS NOT CURRENTLY WORKING BECAUSE i18n require init() AFTER all options set
		//$this->logger->debug('user lang order : $_GET[lang]:' . $_GET['lang'] . '|$_SESSION[lang]:' . $_SESSION['lang'] . '|$_SERVER[HTTP_ACCEPT_LANGUAGE]:' . $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
		//$i18n->setFallbackLang( strtolower( $this->config['fallbackLanguage'] ) );
	
		
		
		//required by some $_SESSION  settings
		if (session_status() == PHP_SESSION_NONE)
		{	
			$this->logger->debug("No session exist for current browser. Creating or resuming one");
			//set sessions directory
			//session_save_path(__DIR__ . '/../../' . $this->config['sessiondir'] );
            session_name($this->cookieSessionName);
			session_start();

			setcookie(session_name(), session_id(), time()+ $this->config['sessionlifetime'], '/' );
            //set default geoapi for anonymous users
			if (!isset($_SESSION['geo_api']) )
			{
				$_SESSION['geo_api'] =  $this->getConfigElement('default_geo_api');
			}

            //set default group anonymous for anonymous users
			if (!isset($_SESSION['groups']) )
			{
				$_SESSION['groups'] =  'anonymous';
                $_SESSION['username'] =  'anonymous';
                $this->logger->debug('Full anon user, adding anonymous group');
			}
            else
            {
                $groups = explode(',', $_SESSION['groups']);
                
                if( !in_array('anonymous', $groups) )
                {
                    $this->logger->debug('anonymous group not found adding group');
                }
            }
			
		}
		
		
    }
    
    function __destruct() 
    {
        
    }
    
	#get current configuration from database
	private function retreiveConfig(){
		try
		{
			
			if ( ! @file_exists($this->configFile) )
			{
				throw new Exception ($this->configFile . ' does not exist');
			}
			else
			{
				require($this->configFile);
				foreach ($LOCALCONFIG as $key => $value)
				{
					if ($key == 'engine')
					{
						$this->engine = $value;
					}
					else if ($key == 'dbhost')
					{
						$this->dbhost = $value;
					}
					else if ($key == 'dbname')
					{
						$this->dbname = $value;
					}
					else if ($key == 'dbtableprefix')
					{
						$this->dbtableprefix = $value;
					}
					else if ($key == 'dbuser')
					{
						$this->dbuser = $value;
					}
					else if ($key == 'dbpasswd')
					{
						$this->dbpasswd = $value;
					}
					else
					{
						throw new Exception('Configuration keyword unavailable : ' . $key);
					}
				}					
			}
		}
		catch (Exception $e)
		{
			 echo ('ERROR : ' . $e->getmessage() . '<br/>');
			 exit;
		}
		
		#geting configuration info from db
		try
		{
            $options = array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                
            );
			$this->pdoDSN = $this->engine . ':dbname=' . $this->dbname . ';host=' . $this->dbhost. ';charset=utf8';
			$this->PDO = new PDO($this->pdoDSN, $this->dbuser, $this->dbpasswd);
            $this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
			
			$this->fetchConfigSettings();
			
		}
		catch (Exception $e)
		{
			echo ('Error getting config from database:<br/>' . $e->getmessage() . '<br/>' . $e->getline() . '<br/>' . $e->getTraceAsString(). '<br/>' . $e->getfile() );
			exit();
		}
		
	}
    
	/**
    * Legacy function, deprecated, avoid use
	* This method permit to register some var to return
	* message to user.
	* verbose logs for admin are handled by $this->logger().
	**/

	public function setErrorMsg($context, $title, $msg){
		$datetime = time();
		$errCntr = $this->errorMsgCntr;
		$this->errorMsg[$this->errorMsgCntr]['context'] = $context;
		$this->errorMsg[$this->errorMsgCntr]['unixtimestamp'] = $datetime;
		$this->errorMsg[$this->errorMsgCntr]['title'] = $title;
		$this->errorMsg[$this->errorMsgCntr]['msg'] = $msg;
		$this->errorMsgCntr++;
		return $errCntr;
	}
	
	public function getErrorMsg($asString = false){
		if ( isNullOrEmptyArray($this->errorMsg) )
		{
			$this->errorMsg = array("WARNING" => 'No error message', );
		}
		if ($asString)
		{
			return print_r( $this->errorMsg, true);
		}
		return $this->errorMsg;
	}
    
	/*
	 * get ROOT_DIR
	 */
	 public function getROOT_DIR(){
			return $this->ROOT_DIR ;
	 }
	
	/*
	 * getter for bdtableprefix
	 */
	public function getTablePrefix(){
		return $this->dbtableprefix;
	}
	

    
    /*
     * Retrieve loglevel from DB
     * and translate it to an understandable value.
     */
    public function getLogLevel(){
       $level =  $this->getConfigElement('loglevel');
       
       switch($level)
       {
            case 7:
                return Psr\Log\LogLevel::EMERGENCY;
            case 6:
                return Psr\Log\LogLevel::ALERT;
            case 5:
                return Psr\Log\LogLevel::CRITICAL;
            case 4:
                return Psr\Log\LogLevel::ERROR;
            case 3:
                return Psr\Log\LogLevel::WARNING;
            case 2:
                return Psr\Log\LogLevel::NOTICE;
            case 1:
                return Psr\Log\LogLevel::INFO;
            default:
                return Psr\Log\LogLevel::DEBUG;
        }
     }       

	/*
     * Get all configuration settings from db
	 * by default only user editable settings are available
     * @param     $getAll : bool, if true get all settings, including some avanced parameters
	 *            $fullInfo : set $this->configAllInfo to get config in 2dim array 
	 *            
	 * @return on success  : true 
	 * 		   on error or failure :  throw exception
     * 
     */
	public function fetchConfigSettings($getAll = true, $fullInfo = false){
		$this->logger->debug(__METHOD__ . ' : Getting config settings');
		$this->logger->debug('getAll:'.$getAll.'|fullInfo:'.$fullInfo);
		if($getAll == true)
		{
				$q = 'SELECT configItem, configItemValue, configIndexid, configItemGroup, configItemType FROM ' .  $this->dbtableprefix . 'config WHERE 1 ORDER BY configIndexid  ASC';
		}
		else
		{
			$q = 'SELECT configItem, configItemValue, configIndexid, configItemGroup,configItemType FROM ' .  $this->dbtableprefix . 'config WHERE configItemAdminOnly=0 ORDER BY configIndexid  ASC';
		}
		
		//fetching DB
		try
		{
			$configPDOstmt = $this->PDO->query($q);
			$results = $configPDOstmt->fetchall(PDO::FETCH_ASSOC);
			//removing 1st level of array
			foreach ( $results as $key => $value)
			{
				$this->config[ $value['configItem'] ] = $value['configItemValue'];
			}
			
			if($fullInfo)
			{
				$this->logger->debug('exporting ALL config to array varcave::allConfigInfo');
				$this->allConfigInfo = $results;
			}
			
			return true;
		}
		catch(Exception $e)
		{
			$this->logger->error('Fail to fetch configuration : ' . $e->getmessage() );
			throw new exception('Fail to fetch configuration : ' . $e->getmessage() );
		}
	}

	/*
     * Set configuration settings in db
     * @param     $itemID : int, item primary key to update
	 *			  $value : string, new item value             
	 * @return on success  : true
	 * 		   on error or failure :  throw exception
     * 
	 */						
	public function setConfigSettings($itemID, $value){
		$this->logger->info(__METHOD__ . ' : updating config item [' . $itemID . ']' );
		$q = 'UPDATE ' . $this->dbtableprefix . 'config SET configItemValue =' . $this->PDO->quote($value) . ' WHERE configIndexid=' . $this->PDO->quote($itemID);
		
		try
		{
			$this->PDO->query($q);
			$this->logger->debug('Full query :' . $q);
			return true;
		}
		catch(Exception $e)
		{
			$this->logger->error('Fail to update config data : ' . $e->getmessage() );
			$this->logger->debug('Full query :' . $q);
			throw new exception(L::varcave_updateconfigfail);
		}
		
	}
	
	/*
	 * This function request a specific configuration item
	 * @param $element = the configuration element to fetch
	 * @return  if element found :  mixed
	 *          if element not found: false
	 */
	
	public function getConfigElement($element){
		if ( isset ($element) )
		{
            if( isset($this->config[$element]) )
			{   
                return $this->config[$element];
            }
            else
            {
                return false;
            }
		}
		return false;
	}

    /* This function add a new config element in varcave  database
     * @param array $data : an associative array containing in order : [configItem, configItemValue, configItemType, configItemGroup, adminOnly ]
     * @return true on success, throw exception on error
     */
    public function addConfigElement($data)
    {
        $this->logger->debug(__METHOD__ . ' : start adding [' . $data['configItem'] . ']');
        if( $this->getConfigElement($data['configItem']) )
        {
            $this->logger->error('Error, config element exists');
            throw new exception(L::errors_ERROR . ' : ' . L::varcave_configElementExists);
        }
        else
        {
            $this->logger->debug('  OK: Config element do not exists');
        }

        if(   empty($data['configItem'])
           || empty($data['configItemValue'])  
           || empty($data['configItemType'])
           || empty($data['configItemGroup'])
           || empty($data['configItem_dsp'])
        )
        {
            $this->logger->error('Error, bad argument value');
            throw new exception(L::errors_ERROR . ' : ' . L::errors_badArgs);

        }
        $q = 'INSERT INTO ' .  $this->dbtableprefix . 'config('. 
            '`configItem`, `configItemValue`, `configItemType`, `configItemMtime`, `configItemGroup`, `configItemAdminOnly`, `configIndexid`)' .
            ' VALUES ( '. 
                    $this->PDO->quote($data['configItem']) . ', ' .
                    $this->PDO->quote($data['configItemValue']) . ', ' .
                    $this->PDO->quote($data['configItemType']) . ', ' .
                    '0, ' .
                    $this->PDO->quote($data['configItemGroup']) . ', ' .
                    '0, ' .
                    'NULL);';
        try
        {
            $this->PDO->query($q);
            $this->updatei18nIniVal('lang/local/custom_' . $this->getlangcode() . '.ini', 'siteconfighelp', $data['configItem'] . '_dsp', $data['configItem_dsp']);
            $this->updatei18nIniVal('lang/local/custom_' . $this->getlangcode() . '.ini', 'siteconfighelp', $data['configItem'] . '_hlp', $data['configItem_hlp']);
            return true;
        }
        catch(Exception $e)
        {
            $this->logger->error(__METHOD__ . ' :  Error while adding ressource to config : ' . $e->getMessage() );
            throw new exception(L::varcave_updateconfigfail);
        }
    }

	
	//get all config elements as array
	public function getAllConfigElements(){
		return $this->allConfigInfo;
	}
	
	/*
	 * Get a list of files from `ressources` table 
	 * @param $userGroupArray [currentlyNotSupported] an array of user group membership acceding ressource
     * @return  array on sucess, throw exeption on failure
     * 
	 */
    function getFilesRessources($userGroupsArray = ''){
        $this->logger->info(__METHOD__ . ' : fetch a list of files ressources');
        $auth = new VarcaveAuth();
        try{
            
            $q = 'SELECT * ' .
                 'FROM ' . $this->dbtableprefix . 'files_ressources' .
                 ' WHERE 1 ORDER BY display_group ASC';
            $stm = $this->PDO->query($q);
            $ressources_all = $stm->fetchall(PDO::FETCH_ASSOC);
            $user_groups = explode(',', $_SESSION['groups']);
            
            //get current permited files
            $available_ressources = array();
            foreach($ressources_all as $key => $value)
            {
                $cur_ressource_group = explode(',', $value['access_rights']);

                //lookup all user group to search if user is member of thoses groups
                
                foreach($user_groups as $usr_grp_key => $usr_grp_val){
                    if( $auth->isSessionValid() && $auth->isMember( 'admin' ) ){ //force show file if user admin
                        $available_ressources[] = $ressources_all[$key];
                        break; 
                    }
                    //show file depending on user group
                    if( in_array($usr_grp_val, $cur_ressource_group) ){
                        $available_ressources[] = $ressources_all[$key];
                        break; //skip further test on same ressource
                    }
                }                
            }
            
            //reorder result by display group
            $user_res = array();
            foreach($available_ressources as $key => $values){
                //extract all display_group
                foreach($available_ressources[$key] as $k => $val){
                    if($k == 'display_group'){
                        continue;
                    }
                    
                    $user_res[ $values['display_group'] ][$key][$k] = $val;
                }
                
            }
            return $user_res;
        }
        catch(Exception $e){
            $this->logger->error('  Error while fetching permitted ressources : ' . $e->getMessage() );
            throw new exception(L::varcave_ressourcesfetchfail);
        }
    }
    
    
    /*
     * addFilesRessources add a file to table ressource that can be
     * later displayed in ressources.php
     * @param $display_name 
     * @param $display_group
     * @param $filepath
     * @param $description
     * @param $creatorID
     * @param $accessRights
     * 
     * @return new element indexid from DB. Throw exception on failure  
     * 
     */
    function addFilesRessources($display_name,$display_group,$filepath,$description,$creatorID,$accessRights = 'admin'){
        $this->logger->debug(__METHOD__ . ': adding file to ressources');
        try{
            $q = 'INSERT INTO ' . $this->getTablePrefix() . 'files_ressources
                (display_name,display_group,filepath,description,creator,access_rights) 
                VALUES(' . 
                $this->PDO->quote($display_name)  . ',' . 
                $this->PDO->quote($display_group) . ',' .
                $this->PDO->quote($filepath)      . ',' .
                $this->PDO->quote($description)   . ',' .
                $this->PDO->quote($creatorID)     . ',' . 
                $this->PDO->quote($accessRights)  . ')';
                
            $this->PDO->query($q);
            $lastid = $this->PDO->lastinsertid();
            $this->logger->debug('File added successfully to db, lastid :' . $lastid);
            return $lastid;
        }
        catch(exception $e){
            $this->logger->error('Fail to update DB : ' . $e->getmessage() );
            $this->logger->debug('Full query : ' . $q);
            throw new exception(L::errors_databaseUpdateFail);
        }
				
    }

	/*
	 * get a list of available coordinate systems
	 * list is store in db `cooordinate_systems`
     * Common display name should be inserted in coordsSystems_SYSTEMNAME
     * lang_XX.ini to get a correct display name
	 */
	public function getCoordsSysList()
	{
		try
		{
			$this->logger->debug(__METHOD__ . ' :get a list of coordinate systems');
            //$cs_list = $this->getListElement('list_coordinates_systems');
			$q = ''.
            'SELECT lists.indexid as indexid,list_name,list_item as name,js_lib_filename,php_lib_filename ' .
            'FROM  `' . $this->dbtableprefix . 'lists` as lists ' . 
            'INNER JOIN `' . $this->dbtableprefix . 'list_coordinates_systems` as lcs ' .
            'ON lists.indexid = lcs.indexid_lists  ' . 
            'WHERE `list_name` =   "list_coordinates_systems"';
            
			$qPdostmt = $this->PDO->query($q);
            $datas = $qPdostmt->fetchall(PDO::FETCH_ASSOC);
            
            foreach ($datas as $key => &$data)
            {
                //add the localized name  if available
                if ( defined('L::coordsSystems_' . $data['name']) ){
					$data['display_name'] = constant('L::coordsSystems_' . $data['name']);
				}else{
					$data['display_name'] = $data['name'];
				}
                
            }
            return $datas;
		}
		catch(exception $e)
		{
			//on failure we send back default data
			$this->logger->error('Fetch failed :' . $e->getmessage() );
			$this->logger->debug('Full query:' . $q );
			return false;
		}
	}
	
	/*
	 * createCacheFile
	 * this function create new file into "cache" dir
	 * @param string $type : type of cache target can be url, jpg (not supported yet. ie to create a thumbnail)
	 * @param mixed  $typeArg : needed args for cache type
	 * @param string $localFilepath : file path to write cached file
	 * 
	 * @return true on success, either false
	 */
	public function createCacheFile($type, $typeArg, $localFilepath)
	{
		$this->logger->debug(__METHOD__ . ' : trying to cache file');
		switch($type)
		{
			case 'url':
				$this->logger->debug('type is url, target is:' . $typeArg . 'local store is ['.$localFilepath.']');
				
				$file = file_get_contents($typeArg); 
				
				$fh  = fopen($localFilepath, 'w+'); 
				
				if(! $fh || !$file)
				{
					$this->logger->error('Unable to open file ' . $localFilepath);
					return false;
				}
				
				fputs($fh, $file); 
				fclose($fh); 
				unset($file);
				return true;
				break;
			
			default;
				$this->logger->debug('unspported method: ' . $type);
				return false;
				break;
		}
		
		return false;
	}
	
	function caveStats()
	{
		$this->logger->debug(__METHOD__ . ' : get cave stats');
		$req = 'SELECT COUNT(*) as caves, SUM(*) FROM ' . $this->dbtableprefix . 'caves WHERE 1;';
		
	}
    
    /*
     * getListElement
     * Get a list of element by fetching table lists
     * @param $listname list name to query an get elements
     * 
     * @return false on error or empty set array on sucess
     */
    function getListElements($listname){
        $this->logger->debug(__METHOD__ . ': fetching list of item for list :' . $listname);
        try{
            $q = 'SELECT * FROM ' . $this->dbtableprefix . 'lists WHERE list_name=' . $this->PDO->quote($listname) ;
            $pdoStmt = $this->PDO->query($q);
            if ($pdoStmt->rowCount() == 0 )
            {
                // empty list
                return false;
            }
            $r = $pdoStmt->fetchall(PDO::FETCH_ASSOC);
            $this->logger->debug('find this list :' . print_r($r,true) );
            return $r;
        }
        catch(Exception $e)
        {
            $this->logger->error('Fail to fetch database :' . $e->getMessage() );
            $this->logger->debug('Full Query: ' . $q);
            return false;
        }
    }

    /*
     * addListElement
     * add a unique list element in table lists
     * @param $listname list group name
     * @param $value associated value 
     * 
     * @return true on success, false otherwise
     */
    public function addListElement($listname, $value)
    {
        $this->logger->info(__METHOD__ . ' : Add new element to list : '. $listname);
        try
        {
            $q = 'INSERT INTO ' . $this->dbtableprefix . 'lists ' .
               '(`indexid`, `list_item`, `list_name`) VALUES (' .
               'NULL, ' . $this->PDO->quote($value) . ', ' . $this->PDO->quote($listname) . ')';
            $this->PDO->query($q);
            return true;
        }
        catch(Exception $e)
        {
            $this->logger->error('  Fail to add new list element' . $e->getmessage() );
            return false;
        }

    }

    
    /*
     * getIssues
     * Retrieve a list of know issue of website like debug on,
     * some dirs that are not rw and so on
     * 
     * @return array() of messages  
     * @return false on error or if no issues found
     */
    function getIssues(){
        $this->logger->debug(__METHOD__ . ' get a list of known issues');
        
        // check if debug is on
        $issues = false; 
        if ($this->getConfigElement('loglevel') == 0){
            $issues[] = L::alertIssues_debugSet;
        }
        
        //check some read/write folders
        $dirList  = array_map('trim', explode(',', $this->getConfigElement('RWfolders') ) ) ;
        foreach($dirList as $key => $folder){
            $dir = $this->ROOT_DIR . '/' . $folder ;
            if( ! is_writable($dir) ){
                $issues[] = L::alertIssues_roFolder . ' : ' . $dir;
            }
        }
        
        
        return $issues;
    }

    /*
     * updateEndUserFields
     * Update end_user_fields table
     * @param $id indexid of db row
     * @param $field Db col name to update
     * @param $value new value
     * 
     * @return true on success false on error
     */
    public function updateEndUserFields($id, $field, $value){
        $this->logger->debug(__METHOD__ . ': Try to update end_user_fields data');
        if ( empty($id) || empty($field) ) {
            throw new Exception(L::errors_ERROR . ' : ' . L::errors_badArgs);
        }
        
        //update val       
        try{
            $q  = 'UPDATE ' . $this->getTablePrefix() . 'end_user_fields';
            $q .= ' SET `' . $field . '`=' . $this->PDO->quote($value) ;
            $q .= ' WHERE indexid =' . $this->PDO->quote($id) ;
            
            $this->PDO->query($q);
            return true;
        }
        catch (exception $e){
            $this->logger->error(__METHOD__ . ': Fail to update db : ' . $e->getmessage() );
            $this->logger->debug('full query :'. $q);
            return false;
        }
    }
    
     /*
     * addEndUserFields
     * add a new editable field in end_user_fields table usable by end users
     * @param $fieldName the field name
     * @param $value 
     * 
     * @return last inserted line indexid on success, false on error
     */
    public function addEndUserFields($fieldName, $fieldGroup, $fieldType){
        $this->logger->debug(__METHOD__ . ': Try to add a new end user field to DB:[' . $fieldName . ']');
        
        /*
         * sanitize fieldName ; fieldgroup and field type are pre-registered data so no check on it
         * remove keep only alpha char az-AZ
         */
        $fieldName = preg_replace( '/[^a-z_]/i', '', $fieldName);
        try{
            $field = $this->PDO->quote($fieldName);
            $field_group = $this->PDO->quote($fieldGroup);
            $type = $this->PDO->quote($fieldType);
            // set some default data value for Varcave correct operation
            $q = 'INSERT INTO ' . $this->dbtableprefix .'end_user_fields ' .
                     ' (`indexid`, `field`, `type`, `sort_order`, `show_on_display`, `show_on_search`, `show_on_edit`, `show_on_pdf`, `field_group` ) ' .
                     ' VALUES('. 
                                'NULL,'.//id
                                $field .','.//field
                                $type .','.//type
                                '9999,'.//sort order
                                '0,'.//show display
                                '0,'.//show search
                                '0,'.//show edit
                                '0,'.//show pdf
                                $field_group.
                            ')';
                $this->PDO->query($q);
                return $this->PDO->lastInsertId();
        }
        catch(exception $e){
            $this->logger->error('Fail to update DB :' . $e->getmessage() );
            $this->logger->debug('Full query:'. $q);
            return false;
        }
    }
    
    /*
     * updatei18nIniVal()
     * update i18n file in current language. If value do not exists it will be created
     *
     * @param string $filename target ini file to read/write, must be writable
     * @param string $section ini section name to add/update data
     * @param string $inivar ini varname to update
     * @param string $value varname corresponding value
     * 
     * @return string the effective written string value false on error
     */
     public function updatei18nIniVal($filename, $section, $inivar, $value){
        $this->logger->debug(__METHOD__ . ': Updating ini file:[' . $filename . ']');
        
        $section = preg_replace( '/[^a-z_]/i', '', $section);
        $inivar = preg_replace( '/[^a-z_]/i', '', $inivar);
        
        if( file_exists($filename) ){
            if(is_Writable($filename) == false ){
                $this->logger->error('File is not writable');
                return false;
            }
            $this->logger->debug('get custom_xx.ini file data');
            $ini_array = parse_ini_file($filename, true);

            if($ini_array === false){
                $this->logger->error('fail to load ini file. Check syntax.');
                return false;
            }
        }else{
             $this->logger->notice('File do not exist. A new one will be created later');
        }
        
        

        $safeVal = str_replace ( '"' , '' , $value);

        //update var but escape double quote char
        $ini_array[$section][$inivar] = $safeVal;
        
        $this->logger->debug('Writing new value to file');
        write_php_ini($ini_array, $filename );
        
        $this->logger->debug('Operation success');
        
        return $ini_array[$section][$inivar];
     }
     
     /*
     * addCaveCol()
     * update table caves to add an additionnal col.
     *
     * @param string $filename target ini file to read/write, must be writable
     * @param string $section ini section name to add/update data
     * @param string $inivar ini varname to update
     * @param string $value varname corresponding value
     * 
     * @return string the effective written string value false on error
     */
     public function addCaveCol($colName, $colType, $colDefault = ''){
        //remove non standard chars
        $colName = preg_replace( '/[^a-z_]/i', '', $colName);
        
        $this->logger->debug(__METHOD__ . ': Adding new col: [' . $colName .'] to caves table');
        
        if( empty($colName) || empty($colType) ){
            $this->logger->error(L::errors_ERROR . ':' . L::errors_badArgs . ':'. L::errors_emptyVal);
            return false;
        }
        
        $default ='';
        if($colDefault){
            $default =  'DEFAULT ' . $this->PDO->quote($colDefault);
        }
        
        //prepare sql statement 
        switch($colType){
            case 'text':
                $colType = 'TEXT';
                break;
            case 'decimal':
                $colType = 'DECIMAL (10,3)'; //number from -9999999.999 to +9999999.999
                break;
            case 'bool':
                $colType = 'BOOLEAN';
                break;
            case 'timestamp':
                $colType = 'INT';
                break;
            default:
            $this->logger->error(L::errors_ERROR . ':' . L::errors_badArgs . ' : coltype : [' . $colType .']' );
            return false;
        }
        
        try{
            $this->logger->debug('alter database...');
            $q = 'ALTER TABLE ' . $this->dbtableprefix .'caves ' .
                 'ADD `' . $colName . '` ' . $colType . ' DEFAULT NULL ' .
                 $default ;
            
            $this->PDO->query($q);
            $this->logger->debug('success !');
            return true;
        }
        catch(exception $e){
            $this->logger->error('Fail to alter database ' . $e->getmessage() );
            $this->logger->debug('full query :' . $q);
            return false;
        }
     }
    
    /*
     * version
     * get current database and php application version
     * 
     * @return an array describing current versions or false on error
     */
    public function version(){
        try{
            $qdbv = 'SELECT * FROM ' . $this->dbtableprefix . 'dbversion WHERE id = 1';
            $rPDOStmt = $this->PDO->query($qdbv);
            $dbver = $rPDOStmt->fetch(PDO::FETCH_ASSOC);
            $r = array(
                    'dbversion' => $dbver['major_version'] . '.' .
                                   $dbver['minor_version'] . '.' .
                                   $dbver['patch_version'],
                    'pgmversion' => varcave::version,
                    );
            return $r;
        }
        catch(Exception $e){
            $this->logger->error(__METHOD__ . ': Fail to get version info : ' . $e->getmessage() );
            $this->logger->debug('full query :'. $qdbv);
            return false;
        }
    }

    /*
     * getlangcode getter for i18 language code
     */
     public function getlangcode(){
         return $this->langcode;
     }

    public function setFilesRessourcesRights($ressourceID, $accessrights){
        $this->logger->info(__METHOD__ . ' update file ressources acces rights');
        $this->logger->debug('target ressource :' . $ressourceID . ' rights :'. $accessrights);
        try{
            $q = 'UPDATE ' . $this->getTablePrefix() . 'files_ressources ' .
             'SET access_rights=' . $this->PDO->quote($accessrights) . ' '.
             'WHERE indexid=' . $this->PDO->quote($ressourceID);
            $this->PDO->query($q);
            return true;
        }
        catch(Exception $e){
            $this->logger->error('  Fail to update access rights ' . $e->getMessage() );
            return false;
        }
    }


    /* 
     * Get a list of registered ol plugins in database
     * @return array of data on success or true if no pluggin found
     * false on error
    */
    public function getOlRegisteredPlugins($id = false)
    {
        $this->logger->debug(__METHOD__ . ' search plugins');
        try
        {
            if($id === false)
            {
                $q = 'SELECT * FROM ' . $this->getTablePrefix() . 'layers_plugins WHERE 1';
            }
            else
            {
                $q = 'SELECT * FROM ' . $this->getTablePrefix() . 'layers_plugins WHERE indexid = ' . $this->PDO->quote($id);
            }
            
            $stm = $this->PDO->query($q);
            $data = $stm->fetchall(PDO::FETCH_ASSOC);
            if (empty($data) )
            {
                $this->logger->warning(__METHOD__ . ': No registered pluggins');
                return array();
            }
            return $data;
        }
        catch(exception $e)
        {
            $this->logger->error('Failed to find pluggins . ' . $e->getmessage() );
            return false;
        }
    }

    public function registerOlPlugin($pluginConfig, $filename)
    {
        $this->logger->debug(__METHOD__ . ' : start plugin registration [' . $pluginConfig[0]['pluginName'] . ']');
                
        try
        {
            foreach($pluginConfig as $key => $config)
            {
                if( isset($config['configItem'] ) && !empty($config['configItem']) )
                {
                    $this->addConfigElement($config);
                }
            }
            $this->logger->debug(print_r($config, true) );
            $q = 'INSERT INTO ' . $this->getTablePrefix() . 'layers_plugins (`indexid`, `guid`, `map_name`, `map_display_name`, `path`, `is_active`) ' .  
             'VALUES ('. 
                    'NULL, ' .
                    $this->PDO->quote($config['pluginGUID']) . ', ' .  
                    $this->PDO->quote($config['pluginShortName']) . ', ' .   
                    $this->PDO->quote($config['pluginName']) . ', ' .
                    $this->PDO->quote($filename) . ', ' . 
                    '0
            )';

            $this->PDO->query($q);
            return true;
        }
        catch(Exception $e)
        {
            $this->logger->error('fail to register plugin' . $e->getmessage() );
            $this->logger->debug('full query : ' . $q );
            throw new exception('update failed : ' . $e->getmessage());
        }
    }

    /*
     * Set open layer pluggin state to enabled/disabled
     * @param $is : plugin indexid
     * @param $state boolean true=enaled, false=disabled
     * return true on success, false otherwise
     */
    public function setOlPluginState($id, $state)
    {
        $this->logger->info(__METHOD__ . ' Change plugin state : ' . $id);
        try
        {
            $q = 'UPDATE layers_plugins SET is_active=' . $this->PDO->quote($state) . 
                 ' WHERE `layers_plugins`.`indexid` = ' . $this->PDO->quote($id);
            $this->PDO->query($q);
            return true;

        }
        catch (Exception $e)
        {
            $this->logger->error('  Fail to update plugin state : ' . $e->getmessage() );
            $this->logger->debug('Full query : ' .  $q);
            return false;
        }

    }
}
?>
