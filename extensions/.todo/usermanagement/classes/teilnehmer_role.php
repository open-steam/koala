<?php
class teilnehmer_role extends benutzer_role {
	private static $id = "TEILNEHMER_ROLE";
	private static $name = "Teilnehmer";
	
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
				
				$course_group_learner = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), $course_group->get_groupname() . ".learners");
				
			    $user = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $user_id);
			    
			    if ($course_group_learner->is_member($user)) 
			    	return true;
			    else
			    	return false;
			}
	}
	
	static function make_role($user_id, $context) {
		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->updateUserStatus($user_id, "Teilnehmer");
		if (self::is_role($user_id, $context)) return;
		parent::make_role($user_id, $context);
		if ($context instanceof kurs_context) {
			$course_group_id = $context->get_context_object();
			
			$course_group = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $course_group_id);
			
			$course_group_learner = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), $course_group->get_groupname() . ".learners");
			
		    $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->addUserToGroup($user_id, $course_group_learner->get_id());
		    $cache = get_cache_function( $course_group_learner->get_id() );
            $cache->drop( "lms_steam::group_get_members", $course_group_learner->get_id() );
		}
	}
	
	function remove_role() {
		$course_group_id = $this->get_context()->get_context_object();
		
		$course_group = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $course_group_id);
		
		$course_group_learner = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), $course_group->get_groupname() . ".learners");
		
		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeUserFromGroup($this->get_userID(), $course_group_learner->get_id());
		$cache = get_cache_function( $course_group_learner->get_id() );
        $cache->drop( "lms_steam::group_get_members", $course_group_learner->get_id() );
	}
	
	function allowed_make_role($target_user) {
		
	}
	
	function allowed_remove_role($target_user) {
		
	}
}
?>