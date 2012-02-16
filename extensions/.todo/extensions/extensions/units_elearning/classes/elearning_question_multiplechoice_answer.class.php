<?php
require_once( PATH_EXTENSIONS . "units_elearning/classes/elearning_object.class.php");

class elearning_question_multiplechoice_answer extends elearning_object {
	
	private $i, $user;
	
	function __construct($parent_tmp, $steamObject_tmp, $xml_tmp, $i) {
		$this->parent = $parent_tmp;
		$this->steamObject = $steamObject_tmp;
		$this->xml = $xml_tmp;
		$this->i = $i;
		$this->user = $this->parent->get_user();
	}
	
	function get_id() {
		return $this->i;
	}
	
	function get_name() {
		return "Antwort " . $this->get_id();
	}
	
	function get_description() {
		return "dies ist die Antwort auf Frage " . $this->parent->get_id();
	}
	
	function is_correct() {
		if ($this->xml->attributes()->correct == "true") {
			return true;
		} else {
			return false;
		}
	}
	
	function get_answertext() {
		return (string) $this->xml->answertext;
	}
	
	function get_feedback() {
		return (string) $this->xml->feedback;
	}
	
	function get_score_correct() {
		return (int) $this->xml->score->correct;
	}
	
	function get_score_wrong() {
		return (int) $this->xml->score->wrong;
	}
	
	function store_answer($value) {
		$user = $this->user;
		$elearning_user = elearning_user::get_instance($user->get_name(), elearning_mediathek::get_instance()->get_course()->get_id());
		$attribute = $elearning_user->get_exam_answers();
		if ($attribute == null) {
			$attribute = array();
		} 
		
		if (!isset($attribute[$this->get_parent_chapter()->get_id() . "." . $this->get_parent_question()->get_id()])) {
			$qdata = array();
		} else {
			$qdata = $attribute[$this->get_parent_chapter()->get_id() . "." . $this->get_parent_question()->get_id()];
		}
		
		if ($value == 1) {
			$qdata[$this->get_id()] = 1;
		} else {
			$qdata[$this->get_id()] = 0;
		}
		
		//store
		$attribute[$this->get_parent_chapter()->get_id() . "." . $this->get_parent_question()->get_id()] = $qdata;
		$elearning_user->set_exam_answers($attribute);
	}
	
	function load_answer($u) {
		if ($u == null) {
			$user = lms_steam::get_current_user();
		} else {
			$user = $u;
		}
		$attribute = elearning_user::get_instance($user->get_name(), elearning_mediathek::get_instance()->get_course()->get_id())->get_exam_answers();
		if ($attribute != null) {
			if (isset($attribute[$this->get_parent_chapter()->get_id() . "." . $this->get_parent_question()->get_id()])) {
				$qdata = $attribute[$this->get_parent_chapter()->get_id() . "." . $this->get_parent_question()->get_id()];
				if ($qdata != 0) {
					if (isset($qdata[$this->get_id()]) && $qdata[$this->get_id()] == 1) {
						return 1;
					}
				}
			}
		}
		return 0;
	}
	
}

?>