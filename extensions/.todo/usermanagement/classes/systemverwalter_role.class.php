<?php
class systemverwalter_role extends benutzer_role {
	
	private static $id = "SYSTEMVERWALTER_ROLE";
	private static $name = "Systemverwalter";
	
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
		
		$admin_group = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), "Admin");
		
		$user = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $user_id);
		
		if ($admin_group->is_member($user))
			return true;
		else
			return false;
	}
	
	static function make_role($user_id, $context) {
		if (self::is_role($user_id, $context)) return;
		
		$admin_group = steam_factory::get_group($GLOBALS["STEAM"]->get_id(), "Admin");
		
		$user = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $user_id);
		
		$admin_group->add_member($user);
	}
	
	function remove_role() {
		
	}
	
	function allowed_make_role($target_user) {
		
	}
	
	function allowed_remove_role($target_user) {
		
	}
	
}
?>