<?php
require_once( PATH_EXTENSIONS . "units_elearning/classes/elearning_object.class.php");
require_once( PATH_EXTENSIONS . "units_elearning/classes/elearning_question_multiplechoice.class.php");

abstract class elearning_question extends elearning_object {
	
	static private $user;
	
	function get_question_text() {
		return (string) $this->xml->text;
	}
	
	function get_question_image() {
		$path = $this->xml->image;
		if ($path != null) {
			$path = str_replace("../", "", $path);
			return (string) $path;
		} else {
			return null;
		}
	}
	
	function get_question_exam_image() {
		$path = $this->xml->image;
		if ($path != null) {
			//$path = str_replace("../", "", $path);
			return (string) $path;
		} else {
			return null;
		}
	}
	
	function get_question_video() {
		$path = $this->xml->video;
		if ($path != null) {
			$path = str_replace("../", "", $path);
			return (string) $path;
		} else {
			return null;
		}
	}
	
	function is_testrelevant() {
		return (boolean) $this->xml->testrelevant;
	}
	
	function get_type() {
		return (string) $this->xml->type;
	}
	
	static function create_question($parent, $so, $user = null) {
		isset($user) or $user = lms_steam::get_current_user();
		self::$user = $user;
		if ($so instanceof steam_document) {
			$xml = simplexml_load_string($so->get_content());
			if ($xml->type == "MultipleChoice" || $xml->type == "MultipleChoiceQuestion") {
				return new elearning_question_multiplechoice($parent, $so, $xml);
			}
		} else {
			return NULL;
		}
	}
	
	function get_user() {
		return self::$user;
	}
	
}
?>