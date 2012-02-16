<?php
class license {
	
	private $key, $customer_id, $course_id, $seats, $expire_date, $adding_date, $user_data;
	
	public function __construct($xml) {
		$this->key = $xml->key;
		$this->customer_id = $xml->customer_id;
		$this->course_id = $xml->course_id;
		$this->seats = $xml->seats;
		$this->expire_date = $xml->expire_date;
		$this->adding_date = $xml->adding_date;
		$this->user_data = json_decode($xml->user_data);
	}
	
	public function get_seats() {
		return (integer)$this->seats;
	}
	
	public function is_seat_available() {
		if ($this->user_data != null) {
			$used_seats = count(get_object_vars($this->user_data));
		} else {
			$used_seats = 0;
		}
		if ($used_seats < $this->seats) {
			return true;
		} else {
			return false;
		}
	}
	
	public function get_expire_date() {
		return $this->expire_date;
	}
	
	public function is_license_valid() {
		if ($this->expire_date > time()) {
			return true;
		} else {
			return false;
		}
	}
	
	public function get_user_data() {
		return $this->user_data;
	}
	
	public function register_user($userID) {
		if ($this->is_seat_available() && $this->is_license_valid() && !isset($this->user_data->$userID)) {
			$this->user_data->$userID = time(); 
			licensemanager::get_instance()->store_license($this);
			return true;
		} else {
			return false;
		}
	}
	
	public function is_license_in_use($userID) {
		if ($this->is_user_registerd($userID)) {
			return !$this->can_unregister_user($userID);
		}
		return false;
	}
	
	public function can_unregister_user($userID) {
		if ($this->is_user_registerd($userID)) {
			$user = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $userID);
			$user_last_login = $user->get_attribute("USER_LAST_LOGIN");
			if ($user_last_login < $this->user_data[$userID]) {
				return true;
			}
		}
		return false;
	}
	
	public function unregister_user($userID) {
		if ($this->is_user_registerd($userID) && $this->can_unregister_user($userID)) {
			unset($this->user_data[$userID]);
			return true;
		}
		return false;
	}
	
	public function is_user_registerd($userID) {
		if (isset($this->user_data[$userID])) {
			return true;
		} 
		return false;
	}
	
	public function get_customer_id() {
		return $this->customer_id;
	}
	
	public function get_course_id() {
		return $this->course_id;
	}
	
	public function get_key() {
		return $this->key;
	}
	
	public function get_adding_date() {
		return $this->adding_date;
	}
	
	public function get_xml() {
		$xml = new SimpleXMLElement("<license></license>");
		$xml->seats = $this->seats;
		$xml->expire_date = $this->expire_date;
		$xml->customer_id = $this->customer_id;
		$xml->course_id = $this->course_id;
		$xml->user_data = json_encode($this->user_data);
		return $xml;
	}
}
?>