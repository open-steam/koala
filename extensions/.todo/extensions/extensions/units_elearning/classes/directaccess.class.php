<?php
require_once( PATH_EXTENSIONS . "units_elearning/classes/elearning_user.class.php");
require_once( PATH_EXTENSIONS . "units_elearning/classes/elearning_mediathek.class.php");
class directaccess {

		
	private $courseid;	
	private $unit;
		
	function __construct($courseid, $unit) {
		$this->courseid = $courseid;
		$this->unit = $unit;
	}
		
	function callfunction($name) {
		if ($name == "tellanswer") {
			$this->tellanswer();
		} else if ($name == "storeanswer") {
			$this->storeanswer();
		} else if ($name == "finishexam") {
			$this->finishexam();
		} else if ($name == "resetexam") {
			$this->resetexam();
		} else if ($name == "toggleexam") {
			$this->toggleexam();
		} else if ($name == "clearexam") {
			$this->clearexam();
		} else if ($name == "nexttry") {
			$this->nexttry();
		}
	}
	
	function tellanswer() {
		$cid = $_POST["cid"];
		$qid = $_POST["qid"];
		$aid = $_POST["aid"];
		$course = elearning_mediathek::get_elearning_course_by_id($this->courseid);
		$chapter = $course->get_chapter_by_id($cid);
		$question = $chapter->get_question_by_id($qid);
		$answer = $question->get_answer_by_id($aid);
		$feedback = $answer->get_feedback();
		if ($feedback == "" && $answer->is_correct()) {
			$feeback = "Richtig!";
		} else if ($feedback == "" && !$answer->is_correct()) {
			$feeback = "Falsch!";
		}
		echo $qid . "@@" . $aid . "@@" . $answer->is_correct() . "@@" . $feedback;
	}
	
	function storeanswer() {
		$cid = $_POST["cid"];
		$eid = $_POST["eid"];
		$qid = $_POST["qid"];
		$aid = $_POST["aid"];
		$value = $_POST["value"];
		
		if (elearning_user::get_instance(lms_steam::get_current_user()->get_name(), elearning_mediathek::get_instance()->get_course()->get_id())->has_exam_finished()) {
			echo !$value;
		} else {
			$course = elearning_mediathek::get_elearning_course_by_id($this->courseid);
			$chapter = $course->get_chapter_by_id($cid);
			$question = $chapter->get_question_by_id($qid);
			$answer = $question->get_answer_by_id($aid);
			$answer->store_answer($value);
			echo $value;
		}
	}
	
	function finishexam() {
		$eid = $_POST["eid"];
		$course = elearning_mediathek::get_elearning_course_by_id($this->courseid);
		$exam = $course->get_exam_by_id($eid);
		$exam->set_finished(true);
	}
	
	function resetexam() {
		$html_id = $_POST["html_id"];
		$user_id = $_POST["user_id"];
		$cid = $_POST["cid"];
		$exam_id = $_POST["exam_id"];
		
		$elearning_course = elearning_mediathek::get_elearning_course_by_id($cid);
		
		$user = steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $user_id);
		$el_user = new elearning_user($user, $elearning_course);
		$el_user->set_finished_exam(false);
		echo $html_id;
	}
	
	function clearexam() {
		$html_id = $_POST["html_id"];
		$user_id = $_POST["user_id"];
		$cid = $_POST["cid"];
		$exam_id = $_POST["exam_id"];
		
		$elearning_course = elearning_mediathek::get_elearning_course_by_id($cid);
		
		$user = steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $user_id);
		$el_user = elearning_user::get_instance($user->get_name(), elearning_mediathek::get_instance()->get_course()->get_id());
		$el_user->reset_elearning_unit_user_data();
		echo $html_id;
	}
	
	function toggleexam() {
		$course = elearning_mediathek::get_elearning_course_by_id($this->courseid);
		$course->get_exam_by_type("final_exam")->set_global_enabled(!$course->get_exam_by_type("final_exam")->is_global_enabled());
		if ($course->get_exam_by_type("final_exam")->is_global_enabled()) {
			echo "true";
		} else {
			echo "false";
		}
	}
	
	function nexttry() {
		$user_name = $_POST["user_name"];
		$course_id= $_POST["course_id"];
	
		$el_user = elearning_user::get_instance($user_name, $course_id);
		$el_user->set_next_try();
		
		echo $el_user->get_internal_status_HTML();
	}
	
}
?>