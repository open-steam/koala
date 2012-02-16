<?php

require_once( PATH_EXTENSIONS . "units_elearning/classes/xmlhelper.class.php");

abstract class elearning_object {
	
	protected $steamObject = NULL;
	protected $parent = NULL;
	protected $xml = NULL;
	protected $xmlhelper;
	protected $internal_path;
	protected $path;
	
	function get_id() {
		return (string)$this->xml->id;
	}
	
	function get_parent() {
		return $this->parent;
	}
	
	function get_name() {
		return (string)$this->xml->name;
	}
	
	function get_description() {
		return (string)$this->xml->description;
	}
	
	function get_xmlhelper() {
		if (!isset($this->xmlhelper)) {
			$this->xmlhelper = new xmlhelper();
		}
		return $this->xmlhelper;
	}
	
	function get_internal_path() {
		if (!isset($this->internal_path)) {
			$this->internal_path = $this->steamObject->get_path();
		}
		return $this->internal_path;
	}
	
	function get_path() {
		return $this->path;
	}
	
	function get_parent_question() {
		return $this->parent;
	}
	
	function get_parent_chapter() {
		return $this->parent->parent;
	}
	
	function get_parent_course() {
		return $this->parent->parent->parent;
	}
	
	function get_steam_object() {
		return $this->steamObject;
	}
	
}
?>