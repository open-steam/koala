<?php
class licensemanager {
	
	private static $instance;
	private $iv;
	private $license_xml;
	
	private function __construct() {
		$this->load();
		$this->iv = mcrypt_create_iv(mcrypt_get_block_size (MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_DEV_RANDOM);
	}
	
	public static function get_instance() {
		if (self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function get_licenses($context) {
		$elearning_id = "";
		$customer_id = "";
		foreach ($context as $c) {
			if ($c instanceof elearning_context) {
				$elearning_id = $c->get_context_object();
			} else if ($c instanceof unternehmens_context) {
				$customer_id = $c->get_context_object();
			}
		}
		$licenses = array();
		foreach ($this->license_xml->license as $xml) {
			$license = new license($xml);
			if ($license->get_course_id() == $elearning_id && $license->get_customer_id() == $customer_id) {
				$licenses[] = $license;
			}
		}
		return $licenses;
	}
	
	public function store_license($license) {
		$count = 0;
		foreach ($this->license_xml->license as $xml) {
			if ($xml->key == $license->get_key()){ 
				$this->license_xml->license[$count]->user_data = json_encode($license->get_user_data()); 
			}
			$count++;
		}
		$this->save();
	}
	
	public function get_registered_license_seats($context) {
		$licenses = $this->get_licenses($context);
		$seats = 0;
		foreach ($licenses as $license) {
			$seats += $license->get_seats();
		}
		return $seats;
	}
	
	public function get_used_license_seats($context) {
		$licenses = $this->get_licenses($context);
		$used_seats = 0;
		foreach ($licenses as $license) {
			if ($license->get_user_data() != null) {
				$used_seats += count(get_object_vars($license->get_user_data()));
			}
		}
		return $used_seats;
	}
	
	public function get_available_license_seats($context) {
		return $this->get_registered_license_seats($context) - $this->get_used_license_seats($context);
	}
	
	public function is_license_in_use($user_id, $context) {
		$licenses = $this->get_licenses($context);
		foreach ($licenses as $license) {
			return $license->is_license_in_use($user_id);
		}
	}
	
	public function register_user($user_id, $context) {
		$licenses = $this->get_licenses($context);
		foreach ($licenses as $license) {
			$result = $license->register_user($user_id);
			return $result;
		}
	}
	
	public function is_user_registered($user_id, $context) {
		$licenses = $this->get_licenses($context);
		foreach ($licenses as $license) {
			return $license->is_user_registered($user_id);
		}
	}
	
	public function can_unregister_user($user_id, $context) {
		$licenses = $this->get_licenses($context);
		foreach ($licenses as $license) {
			return $license->can_unregister_user($user_id);
		}
	}
	
	public function unregister_user($user_id, $context) {
		$licenses = $this->get_licenses($context);
		foreach ($licenses as $license) {
			return $license->unregister_user($user_id);
		}
	}
	
	public function add_license($encrypted) {
		$key = $this->get_encrypt_key();
		$decrypted = $this->decrypt($this->hexToStr($encrypted), $key);
		$parts = explode(".", $decrypted);
		if (count($parts) != 4) {
			return false;
		}
		
		foreach ($this->license_xml->license as $license) {
			if  ($license->key == $encrypted) {
				return false;
			}
		}
		
		$xml = $this->license_xml->addChild("license");
		$xml->addChild("key", $encrypted);
		$xml->addChild("customer_id", $parts[0]);
		$xml->addChild("course_id", $parts[1]);
		$xml->addChild("seats", $parts[2]);
		$xml->addChild("expire_date", $parts[3]);
		$xml->addChild("adding_date", time());
		$xml->addChild("user_data","");
		$this->save();
		return true;
	}
	
	public function get_encrypt_key() {
		if ($GLOBALS["STEAM"]->get_login_user_name() != "root") {
			$sc = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		} else {
			$sc = $GLOBALS["STEAM"];
		}
		
		$license_container = steam_factory::get_object_by_name($sc->get_id(), "/home/root/licenses/");
		if ($license_container instanceof steam_container) {
			$key = $license_container->get_attribute("LICENSEMANAGER_KEY");
			if (!($key === 0 || $key == "")) {
				return $key;
			}
		}
		return "masterpassword";
	}
	
	public function set_encrypt_key($new_key) {
		if ($GLOBALS["STEAM"]->get_login_user_name() != "root") {
			$sc = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		} else {
			$sc = $GLOBALS["STEAM"];
		}
		
		$license_container = steam_factory::get_object_by_name($sc->get_id(), "/home/root/licenses/");
		if (!($license_container instanceof steam_container)) {
			$root_home = steam_factory::get_object_by_name($sc->get_id(), "/home/root/");
			$license_container = steam_factory::create_container($sc->get_id(), "licenses", $root_home, "licenses for elearning plattform a stored here");
			$license_container->set_attribute("OBJ_HIDDEN", "TRUE");
			$license_container->set_attribute("OBJ_TYPE", "usermanagement_licensemanager_data");
		}
		$license_container->set_attribute("LICENSEMANAGER_KEY", $new_key);
	}
	
	function date2timestamp ($a = '') {
    	if (empty($a)) return;
    	$a = explode ('.', $a);
    	return mktime (0,0,0, $a[1], $a[0], $a[2]);
	}
	
	public function generate_license($customer_id, $course_id, $seats, $expire_date) {
		$customer_id = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getObjectName($customer_id);
		$stuff = $customer_id . "." . $course_id . "." . $seats . "." . $this->date2timestamp($expire_date);
		$key = $this->get_encrypt_key();
		
		$encrypted = $this->strToHex($this->encrypt(trim($stuff), $key));
		return $encrypted;
	}
	
	private  function load() {
		if ($GLOBALS["STEAM"]->get_login_user_name() != "root") {
			$sc = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		} else {
			$sc = $GLOBALS["STEAM"];
		}
		
		$license_file = steam_factory::get_object_by_name($sc->get_id(), "/home/root/licenses/licenses.xml");
		if ($license_file instanceof steam_document) {
			$content = $license_file->get_content();
			$this->license_xml = simplexml_load_string($content);
		} else {
			$this->license_xml = new SimpleXMLElement("<licenses></licenses>");
		}
	}
	
	private function save() {
		if ($GLOBALS["STEAM"]->get_login_user_name() != "root") {
			$sc = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		} else {
			$sc = $GLOBALS["STEAM"];
		}
		
		$license_container = steam_factory::get_object_by_name($sc->get_id(), "/home/root/licenses/");
		if (!($license_container instanceof steam_container)) {
			$root_home = steam_factory::get_object_by_name($sc->get_id(), "/home/root/");
			$license_container = steam_factory::create_container($sc->get_id(), "licenses", $root_home, "licenses for elearning plattform a stored here");
			$license_container->set_attribute("OBJ_HIDDEN", "TRUE");
			$license_container->set_attribute("OBJ_TYPE", "usermanagement_licensemanager_data");
		}
		
		$license_file = steam_factory::get_object_by_name($sc->get_id(), "/home/root/licenses/licenses.xml");
		if (!($license_file instanceof steam_document)) {
			$license_file = steam_factory::create_document($sc->get_id(), "licenses.xml", $this->license_xml->asXML(), "text/xml");
			$license_file->move($license_container);
		} else {
			$license_file->set_content($this->license_xml->asXML());
		}
	}
	
	// Encrypting
	function encrypt($string, $key) {
	    $enc = "";
	    $enc = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_ECB, $this->iv);
		return base64_encode($enc);
	}
	
	// Decrypting 
	function decrypt($string, $key) {
	    $dec = "";
	    $string = trim(base64_decode($string));
	    $dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_ECB, $this->iv);
	    $dec = str_replace(chr(0), "", $dec);
	  	return $dec;
	}
	
	function strToHex($string)
	{
	    $hex='';
	    for ($i=0; $i < strlen($string); $i++)
	    {
	        $hex .= dechex(ord($string[$i]));
	    }
	    return $hex;
	}
	
	function hexToStr($hex)
	{
	    $string='';
	    for ($i=0; $i < strlen($hex)-1; $i+=2)
	    {
	        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
	    }
	    return $string;
	}
}