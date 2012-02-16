<?php
require_once( PATH_EXTENSIONS . "units_elearning/classes/elearning_object.class.php");
require_once( PATH_EXTENSIONS . "units_elearning/classes/elearning_chapter.class.php");
require_once( PATH_EXTENSIONS . "units_elearning/classes/elearning_exam.class.php");

class elearning_course extends elearning_object {
	
	private $steam_object_path;
	private $cache;

	function __construct($parent_tmp, $steamObject_tmp) {
		$this->parent = $parent_tmp;
		$this->steamObject = $steamObject_tmp;
		//get meta data
		$this->cache = get_cache_function( "unit_elearning", 3600 );
		$this->steam_object_path = $this->cache->call(array($this->steamObject, "get_path"));
		$doc = $this->cache->call("steam_factory::get_object_by_name", $GLOBALS[ "STEAM" ]->get_id(), $this->steam_object_path . "/course.xml");
		$this->xml = simplexml_load_string($this->cache->call(array($doc, "get_content")));
	}
	
	function get_version() {
		$package_name = $this->cache->call(array($this->steamObject, "get_attribute"), "OBJ_NAME");
		$package = $this->cache->call(array($GLOBALS['STEAM'], "get_module"), "package:" . $package_name);
		if (is_object($package)) {
		      $version = $this->cache->call(array($GLOBALS['STEAM'], "predefined_command"), $package, "get_version", array(), false);
		} else {
			return "";
		}
		return $version;
	}

	function get_copyright() {
		return (string) $this->xml->copyright;
	}

	function get_content() {

	}

	function get_variations() {

	}
	
	function get_path() {
		return (string) $this->steam_object_path;
	}

	function get_icon_url() {
		return (string)PATH_URL . "get_document.php?id=" . $this->get_content_steam_object_by_type($this->xml, "icon-file")->get_id(); //"&type=usericon&width=60&height=70
	}
	
	function get_chapters() {
		$steamContainer = $this->get_content_steam_object_by_type($this->xml, "chapters");
		if ($steamContainer != NULL || $steamContainer instanceof steam_container) {
			$chapters_xml_steam_doc = $this->cache->call("steam_factory::get_object_by_name", $GLOBALS[ "STEAM" ]->get_id(), $steamContainer->get_path() . "/chapters.xml");
			$chapters_xml = simplexml_load_string($this->cache->call(array($chapters_xml_steam_doc, "get_content")));
			$items = $chapters_xml->content->array->children();
			$chapters = array();
			foreach ($items as $item) {
				//TODO: Fehlertollerant programmieren!!
				$chapter_steam_container = $this->cache->call("steam_factory::get_object_by_name", $GLOBALS[ "STEAM" ]->get_id(), $this->steamObject->get_path() . "/chapters/" . $item->string[0]);
				$chapter = new elearning_chapter($this, $chapter_steam_container);
				$chapters[] = $chapter;
			}
			return $chapters;
		}
		return NULL;
	}
	
	function get_chapter_by_id($id) {
		//error_log("Suche " . $id);
		$chapters = $this->get_chapters();
		foreach ($chapters as $chapter) {
			//error_log("ist es " . $chapter->get_id());
			if ($chapter->get_id() == $id) {
				return $chapter;
			}
		}
		return NULL;
	}
	
	
	function get_exams() {
		$steamContainer = $this->get_content_steam_object_by_type($this->xml, "exams");
		
		if ($steamContainer != NULL || $steamContainer instanceof steam_container) {
			$exams_xml = simplexml_load_string(steam_factory::get_object_by_name($GLOBALS[ "STEAM" ]->get_id(), $steamContainer->get_path() . "/exams.xml")->get_content());
			$items = $exams_xml->content->array->children();
			$exams = array();
			foreach ($items as $item) {
				//TODO: Fehlertollerant programmieren!!
				$exam = new elearning_exam($this,steam_factory::get_object_by_name($GLOBALS[ "STEAM" ]->get_id(), $this->steamObject->get_path() . "/exams/" . $item->string[0]), $item->string[1]);
				$exams[] = $exam;
			}
			return $exams;
		}
		return NULL;
	}
	
	function get_exam_by_id($id) {
		$exams = $this->get_exams();
		foreach($exams as $exam) {
			if ($exam->get_id() == $id) {
				return $exam;
			}
		}
		return null;
	}
	
	function get_exam_by_type($type) {
		$exams = $this->get_exams();
		foreach($exams as $exam) {
			if ($exam->get_type() == $type) {
				return $exam;
			}
		}
		return null;
	}

	private function get_content_steam_object_by_type($xml, $type) {
		$items = $xml->content->array->children();
		$found = FALSE;
		foreach ($items as $item) {
			$keys = $item->key;
			$i = 0;
			foreach ($keys as $key) {
				if ($key == "type") {
					if ($item->string[$i] == "$type") {
						$found = TRUE;
						break;
					}
					break;
				}
				$i++;
			}
			if ($found) {
				$keys = $item->key;
				$i = 0;
				foreach ($keys as $key) {
					if ($key == "id") {
						return $this->cache->call("steam_factory::get_object_by_name", $GLOBALS[ "STEAM" ]->get_id(), $this->steamObject->get_path() . "/" . $item->string[$i]);
					}
					$i++;
				}
			}
		}
		return NULL;
	}
}
?>