<?php 
require_once (__DIR__ . '/varcaveI18n.class.php');
require_once(__DIR__ . '/../Klogger/logger.php');
require_once('functions.php');

try
{
	$i18n = new varcavei18n(__DIR__ . '/../../lang/lang_{LANGUAGE}.ini',  __DIR__ . '/../../langcache/');
	$i18n->setFallbackLang(); //set fallback lang to default one specified in config(by using no args)
	$i18n->setMergeFallback(true);
	$i18n->init();
}
catch (Exception $e)
{
    //capture error messagest
    echo '<strong>Varcave initiating fail in ' . $e->getFile() . ' at line : '. $e->getline() . '</strong><br>';
    echo $e->getmessage();
}

class Varcave {
	//engine version
	public const version = '3.0';
	
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
    
    
    function __construct() 
    {
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
			session_save_path(__DIR__ . '/../../' . $this->config['sessiondir'] );
			session_start();

			setcookie(session_name(), session_id(), time()+ $this->config['sessionlifetime'], '/' );
            //set default geoapi for anonymous users
			if (!isset($_SESSION['userGeoApi']) )
			{
				$_SESSION['userGeoApi'] =  $this->getConfigElement('default_geo_api');
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
	private function retreiveConfig()
	{
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
	* This method permit to register some var to return
	* message to user.
	* verbose logs for admin are handled by $this->logger().
	**/
	public function setErrorMsg($context, $title, $msg)
	{
		$datetime = time();
		$errCntr = $this->errorMsgCntr;
		$this->errorMsg[$this->errorMsgCntr]['context'] = $context;
		$this->errorMsg[$this->errorMsgCntr]['unixtimestamp'] = $datetime;
		$this->errorMsg[$this->errorMsgCntr]['title'] = $title;
		$this->errorMsg[$this->errorMsgCntr]['msg'] = $msg;
		$this->errorMsgCntr++;
		return $errCntr;
	}
	
	public function getErrorMsg($asString = false)
	{
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
	 public function getROOT_DIR()
	 {
			return $this->ROOT_DIR ;
	 }
	
	/*
	 * getter for bdtableprefix
	 */
	public function getTablePrefix()
	{
		return $this->dbtableprefix;
	}
	

    
    /*
     * Retrieve loglevel from DB
     * and translate it to an understandable value.
     */
    public function getLogLevel()
    {
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
	public function fetchConfigSettings($getAll = true, $fullInfo = false)
	{
		$this->logger->debug(__METHOD__ . ' : Getting config settings');
		$this->logger->debug('getAll:'.$getAll.'|fullInfo:'.$fullInfo);
		if($getAll == true)
		{
				$q = 'SELECT configItem, configItemValue, configIndexid, configItemGroup FROM ' .  $this->dbtableprefix . 'config WHERE 1 ORDER BY configIndexid  ASC';
		}
		else
		{
			$q = 'SELECT configItem, configItemValue, configIndexid, configItemGroup FROM ' .  $this->dbtableprefix . 'config WHERE configItemAdminOnly=0 ORDER BY configIndexid  ASC';
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
	public function setConfigSettings($itemID, $value)
	{
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
	
	public function getConfigElement($element)
	{
		if ( isset ($element) )
		{
			return $this->config[$element];
		}
		return false;
	}
	
	//get all config elements as array
	public function getAllConfigElements()
	{
		return $this->allConfigInfo;
	}
	
	/*
	 * Get a list of files from `ressources` table 
	 * @return  array on sucess, throw exeption on failure
	 */
	function getFilesRessources()
	{
		$this->logger->info(__METHOD__ . ' : fetch a list of files ressources');
		try
		{
			//generate 	csv content 
			$q = 'SELECT 
					
					display_group,
					GROUP_CONCAT(\'"\',indexid,\'"\') as indexid,
					GROUP_CONCAT(\'"\',display_name,\'"\') as display_name,
					GROUP_CONCAT(\'"\',filepath,\'"\') as filepath,
					GROUP_CONCAT(\'"\',description,\'"\') as description,
					GROUP_CONCAT(\'"\',display_name,\'"\') as display_name,
					GROUP_CONCAT(\'"\',creation_date,\'"\') as creation_date,
					GROUP_CONCAT(\'"\',access_rights,\'"\') as access_rights
					
					FROM ' . $this->dbtableprefix . 'files_ressources
					WHERE 1 
					GROUP BY display_group ASC';
			$resPDOStmt = $this->PDO->query($q);
			$ressources = $resPDOStmt->fetchall(PDO::FETCH_ASSOC);
			
			$this->logger->info('successfully get data from db');
			return $ressources;
		}
		catch(Exception $e)
		{
			$this->logger->error('Unable to fetch from db');
			$this->logger->debug('original query : ' . $q);
			throw new exception(L::varcave_ressourcesfetchfail);
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
			$q = 'SELECT * FROM ' . $this->dbtableprefix . 'list_coordinates_systems WHERE 1';
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
				
				if(! $fh)
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
}

   

?>
