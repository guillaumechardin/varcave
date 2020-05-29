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
					/*'expire',
					'created',
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
            
            //add default group membership
            $userSettings['groups'] = 'users';
			$Def_datatablesMaxItems = 10;
            
            
			$qNewUser = 'INSERT INTO ' . $this->dbtableprefix . '
							users (username, groups, password, expire, created,
									lastUpdate, firstname, lastname, theme,
									geo_api, last_php_session, datatablesMaxItems, 
                                    disabled, emailaddr, streetNum,
									address1,address2,postCode, town,country,licenceNumber,
									phoneNum,cavingGroup,notes,uiLanguage) 
									
									VALUES (' . 
									$this->PDO->quote( strtolower($userSettings['username']) )  . ','.
									$this->PDO->quote($userSettings['groups']) . ','.
									$this->PDO->quote($userSettings['password']) . ','.
									-1 . ','. //expire
									time() . ','. //creation date
									time() . ',' . //last update
									$this->PDO->quote($userSettings['firstname']) . ','.
									$this->PDO->quote( strtoupper( $userSettings['lastname'] )) . ','.
									'\'default\','. //theme
									$this->PDO->quote($this->config['default_geo_api']) . ',' .
									$this->PDO->quote($userSettings['last_php_session']) . ',' .
									$Def_datatablesMaxItems . ','.
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
			
			$q = 'INSERT INTO ' . $this->dbtableprefix . '
							groups (groupName, description) 
									
									VALUES (' . 
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
				
				default:
					$this->logger->debug('this pref do not request $_SESSION update');
					break;
					
			}
			return true;
			
		}
		catch (exception $e)
		{
			$this->logger->info('Change theme failed :' . $e->getmessage() );
			$this->logger->debug('Update request: ' . $updateUserTheme  );
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
}
