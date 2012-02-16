<?php
class benutzer_role extends role {

	private static $id = "BENUTZER_ROLE";
	private static $name = "Benutzer";
	
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
		return true;
	}
	
	static function make_role($user_id, $context) {
		if (self::is_role($user_id, $context)) return;
	}
	
	function remove_role() {
		
	}
	
	function allowed_make_role($target_user) {
		
	}
	
	function allowed_remove_role($target_user) {
		
	}
	
}
?>
