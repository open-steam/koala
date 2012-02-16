<?php

class elearning_mediathek {
	
	private static $instance = NULL;
	private $unit;
	private $course;
	
	public static function get_elearning_courses() {
		$courses = array();
		//get all available elearning courses installed on server (/packages/elearning_...)
		$cache = get_cache_function( "unit_elearning", 3600 );
      	$packages = $cache->call( "steam_factory::get_object_by_name", $GLOBALS[ "STEAM" ]->get_id(), "/packages" );
		$items = $cache->call(array($packages, "get_inventory"));
		foreach ($items as $item) {
			if ($item instanceof steam_container) {
				if(substr($cache->call(array($item, "get_name")), 0, strlen('elearning_')) == 'elearning_')
				array_push($courses, new elearning_course(self::get_instance(), $item));
			}	
		}
		return $courses;
	}
	
	public static function get_elearning_course_by_id($id) {
		$courses = self::get_elearning_courses();	
		foreach($courses as $course) {
			if ($course->get_id() == (string)$id) {			
				return $course;
			}
		}
		return NULL;
	}
	
	public static function get_elearning_course_for_unit($unit) {
		return self::get_elearning_course_by_id($unit->get_attribute("ELEARNING_UNIT_ID"));
	}
	
	public static function get_elearning_course_for_course($course) {
		$unit = self::get_elearning_unit($course->get_id());
		return self::get_elearning_course_for_unit($unit);
	}
	
	public static function get_instance(){
		if (self::$instance === NULL) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	public function set_unit($unit) {
		$this->unit = $unit;
	}
	
	public function get_unit(){
		return $this->unit;
	}
	
	public function set_course($course) {
		$this->course = $course;
	}
	
	public function get_course(){
		if ($this->course != null) {
			return $this->course;
		} else {
			global $course;  //TODO: very bullshit here!!
			if (isset($course)) {
				return $course;
			} else {
				global $group;
				return $group;
			}
		}
	}
	
	public static function is_exam_global_enabled($courseid) {
		$cache = get_cache_function( "unit_elearning", 3600 );
		$steamgroup = $cache->call("steam_factory::get_object", $GLOBALS[ "STEAM" ]->get_id(), $courseid);
		$course = new koala_group_course($steamgroup);
		$unit = self::get_elearning_unit($courseid);
		if (is_object($unit)) {
			$exam = new exam($unit, $cache->call(array($unit, "get_attribute"), "ELEARNING_UNIT_ID"), $course);
			return $exam->get_exam()->is_global_enabled();
		}
		return false;
	}
	
	public static function get_elearning_unit($courseid) {
		$cache = get_cache_function( "unit_elearning", 3600 );
		$steamgroup = $cache->call("steam_factory::get_object", $GLOBALS[ "STEAM" ]->get_id(), $courseid);
		$course = new koala_group_course($steamgroup);
		$workroom = $cache->call(array($course, "get_workroom"));
		$units = $cache->call(array($workroom, "get_inventory"));
		
		//TODO: Little Hack: Elearning Unit MUST be the first unit
		if (isset($units[0])) {
			$attrib = $cache->call(array($units[0], "get_attribute"), "UNIT_TYPE");
			if ($attrib === "units_elearning") {
				return $units[0];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	public static function get_elearning_unit_id($courseid) {
		$cache = get_cache_function( "unit_elearning", 3600 );
		$unit = self::get_elearning_unit($courseid);
		if (is_object($unit)) {
			$id = $cache->call(array($unit, "get_attribute"), "ELEARNING_UNIT_ID");
			return $id;
		} else {
			return false;
		}
	}
}
?>