<?php
abstract class role {
	
	private $userID, $context;
	
	function __construct($userID, $context) {
		//if(self::is_role($userID, $context)) {
			$this->userID = $userID;
			$this->context = $context;
		//} else {
		//	throw new Exception("Wrong role!");
		//}
	}
	
	abstract static function role_id();
	
	abstract static function role_name();
	
	abstract static function is_role($user, $context);
	
	abstract static function make_role($user, $context);
	
//	static function remove_role($user, $context) {
//		$role = new self($user, $context);
//		return $role->remove_role();
//	}
	
	abstract function remove_role();
	
//	static function allowed_make_role($caller_user, $target_user, $context) {
//		$role = new self($caller_user, $context);
//		return $role->allowed_make_role($caller_user);
//	}
	
	abstract function allowed_make_role($target_user);
	
//	static function allowed_remove_role($caller_user, $target_user, $context) {
//		$role = new self($target_user, $context);
//		return $role->allowed_remove_role($caller_user);
//	}
	
	abstract function allowed_remove_role($caller_user);
	
	abstract static function get_role($user_id, $context);
	
//	public static function get_role($user, $context) {
//		if (self::is_role($user, $context)){
//			return new self($user, $context);
//		}
//		return null;
//	}
	
	public static function get_roles($user, $contexts){
		$roles = array();
		
		$roles[] = benutzer_role::get_role($user, $context);
		
		$roles[] = teilnehmer_role::get_role($user, $context);
		$roles[] = betreuer_role::get_role($user, $context);
		$roles[] = ansprechpartner_role::get_role($user, $context);
		$roles[] = stahl_role::get_role($user, $context);
		
		$roles[] = unternehmensverwalter_role::get_role($user, $context);
		
		$roles[] = systemverwalter_role::get_role($user, $context);
		$roles[] = coactum_role::get_role($user, $context);
		
		var_dump($roles);
		return $roles;
	}
	
	public function get_context() {
		return $this->context;
	}
	
	public function get_userID() {
		return $this->userID;
	}
}
?>