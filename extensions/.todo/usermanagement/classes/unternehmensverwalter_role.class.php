<?php
class unternehmensverwalter_role extends benutzer_role {
private static $id = "UNTERNEHMENSVERWALTER_ROLE";
	private static $name = "Unternehmensverwalter";
	
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
		
		if ($context instanceof unternehmens_context) {
				return $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isCustomerAdmin($user_id);
		}
	}
	
	static function make_role($user_id, $context) {
		if (self::is_role($user_id, $context)) return;
		
		parent::make_role($user, $context);
		
		if ($context instanceof unternehmens_context) {
			//TODO:
		}
	}
	
	function remove_role() {
		
	}
	
	function allowed_make_role($target_user) {
		
	}
	
	function allowed_remove_role($target_user) {
		
	}
}
?>