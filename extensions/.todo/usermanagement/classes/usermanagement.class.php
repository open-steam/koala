<?php
class usermanangement {
	
	private static $instance;
	
	private function __construct() {
		
	}
	
	public static function get_instance() {
		if (self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function get_user_status_html($user_name) {
		$html = null;
		$user = steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $user_name);
		if (!($user->get_attribute("LLMS_NETWORKING_PROFILE") instanceof steam_object)) {
				$html = "<i>noch nie angemeldet</i>";
		} 
		
		$sTeamServerDataAccess = new sTeamServerDataAccess();
		if ($sTeamServerDataAccess->isLocked($user->get_id())) {
			if ($html == null) {
				$html = "<i>Benutzer gesperrt</i>";
			} else {
				$html .= " und <i>Benutzer gesperrt</i>";
			}
		}
		
		if ($sTeamServerDataAccess->isTrashed($user->get_id())) {
			if ($html == null) {
				$html = "<i>Benutzer gelöscht</i>";
			} else {
				$html .= " und <i>Benutzer gelöscht</i>";
			}
		}
		
		return $html;
	}
}
?>