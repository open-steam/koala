<?php

// The base group, that contains all customers as subgroups
define ("BASE_GROUP", "customers");

// The sTeam admin group
define ("STEAM_ADMINS", "Admin");

// sTeam attributes
define ("GENERATED_PASSWORD", "USER_GENERATED_PASSWORD");
define ("IS_LOGFILE", "IS_LOGFILE");

class sTeamServerDataAccess /*implements DataAccess*/ {

	
	public function isInitialized () {
		
		$baseGroup = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), BASE_GROUP);

		if ($baseGroup != null) {
			foreach ($baseGroup->get_subgroups() as $subgroup) {
				if ($subgroup->get_name() == "admins") {
					return true;
				}
			}
		}
		
		return false;
	}
	
	public function initialize () {	
		$baseGroup  = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), BASE_GROUP);
		if ($baseGroup == NULL) {	
			$baseGroup 	= steam_factory::create_group($GLOBALS["STEAM"]->get_id(), BASE_GROUP, NULL);
		}
		$admins 	= steam_factory::create_group($GLOBALS["STEAM"]->get_id(), "admins", $baseGroup);
		// Add root user to admin group
		$rootID		= $currentUserID = $GLOBALS["STEAM"]->get_current_steam_user()->get_id();
		$root		= new steam_user($GLOBALS["STEAM"]->get_id(), $rootID);
		
		$admins->add_member($root);
		$baseGroup->add_member($root);
	}
	
	
	private function setAttribute ($objID, $attribute, $value) {
		$connector = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		$user = new steam_object($connector->get_id(), $objID);
		$result = $user->set_attribute($attribute, $value);
		//$connector->disconnect();
		$connector->buffer_flush();
		return $result;
	}
	
	private function getAttribute ($objID, $attribute) {
		$connector = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		$user = new steam_object($connector->get_id(), $objID);
		$result = $user->get_attribute($attribute);
		//$connector->disconnect();
		return $result;
	}
	
	private function deleteAttribute ($objID, $attribute) {
		$connector = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		$user = new steam_object($connector->get_id(), $objID);
		$result = $user->delete_attribute($attribute);
		//$connector->disconnect();
		return $result;
	}
	
	private function setUserPassword ($userID, $password) {
		$connector = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		$user = new steam_user($connector->get_id(), $userID);
		$user->set_password($password);
		//$connector->disconnect();
	}
	
	public function addUserToGroup ($userID, $groupID) {
		$connector = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		$user = new steam_user($connector->get_id(), $userID);
		$group = new steam_group($connector->get_id(), $groupID);
		$group->add_member($user);
		//$connector->disconnect();
	}
	
	public function removeUserFromGroup ($userID, $groupID) {
		$connector = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		$user = new steam_user($connector->get_id(), $userID);
		$group = new steam_group($connector->get_id(), $groupID);
		$group->remove_member($user);
		//$connector->disconnect();
	}
	
	private function deleteUser ($userID) {
		$connector = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		$user = new steam_user($connector->get_id(), $userID);
		$result = $user->delete();
		//$connector->disconnect();
		return $result;		
	}
	
	
	
	
	public function isAdmin ($userID) {
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), $userID);
		$baseGroup = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), BASE_GROUP);
		
		foreach ($baseGroup->get_subgroups() as $subgroup) {
			
			if ($subgroup->get_name() == "admins") {
				return $subgroup->is_member($user);
			}
			
		}
		
		return false;
	}
	
	public function isCustomerAdmin ($userID) {
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), $userID);
		$baseGroup = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), BASE_GROUP);
		
		foreach ($baseGroup->get_subgroups() as $customer) {
			if ($customer->get_name() != "admins") {
				foreach ($customer->get_subgroups() as $branch) {
					if ($branch->get_name() == "admins" && $branch->is_member($user)) {
						return true;
					}
				}
			}
		}
		
		return false;		
	}
	
	public function isBranchAdmin ($userID, $branchID = "") {
		
		// Check if user is branchAdmin of a specific branch
		if ($branchID != "" && $branchID != "0") {
		
			$branch = new steam_group($GLOBALS["STEAM"]->get_id(), $branchID);
			
			foreach ($branch->get_subgroups() as $subgroup) {
				if ($subgroup->get_name() == "admins") {
					foreach ($subgroup->get_members() as $member) {
						if ($member->get_id() == $userID) {
							return true;
						}
					}
				}
			}
		}
		// Check if user is branchAdmin at all
		else {
			foreach ($this->getAllCustomers() as $customerID => $customerName) {
				foreach ($this->getAllBranches($customerID) as $branchID => $branchName) {
					if ($this->isBranchAdmin($userID, $branchID)) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	public function getMaxUserRights ($userID) {
		if ($this->isAdmin($userID)) {
			return 3;
		}
		else if ($this->isCustomerAdmin($userID)) {
			return 2;
		}
		else if ($this->isBranchAdmin($userID)) {
			return 1;
		}
		else {
			return 0;
		}
	}
	
	public function setBranchAdminRights ($userID, $branchID = "") {
		$this->updateAdminsGroup($userID, ($branchID == "") ? $this->getEmployeeBranchID($userID) : $branchID, true);	
	}
	
	public function removeBranchAdminRights ($userID, $branchID = "") {
		$this->updateAdminsGroup($userID, ($branchID == "") ? $this->getEmployeeBranchID($userID) : $branchID, false);
	}
	
	public function setCustomerAdminRights ($userID, $customerID = "") {
		$this->updateAdminsGroup($userID, ($customerID == "") ? $this->getEmployeeCustomerID($userID) : $customerID, true);	
	}
	
	public function removeCustomerAdminRights ($userID, $customerID = "") {
		$this->updateAdminsGroup($userID, ($customerID == "") ? $this->getEmployeeCustomerID($userID) : $customerID, false);
	}
	
	public function setAdminRights ($userID) {
		$group = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), BASE_GROUP);
		$this->updateAdminsGroup($userID, $group->get_id(), true);	
		
		$admins = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), STEAM_ADMINS);
		$connector 	= new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		$user  		= new steam_user($connector->get_id(), $userID);
		$admins->add_member($user);
		
	}
	
	public function removeAdminRights ($userID) {
		$group = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), BASE_GROUP);
		$this->updateAdminsGroup($userID, $group->get_id(), false);
		
		$admins = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), STEAM_ADMINS);
		$connector 	= new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		$user  		= new steam_user($connector->get_id(), $userID);
		$admins->remove_member($user);
	}	
	
	private function updateAdminsGroup ($userID, $groupID, $isAdmin) {
		
		$connector 	= new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		$group 		= new steam_group($connector->get_id(), $groupID);
		$user  		= new steam_user($connector->get_id(), $userID);
		
		foreach ($group->get_subgroups() as $subgroup) {
			if ($subgroup->get_name() == "admins") {
				($isAdmin) ? $subgroup->add_member($user) : $subgroup->remove_member($user);
			}
		}
		
		//$connector->disconnect();
	}

	
	
	
	
	// __________________________________________________________________________
	//																 		user
	
	public function changePassword ($userID, $password) {
		
		// If current user changes its own password
		if ($userID == $GLOBALS["STEAM"]->get_current_steam_user()->get_id()) {
			$user = new steam_user($GLOBALS["STEAM"]->get_id(), $userID);
			$user->set_password($password);
			$user->set_attribute(GENERATED_PASSWORD, "");
		}
		// If users password is changed by an admin
		else {
			$this->setUserPassword($userID, $password);
			$this->setAttribute($userID, GENERATED_PASSWORD, $password);
		}
	}
	
	public function isGeneratedPassword ($userID) {
		
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), $userID);
		
		if (array_search(GENERATED_PASSWORD, $user->get_attribute_names()) != false) {
			return ($user->get_attribute(GENERATED_PASSWORD) != "") ? true : false;
		}
		else {
			return false;
		}
	}
	
	public function getGeneratedPassword ($userID) {
		
		if ($this->isGeneratedPassword($userID)) {
			$user = new steam_user($GLOBALS["STEAM"]->get_id(), $userID);
			return $user->get_attribute(GENERATED_PASSWORD);
		}
		
		return "";
		
	}
	
	public function getPasswordHTML($userID) {
		if ($this->isGeneratedPassword($userID)) {
			return $this->getGeneratedPassword($userID);
		} else {
			return "*****";
		}
	}
	
	public function getUserList () {
		
		$userList 			= array();
		$steamGroup 		= $GLOBALS["STEAM"]->get_steam_group();
		$steamGroupMembers 	= $steamGroup->get_members();
		
		foreach ($steamGroupMembers as $member) {
			$userList[$member->get_id()] = $member->get_name();
		}
		
		return $userList;
	}
	
	public function getAllAttributes ($objID) {
		$user = new steam_user ($GLOBALS["STEAM"]->get_id(), $objID);
		$attributes = array();
		$steamAttributes = $user->get_all_attributes();
		
		$attributes["id"] 			= $objID;
		$attributes["name"] 		= isset($steamAttributes["OBJ_NAME"]) ? $steamAttributes["OBJ_NAME"] : null;
		$attributes["firstname"] 	= isset($steamAttributes["USER_FIRSTNAME"]) ? $steamAttributes["USER_FIRSTNAME"] : null;
		$attributes["lastname"] 	= isset($steamAttributes["USER_FULLNAME"]) ? $steamAttributes["USER_FULLNAME"] : null;
		
		return $attributes;
	}
	
	public function getUserLogin($userID) {
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), (int)$userID);
		return $user->get_name();
	}
	
	public function getUserFirstName($userID) {
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), (int)$userID);
		return $user->get_attribute("USER_FIRSTNAME");
	}

	public function getUserLastName($userID) {
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), (int)$userID);
		return $user->get_attribute("USER_FULLNAME");
	}	
	
	public function getUserEmail($userID) {
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), (int)$userID);
		return $user->get_attribute("USER_EMAIL");
	}
	
	public function getUserIconId($userID) {
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), (int)$userID);
		return $user->get_attribute("OBJ_ICON")->get_id();
	}
	
	public function getUserStatus($userID) {
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), (int)$userID);
		return $user->get_attribute("OBJ_DESC");
	}
	
	public function setUserFirstName($userID, $firstname) {
		return $this->setAttribute($userID, "USER_FIRSTNAME", $firstname);
	}

	public function setUserLastName($userID, $lastname) {
		return $this->setAttribute($userID, "USER_FULLNAME", $lastname);
	}

	public function setUserEmail($userID, $email) {
		return $this->setAttribute($userID, "USER_EMAIL", $email);
	}
	
	public function updateUserStatus($userID, $status = "") {
		if ($status == "Ansprechpartner") {
			$this->setAttribute($userID, "OBJ_DESC", "Ansprechpartner");
			return;
		}
		$current_status_text = $this->getUserStatus($userID);
		if ($current_status_text == "Unternehmensverwalter" ||
		    $current_status_text == "Ansprechpartner" ||
		    $current_status_text == "Betreuer" ||
		    $current_status_text == "Teilnehmer") {
		    	if ($current_status_text == $status) {
		    		return;
		    	}
				if ($status != "") {
					if ($current_status_text == "Unternehmensverwalter" && $status != "Unternehmensverwalter") {
						$this->setAttribute($userID, "OBJ_PREVIOUS_DESC", $status);
						return;
					}
					$status_text = $status;
				} else {
					if ($this->isCustomerAdmin($userID)) {
						$status_text = "Unternehmensverwalter";
						$this->setAttribute($userID, "OBJ_PREVIOUS_DESC", $current_status_text);
					} else {
						$previous = $this->getAttribute($userID, "OBJ_PREVIOUS_DESC");
						if ($previous === 0 || $previous === "") {
							$status_text = "Teilnehmer";
						} else {
							$status_text = $previous;
							$this->deleteAttribute($userID, "OBJ_PREVIOUS_DESC");
						}
					}
				}
				$this->setAttribute($userID, "OBJ_DESC", $status_text);
				$login = $this->getUserLogin($userID);
				$cache = get_cache_function($login);
		        $cache->drop("lms_steam::user_get_profile", $login);
		}
	}
	
	public function removeUserFromAllGroups ($userID) {
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), $userID);
		
		foreach ($user->get_groups() as $group) {
			$this->removeUserFromGroup($userID, $group->get_id());
		}
	}
	
	public function removeUser ($userID) {
		return $this->deleteUser($userID);
	}
	
	public function lockUser ($userID) {
		return $this->setAttribute($userID, "USER_LOCKED", true);
	}
	
	public function unlockUser ($userID) {
		return $this->setAttribute($userID, "USER_LOCKED", false);
	}
	
	public function isLocked ($userID) {
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), $userID);
		
		try {
			return $user->get_attribute("USER_LOCKED");
		}
		
		catch (Exception $exception) {
			return true;
		}
	}
	
	public function trashUser($userID) {
		$this->setAttribute($userID, "USER_TRASHDATE", time());
		return $this->setAttribute($userID, "USER_TRASHED", true); 
	}
	
	public function restoreUser($userID) {
		$this->setAttribute($userID, "USER_TRASHDATE", null);
		return $this->setAttribute($userID, "USER_TRASHED", false);
		
	}
	
	public function isTrashed ($userID) {
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), $userID);
		
		try {
			return $user->get_attribute("USER_TRASHED");
		}
		
		catch (Exception $exception) {
			return true;
		}
	}
	
	public function getTrashDate($userID) {
		$deleteLimit = 7776000; //90 days
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), $userID);
		
		try {
			if ($user->get_attribute("USER_TRASHDATE") == 0) {
				return "";
			} else {
				return ceil(($deleteLimit - ((integer)time() - (integer)$user->get_attribute("USER_TRASHDATE"))) / 86400);
			}
		}
		
		catch (Exception $exception) {
			return true;
		}
	}
	
	public function login2ID ($login) {

		$user = steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $login);
		
		if (!$user) {
			return "-1";
		}
		
		else {
			return $user->get_id();
		}
	}
	
	public function saveUserCreationLogFile ($fileName, $fileContent) {
		
		$container = new steam_room($GLOBALS["STEAM"]->get_id(), $this->getCurrentUsersWorkrommID());
		
		$doc = steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $fileName, $fileContent, "text/xml", $container, "");
		$doc->set_attribute(IS_LOGFILE, 1);
	}
	
	public function getUserCreationLogFiles () {
		$container = new steam_room($GLOBALS["STEAM"]->get_id(), $this->getCurrentUsersWorkrommID());
		$inventory = $container->get_inventory();
		$logFiles = array();
		
		foreach ($inventory as $object) {
			if ($object->get_type() == CLASS_DOCUMENT && $object->get_attribute(IS_LOGFILE) == 1) {
				$logFiles[] = array("id" => $object->get_id(), "name" => $object->get_name(), "content" => $object->get_content());
			}
		}
		
		return $logFiles;
		
	}
	
	public function saveCustomerCreationLogFile ($customerID, $fileName, $fileContent) {
		
		$container = new steam_room($GLOBALS["STEAM"]->get_id(), $this->getCustomerWorkroom($customerID));
		
		$doc = steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $fileName, $fileContent, "text/xml", $container, "");
		$doc->set_attribute(IS_LOGFILE, 1);
	}
	
	public function getCustomerCreationLogFiles ($customerID) {
		$container = new steam_room($GLOBALS["STEAM"]->get_id(), $this->getCustomerWorkroom($customerID));
		$inventory = $container->get_inventory();
		$logFiles = array();
		
		foreach ($inventory as $object) {
			if ($object->get_type() == CLASS_DOCUMENT && $object->get_attribute(IS_LOGFILE) == 1) {
				$logFiles[] = array("id" => $object->get_id(), "name" => $object->get_name(), "content" => $object->get_content());
			}
		}
		
		return $logFiles;
		
	}
	
	public function deleteUserCreationLogFiles () {
		
		$container = new steam_room($GLOBALS["STEAM"]->get_id(), $this->getCurrentUsersWorkrommID());
		$inventory = $container->get_inventory();

		foreach ($inventory as $object) {
			if ($object->get_type() == CLASS_DOCUMENT && $object->get_attribute(IS_LOGFILE) == 1) {
				$object->delete();
			}
		}		
	}
	
	public function updateUserCreationLogFile ($filename, $newXMLContent) {
		
		$container = new steam_room($GLOBALS["STEAM"]->get_id(), $this->getCurrentUsersWorkrommID());
		$inventory = $container->get_inventory();

		foreach ($inventory as $object) {
			if ($object->get_type() == CLASS_DOCUMENT && $object->get_attribute(IS_LOGFILE) == 1 && $object->get_name() == $filename) {
				$object->set_content($newXMLContent);
			}
		}		
	}

	private function getCurrentUsersWorkrommID () {
		$currentUserID = $GLOBALS["STEAM"]->get_current_steam_user()->get_id();
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), $currentUserID);

		$workroom = $user->get_attribute("USER_WORKROOM");
		
		return $workroom->get_id();
	}
	
	private function getCustomerWorkroom($customerID) {
		$customer_group = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $customerID);
		$workroom = $customer_group->get_attribute("GROUP_WORKROOM");
		return $workroom->get_id();
	}
	
	
	
	// __________________________________________________________________________
	//																   employees
	
	public function createEmployee ($login, $password, $email, $firstname, $lastname, $branchID = "", $customerID = "") {
		
		$connector = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		
		// Create user
		$activationCode = steam_factory::create_user($connector->get_id(), $login, $password, $email, $lastname, $firstname);
		
		// Get new user and activate
		$newUser = steam_factory::get_user($connector->get_id(), $login);
		$newUser->activate($activationCode);

		if ($branchID != "" && $branchID != "0") {
			// Add user to branch group
			$branch = new steam_group($connector->get_id(), $branchID);
			$branch->add_member($newUser);
			// Add user to customer group
			$customer = $branch->get_parent_group();
			$customer->add_member($newUser);
			
			$newUser->set_attribute("BRANCH_ID", $branchID);
		}
		
		else if ($customerID != "" && $customerID != "0") {
			$customer = new steam_group($connector->get_id(), $customerID);
			$customer->add_member($newUser);
		}
		
		// Add user to base group
		$base = steam_factory::get_group($connector->get_id(), BASE_GROUP);
		$base->add_member($newUser);
		
		$newUser->set_attribute(GENERATED_PASSWORD, $password);
		$newUser->set_attribute("USER_LANGUAGE", "german");
		$newUser->set_attribute("OBJ_DESC", "Teilnehmer");
		
		//hack, adding user to course. remove this
		//steam_factory::get_group($GLOBALS["STEAM"]->get_id(), "Courses.Goertz.G-01.learners")->add_member($newUser);
		
		//$connector->disconnect();
		
		return $activationCode;
		
	}
	
	public function getEmployeeBranch ($employeeID) {
		
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), $employeeID);
		$branchID = $user->get_attribute("BRANCH_ID");
		
		if ($branchID != "") {
			$branch = new steam_group($GLOBALS["STEAM"]->get_id(), $branchID);
			return array ("id" => $branch->get_id(), "name" => $branch->get_name());
		}
		
		return array ("id" => "", "name" => "");
	}
	
	public function getEmployeeBranchID ($employeeID) {
		$branchData = $this->getEmployeeBranch($employeeID);
		return $branchData["id"];
	}
	
	public function getEmployeeCustomer ($employeeID) {

		$baseGroup = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), BASE_GROUP);	
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), $employeeID);
		
		foreach ($baseGroup->get_subgroups() as $customer) {
			if ($customer->get_name() != "admins" && $customer->is_member($user)) {
				return array ("id" => $customer->get_id(), "name" => $customer->get_attribute("OBJ_NAME"));
			}
		}
		
		return array ("id" => "", "name" => "");
	}
	
	public function getEmployeeCustomerID ($employeeID) {
		$customerData = $this->getEmployeeCustomer($employeeID);
		return $customerData["id"];
	}
	
	public function getGroupMembers($customerID) {
		$temp = lms_steam::group_get_members($customerID);
		$userlist = array();
		foreach ($temp as $user) {
			$userlist[$user["OBJ_ID"]] = $user["OBJ_NAME"];
		}
		return $userlist;
	}

	public function getAllEmployees ($customerID = "", $branchID = "") {
		
		$employees 	= array();
		
		if ($branchID != "") {
			$group = new steam_group($GLOBALS["STEAM"]->get_id(), $branchID);
		}
		
		else if ($customerID != "") {
			$group = new steam_group($GLOBALS["STEAM"]->get_id(), $customerID);
		}
		
		else {
			$group = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), BASE_GROUP);
		}
			
		if ($group instanceof steam_group) {
			try {		//fixes problem when deleting consumer
			$members 	= $group->get_members();
			} catch (Exception $e) {
				$members = array();
			}
		} else {
			$members = array();
		}
		
		foreach ($members as $member) {
			if ($member->get_type() == CLASS_USER) {
				$employees[$member->get_id()] = $member->get_name();
			}
		}
		
		return $employees;
	}
	
	

	
	
	
	
	// __________________________________________________________________________
	//																   customers
	/*
	 * $id is the short name of a customers
	 * $name is the long readable Name of a customer
	 */
	public function createCustomer ($name, $id) {
		
		// Create group for new customer
		$customers = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), BASE_GROUP);
		$customer = $customers->create_subgroup($id);
		$customer->set_attribute("OBJ_DESC", $name);
		
		// Create group for customer admins
		$admins = $customer->create_subgroup("admins");
		$admins->set_attribute("OBJ_DESC", "group for customer admins");
		
		// Create "semster"
		$courses = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );
		$all_user = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
		$new_semester = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), $id, $courses, FALSE, $name );
		$new_semester_admins = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), "admins", $new_semester, FALSE, "admin group for " . $name );
		$new_semester_admins->set_attribute( "OBJ_TYPE", "semester_admins" );
		$new_semester_admins->add_member( $GLOBALS["STEAM"]->get_current_steam_user() );
		$new_semester->set_insert_access( $new_semester_admins, TRUE );
		$new_semester->set_read_access( $all_user, TRUE );
		$new_semester->set_attributes( array(
			"SEMESTER_START_DATE" => "",
			"SEMESTER_END_DATE"   => ""
		));	 
		
	}
	
	public function getAllCustomers () {
		
		$data	 	= array();
		$baseGroup	= steam_factory::get_group($GLOBALS["STEAM"]->get_id(), BASE_GROUP);
		$customers 	= $baseGroup->get_subgroups();
		
		foreach ($customers as $customer) {
			if ($customer->get_name() != "admins") {
				$data[$customer->get_id()] = $customer->get_name();
			}
		}
		
		return $data;
	}
	
	public function getAllElearningCourses() {
		$data = array();
		$courses = elearning_mediathek::get_elearning_courses();
		
		foreach($courses as $course) {
			$data[$course->get_id()] = $course->get_name();
		}
		return $data;
	}
	
	public function renameCustomer ($customerID, $newCustomerName) {
		$customer = new steam_group($GLOBALS["STEAM"]->get_id(), $customerID);
		$customer->set_name($newCustomerName);
	}
	
	public function deleteCustomer ($customerID) {
		$this->deleteGroup($customerID);
	}	
	
	public function removeAllEmployeesFromCustomer ($branchID) {
		$this->removeAllUsersFromGroup($branchID);
	}
		
	
	// __________________________________________________________________________
	//																	branches
	
	public function createBranch ($name, $customerID) {
		
		// Get customer
		$customerGroup = new steam_group($GLOBALS["STEAM"]->get_id(), $customerID);
		
		// Create subgroup for new branch
		$branchGroup = $customerGroup->create_subgroup($name);

		// Add a subgroup "admins" for branch admins
		$adminsGroup = $branchGroup->create_subgroup("admins");
	}
	
	public function getAllBranches ($customerID = "") {
		
		$data = array();
		
		if ($customerID == "") {
			foreach ($this->getAllCustomers() as $customerID => $customerName) {
				//$data = array_merge($data, $this->getAllBranches($customerID));
				$data = $data + $this->getAllBranches($customerID);
			}
		}		
		else {
			$customer = new steam_group($GLOBALS["STEAM"]->get_id(), $customerID);
			if ($customer->get_name() != "admins") {
				foreach ($customer->get_subgroups() as $branch) {
					if ($branch->get_name() != "admins") {
						$data[$branch->get_id()] = $branch->get_name();
					}	
				}
			}
		}
		return $data;
	}
	
	public function getBranchName ($branchID) {
		$branch = new steam_group($GLOBALS["STEAM"]->get_id(), $branchID);
		return $branch->get_name();
	}
	
	public function setBranchName ($branchID, $branchName) {
		$branch = new steam_group($GLOBALS["STEAM"]->get_id(), $branchID);
		$branch->set_name($branchName);
	}
	
	public function getBranchOwner ($branchID) {
		$branch = new steam_group($GLOBALS["STEAM"]->get_id(), $branchID);
		$customer = $branch->get_parent_group();
		return $customer->get_name();
	}
	
	public function addEmployeeToBranch ($branchID, $employeeID) {
		$branch = new steam_group($GLOBALS["STEAM"]->get_id(), $branchID);
		$employee = new steam_user($GLOBALS["STEAM"]->get_id(), $employeeID);

		// Add user to branch group
		$branch = new steam_group($GLOBALS["STEAM"]->get_id(), $branchID);
		$branch->add_member($employee);
		// Add user to customer group
		$customer = $branch->get_parent_group();
		$customer->add_member($employee);
		// Add user to base group
		$base = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), BASE_GROUP);
		$base->add_member($employee);
		// Set branchID attribute 
		if ($employee->get_attribute("BRANCH_ID") != $branchID) {
			$employee->set_attribute("BRANCH_ID", $branchID);
		}
	}
	
	public function removeEmployeeFromBranch ($branchID, $employeeID) {
		$branch = new steam_group($GLOBALS["STEAM"]->get_id(), $branchID);
		$employee = new steam_user($GLOBALS["STEAM"]->get_id(), $employeeID);

		// Remove user to branch group
		$branch = new steam_group($GLOBALS["STEAM"]->get_id(), $branchID);
		$branch->remove_member($employee);
		// Remove user to customer group
		$customer = $branch->get_parent_group();
		$customer->remove_member($employee);
		// Remove user to base group
		$base = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), BASE_GROUP);
		$base->remove_member($employee);

		if ($employee->get_attribute("BRANCH_ID") == $branchID) {
			$employee->set_attribute("BRANCH_ID", "");
		}
	}
	
	public function removeAllEmployeesFromBranch ($branchID) {
		$this->removeAllUsersFromGroup($branchID);
	}
	
	public function deleteBranch ($branchID) {
		$this->deleteGroup($branchID);
	}
	
	
	
	
	
	
	
	
	// __________________________________________________________________________
	//																	 courses
	
	public function getAllCourseIDs () {
		
		$coursesGroup = new steam_group($GLOBALS["STEAM"]->get_id(), STEAM_COURSES_GROUP);
		$courseIDs = array();
		
		foreach ($coursesGroup->get_subgroups() as $semesterGroupCandidate) {
			foreach ($semesterGroupCandidate->get_subgroups() as $course) {
				if ($course->get_attribute("OBJ_TYPE") == "course") {
					$courseIDs[] = $course->get_id();
				}
			}
		}
		
		
		return $courseIDs;
	}
	
	public function getCourseData ($courseID) {
		$course = new steam_group($GLOBALS["STEAM"]->get_id(), $courseID);
		$data = array();
		
		$data["id"] 		= $course->get_id();
		$data["course_id"] 	= $course->get_name();
		$data["name"] 		= $course->get_attribute("OBJ_DESC");
		$data["shortDesc"] 	= $course->get_attribute("COURSE_SHORT_DSC");
		$data["longDesc"] 	= $course->get_attribute("COURSE_LONG_DSC");
		
		try {
			$data["customerID"] = $course->get_attribute("ACTIVATED_FOR_CUSTOMER");
		}
		
		catch (Exception $exception) {
			$data["customerID"] = "";
		}
		
		return $data;
	}
	
	public function getCourseDataForCustomer($customerID) {
		$result = array();
		foreach ($this->getAllCourseIDs() as $courseID) {
			
			$data = $this->getCourseData($courseID);
			
			if ($data["customerID"] == $customerID && $data["customerID"] != "") {
				$result[] = $data["id"];
			}
		}
		return $result;
	}
	
//	public function activateCourseForCustomer ($courseID, $customerID, $licenses) {
//		$course = new steam_group($GLOBALS["STEAM"]->get_id(), $courseID);
//		
//		// Set ID for according customer
//		$this->setAttribute($courseID, "ACTIVATED_FOR_CUSTOMER", $customerID);
//		
//		// Set subgroup maxsize (licenses)
//		foreach ($course->get_subgroups() as $subgroup) {
//			if ($subgroup->get_name() == "learners") {
//				$this->setAttribute($subgroup->get_id(), "GROUP_MAXSIZE", $licenses);
//			}
//		}
//	}
//	
//	public function deactivateCourseForCustomer ($courseID, $customerID) {
//		$this->setAttribute($courseID, "ACTIVATED_FOR_CUSTOMER", "");
//	}
	
//	public function getCountCourseLicenses ($courseID) {
//		$course = new steam_group($GLOBALS["STEAM"]->get_id(), $courseID);
//		foreach ($course->get_subgroups() as $subgroup) {
//			if ($subgroup->get_name() == "learners") {
//				return $subgroup->get_attribute("GROUP_MAXSIZE");
//			}
//		}
//	}
	
//	public function setCourseLicenses ($courseID, $numberOfLicenses) {
//		$course = new steam_group($GLOBALS["STEAM"]->get_id(), $courseID);
//		foreach ($course->get_subgroups() as $subgroup) {
//			if ($subgroup->get_name() == "learners") {
//				return $this->setAttribute($subgroup->get_id(), "GROUP_MAXSIZE", $numberOfLicenses);
//			}
//		}	
//	}
	
	public function getCourseParticipants ($courseID) {
		$course = new steam_group($GLOBALS["STEAM"]->get_id(), $courseID);
		$participants = array();
		foreach ($course->get_subgroups() as $subgroup) {
			if ($subgroup->get_name() == "learners") {
				foreach ($subgroup->get_members() as $participant) {
					$participants[$participant->get_id()] = $participant->get_name();
				}
			}
		}
		return $participants;		
	}
	
	public function getCoursesForUser($userID) {
		return lms_steam::user_get_booked_courses($userID);
	}
	
	public function addParticipant ($userID, $courseID) {
		$course = new steam_group($GLOBALS["STEAM"]->get_id(), $courseID);
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), $userID);
		foreach ($course->get_subgroups() as $subgroup) {
			if ($subgroup->get_name() == "learners") {
				$subgroup->add_member($user);
			}
		}	
	}
	
	public function removeParticipant ($userID, $courseID) {
		$course = new steam_group($GLOBALS["STEAM"]->get_id(), $courseID);
		$user = new steam_user($GLOBALS["STEAM"]->get_id(), $userID);
		foreach ($course->get_subgroups() as $subgroup) {
			if ($subgroup->get_name() == "learners") {
				$subgroup->remove_member($user);
			}
		}	
	}











	// Removes a member of a group and all its subgroups
	private function removeAllUsersFromGroup ($groupID) {
		
		$group = new steam_group($GLOBALS["STEAM"]->get_id(), $groupID);
		
		if ($group != null) {
			
			foreach ($group->get_subgroups() as $subgroup) {
				$this->removeAllUsersFromGroup($subgroup->get_id());
			}
			
			foreach ($group->get_members() as $member) {
				if ($member->get_type() == CLASS_USER) {
					$group->remove_member($member);
				}
			}
		} 
	}

	private function deleteGroup ($groupID) {
		
		$group = new steam_group($GLOBALS["STEAM"]->get_id(), $groupID);
		
		if ($group != null) {
			
			foreach ($group->get_subgroups() as $subgroup) {
				$this->deleteGroup($subgroup->get_id());
			}
			
			$group->delete();
		} 
	}

	public function getRootUser() {
		$root = steam_factory::get_user($GLOBALS["STEAM"]->get_id(), "root");
		return $root->get_id();
	}

	public function getObjectDesc($id) {
		$object = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		return $object->get_attribute("OBJ_DESC");
	}
	
	public function getObjectName($id) {
		$object = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		return $object->get_name();
	}
	
	public function getObjCreationDate($id) {
		$object = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		return $object->get_attribute("OBJ_CREATION_TIME");
	}
	
	public function createCourse($id, $courseID, $customerID) {
		$user = lms_steam::get_current_user();
		$all_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
		$current_semester = steam_factory::get_group($GLOBALS[ "STEAM" ]->get_id(), "Courses." . $this->getObjectName($customerID));
		$elearning_course = elearning_mediathek::get_instance()->get_elearning_course_by_id($courseID);
		$name = $elearning_course->get_name();
		
		$new_course = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), $id, $current_semester, FALSE, $name );
		
		$icon_id = steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), "/packages/elearning_stahl_verkauf/icon_verkauf.jpg")->get_id();
        $new_course->set_attributes( array(
                                           "OBJ_TYPE"                     => "course",
                                           "COURSE_PARTICIPANT_MNGMNT" => 0,  // TODO: $obj_type seems not to be set...?
                                           "COURSE_SEMESTER"              => $this->getObjectName($customerID),
                                           "COURSE_TUTORS"                => "",
                                           "COURSE_SHORT_DSC"             => $elearning_course->get_description(),
                                           "COURSE_LONG_DSC"              => "[img]".PATH_SERVER."/cached/get_document.php?id=$icon_id&height=100[/img]
																			In dieser Schulung möchten wir Ihnen die notwendigen Kenntnisse vermitteln, wie Sie ein Verkaufsgespräch gut führen – beginnend mit dem Blickkontakt, wenn eine Kundin oder ein Kunde Ihr Geschäft betritt, bis hin zur Verabschiedung, mit der Sie den Kunden hoffentlich mit dem Gefühl verabschieden, dass er bald wiederkommen wird.
																			
																			Oberhalb dieses Textes befindet sich eine Registerkarte »Lektionen«, über die Sie sich zunächst die Schulungsinhalte aneignen können. Auf diese Inhalte haben Sie jederzeit Zugriff; Sie können die Inhalte so oft durcharbeiten, wie Sie wünschen. Und Sie können dies von jedem an das Internet angeschlossenen Computer aus, also beispielsweise auch von zu Hause.
																			
																			Um zu zeigen, dass Sie die Inhalte beherrschen, müssen Sie an einem Test teilnehmen. Dieser Test wird zu einem bestimmten Termin für Sie freigeschaltet. Sie erhalten dann zu gegebener Zeit eine Mitteilung, dass Sie zu dem Test zugelassen sind. Neben der Registerkarte, mit der Sie zu den Schulungsinhalten gelangen, finden Sie dann eine zusätzliche Registerkarte, mit der Sie den Test ablegen können.
																			
																			Sollten Sie auf Probleme stoßen – seien es technische, inhaltliche oder Fragen zur Benutzung dieses Systems – finden Sie Hilfe am oberen Rand dieser Seite. Außerdem können Sie jederzeit Kontakt zu Ihrem Ansprechpartner aufnehmen, der Ihnen gerne weiterhelfen wird.",
                                           "COURSE_HISLSF_ID" => "",
										   "ACTIVATED_FOR_CUSTOMER" => $customerID
                                            ));
        $learners = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), "learners", $new_course, FALSE, "Participants of course '" . $name. "'" );
        $learners->set_attribute( "OBJ_TYPE", "course_learners" );
        $staff    = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), "staff", $new_course, FALSE, "Tutors of course '" . $name . "'");
        $staff->set_attribute( "OBJ_TYPE", "course_staff" );
        $admins    = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), "admins", $new_course, FALSE, "Admins of course '" . $name . "'"); 
        $admins->set_attribute( "OBJ_TYPE", "course_admins" );
        //$staff->add_member( $user );
          

                          // RIGHTS MANAGEMENT =======================================
                          $course_calendar = $new_course->get_calendar();
                          $learners_workroom = $learners->get_workroom();
                          $course_workroom = $new_course->get_workroom();

                          $staff->set_sanction_all( $staff );
                          $staff->sanction_meta( SANCTION_ALL, $staff);
                          $learners->set_sanction_all( $staff );
                          $learners->sanction_meta( SANCTION_ALL, $staff );
                          $new_course->set_sanction_all( $staff );
                          $new_course->sanction_meta( SANCTION_ALL, $staff );

                          $admins->set_sanction_all( $admins );
                          $admins->sanction_meta( SANCTION_ALL, $admins);
                          $staff->set_sanction_all( $admins );
                          $staff->sanction_meta( SANCTION_ALL, $admins);
                          $learners->set_sanction_all( $admins );
                          $learners->sanction_meta( SANCTION_ALL, $admins );
                          $new_course->set_sanction_all( $admins );
                          $new_course->sanction_meta( SANCTION_ALL, $admins );

                          $course_calendar->set_acquire( FALSE );
                          $course_calendar->set_sanction_all( $staff );
                          $course_calendar->sanction_meta(SANCTION_ALL, $staff);
                          $course_calendar->set_sanction_all( $admins );
                          $course_calendar->sanction_meta(SANCTION_ALL, $admins);
                          $course_calendar->set_read_access( $learners, TRUE );
                          $course_calendar->set_write_access( $new_course, FALSE );
                          $course_calendar->set_insert_access( $new_course, FALSE );
                          $course_calendar->set_insert_access( $all_users, FALSE );
                          // Course workroom
                          $course_workroom->set_sanction($new_course, SANCTION_READ | SANCTION_EXECUTE | SANCTION_ANNOTATE);
                          $course_workroom->set_sanction_all( $staff );
                          $course_workroom->set_sanction_all( $admins );
                          $course_workroom->sanction_meta(SANCTION_ALL, $staff);
                          $course_workroom->sanction_meta(SANCTION_ALL, $admins);
                          // Learners workroom
                          $learners_workroom->set_read_access( $all_users, TRUE );
                          $learners_workroom->set_sanction($learners, SANCTION_READ | SANCTION_EXECUTE | SANCTION_ANNOTATE);
                          $learners_workroom->set_sanction_all( $staff );
                          $learners_workroom->set_sanction_all( $admins );
                          $learners_workroom->sanction_meta(SANCTION_ALL, $staff);
                          $learners_workroom->sanction_meta(SANCTION_ALL, $admins);

                          $koala_course = new koala_group_course($new_course);
                          $koala_course->set_access(1, $learners, $staff, $admins, KOALA_GROUP_ACCESS );

                          $new_course->set_attributes( array(
                                           	"COURSE_UNITS_ENABLED"			=> "TRUE",
                            				"UNITS_DOCPOOL_ENABLED" 		=> "TRUE",
											"UNITS_ELEARNING_ENABLED"		=> "TRUE",
											"UNITS_MEDIATHING_ENABLED"      => "FALSE",
											"UNITS_ORGANIZATION_ENABLED"    => "FALSE",
											"UNITS_VIDEOSTREAMING_ENABLED"  => "FALSE"
                                            ));

						// create unit elearning
						$env = $koala_course->get_workroom();
						$new_unit_elearning_course = steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $elearning_course->get_name(), $env, $elearning_course->get_description());
		
						$new_unit_elearning_course->set_attributes(array(
			    								"UNIT_TYPE" => "units_elearning",
			    								"OBJ_TYPE" => "elearning_unit_koala",
												"UNIT_DISPLAY_TYPE" => gettext("units_elearning"),
												"ELEARNING_UNIT_ID" => $courseID
						));		
		return true;
	}

}
?>