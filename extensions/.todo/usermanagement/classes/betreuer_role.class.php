<?php
class betreuer_role extends benutzer_role {

	private static $id = "BETREUER_ROLE";
	private static $name = "Betreuer";
	
	static function role_id() {
		return $this->id;
	}
	
	static function role_name() {
		return $this->name;
	}
	
	public static function get_role($userID, $context) {
		if (self::is_role($userID, $context)){
			return new self($userID, $context);
		}
		return null;
	}
	
	static function is_role($user_id, $context) {
		if (!parent::is_role($user_id, $context)) return false;
		
		if ($context instanceof kurs_context) {
				$course_group_id = $context->get_context_object();
				
				$course_group = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $course_group_id);
				
				$course_group_staff = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), $course_group->get_groupname() . ".staff");
				
			    $user = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $user_id);
			    
			    if ($course_group_staff->is_member($user)) 
			    	return true;
			    else
			    	return false;
			}
	}
	
	static function make_role($user_id, $context) {
		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->updateUserStatus($user_id, "Betreuer");
		if (self::is_role($user_id, $context)) return;
		
		parent::make_role($user_id, $context);
		
		if ($context instanceof kurs_context) {
			$course_group_id = $context->get_context_object();
			
			$course_group = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $course_group_id);
			
			$course_group_staff = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), $course_group->get_groupname() . ".staff");
		    
		    $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->addUserToGroup($user_id, $course_group_staff->get_id());
		    $cache = get_cache_function( $course_group_staff->get_id() );
            $cache->drop( "lms_steam::group_get_members", $course_group_staff->get_id() );
		    
		    $course_group_id = $context->get_context_object();
		
						
		    $hidden_members = $course_group->get_attribute("COURSE_HIDDEN_STAFF");
	    	if (!is_array($hidden_members)) {
	    		$hidden_members = array();
	    	}
	    	
	    	if (!in_array($user_id, $hidden_members)) {
		    	array_push($hidden_members, $user_id);
		    	$users_to_hide = $hidden_members;
		    	
				$course_group->set_attribute("COURSE_HIDDEN_STAFF", $users_to_hide);
	    	}
		}
	}
	
	function remove_role() {
		$course_group_id = $this->get_context()->get_context_object();
		
		$course_group = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $course_group_id);
		
		$course_group_staff = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), $course_group->get_groupname() . ".staff");
		
	    $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeUserFromGroup($this->get_userID(), $course_group_staff->get_id());
	    $cache = get_cache_function( $course_group_staff->get_id() );
        $cache->drop( "lms_steam::group_get_members", $course_group_staff->get_id() );
	}
	
	function allowed_make_role($target_user) {
		
	}
	
	function allowed_remove_role($target_user) {
		
	}
}
?>