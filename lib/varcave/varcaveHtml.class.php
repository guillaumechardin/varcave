<?php 
require_once ('varcave.class.php');
require_once ('varcaveAuth.class.php');
require_once ('varcaveCave.class.php');

class VarcaveHtml extends Varcave {
	//pagename used to be inserted in <title> tag
	private $pageName = null ;
	//lang used
	private $language = '';
	
	private $html = '';
	// force html entities convertion for user generated html
	private $forceHtmlEntities = true;
	//set htmlentities args 
	private $htmlEntitiesArgs = ENT_QUOTES|ENT_HTML5;
	
	//insert the no index header into <head> page to prevent search engine indexing
	private $noindex = false;
	
    function __construct($pageName) 
    {
		parent::__construct(); 
		$this->pageName = $pageName;
    }
    
    function __destruct() 
    {
        
    }
    
    private function buildHeader()
	{
		$this->html .= '<!doctype html>';
		$this->html .= '<html lang="' . $this->langcode . '">';
		$this->html .= '<head>';
		$this->html .= '<meta charset="utf-8">';
		if($this->noindex)
		{
			$this->html .= '<meta name="robots" content="noindex" />';
		}
		$this->html .= '<title>' . $this->pageName . '</title>';
		
		$this->html .= '<link href="/css/default/default.css" rel="stylesheet" type="text/css" media="screen" />';
		$this->html .= '<link href="/css/default/print.css" rel="stylesheet" type="text/css" media="print" />';
		$this->html .= '<link href="/css/default/mobile.css" rel="stylesheet" type="text/css"  media="screen and (max-width: 968px)" />';
		
		//overide some css settings if user set a predfered css
		if (isset($_SESSION['userCss']) && $_SESSION['userCss'] != '' )
		{
			$this->html .= '<link href="/css/custom/'. $_SESSION['userCss'] . '/custom.css" rel="stylesheet" type="text/css" media="screen" />';
			$this->html .= '<link href="/css/custom/'. $_SESSION['userCss'] . '/print.css" rel="stylesheet" type="text/css" media="print" />';
			$this->html .= '<link href="/css/custom/'. $_SESSION['userCss'] . '/mobile.css" rel="stylesheet" type="text/css"  media="screen and (max-width: 968px)" />';
		}
		
		
       
       
        $this->html .= '<link href="/lib/fontawesome-free/5.6.3/web/css/all.css" rel="stylesheet" type="text/css"  />';
        //loading purecss.io css files
        $this->html .= '<link href="/css/pure-1.0.0/src/base/css/base.css" rel="stylesheet" type="text/css"  />';
        $this->html .= '<link href="/css/pure-1.0.0/src/buttons/css/buttons.css" rel="stylesheet" type="text/css"  />';
        
		$this->html .= '<script defer src="/lib/fontawesome-free/5.6.3/web/js/all.js" data-auto-replace-svg="nest"></script>';
		//script for fontawesome special features
		
        $this->html .= '<script src="/lib/jquery/jquery-3.3.1.min.js"></script>';
		$this->html .= '<script src="/lib/varcave/common.js"></script>';
        /*
         * handling javascript payload for login
         */
        $this->html .= '<script src="/lib/varcave/login.js"></script>';
        $this->html .= '<script>'."\n";
        $this->html .= '  var errors_ERROR ="' . L::errors_ERROR. '";';
        $this->html .= '  var login_pwdTooShort ="' . L::login_pwdTooShort . '";';
        $this->html .= '  var chars ="' . L::chars . '";';
        $this->html .= '  var login_pwdAreNotSame ="' . L::login_pwdAreNotSame . '";';
        $this->html .= '</script>';
        
        /* 
         * Manage usage of quick search
         */
        if($this->getConfigElement('useSearchAutocomplete') )
		{
            $this->html .= '<script src="lib/jqueryui/jquery-ui-1.12.1/jquery-ui.js"></script>';
            $this->html .= '<link rel="stylesheet" href="lib/jqueryui/jquery-ui-themes-1.12.1/themes/base/jquery-ui.css" />';
			$this->html .= '<script>var active_autocomplete = true </script>';
        }
        else{
            //disabling usage of quick search
            $this->html .= '<script>var active_autocomplete = false </script>';
        }
		$this->html .= '<link rel="shortcut icon" href="img/favicon.png" />';
		$this->html .= '</head>';
		$this->html .= '<body>';
		$this->html .= '<div class=topimg>' . $this->config['websiteFullName'];
		$this->html .= '</div>';
		//adding website menu
		$this->addTopMenu();
		$this->html .= '<div class="userPageContent">';
		$this->html .= '<div id="jqUiDialog" ><div id="jqUiDialogContent">  </div></div>';
	}
	
	private function buildFooter()
	{
        $version = $this->version();

		$this->html .= '</div>'; // end div userPageContent
		$this->html .= '<div id="footer">';
        $this->html .= '  <div class="">';
        $this->html .= '    <a href="https://github.com/guillaumechardin/varcave/">' . L::general_poweredBy. ' Varcave</a> ';
        $this->html .= '    <span>' . $version['pgmversion'] . '/'. $version['dbversion'] . '</span>';

        $this->html .= '  </div>';
        $this->html .= '  <div class="footerMsg">';
		$this->html .=  $this->config['footerMsg'];
        $this->html .= '  </div>';
        
		$this->html .= '  <div class="">';
		$this->html .= '    <a href="contact.php">' . L::contactus . '</a>';
		$this->html .= '  </div>';
        
        if ($this->config['loglevel'] == 0){
            $this->html .= '  <div class="">';
            $time = (microtime(true) - $this->startInvoke)*1000 ;
            $this->html .= '[Exec:' . round($time,2) . 'ms]';
            $this->html .= '  </div>';
		}
        
		$this->html .= '</div>'; //end footer div
		
		
    
		$this->html .= '</body>';
		$this->html .= '</html>';

	
	}
	
	public function save()
	{
			return $this->html;
	}
	
	/*
	 * this function insert the user generated HTML into 
	 * the website common "html header". 
	 * Optionnal arg automaticaly add the "page footer" after the user content
	 */
	function insert($newHtml,$addFooter = true)
	{
		$this->buildHeader();
		
		$this->html .= $newHtml;
		if ($addFooter)
		{
			$this->buildFooter();
		}
		
	}
	
	private function addTopMenu()
	{
		$auth =  new VarcaveAuth;
		//home menu
		$this->html .= '<div class="topmenu">';
		$this->html .= '  <div class="dropdown">';
		$this->html .= '    <button class="dropbtn">'. L::menu_thisSite  ;
		$this->html .= '      <i class="fas fa-caret-down"></i>';
		$this->html .= '    </button>';
		$this->html .= '    <div class="dropdown-content">';
		$this->html .= '      <a href="/index.php">' . L::menu_home . '</a>';
		$this->html .= '      <a href="/stats.php">' . L::menu_mostViewed . '</a>';
        $this->html .= '      <a href="/contact.php">' . L::menu_contact . '</a>';
		$this->html .= '    </div>';
		$this->html .= '  </div>';
		
		//cave menu
		$acl = $auth->getacl('370f195f-0d8d-46bc-b3a6-7c9da3e88289');
        if ( $auth->isSessionValid() &&  $auth->isMember( $acl[0]) )
		{
            
            $newcaveMenu = '';
            $acl = $auth->getacl('7c19458c-5d4f-5f25-9af5-9b2ea7f7a79b');
            if ( $auth->isSessionValid() &&  $auth->isMember( $acl[0]) )
            {
                $newcaveMenu = '<a href="/newcave.php">' . L::menu_newcave . '</a>';
            }
            
            $this->html .= '  <div class="dropdown">';
            $this->html .= '    <button class="dropbtn">'. L::menu_caves  ;
            $this->html .= '      <i class="fas fa-caret-down"></i>';
            $this->html .= '    </button>';
            $this->html .= '    <div class="dropdown-content">';
            $this->html .= '      <a href="/search.php?search=all">' . L::menu_fullList . '</a>';
            $this->html .= '      <a href="/search.php">' . L::menu_search . '</a>';
            $this->html .= '      <a href="/lastchanges.php">' . L::menu_lastchanges . '</a>';
            $this->html .= $newcaveMenu;
            $this->html .= '    </div>';
            $this->html .= '  </div>';
        }
		
		
		//Ressources
		
		$this->html .= '  <div class="dropdown">';
		$this->html .= '    <button class="dropbtn">'. L::menu_ressources  ;
		$this->html .= '      <i class="fas fa-caret-down"></i>';
		$this->html .= '    </button>';
		$this->html .= '    <div class="dropdown-content">';
		$this->html .= '      <a href="/pdfbook.php">' . L::menu_pdfbookPage . '</a>';
		$this->html .= '      <a href="/ressources.php">' . L::menu_files . '</a>';
		$this->html .= '    </div>';
		$this->html .= '  </div>';
		
		//other
		
		$this->html .= '  <div class="dropdown">';
		$this->html .= '    <button class="dropbtn">'. L::menu_other  ;
		$this->html .= '      <i class="fas fa-caret-down"></i>';
		$this->html .= '    </button>';
		$this->html .= '    <div class="dropdown-content">';
		$this->html .= '      <a href="/about.php">' . L::menu_about . '</a>';
		$this->html .= '      <a href="/contact.php">' . L::menu_contact . '</a>';
		$this->html .= '    </div>';
		$this->html .= '  </div>';
		
		//administration
		$acl = $auth->getacl('98aef116-c96d-5163-9ed3-4cd8482b10a4');
        if ( $auth->isSessionValid() &&  $auth->isMember( $acl[0]) )
		{
			$this->html .= '  <div class="dropdown">';
			$this->html .= '    <button class="dropbtn">'. L::menu_admin  ;
			$this->html .= '      <i class="fas fa-caret-down"></i>';
			$this->html .= '    </button>';
			$this->html .= '    <div class="dropdown-content">';
			$this->html .= '      <a href="/techsupport.php">' . L::menu_techInfo . '</a>';
			$this->html .= '      <a href="/siteconfig.php">' . L::menu_siteConfig . '</a>';
			$this->html .= '      <a href="/usermgmt.php">' . L::menu_userMgmt . '</a>';
			$this->html .= '      <a href="/olconfigplugins.php?action=default">' . L::menu_olLayers . '</a>';
			$this->html .= '      <a href="/ressources.php?admin=true">' . L::menu_ressourcesMgmt . '</a>';
            $this->html .= '      <a href="/usermgmt.php?#tab-acl">' . L::menu_aclMgmt . '</a>';
            $this->html .= '      <a href="/newsmgmt.php">' . L::menu_newsMgmt . '</a>';
			$this->html .= '      <a href="/fieldssettings.php">' . L::menu_fieldsMgmt . '</a>';
            
			$this->html .= '    </div>';
			$this->html .= '  </div>';
		}
		
		
		
		$this->html .= '  <div class="dropdown rigthy">';
		

        if ( $auth->isSessionValid() &&  $auth->isMember( 'users') ) //hardcoded 'users' group 
		{
			$accountMenu  = '<button class="dropbtn login"> ';
			$accountMenu .= L::menu_myAccount . '<i class="fas fa-caret-down"></i>';
			$accountMenu .= '</button>';
			$accountMenu .= '<div class="dropdown-content">';
			$accountMenu .= '      <a href="/myaccount.php">' . L::menu_myaccount . '</a>';
			$accountMenu .= '      <a href="/login.php?logout=1">' . L::menu_logout . '</a>';
			$accountMenu .= '</div>';
			
		}
		else
		{
			$accountMenu  = '<button class="dropbtn login" id="connectBtn"> ';
			$accountMenu .=  L::menu_login ;
			$accountMenu .= '</button>';
			/*$accountMenu .= '<div class="dropdown-content">';
			$accountMenu .= '      <a href="login.php>' . L::menu_logout . '</a>';
			$accountMenu .= '</div>';*/
			
		}
		$this->html .=      $accountMenu;
		$this->html .= '</div>'; //login menu
		
		$this->html .= '  <div class="quickSearch">';
		$this->html .= ' <input  placeholder="' . htmlentities(L::quickSearchByName) . '" type="text" id="quickSearchInput"><span class="pure-button quickSearchBtn">OK</span>';
		$this->html .= '  </div>';
		
		$this->html .= '</div>'; //topmenu div end
        
        //alert bar
        $issues = $this->getIssues();
        if( $issues && $auth->isSessionValid() &&  $auth->isMember( 'admin') ){
            $issues = $this->getIssues();
            $this->html .= '<div id="alert-bar">';
            if($issues) {
                $this->html .= '<div id="alert-title">' . L::alertIssues_requireAttention . '</div>';
                $this->html .= '<ul id="alert-list">';
                foreach( $issues as $key => $alert) {
                    $this->html .= '<li class="alert-' . $key . '">' . $alert . '</li>';
                }
                $this->html .= '</ul>';
                $this->html .=  '<button id ="alert-bar-close">X</button>';
            }
            $this->html .= '</div>';
        }
        
        

		$this->html .= '<div class="dbStat">';
		$this->html .= '  <span>' . L::menu_nbrOfCavesInDB . ' : ';
		try
		{
			$req = 'SELECT COUNT(*) as caves FROM ' . $this->dbtableprefix . 'caves WHERE 1;';
			$PDOstmt = $this->PDO->query($req);
			$results = $PDOstmt->fetch(PDO::FETCH_ASSOC);
			$this->html .= $results['caves'];
		}
		catch (Exception $e)
		{
			$this->logger->error( basename(__FILE__) . ' : dbstat : fail to get nbr of cave : ' . $e->getmessage() );
			$this->logger->debug('Full query : ' . $req);
			$this->html .= L::errors_ERROR;
		}
		$this->html .= '</span>';
		$this->html .= '</div>';
		
		
	}
	
	public function insertAfter($divName)
	{
		
	}
	
	public function insertBefore($divName)
	{
		
	}

	//set this->noindex attribute
	function setNoindex($val)
	{
		$this->noindex = $val;
	}
	
	function stopWithMessage($title, $msg, $httpStateCode, $httpStateString)
	{
		header('HTTP/1.1 ' . $httpStateCode . '  ' . $httpStateString);
		$htmlstr = '<h1 id="msgTitle">' . $title . '</h1>';
		$htmlstr .= '<div id="msgContent">' . $msg . '</div>';
		
		$this->insert($htmlstr,true);
		echo $this->save();
        exit();
	}
    
    /*
     * this function output json data for ajax queries
     */
    function writeJson( $clientReturnData,  $httpCode = 200, $httpCodeStr = 'OK')
    {
        $this->logger->debug(__CLASS__ . ' : reply json data to client browser');
        if( !is_array($clientReturnData) )
        {
            $this->error(__CLASS__  . 'argument is not array');
        }
        
        //send back to browser
        header('HTTP/1.1 ' . $httpCode . $httpCodeStr);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($clientReturnData);
        exit();
    }
    
}



?>
