<?php
require_once (__DIR__ . '/varcave.class.php');
require_once (__DIR__ . '/varcaveAuth.class.php');

class VarcaveUsers extends Varcave
{
	/*
	 * this method add a new user in database.
	 * @param array $userSettings
	 *
	 * @return mixed the indexid of last created user on success, throw error on  error
	 */
    public function adduser($userSettings){
		$this->logger->info(__METHOD__ . ' : Adding new user');
		$this->logger->debug('settings : ' . print_r($userSettings,true) );
		try
		{
			//arg should be an array
			if(!is_array($userSettings) )
			{
				throw new exception(L::error_badArgs );	
			}
			
			
			//define a list of available fields
			$fieldList = array(
					'username',
					'groups',
					'password',
					'expire',
					/*'created',
					'lastUpdate',*/
					'firstname',
					'lastname',
					'theme',
					'geo_api',
					'last_php_session',
					'disabled',
					'emailaddr',
					'streetNum',
					'address1',
					'address2',
					'postCode',
					'town',
					'country',
					'licenceNumber',
					'phoneNum',
					'cavingGroup',
					'notes',
					'uiLanguage',
                    'pref_coord_system',
			);
			
			//populate missing fields if needed to void UPDATE errors
			foreach($fieldList as $field)
			{
				if( !isset( $userSettings[$field] ))
				{
					$userSettings[$field] = '';
				}
			}
			
			//setting a right type value for disabled
			if(empty($userSettings['disabled']) )
			{
				$userSettings['disabled'] = 0;	
			}
            
            if(empty($userSettings['expire']) )
			{
				$userSettings['expire'] = strtotime('+1 year');	//expire after one  year by default
			}
            
            //add default group membership
            $userSettings['groups'] = 'users';
			$Def_datatablesMaxItems = 10;
            
            
			$qNewUser = 'INSERT INTO ' . $this->dbtableprefix . 'users(' .
                            'username, groups, password, expire, created,' .
                            'lastUpdate, firstname, lastname, theme,'.
                            'geo_api, last_php_session, datatablesMaxItems,'.
                            'pref_coord_system, disabled, emailaddr, streetNum,'.
                            'address1,address2,postCode, town,country,licenceNumber,'.
                            'phoneNum,cavingGroup,notes,uiLanguage) '. 
									
                        'VALUES (' . 
                            $this->PDO->quote( strtolower($userSettings['username']) )  . ','.
                            $this->PDO->quote($userSettings['groups']) . ','.
                            $this->PDO->quote($userSettings['password']) . ','.
                            $this->PDO->quote($userSettings['expire']) . ','. 
                            time() . ','. //creation date
                            time() . ',' . //last update
                            $this->PDO->quote($userSettings['firstname']) . ','.
                            $this->PDO->quote( strtoupper( $userSettings['lastname'] )) . ','.
                            '\'default\','. //theme
                            $this->PDO->quote($this->config['default_geo_api']) . ',' .
                            $this->PDO->quote($userSettings['last_php_session']) . ',' .
                            $Def_datatablesMaxItems . ','.
                            $this->PDO->quote($userSettings['pref_coord_system']) . ','.
                            $this->PDO->quote($userSettings['disabled']) . ','.
                            $this->PDO->quote($userSettings['emailaddr']) . ','.
                            $this->PDO->quote($userSettings['streetNum']) . ','.
                            $this->PDO->quote($userSettings['address1']) . ','.
                            $this->PDO->quote($userSettings['address2']) . ','.
                            $this->PDO->quote($userSettings['postCode']) . ','.
                            $this->PDO->quote($userSettings['town']) . ','.
                            $this->PDO->quote($userSettings['country']) . ','.
                            $this->PDO->quote($userSettings['licenceNumber']) . ','.
                            $this->PDO->quote($userSettings['phoneNum']) . ','.
                            $this->PDO->quote($userSettings['cavingGroup']) . ','.
                            $this->PDO->quote($userSettings['notes']) . ','.
                            $this->PDO->quote($userSettings['uiLanguage']) .
                    ')';
			$this->PDO->beginTransaction();
			$this->PDO->query($qNewUser);
			$userIndexid = $this->PDO->lastinsertid();
			$this->PDO->commit();
			
			$this->logger->info('User creation successfull');
			$this->logger->debug('created indexid : ' .$userIndexid);
			
			return $userIndexid;
		}
		catch (exception $e)
		{
			$this->logger->error('Unable to add user : ' . $e->getmessage());
			$this->logger->debug('insert request : ' . $qNewUser);
			throw New Exception(L::varcaveUser_adduserfail. $e->getmessage() );
		}
		
	}
	
	/*
	 * this method add a new user in database.
	 * @param array $userSettings
	 *
	 * @return mixed the indexid of last created user on success, throw error on  error
	 */
    public function addGroup($groupSettings){
		$this->logger->info(__METHOD__ . ' : Adding new group');
		$this->logger->debug('settings : ' . print_r($groupSettings,true) );
		try
		{
			//arg should be an array
			if(!is_array($groupSettings) )
			{
				throw new exception(L::error_badArgs );	
			}
			
			
			//define a list of available fields
			$fieldList = array(
					'groupName',
					'description',
			);
			
			//populate missing fields if needed
			foreach($fieldList as $field)
			{
				if( !isset( $groupSettings[$field] ))
				{
					$groupSettings[$field] = '';
				}
			}
			
			$q = 'INSERT INTO ' . $this->dbtableprefix . 'groups' . 
                        '(groupName, description) '.
                'VALUES (' . 
                            $this->PDO->quote($groupSettings['groupName']) . ','.
                            $this->PDO->quote($groupSettings['description']) .
                ')';
			$this->PDO->beginTransaction();
			$this->PDO->query($q);
			$grpIndexid = $this->PDO->lastinsertid();
			$this->PDO->commit();
			
			$this->logger->info('group creation successfull');
			$this->logger->debug('created indexid : ' .$grpIndexid);
			
			return $grpIndexid;
		}
		catch (exception $e)
		{
			$this->logger->error('Unable to add user : ' . $e->getmessage());
			$this->logger->debug('insert request : ' . $q);
			throw New Exception(L::varcaveUser_addgroupfail . $e->getmessage() );
		}
		
	}
	
	/*
	 * this method delete an existing user in database.
	 * @param int $id user id as primary key 
	 *
	 * @return bool true on success, throw exception on  error
	 */
	public function deluser($id){
		$this->logger->info(__METHOD__ . ': Deleting user  : ' . $id);
		try
		{
			$intID = (int)$id;
			$qDeleteUSer = 'DELETE FROM ' . $this->dbtableprefix . 'users WHERE indexid =' . $this->PDO->quote($intID);
			$this->PDO->beginTransaction();
			$this->PDO->query($qDeleteUSer);
			$this->PDO->commit();
			$this->logger->info('User deleted');  
			return true;
		}
		catch (exception $e)
		{
			$this->logger->error('Unable delete user : ' . $e->getmessage());
			$this->logger->debug('Delete request : ' . $qDeleteUSer);
			return false;
		}
	}
	
	/*
	 * deletes an existing group in database.
	 * @param int $gid : id as primary key 
	 *
	 * @return bool true on success, throw exception on  error
	 */
	public function delGroup($gid){
		$this->logger->info(__METHOD__ . ': Deleting user  : ' . $gid);
		try
		{
			$intID = (int)$gid;
			$qDeleteGrp = 'DELETE FROM ' . $this->dbtableprefix . 'groups WHERE indexid =' . $this->PDO->quote($intID);
			$this->PDO->beginTransaction();
			$this->PDO->query($qDeleteGrp);
			$this->PDO->commit();
			$this->logger->info('User deleted');  
			return true;
		}
		catch (exception $e)
		{
			$this->logger->error('Unable delete user : ' . $e->getmessage());
			$this->logger->debug('Delete request : ' . $qDeleteGrp);
			return false;
		}
	}
	
	
	/*
	 * this method update password for an existing user in database.
	 * @param string $sha256 an sha256 string representing user password
	  * @param int $uid user ID (primary key)
	 *
	 * @return bool true on success, false on  error
	 */
	public function changeUserPwd($sha256,$uid){
		$this->logger->info('Trying to update password for uid: ' . $uid);
		
		try
		{
			if (strlen($sha256) != 64)
			{
				throw new Exception ('Password is not a sha256 string'); 
			}

			$updateUserPwd = 'UPDATE ' . $this->dbtableprefix . 'users SET password=' . $this->PDO->quote($sha256) . ' WHERE indexid=' . $this->PDO->quote(intval($uid) );
			$this->PDO->beginTransaction();
			$this->PDO->query($updateUserPwd);
			$this->PDO->commit();
			$this->logger->info('Update successfull');
			return true;
			
		}
		catch (exception $e)
		{
			$this->logger->info('Change password failed :' . $e->getmessage() );
			$this->logger->debug('Update request: ' . $updateUserPwd  );
			$this->setErrorMsg(__METHOD__, time(), L::myaccount_pwdNotUpdated);
			return false;
		}
	}
	
	/*
	 * this method delete an existing user in database.
	 * @param string $themeName a string contening the new css requested by user. Theme name is the folder name containing css files inf css/custom/<ThemeName>
	 * @param int $id user ID (primary key) 
	 *
	 * @return bool  true on success,false on  error
	 */
	public function changeUserPref($prefName, $prefValue, $id){
		$this->logger->info('Trying to update ' . $prefName . '  for user: ' . $id);
		try
		{
			$qUpdateUser = 'UPDATE ' . $this->dbtableprefix . 'users SET '. $prefName . '=' . $this->PDO->quote($prefValue) . ' WHERE indexid=' . $this->PDO->quote(intval($id) );
			$this->PDO->beginTransaction();
			$this->PDO->query($qUpdateUser);
			$this->PDO->commit();
			$this->logger->info('Update '. $prefName . ' successfully');
			
            //force $_SESSION update for some settings
			switch ($prefName)
			{
				case 'theme':
					$_SESSION['userCss'] = $prefValue;
					break;
				
				case 'geo_api':
					$_SESSION['geo_api'] = $prefValue;
					break;
				case 'firstname':
                case 'lastname':
                case 'emailaddr':
                    $_SESSION[$prefName] = $prefValue;
                    break;
				default:
					$this->logger->debug('this pref do not request $_SESSION update');
					break;
					
			}
			return true;
			
		}
		catch (exception $e)
		{
			$this->logger->info('Change failed :' . $e->getmessage() );
			$this->logger->debug('Update request: ' . $qUpdateUser  );
			return false;
		}
	}
	
	/*
	 * this method get current configured theme in database.
	 * @param int $id user ID (primary key) 
	 *
	 * @return mixed  a string containing theme name on success,false on  error
	 */
	public function getUserTheme($id){
		$this->logger->info('Trying to get theme  for user: ' . $id);
		try
		{
			$getUserTheme = 'SELECT theme FROM  ' . $this->dbtableprefix . 'users WHERE indexid=' . $this->PDO->quote(intval($id) );
			$pdoStmt = $this->PDO->query($getUserTheme);
			$currentTheme = $pdoStmt->fetch();
			$this->logger->info('get theme successfully : ['. $currentTheme[0] . ']');
			return $currentTheme[0];
			
		}
		catch (exception $e)
		{
			$this->logger->info('Get current user theme failed :' . $e->getmessage() );
			$this->logger->debug('get theme request: ' . $getUserTheme  );
			$this->setErrorMsg(__METHOD__, time(), L::myaccount_themeNotUpdated);
			return false;
		}
		
	}
	
	/*
	 * this function recover a list of available users
	 * with minimal informations like firstname,lastname,id,
	 * @param : $assocArray if true return a non associative array (needed sometimes for json conversion)
	 * @return : array on success, throw exeption on error
	 */
	function getUsersList($assocArray = true){
		try
		{
			$this->logger->info(__METHOD__. ' : Starting to fetch a list of user');
			$q = 'SELECT indexid,username,firstName,lastName FROM ' . $this->getTablePrefix() . 'users WHERE 1';
			$usersPDOstmt = $this->PDO->query($q);
			if($assocArray == true)
			{
				$results = $usersPDOstmt->fetchall(PDO::FETCH_ASSOC);
			}
			else
			{
				$results = $usersPDOstmt->fetchall(PDO::FETCH_NUM);
			}
			
			$this->logger->debug('Found [' . count($results) .'] users');
			return $results;
		}
		catch(Exception $e)
		{
			$this->logger->error('Unable to fetch users list'. $e->getmessage() );
			throw new Exception(L::varcaveUser_failuserlist . ' : ' .$e->getmessage);
		}
	}
	
	
	/*
	 * this function recover a list of available groups
	 * with minimal informations like name,id,description
	 * @param : $assocArray if true return a non associative array (needed sometimes for json conversion)
	 * @return : array on success, throw exeption on error
	 */
	function getGroupsList($assocArray = true){
		try
		{
			$this->logger->info(__METHOD__. ' : Starting to fetch a list of groups');
			$q = 'SELECT indexid,groupName,description FROM ' . $this->getTablePrefix() . 'groups WHERE 1';
			$usersPDOstmt = $this->PDO->query($q);
			if($assocArray == true)
			{
				$results = $usersPDOstmt->fetchall(PDO::FETCH_ASSOC);
			}
			else
			{
				$results = $usersPDOstmt->fetchall(PDO::FETCH_NUM);
			}
			
			$this->logger->debug('Found [' . count($results) .'] groups');
			return $results;
		}
		catch(Exception $e)
		{
			$this->logger->error('Unable to fetch groups list'. $e->getmessage() );
			throw new Exception(L::varcaveUser_failgrouplist . ' : ' .$e->getmessage);
		}
		
	}
	
	
	/*
	 * this function recover all details for a specified user
	 * @param : $uid : user indexid into db
	 * @return : array on success, false on error
	 */
	function getUserDetails($uid){
		$this->logger->info(__METHOD__ . ' : Trying to fetch user details from DB');
		$q = 'SELECT * FROM ' . $this->getTablePrefix() . 'users WHERE indexid = ' . $this->PDO->quote($uid);
		$userPdoStmt = $this->PDO->query($q);
		$results = $userPdoStmt->fetchall(PDO::FETCH_ASSOC);

        //change some specific infos for privacy
		unset($results[0]['password']);
        $results[0]['uid'] = $results[0]['indexid'];
		unset( $results[0]['indexid'] );
        $this->logger->debug('Results from db:'. print_r($results, true) );

        //dataset empty
		if( empty($results) )
		{
			$this->logger->error('No user found for uid['. $uid .']');
			return false;
		}
		//removing first array level
		$results = $results[0];
		
		
		
		$this->logger->debug('Find user:' . print_r($results, true) );
		
		return $results;
	}
	
	/*
	 * this function recover all details for a specified group
	 * @param : $gid : group indexid into db
	 * @return : array on success, false on error
	 */
	function getGroupDetails($gid){
		$this->logger->info(__METHOD__ . ' : Trying to fetch group details from DB');
		$q = 'SELECT indexid as gid,groupName,description FROM ' . $this->getTablePrefix() . 'groups WHERE indexid = ' . $this->PDO->quote($gid);
		$userPdoStmt = $this->PDO->query($q);
		$results = $userPdoStmt->fetchall(PDO::FETCH_ASSOC);
		//no dataset
        $this->logger->debug('REsults from db:'. print_r($results, true) );
		if( empty($results) )
		{
			return false;
		}
		//removing first array level
		$results = $results[0];
		
		return $results;
	}
	
    /*
     * Update user properties
     * @param $uid the user id
     * @param $item the user property to update
     * @param $value value to be updated
     * @return true on success, throw exception on failure
     */
    function setUserProp($uid, $item, $value){
        $this->logger->info(__METHOD__ . ' : updating user detail for ['. $uid .']');
		try
		{
			$q = 'UPDATE ' . $this->getTablePrefix() . 'users SET `' . $item . '` = ' . $this->PDO->quote( $value ) . ' WHERE `indexid` = ' . $this->PDO->quote( $uid );
			$this->logger->debug('Query: ' .$q);
			
			$this->PDO->beginTransaction();
			$this->PDO->query($q);
			$this->PDO->commit();
			
			//force update of session var to prevent security issue
			switch($item)
			{
				case 'groups':
				case 'expire':
					$auth = new varcaveAuth();
					
					if( $this->getConfigElement('dynamic_rights') )
					{
                        $this->logger->debug('dynamic_rights set, updating sessionvar');
						$auth->updateSessionVar($uid, $item, $value);
					}
					
				break ;
				
			}
			
			return true;
		}	
		catch(Exception $e)
		{
			$this->logger->error('Fail to change user data [' .$item . '] =>['. $value.']');
			$this->logger->debug('error:' . $e->getmessage());
		}
    }
	
	/*
     * Update group properties
     * @param $gid the group id
     * @param $item the group property to update
     * @param $value value to be updated
     * @return true on success, throw exception on failure
     */
    function setGroupProp($gid, $item, $value){
        $this->logger->info(__METHOD__ . ' : updating user detail for ['. $gid .']');
		try
		{
			$q = 'UPDATE ' . $this->getTablePrefix() . 'groups SET `' . $item . '` = ' . $this->PDO->quote( $value ) . ' WHERE `indexid` = ' . $this->PDO->quote( $gid );
			$this->logger->debug('Query: ' . $q);
			
			$this->PDO->query($q);
			return true;
		}	
		catch(Exception $e)
		{
			$this->logger->error('Fail to change group data [' . $item . '] =>['. $value.']');
			$this->logger->debug('error:' . $e->getmessage());
		}
    }
    
    /*
     * function updPwdResetLink() tries to update database temporarary reset password link id.
     * @param userid
     * @return a new unique linkid to be used by user
     */
     function updPwdResetLink($uid){
        $this->logger->info(__METHOD__ . ' : updating user reset link ['. $uid .']');
        $linkid = GUIDv4();
        $linkExpire = time() + 14400 ; //link expire every 4hours
        
        try{
            //clear database all outdated links
            $q = 'DELETE FROM ' . $this->getTablePrefix() . 'password_reset WHERE `reset_pwd_max_age` < ' . time() ;
            $stmt = $this->PDO->query($q);
            $this->logger->debug('  deleted : ' . $stmt->rowcount() . ' lines');
            
            //check if a link already exists
            $q = 'SELECT * FROM ' . $this->getTablePrefix() . 'password_reset WHERE users_indexid = ' . $this->PDO->quote($uid);
            $stmt = $this->PDO->query($q);
            if($stmt->rowCount() > 0){
                //user have a valid link.
                $q = 'UPDATE ' . $this->getTablePrefix() . 'password_reset ' . 
                     'SET reset_pwd_max_age=' . $linkExpire . ', ' . 
                     '    reset_pwd_id = "' . $linkid . '" ' . 
                     'WHERE users_indexid = ' . $uid ;
                $this->logger->debug('  updated user  linkid with data : [' . substr($linkid, 0, -31) . ']' );
                $this->PDO->query($q);
            }
            else
            {
                //no existing link, adding new one
               $q = 'INSERT INTO ' . $this->getTablePrefix() . 'password_reset (`indexid`, `users_indexid`, `reset_pwd_id`, `reset_pwd_max_age`)' . 
                    ' VALUES ('.
                    ' NULL, ' . $this->PDO->quote($uid) . ', "' . $linkid . '", ' . $linkExpire .
                ')';
                $this->logger->debug('  adding new user linkid : [' . substr($linkid, 0, -31) . ']' );
                $this->PDO->query($q); 
            }
            return array( 'linkid' => $linkid, 'expiration' => $linkExpire);
        }
        catch (exception $e)
        {
            $this->logger->error('Fail to update database : ' . $e->getmessage() );
            return false;
        }
        
     }
     
    /*
     * getUserById() this function try to lookup user uid for a known username
     * @param string username  a username to lookup
     * @return corespondig userid on success or false on error or if lookup failed
     */
    function getUidByUsername($username){
        $this->logger->debug(__METHOD__ . ' : resolving uid for : ' . $username );
        try
        {
            $q = 'SELECT indexid as uid,username FROM ' . $this->getTablePrefix() . 'users WHERE username = ' . $this->PDO->quote($username);
            $pdoStmt = $this->PDO->query($q);
            if($pdoStmt->rowcount() > 0)
            {
                $this->logger->debug('  user found');
                return $pdoStmt->fetch(PDO::FETCH_ASSOC);
            }
            else
            {
                $this->logger->debug('  user NOT found');
                return false;
            }
        }
        catch(Exception $e)
        {
            $this->logger->error('  Fail to resolve uid : ' . $e->getMessage() );
            return false;
        }
    }
    
    /*
     * isResetLinkValid check provided reset link if it is valid (existant, time validity)
     * @param $linkid link to check
     * @return true on success, false in other case
     */
    function isResetLinkValid($linkid){
        $this->logger->debug(__METHOD__ . ' : check reset link validity');
        if(strlen($linkid) != 36)
        {
            $this->logger->error(  '__Bad link length');
            return false;
        }
        try
        {
            $q = 'SELECT indexid, users_indexid, reset_pwd_id FROM ' . $this->getTablePrefix() . 'password_reset ' .
                 'WHERE reset_pwd_id = ' . $this->PDO->quote($linkid) . ' AND reset_pwd_max_age > UNIX_TIMESTAMP()';
            $stm = $this->PDO->query($q);
            
            if($stm->rowcount() == 0 )
            {
                //no valid link present
                $this->logger->debug('  no valid link in database');
                return false;
            }
            else
            {
                $this->logger->debug('  found a link : ' . print_r($stm->fetch(PDO::FETCH_ASSOC), true) );
                return true;
            }
        }
        catch (Exception $e)
        {
            $this->logger->error('  fail to lookup linkid');
            return false;
        }
    }
    
    /*
     * resetPwdFromLink
     * @param $resetPwdId a special string known only by user
     * @param $passwHash a sha256 hash representing user password
     * @return true on success false 
     */
     function resetPwdFromLink($resetPwdId, $sha256){
        $this->logger->info(__METHOD__ . ' : Start reset password process');
        
        try{
            $q = 'SELECT indexid, users_indexid as uid FROM ' . $this->getTablePrefix() . 'password_reset ' .
                 'WHERE reset_pwd_id = ' . $this->PDO->quote($resetPwdId) . ' LIMIT 1';
            $stm = $this->PDO->query($q);
            if($stm->rowcount() > 0)
            {
                $user = $stm->fetch(PDO::FETCH_ASSOC);
                if ( ! $this->getUserDetails($user['uid']) || ! $this->changeUserPwd($sha256, $user['uid']) ){
                    throw new Exception(' failed');
                }
                //delete link from db
                $q = 'DELETE FROM ' . $this->getTablePrefix() . 'password_reset ' .
                     'WHERE reset_pwd_id = ' . $this->PDO->quote($resetPwdId);
                $stm = $this->PDO->query($q);
                return true;
            }
            return false;
            
        }
        catch (Exception $e){
            $this->logger->error('  fail to `on demand` reset user password');
            $this->logger->debug('  ' . $e->getMessage() );
            return false;
        }
         
     }

	 /*
     * this add/remove specified cave guid to a list of favorites in database
     * and update session var
     * @param $guid a guid to toggle on/off the list
	 * @param userid : target user id used to check if cave as to be bookmarked
	 * @param updateSession  set to false if you do not force $_session[favoritescave] updated
     * @return true or array on sucess false if it fails
     */
    public function favoritesCaveToggle($guid, $userid, $updateSession = true)
    {
        $this->logger->debug(__METHOD__ . ' : update database favorites for userid:' . $userid . ' GUID: ' . substr($guid, 0, 7));
        
        try
        {
            if( strlen($guid) != 36 )
            {
                throw new exception('  ERROR : bad guid');
            }
            //check if user already have guid saved, if so we remove if from db else add
            if( in_array($guid, $_SESSION['favorites_caves']) )
            {
                //delete entry
				$this->logger->debug('  cave will be toggled off');
                $q = 'DELETE FROM '  . $this->dbtableprefix . 'users_favorites WHERE cave_guid=' . $this->PDO->quote($guid) . ' AND userid=' . $this->PDO->quote($userid); 
            }
            else
            {
                //add entry
				$this->logger->debug('  cave will be toggled on');
                $q = 'INSERT INTO ' . $this->dbtableprefix . 'users_favorites (`indexid`, `addDate`, `cave_guid`, `userid`) ' .  
                '  VALUES (NULL, ' . time() . ',' .  $this->PDO->quote($guid) . ',' . $this->PDO->quote($userid) . ')';
            }

            $res = $this->PDO->query($q);

            if($updateSession)
            {
                return $this->getFavoritesCaves($userid);
            }

            return true;
        }
        catch(excption $e)
        {
            $this->logger->error('  Fail to update database:' . $e->getmessage()) ;
            $this->logger->debug(  'full query:' . $q);
            return false;

        }
    }
 
    /*
     * fetch a list of saved caves in database
     * and populate $_SESSION[favorites_caves] accordingly
     * @param $userid = id of target user
     * @return array on success false on failure. Note that array can be empty.
     */
    public function getFavoritesCaves($userid)
    {
        $this->logger->debug(__METHOD__ . ': fetch user saved caves');
        try
        {
            $q = 'SELECT * FROM ' .  $this->dbtableprefix . 'users_favorites WHERE `userid`=' .  $this->PDO->quote($userid);
            $pdostmt = $this->PDO->query($q);
            $data = $pdostmt->fetchall(PDO::FETCH_ASSOC);
            $return = array();
            foreach($data as $key => $info)
            {
                $return[] = $info['cave_guid'];
            }
            $_SESSION['favorites_caves'] = $return;
            $this->logger->debug('  Computed user favorites caves : ' . print_r($_SESSION['favorites_caves'],true) );
            return $return;
        }
        catch (exception $e)
        {
            $this->logger->error('  Fail to fetch database:' . $e->getmessage()) ;
            $this->logger->debug('  full query:' . $q);
            return false;
        }
    }

	/*
     * Check if cave is save in current session
     * @param $userid = id of target user
     * @return true on success, or false
     */
    public function isCaveFavorite($guid)
	{
		$this->logger->debug(__METHOD__ . ': check if cave ' . substr($guid, 0, 7) . 'is bookmarked');

		//update sessionvar
		try
		{
			if( strlen($guid) != 36 )
            {
                throw new exception('  ERROR : bad guid : ' . $guid);
            }

			$this->getFavoritesCaves($_SESSION['uid']);
			if(in_array($guid, $_SESSION['favorites_caves']))
			{
				$this->logger->debug('  cave IS favorite');
				return true;
			}
			$this->logger->debug('  cave IS NOT favorite');
			return false;
		}
		catch(Exception $e)
		{
			$this->logger->error('  check failed' . $e->getmessage() );
			return false;
		}
		

	}
}
