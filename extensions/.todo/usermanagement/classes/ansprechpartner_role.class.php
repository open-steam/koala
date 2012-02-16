<?php
class ansprechpartner_role extends betreuer_role {
	
	private static $id = "ANSPRECHPARTNER_ROLE";
	private static $name = "Ansprechpartner";
	
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
				
			    $hidden_members = $course_group->get_attribute("COURSE_HIDDEN_STAFF");
		    	if (!is_array($hidden_members)) {
		    		$hidden_members = array();
		    	}

		    	if (in_array($user_id, $hidden_members))
		    		return false;
		    	else
		    		return true;
			}
	}
	
	static function make_role($user_id, $context) {
		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->updateUserStatus($user_id, "Ansprechpartner");
		if (self::is_role($user_id, $context)) return;
		
		parent::make_role($user_id, $context);
		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->updateUserStatus($user_id, "Ansprechpartner");
		if ($context instanceof kurs_context) {
			$course_group_id = $context->get_context_object();
			
			$course_group = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $course_group_id);
			
		    $hidden_members = $course_group->get_attribute("COURSE_HIDDEN_STAFF");
	    	if (!is_array($hidden_members)) {
	    		$hidden_members = array();
	    	}
	    	
	    	unset($hidden_members[array_search($user_id, $hidden_members)]);
	    	$users_to_hide = $hidden_members;
			$course_group->set_attribute("COURSE_HIDDEN_STAFF", $users_to_hide);
		}
	}
	
	function remove_role() {
		$course_group_id = $this->get_context()->get_context_object();
		
		$course_group = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $course_group_id);
		
	    $hidden_members = $course_group->get_attribute("COURSE_HIDDEN_STAFF");
    	if (!is_array($hidden_members)) {
    		$hidden_members = array();
    	}
    	
		if (!in_array($this->get_userID(), $hidden_members)) {
	    	array_push($hidden_members, $this->get_userID());
	    	$users_to_hide = $hidden_members;
			$course_group->set_attribute("COURSE_HIDDEN_STAFF", $users_to_hide);
    }
	}
	
	function allowed_make_role($target_user) {
		
	}
	
	function allowed_remove_role($target_user) {
		
	}
	
}
?>