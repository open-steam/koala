<?php
/*
 * attrbiute names
 */
define("UNIT_ELEARNING_EXAM_FINAL_EXAM_ENABLED", "UNIT_ELEARNING_EXAM_FINAL_EXAM_ENABLED");
define("UNIT_ELEARNING_EXAM_FINAL_EXAM_FINISHED", "UNIT_ELEARNING_EXAM_FINAL_EXAM_FINISHED");
define("UNIT_ELEARNING_EXAM_FINAL_EXAM_PASSED", "UNIT_ELEARNING_EXAM_FINAL_EXAM_PASSED");
define("UNIT_ELEARNING_EXAM_FINAL_EXAM_SUM_POINTS", "UNIT_ELEARNING_EXAM_FINAL_EXAM_SUM_POINTS");
define("UNIT_ELEARNING_EXAM_FINAL_EXAM_SUM_SCORE", "UNIT_ELEARNING_EXAM_FINAL_EXAM_SUM_SCORE");
define("UNIT_ELEARNING_EXAM_FINAL_EXAM_ANSWERS", "UNIT_ELEARNING_EXAM_FINAL_EXAM_ANSWERS");
	
class elearning_user {
	
	/*
	 * Config
	 */
	private $unit_elearning_preferences_name = "unit_elearning.preferences";
	private $path_to_unit_elearning_preferences;
	
	/*
	 * unique data
	 */
	private $user_name;
	private $elearning_course_id;
	private $course_group_name;
	
	/*
	 * steam objects
	 */
	private $steam_user;
	private $course_group;
	private $workroom;
	private $unit_elearning_preferences;
	private $elearning_course_data;
	
	
	private $container_try = array();
	private $try_data_xml = array();
	private $cert_pdf = array();
	private $preview_img = array();
	private $current_try = 1;
	private $max_tries = 3;
	
	
	private static $instances = array();
	
	private function __construct($user_name, $course_group_id)
	{
		$this->user_name = $user_name;
		$this->course_group = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $course_group_id);
		$this->course_group_name = $this->course_group->get_groupname();
		
		$this->elearning_course_id = elearning_mediathek::get_elearning_unit_id($course_group_id);
		
		
		$this->steam_user = steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $this->user_name);
		//try {
			$this->check_elearning_data();
		//} catch (Exception $e) {
//			error_log("Problem in check_elearning_data()\n" . var_export(debug_backtrace(),true));
//			header('Location: ' . $_SERVER["REQUEST_URI"]);
//			die;
//		}
		$this->current_try = $this->get_current_try();
	}
	
	public static function get_instance($user_name, $course_group_id) {
		$course_group = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $course_group_id);
		$course_group_name = $course_group->get_groupname();
		$elearning_course_id = elearning_mediathek::get_elearning_unit_id($course_group_id);
		
		$id =  $user_name . "." . $elearning_course_id . "@" . $course_group_name;
		if (!isset(self::$instances[$id])) {
			$instance = new self($user_name, $course_group_id);
			self::$instances[$id] = $instance;
		}
		return self::$instances[$id];
	}
	
	public function get_id() {
		return $this->user_name . "." . $this->elearning_course_id . "@" . $this->course_group_name;
	}
	
	private function check_elearning_data() {
		$this->workroom = $this->steam_user->get_workroom(); //TODO: Caching
		$workroom_path = $this->workroom->get_path() . "/"; //TODO: Caching
		$this->path_to_unit_elearning_preferences = $workroom_path . $this->unit_elearning_preferences_name . "/";
		
		$this->unit_elearning_preferences = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), $this->path_to_unit_elearning_preferences); //TODO: Caching + Cache invalidate
		if ($this->unit_elearning_preferences === 0) {
			$this->createUnitElearningPreferences();
			$this->createElearningCourseData();
			//create steam container for 1st try
			$this->createTry(1);
		} else {
			// loading data without validating!! should not be neccesary here!
		//	try {
				$this->elearning_course_data = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), $this->path_to_unit_elearning_preferences . $this->elearning_course_id . "@" . $this->course_group_name);
				if ($this->elearning_course_data === 0) {
					$this->elearning_course_data = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), $this->path_to_unit_elearning_preferences . $this->elearning_course_id . "@" . $this->course_group_name);
				}
				if ($this->elearning_course_data === 0) {
					throw new Exception("broke datastructure");
				}
		//	} catch (Exception $e) {
			//	$this->elearning_course_data = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), $this->path_to_unit_elearning_preferences . $this->elearning_course_id . "@" . $this->course_group_name);
		//	}
			if (!is_object($this->elearning_course_data)) {
				//throw new Exception("broke datastructure");
				$this->createElearningCourseData();
				//create steam container for 1st try
				$this->createTry(1);
				return;
			}
			
			for ($i = 1; $i <= $this->get_current_try(); $i++) {
				$this->container_try[$i] = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), $this->path_to_unit_elearning_preferences . $this->elearning_course_id . "@" . $this->course_group_name . "/" . $i);
				if (!is_object($this->container_try[$i])) {
					$this->container_try[$i] = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), $this->path_to_unit_elearning_preferences . $this->elearning_course_id . "@" . $this->course_group_name . "/" . $i);
				}
				if (!is_object($this->container_try[$i])) {
					throw new Exception("broke datastructure");
				}
				
				$this->try_data_xml[$i] = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), $this->path_to_unit_elearning_preferences . $this->elearning_course_id . "@" . $this->course_group_name . "/" . $i . "/attributes.xml");
				if (!is_object($this->try_data_xml[$i])) {
					$this->try_data_xml[$i] = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), $this->path_to_unit_elearning_preferences . $this->elearning_course_id . "@" . $this->course_group_name . "/" . $i . "/attributes.xml");
				}
				if (!is_object($this->try_data_xml[$i])) {
					throw new Exception("broke datastructure");
				}
			}
		}
	}
	
	private function createUnitElearningPreferences(){
		//create steam container for unit elearning preferences
		
		if ($GLOBALS["STEAM"]->get_login_user_name() != "root") {
			// MUST BE DONE AS ROOT USER
			$root_steam = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
			$this->unit_elearning_preferences = steam_factory::create_container($root_steam->get_id(), $this->unit_elearning_preferences_name, $this->workroom);
			$koala_course = new koala_group_course($this->course_group);
			//$this->unit_elearning_preferences->set_sanction_all($this->steam_user);
			$this->unit_elearning_preferences->set_sanction_all($koala_course->get_group_staff());
			//$this->unit_elearning_preferences->set_sanction_all($koala_course->get_group_admins());
			$root_steam->buffer_flush();
			$root_steam->disconnect();
		} else {
			$this->unit_elearning_preferences = steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $this->unit_elearning_preferences_name, $this->workroom);
			$koala_course = new koala_group_course($this->course_group);
			//$this->unit_elearning_preferences->set_sanction_all($this->steam_user);
			$this->unit_elearning_preferences->set_sanction_all($koala_course->get_group_staff());
			//$this->unit_elearning_preferences->set_sanction_all($koala_course->get_group_admins());
			$GLOBALS["STEAM"]->buffer_flush();
		}
		//
		//create attributes.xml
		$this->unit_elearning_preferences = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), $this->path_to_unit_elearning_preferences);
		if ($this->unit_elearning_preferences === 0) {
			$this->unit_elearning_preferences = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), $this->path_to_unit_elearning_preferences);
		}
		if ($this->unit_elearning_preferences === 0) {
			throw new Exception("broke datastructure");
		}
		$attributes_xml = new SimpleXMLElement("<attributes></attributes>");
		$attributes_xml->addChild("OBJ_DESC", "storage for unit_elearning user data");
		$attributes_xml->addChild("OBJ_HIDDEN", "TRUE");
		$attributes_xml->addChild("OBJ_TYPE", "unit_elearning_data");
		steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "attributes.xml", (string) $attributes_xml->asXML(), "text/xml", $this->unit_elearning_preferences);
		$GLOBALS["STEAM"]->buffer_flush();
	}
	
	private function createElearningCourseData() {
		//create steam container for elearning course
		$this->elearning_course_data = steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $this->elearning_course_id . "@" . $this->course_group_name, $this->unit_elearning_preferences);
		//create attributes.xml
		$attributes_xml = new SimpleXMLElement("<attributes></attributes>");
		$attributes_xml->addChild("OBJ_DESC", "data for courseid " . $this->elearning_course_id);
		$attributes_xml->addChild("OBJ_TYPE", "unit_elearning_course_data");
		$attributes_xml->addChild("UNIT_ELEARNING_CURRENT_TRY", 1);
		steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "attributes.xml", (string) $attributes_xml->asXML(), "text/xml", $this->elearning_course_data);
		$GLOBALS["STEAM"]->buffer_flush();
	}
	
	private function createTry($tryNumber) {
		//create steam container for try
		$this->container_try[$tryNumber] = steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "" . $tryNumber, $this->elearning_course_data);
		
		//create attributes xml
		$attributes_xml = new SimpleXMLElement("<attributes></attributes>");
		$attributes_xml->addChild("UNIT_ELEARNING_EXAM_FINAL_EXAM_ENABLED", "false");
		$attributes_xml->addChild("UNIT_ELEARNING_EXAM_FINAL_EXAM_FINISHED", "false");
		$attributes_xml->addChild("UNIT_ELEARNING_EXAM_FINAL_EXAM_PASSED", "false");
		$attributes_xml->addChild("UNIT_ELEARNING_EXAM_FINAL_EXAM_SUM_POINTS", "");
		$attributes_xml->addChild("UNIT_ELEARNING_EXAM_FINAL_EXAM_SUM_SCORE", "");
		$attributes_xml->addChild("UNIT_ELEARNING_EXAM_FINAL_EXAM_ANSWERS", "");
		$this->try_data_xml[$tryNumber] = steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "attributes.xml", (string) $attributes_xml->asXML(), "text/xml", $this->container_try[$tryNumber]);
		$GLOBALS["STEAM"]->buffer_flush();
	}
	
	private function get_data_xml() {
		return simplexml_load_string($this->try_data_xml[$this->get_current_try()]->get_content());
	}
	
	private function save_data_xml($xml) {
		$this->try_data_xml[$this->get_current_try()]->set_content((string) $xml->asXML());		
	}
	
	public function has_next_try() {
		return ($this->current_try < $this->max_tries);
	}
	
	public function get_current_try() {
		if ($this->elearning_course_data == null) {
			echo "Defekte Datenstruktur bei Nutzer: " . $this->user_name . "<br><pre>";
			debug_print_backtrace();
			die;
		}
		
		$doc = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), $this->elearning_course_data->get_path() . "/attributes.xml");
		$xml = simplexml_load_string($doc->get_content());
		return (integer) $xml->UNIT_ELEARNING_CURRENT_TRY;
	}
	
	public function set_next_try() {
		if ($this->has_next_try()) {
			$doc = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), $this->elearning_course_data->get_path() . "/attributes.xml");
			$xml = simplexml_load_string($doc->get_content());
			$try = (integer) $xml->UNIT_ELEARNING_CURRENT_TRY;
			$try++;
			$this->createTry($try);
			$xml->UNIT_ELEARNING_CURRENT_TRY = $try;
			$doc->set_content((string) $xml->asXML());
		}
	}
	
	public function has_exam_enabled() {
		$xml = $this->get_data_xml();
		if ($xml->UNIT_ELEARNING_EXAM_FINAL_EXAM_ENABLED == "true") {
			return true;
		} else {
			return false;
		}
	}
	
	public function set_exam_enabled($enabled = TRUE) {
		$xml = $this->get_data_xml();
		if ($enabled) {
			$xml->UNIT_ELEARNING_EXAM_FINAL_EXAM_ENABLED = "true";
		} else {
			$xml->UNIT_ELEARNING_EXAM_FINAL_EXAM_ENABLED = "false";
		}
		$this->save_data_xml($xml);
	}
	
	public function has_exam_finished() {
		$xml = $this->get_data_xml();
		if ($xml->UNIT_ELEARNING_EXAM_FINAL_EXAM_FINISHED == "true") {
			return true;
		} else {
			return false;
		}
	}
	
	public function set_exam_finished($finished = TRUE) {
		$xml = $this->get_data_xml();
		if ($finished) {
			$xml->UNIT_ELEARNING_EXAM_FINAL_EXAM_FINISHED = "true";
		} else {
			$xml->UNIT_ELEARNING_EXAM_FINAL_EXAM_FINISHED = "false";
		}
		$this->save_data_xml($xml);
	}
	
	public function has_exam_passed() {
		$xml = $this->get_data_xml();
		if ($xml->UNIT_ELEARNING_EXAM_FINAL_EXAM_PASSED == "true") {
			return true;
		} else {
			return false;
		}
	}
	
	public function set_exam_passed($passed = TRUE) {
		$xml = $this->get_data_xml();
		if ($passed) {
			$xml->UNIT_ELEARNING_EXAM_FINAL_EXAM_PASSED = "true";
		} else {
			$xml->UNIT_ELEARNING_EXAM_FINAL_EXAM_PASSED = "false";
		}
		$this->save_data_xml($xml);
	}
	
	public function get_exam_sum_points() {
		$xml = $this->get_data_xml();
		return (integer) $xml->UNIT_ELEARNING_EXAM_FINAL_EXAM_SUM_POINTS;
	}
	
	public function set_exam_sum_points($points) {
		$xml = $this->get_data_xml();
		$xml->UNIT_ELEARNING_EXAM_FINAL_EXAM_SUM_POINTS = $points;
		$this->save_data_xml($xml);
	}
	
	public function get_exam_sum_score() {
		$xml = $this->get_data_xml();
		return (integer) $xml->UNIT_ELEARNING_EXAM_FINAL_EXAM_SUM_SCORE;
	}
	
	public function set_exam_sum_score($score) {
		$xml = $this->get_data_xml();
		$xml->UNIT_ELEARNING_EXAM_FINAL_EXAM_SUM_SCORE = $score;
		$this->save_data_xml($xml);
	}
	
	public function get_exam_answers() {
		$xml = $this->get_data_xml();
		$result = unserialize($xml->UNIT_ELEARNING_EXAM_FINAL_EXAM_ANSWERS);
		if ( $result === false) {
			return null;
		} else {
			return $result;
		}
	}
	
	public function set_exam_answers($answers) {
		$xml = $this->get_data_xml();
		$xml->UNIT_ELEARNING_EXAM_FINAL_EXAM_ANSWERS = serialize($answers);
		$this->save_data_xml($xml);
	}
	
	static $jsCodeOnce = false;
	public function get_status_HTML() {
		$usermanagement_html = usermanangement::get_instance()->get_user_status_html($this->user_name);
		if ($usermanagement_html != null) {
			return $usermanagement_html;
		}
		$html = "";
		
		if (!self::$jsCodeOnce) {
			$js = <<< END
			<script type="text/javascript">
			function nextTry(user_name, course_id, ue_id, html_id) {
				apath = "../units/" + ue_id + "/directaccess";

		
				new Ajax.Request(apath, {
		   	 		method: "post",
		    		parameters: "case=nexttry&user_name=" + user_name + "&course_id=" + course_id,
		    		onFailure: function(){ alert("Error while telling answer."); },
		    		onSuccess: function(response){ document.getElementById(html_id).innerHTML = response.responseText; }});
			}
			</script>
END;
			$html .= $js;
			self::$jsCodeOnce = true;
		}
		
		
		$html .= "<div id=\"unit_elearning_status_".$this->user_name."\">";
		
		
		$html .= $this->get_internal_status_HTML();
		
		$html .= "</div>";
		return $html;
	}
	
	function get_internal_status_HTML() {
		$html = "";
		if ($this->has_exam_finished()) {
			if ($this->has_exam_passed()) {
				$html .=  "<img src=\"/styles/stahl-orange/images/richtig_16.png\"> Prüfung im " . $this->get_current_try() . ". Versuch mit <b>" . $this->get_exam_sum_score() . "</b> von <b>" . $this->get_exam_sum_points() . "</b> Punkten bestanden.";
				if ($this->get_exam_sum_points() - $this->get_exam_sum_score() > 0) {
					$html .= "(<a href=\"" . elearning_mediathek::get_instance()->get_course()->get_url() . "units_elearning/report/" . $this->user_name . "/\">Fehler zeigen</a>)";
				}
			} else {
				$html .= "<img src=\"/styles/stahl-orange/images/falsch_16.png\"> Prüfung im " . $this->get_current_try() . ". Versuch mit <b>" . $this->get_exam_sum_score() . "</b> von <b>" . $this->get_exam_sum_points() . "</b> Punkten nicht bestanden.
				(<a href=\"" . elearning_mediathek::get_instance()->get_course()->get_url() . "units_elearning/report/" . $this->user_name . "/\">Fehler zeigen</a>)";
			}
			$html .= "<br>(<a href=\"javascript:if (confirm('Wollen Sie wirklich die bisherigen Daten dieser Prüfung überschreiben?')) {nextTry('"  .$this->user_name . "', '" . $this->course_group->get_id() . "', '" . elearning_mediathek::get_elearning_unit($this->course_group->get_id())->get_id() . "', 'unit_elearning_status_" . $this->user_name . "');}\">nächsten Versuch freischalten</a>; maximal 3 Versuche möglich)";
		} else {
			$html .=  "noch keine Prüfung im " . $this->get_current_try() . ". Versuch abgelegt";
		}
		return $html;
	}
	
	
	function reset_elearning_unit_user_data() {
		$user = lms_steam::get_current_user();
		if( lms_steam::is_koala_admin($user) ){
			//delete old preferences
			$this->unit_elearning_preferences->delete();
			//create new
			$this->check_elearning_data();
		}
	}
	
	function get_exam_cert() {
		$cert_path = $this->container_try[$this->current_try]->get_path()."/zertifikat.pdf";
		$cert = steam_factory::path_to_object($GLOBALS[ "STEAM" ]->get_id(), $cert_path);
		if (!is_object($cert) || (is_object($cert) && !($cert instanceof steam_document))) {
			$cert = steam_factory::create_document($GLOBALS[ "STEAM" ]->get_id(), "zertifikat.pdf", "", "application/pdf", $this->container_try[$this->current_try]);
			$elearning_course = elearning_mediathek::get_elearning_course_for_course($this->course_group);
			$cert->set_attribute("OBJ_DESC", "Zertifikat der Schulung \"".$elearning_course->get_name()."\"");
			$cert->set_attribute("OBJ_TYPE", "elearning_course_cert");
		}
		return $cert;
	}
	
	function has_exam_cert() {
		$cert_path = $this->container_try[$this->current_try]->get_path()."/zertifikat.pdf";
		$cert = steam_factory::path_to_object($GLOBALS[ "STEAM" ]->get_id(), $cert_path);
		if (!is_object($cert) || (is_object($cert) && !($cert instanceof steam_document))) {
			return false;
		}
		return true;
	}
	
	function get_exam_cert_preview() {
		$cert_preview_path = $this->container_try[$this->current_try]->get_path()."/zertifikat_preview.jpg";
		$cert_preview = steam_factory::path_to_object($GLOBALS[ "STEAM" ]->get_id(), $cert_preview_path);
		if (!is_object($cert_preview) || (is_object($cert_preview) && !($cert_preview instanceof steam_document))) {
			$cert_preview = steam_factory::create_document($GLOBALS[ "STEAM" ]->get_id(), "zertifikat_preview.jpg", "", "image/jpeg", $this->container_try[$this->current_try]);
			$elearning_course = elearning_mediathek::get_elearning_course_for_course($this->course_group);
			$cert_preview->set_attribute("OBJ_DESC", "Zertifikat-Vorschau der Schulung \"".$elearning_course->get_name()."\"");
			$cert_preview->set_attribute("OBJ_TYPE", "elearning_course_cert_perview");
		}
		return $cert_preview;
	}
	
	public static function get_certs($user_name) {
		$result = array();
		$user = steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $user_name);
		$courses = lms_steam::user_get_booked_courses($user->get_id());
		foreach ($courses as $course) {
			$unit = elearning_mediathek::get_elearning_unit($course["OBJ_ID"]);
			if ($unit === false) {
				continue;
			}
			$elearning_user = new elearning_user($user_name, $course["OBJ_ID"]);
			if ($elearning_user->has_exam_cert()) {
				$result[] = $elearning_user->get_exam_cert();
			}
		}
		return $result;
	}
	
}