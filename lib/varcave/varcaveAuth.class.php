<?php 
require_once (__DIR__ . '/varcave.class.php');
require_once (__DIR__ . '/varcaveUsers.class.php');

class VarcaveAuth extends Varcave
{
	/**
	* This function provide a login interface. By 
	* providing username and password, it check it on the db
	* it check if 
	* @input $user  the username
	* @input $password  the password
	*
	* @return true if login success, false if something fail.
	* 
	* NOTE : detail on failure can be obtain by array $debugMsg of
	* current instance of the object
	**************************************************/
	
	public function login($user, $pwd)
	{
		try 
		{
            $this->logger->info('Trying to authenticate [' . $user . ']from IP: [' . $_SERVER['REMOTE_ADDR'] .']' );
			$this->logger->debug('using password :' . $pwd  );
			//force destroy session vars and environement
			$this->logout();
			
			//restart a new session because it has been deleted above by logout()
			//session_save_path(__DIR__ . '/../../' . $this->config['sessiondir'] );
            session_name($this->cookieSessionName);
			session_start();
			
			setcookie(session_name(), session_id(), time()+ $this->config['sessionlifetime'], '/' );
		
			$user = $this->PDO->quote($user);
			$pwd = $this->PDO->quote($pwd);
			$req = 'SELECT *,indexid as uid FROM ' . $this->dbtableprefix . 'users WHERE username=' . strtolower($user)  . ' AND password='. $pwd;
			$PDOstmt = $this->PDO->query($req);
			$results = $PDOstmt->fetch(PDO::FETCH_ASSOC);
		} 
		catch (Exception $e)
		{
			$this->setErrorMsg(__METHOD__, date("ddmmyyyy"), L::authFailed ) ;
			$this->logger->debug('authentication failed ' .$e->getmessage() );
			
			return false;
		}
		
		try 
		{
			if ($results == false)
			{
				throw new Exception(L::auth_badUserNamePassword);
			}
			
			if ( time() > $results['expire']  )
			{
				throw new Exception(L::auth_accountExpired);
			}
			
			if ( $results['disabled']  )
			{
				throw new Exception(L::auth_userDisabled);
			}
			
			// hide last_php_session to user
			unset($results['last_php_session']);
			// Update the last_php_session in db, now the user is connected.
			// This setting is used by the auth handler to update the user's right access
			// when admin change user's group membership.
			if( $this->getConfigElement('dynamic_rights') )
			{
				$q = 'UPDATE ' . $this->dbtableprefix . 'users SET last_php_session=' . $this->PDO->quote( session_id() ) . ' WHERE indexid=' . $this->PDO->quote( $results['uid'] );
				$this->PDO->query($q);
			}
			
			//setting up $_SESSION details for user
			$_SESSION['isauth'] = true;
			$now = time();
			$_SESSION['sessionstart'] = $now;
			$_SESSION['sessionend'] = $now + $this->config['sessionlifetime'];
			
			foreach($results as $key => $result)
			{
				
                if($key != 'password')
                {
                    $_SESSION[$key] = $result;
                }
                /*else  uncomment to debug purpose only!
                {
                    //hide user password from session var (thoses can be exported into logs)
                    // BEWARE $_SESSION[$key] = $result ;// Uncomment this if you know what youre doing !;
                    $_SESSION[$key] = '<hiddenpassword>';
                }*/
			}
			

			//add `users` default group  to current connected user.
            $groupsMembership = explode ( ',' , $_SESSION['groups']);
            $users = new VarcaveUsers();
            if( !in_array('users' , $groupsMembership) )
            {
                //update $_SESSION['group']
                $_SESSION['groups'] .= ',users';
                
                //update db
                $users->setUserProp( $_SESSION['uid'], 'groups', $_SESSION['groups'] );
            }
            $this->logger->info('User login succeed');
			$this->logger->debug('User $_SESSION info :' . print_r($_SESSION,true));

            //populate  info on login  and force update of $SESSION['favorites_caves'] from database
            $users->getFavoritesCaves($_SESSION['uid']);

			return true;
		}
		catch (Exception $e)
		{
            $this->logger->info('loggin failed : ' . $e->getmessage());
			$this->logger->debug('user details from DB : ' . print_r($results,true));
			
			$errMsg = L::authFailed . ' : ' .  $e->getmessage();
			$this->setErrorMsg(__METHOD__,date('m-d-Y'),$errMsg ) ;
			return false;
		}
		return false;
		
	}
	
	
	public function logout()
	{
        $username = 'anonymous';
        if(isset($_SESSION['uid']) )
        {
            $username = $_SESSION['uid'];
        }
		$this->logger->info('logging out user : [' . $username . '] from IP: [' . $_SERVER['REMOTE_ADDR'] .']' );
		try 
		{
			if (session_status() == PHP_SESSION_NONE)
			{
				throw new Exception(L::nosession);
			}
			//clear session global vars
			session_unset() ;
			
			//clear session from browser
			setcookie(session_name(), '', time()-3600, '/' );
		
			//clear session from disk
			session_destroy();
			return true;
		
		}
		catch (Exception $e)
		{
			$this->setErrorMsg(__METHOD__, date("ddmmyyy"), $e->getmessage() );
			return false;
		}
	}
    
	/*
     * isSessionValid()
     * Check if user as a valid Varcave authenticated session.
     * @param none
	 * @return : true if user is authenticated and is not expired or if phpsession is still valid (in time).
     *           false if user is anonymous or is unauthenticated. Note that config parameter `use_anon_auth`
     *           can affect behaviour
     */
	public function isSessionValid()
	{
		$this->logger->debug('Check if user as valid session');
        
		try 
		{
			
			if (session_status() == PHP_SESSION_NONE ) 
			{
				throw new Exception(L::nosession);
			}
            elseif ( !$this->getconfigelement('use_anon_auth') && !isset($_SESSION['isauth']) )
            {
                // we refuse unauthenticated user
                throw new Exception(L::auth_anonymousConnexionRefused);
                 
            }
            elseif ( $this->getconfigelement('use_anon_auth') && !isset($_SESSION['isauth']) )
            {
                // user is automatically accepted as anonymous
                // user do not have an authenticated session and global config use_anon_ is set
                $this->logger->info('Connected as anonymous user (user valid)');
				$this->logger->debug('Session variables' . print_r($_SESSION,true) );
				return true;
            }
			elseif (time()  > $_SESSION['sessionend'] )
			{
				/*
				 * force remove of session data or it leads to security issue 
				 * session are still there and group membership are not removed
				 */
				$this->logout();
				throw new Exception(L::auth_sessionEnd . ' : '. date("Y-m-d H:i:s",$_SESSION['sessionend']));
			}
			elseif ( time() > $_SESSION['expire'] )
			{
				/*
				 * force remove of session data or it leads to security issue 
				 * session are still there and group membership are not removed
				 */
				$this->logout();
				throw new Exception(L::auth_accountExpired . ' : '. date("Y-m-d H:i:s",$_SESSION['expire']));
			}
			else
			{				
				//session seems to be valid
				$this->logger->info('User is valid');
				$this->logger->debug('Session variables: ' . print_r($_SESSION,true) );
				return true;
			}
		}
		catch (exception $e)
		{
			$this->setErrorMsg(__METHOD__, L::errors_ERROR, $e->getmessage() );
			$this->logger->debug('User validation failed : ' . $e->getmessage() );
            $this->logger->debug('current time:' . time());
            
            $sessionExpire = 0;            
            if( isset($_SESSION['accountexpire']) )
            {
                $sessionExpire = $_SESSION['expire'];
            }
            $sessionEnd = 0;
            if( isset($_SESSION['sessionend']) )
            {
                $sessionEnd = $_SESSION['sessionend'];
            }
				
            $this->logger->debug('user expiration:' . $sessionExpire);
            $this->logger->debug('user session timeout:' . $sessionEnd );
			return false;
		}
		
		return false;
	}
	
	/*
	 * This function try to find if user is member of the specified group(s)
	 * given as function arg.
	 * @param $grp : a list of comma separated group 
	 * @return : true if group match succeed, false if it fails
	 */
	public function isMember($grp)
	{
        $this->logger->info(__METHOD__ . ' : Check user group membership on :[' . $grp . '] for user :[' . $_SESSION['username'] . ']');
		try 
		{
			if (session_status() == PHP_SESSION_NONE)
			{
				throw new exception(L::nosession);
			}
			
            $this->logger->debug('user groups:' . print_r($_SESSION['groups'],true) );
			$array1 = explode(',', $_SESSION['groups']);
			$array2 = explode(',', $grp);
			
			// find $grp in $_SESSION['groups');
			foreach ($array1 as $key1 => $value1)
			{
				
				foreach ($array2 as $key2 => $value2)
				{
					//compare present in $SESSION[group] against the specified groups in arg
					//return true if found (ie user is member of).
					if(strtolower($value1) == strtolower($value2) )
					{
                        $this->logger->info('success: user is member');
						return true;
					}
				}
			}
            $this->logger->debug('failed: user is NOT member');
			return false;
		}
		catch (exception $e)
		{
            $this->logger->debug('Check failed : ' . $e->getmessage() );
			$this->setErrorMsg(__METHOD__,time(),$e->getmessage() );
			return false;
		}
	}

    /*
     * this function retreive the corresponding group of a defined ACL
     * for a page.
     * @param $guid a guid to query onto the acl table
     * @param $outString set output[0] as csv string instead of an array
     * @param $alldata a bool to get a multidimensionnal array of the corresponding acl data (groups, metadata,etc.)
     * @return mixed false on error, array of corresponding data on success
     */
    public function getacl($guidv4, $outString = true, $alldata = false)
    {
        $this->logger->debug(__METHOD__ . ' : Retreive acl data for guidv4:[' . $guidv4 .']');
        if(strlen($guidv4) != 36 )
        {
            $this->logger->debug('Bad guid');
            return false;  
        }
        
        try
        {
            //get info from db
            $q = 'SELECT * FROM `' . $this->dbtableprefix . 'acl` WHERE guidv4 =' . $this->PDO->quote($guidv4);
			$this->logger->debug('Query :' . $q);
			$pdostmt = $this->PDO->query($q);
            $data = $pdostmt->fetchAll(PDO::FETCH_ASSOC);
            
            if( empty($data) )
            {
                throw new exception ('No corresponding acl guid in database');
            }
            
            //add acl description for easy debug
            $shortGuid = str_replace('-' , '' , $data[0]['guidv4']);
            $data[0]['description'] = constant('L::acl_' . $shortGuid);
            
            
            
            //prepare  data set
            if($outString)
            {
                $r = array(
                            $data[0]['related_groups'],
                            $data[0]['description'],
                        );
                
            }
            else
            {   
                //outpout as array
                $r = array( 
                        array_map('trim', explode(',', $data[0]['related_groups']) ),
                    );
            }
            
            //send back all data if requested
            if ($alldata)
            {
                //$r[] = $data[0]['description'];
                $r = array_merge($r, $data[0]);
                
                unset( $r['related_groups'] ); //remove duplicate entry $r[0]
                unset( $r['description'] );
            }
            
            $this->logger->debug('Acl content :' . print_r($r,true) );
            return $r;
            
        }
        catch (exception $e)
        {
            $this->logger->debug($e);
            return false;
        }
    }
	
	/*
	 * updateSessionVar updates sessions variable in a specific session file
	 * @param $uid the corresponding uid  the session file to make sure we do not update another one
	 * @param $item session var to update
	 * @param $value value of item
	 * @return true on success, false if unable to change users settings (usualy because session file
     * do not exists any more on server), throw exception on failure
	 */
	public function updateSessionVar($uid, $item, $value)
	{
		$this->logger->info(__METHOD__ . ' : try to update user session info');
		$this->logger->debug('uid:' . $uid . ', sessionvar:' . $item . ', value:' . $value); //clean this later
		
		$users = new varcaveUsers();
		$userInfos = $users->getUserDetails($uid);
		
		//backup session before importing user's one 
		$admin_sessionid = session_id();
		$admin_SESSION = $_SESSION;
        $this->logger->debug('saved data :' . $admin_sessionid  . '$_SESSION:'. print_r($admin_SESSION,true) );
		session_write_close(); //end admin sess

		//load user session
		session_id( $userInfos['last_php_session'] );
        session_name($this->cookieSessionName);
		session_start();
        
		$this->logger->debug('Imported SESSION info :' . print_r($_SESSION,true) );
		$this->logger->debug('settings ['. $item .'] with value ['. $userInfos[$item] .']' );
		$_SESSION[$item] = $userInfos[$item];
		$this->logger->debug('modified $SESSION data [' . $userInfos['last_php_session'] . '] info :' . print_r($_SESSION,true) );
        session_write_close();
        //close user session
		
		//reload admin session
		session_id($admin_sessionid);
        session_name($this->cookieSessionName);
		session_start();
		$_SESSION = $admin_SESSION;
        $this->logger->debug('reload admin session :' . session_id(). ' $_SESSION:' . print_r($_SESSION,true) );
		return true;
		
	}
	
    /* 
     * getacllist() retreive the list of defined ACL in `acl`table
     * and merge it with the i18n infos located in the language file
     * @param $all get all acl even those with readonly attribute
     * @return array on success false on other case
     */
     function getacllist($all = false)
     {
         $this->logger->info(__METHOD__ . ' : get a list of ACL from database');
            try
            {
                //get acl from db
                if($all)
                {
					$whereClause = 'WHERE 1';
				}
				else
				{
					$whereClause = 'WHERE read_only !=1';
				}
                $q = 'SELECT * FROM `' . $this->dbtableprefix . 'acl` ' . $whereClause .' ORDER BY `related_webpage` ASC';
                $pdostmt = $this->PDO->query($q);
                $acls = $pdostmt->fetchall(PDO::FETCH_ASSOC);
                
                if( empty($acls) )
                {
                    $this->logger->error('No ACL found in database');
                    return false;
                }
                //search corresponding i18n info from lang file
                foreach($acls as $key=>&$acl)
                {
                    //remove dashes from guidv4
                    $shortGuid = str_replace('-' , '' , $acl['guidv4']);
                    //add description of acl
                    $acl['description'] = constant('L::acl_' . $shortGuid);
                    
                }
                $this->logger->debug('list of acl:' . print_r($acls,true) );
                return $acls;
            }
            catch(Exception $e)
            {
                $this->logger->error('Fail fo fetch ACLs from database.');
                return false;
            }
     }
     
     /* this function update the group list defined into an acl
      * @param $id indexid of the ACL
      * @param a csv value of groups
      * @return true on success, false in other case 
      */
     function setaclgrouplist($id, $grpValue)
     {
        $this->logger->info( __METHOD__ . ' : update ACL [' . $id . '] with value [' . $grpValue . ']');

        try
        {
            $q = 'UPDATE `' . $this->dbtableprefix . 'acl` SET related_groups = ' . $this->PDO->quote($grpValue) . ' WHERE indexid ='. $this->PDO->quote($id) ;
            $this->PDO->query($q);
            return true;
        }
        catch (exception $e)
        {
            $this->logger->error('Unable to update database : ' . $e->getmessage() );
            $this->logger->debug('query : ' . $q);
            return false;
        }
        return false;
     }
     
     
    /*
     * This reset some acl data to specific ones to set the website to behave
     * specificaly
     * @param $id : an hardcoded id to load
     * @return : true on success false on failure
     */
    function setAclTemplate($id)
    {
         $this->logger->info( __METHOD__ . ' : reset some ACL data.');
        $queries = array();
        if($id === 0){
            
            /* Mode accepting anonymous users to read cave data */ 
            
            //set system to permit anonymous users to access website and data
            // menu cavité->recherche       370f195f-0d8d-46bc-b3a6-7c9da3e88289 => anonymous,users
            // accès display.php            c47d51c4-62c4-5f40-9047-c466388cc52b => anonymous,users
            // acces ressources.php         8e9d7d52-f061-4021-8109-cc48fbfe0e61 => anonymous,users
            // acces getpdf.php             bda76758-f447-4f6a-90da-2c595a4adfb5 => anonymous,users
            // acces getgpxklm.php          52f41225-92c9-4926-a24f-d62f1e824f8d => anonymous,users
            // affichage boutons edition    91562650-629a-4461-aa38-e9e5c7cbd432 => editors
            $queries[] = 'UPDATE ' . $this->dbtableprefix . 'acl SET related_groups = "users,anonymous"
                  WHERE 
                    `guidv4`= "370f195f-0d8d-46bc-b3a6-7c9da3e88289" 
                     OR `guidv4`= "c47d51c4-62c4-5f40-9047-c466388cc52b"
                     OR `guidv4`= "8e9d7d52-f061-4021-8109-cc48fbfe0e61"  
                     OR `guidv4`= "bda76758-f447-4f6a-90da-2c595a4adfb5" 
                     OR `guidv4`= "52f41225-92c9-4926-a24f-d62f1e824f8d" 
                    ';
            $queries[] = 'UPDATE ' . $this->dbtableprefix . 'acl SET related_groups = "editors"
                  WHERE `guidv4`= "91562650-629a-4461-aa38-e9e5c7cbd432"';
            $queries[] = 'UPDATE ' . $this->dbtableprefix . 'config SET `configItemValue` = 1 WHERE configItem = "use_anon_auth"';
            
        }elseif($id === 1){
            /* Mode accepting only registered to read cave data */ 
            
            //set system to permit anonymous users to access website and data
            // menu cavité->recherche       370f195f-0d8d-46bc-b3a6-7c9da3e88289 => users
            // accès display.php            c47d51c4-62c4-5f40-9047-c466388cc52b => users
            // acces ressources.php         8e9d7d52-f061-4021-8109-cc48fbfe0e61 => users
            // acces getpdf.php             bda76758-f447-4f6a-90da-2c595a4adfb5 => users
            // acces getgpxklm.php          52f41225-92c9-4926-a24f-d62f1e824f8d => users
            $queries[] = 'UPDATE ' . $this->dbtableprefix . 'acl SET related_groups = "users"
                  WHERE 
                    `guidv4`= "370f195f-0d8d-46bc-b3a6-7c9da3e88289" 
                     OR `guidv4`= "c47d51c4-62c4-5f40-9047-c466388cc52b"
                     OR `guidv4`= "8e9d7d52-f061-4021-8109-cc48fbfe0e61"  
                     OR `guidv4`= "bda76758-f447-4f6a-90da-2c595a4adfb5" 
                     OR `guidv4`= "52f41225-92c9-4926-a24f-d62f1e824f8d" 
                    ';
            $queries[] = 'UPDATE ' . $this->dbtableprefix . 'acl SET related_groups = "editors"
                  WHERE `guidv4`= "91562650-629a-4461-aa38-e9e5c7cbd432"';
            $queries[] = 'UPDATE ' . $this->dbtableprefix . 'config SET `configItemValue` = 0 WHERE configItem = "use_anon_auth"';
                    
        }
        else
        {
            $this->logger->error('Bad ALC template id [' . $id . ']');
            return false;
        }
         
        // Load template into DB
                        
        foreach($queries as $key => $value)
        {
            try{
                $this->PDO->beginTransaction();
                $this->PDO->query($value);
                $this->PDO->commit();
            }
            catch(Exception $e)
            {
                $this->logger->error('Failed to load template id [' . $id . '] : ' . $e->getmessage() );
                return false;
            }
        }
        
        return true;
    
    }
}

?>
