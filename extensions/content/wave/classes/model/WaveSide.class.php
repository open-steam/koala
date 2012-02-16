<?php
namespace Wave\Model;
/**
 * Wheel model object for a wave side. It's a object with following attributes:
 * OBJ_TYPE: "container_waveside"
 * OBJ_NAME: "<side name string>"
 * WAVESIDE_THEME: "<theme name string>"
 * WAVESIDE_TYPE: "<type name string>"
 * WAVESIDE_SLOGAN: "<slogan string>"
 * @author Dominik Niehus <nicke@uni-paderborn.de>
 *
 */
class WaveSide extends WaveObject{
	private $myEngine;
	
	public function __construct($objectId, $myEngine) {
		$this->myEngine =  $myEngine;
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		if (!$this->object || !($this->object instanceof \steam_container)) {
			throw new Exception("Wave side not found.");
		}
	}
	
	public function getSideName() {
		return $this->object->get_name();
	}
	
	public function setSideName($sideName) {
		$this->object->set_attribute("OBJ_NAME", $sideName);
	}
	
	public function getSideSlogan() {
		return $this->object->get_attribute("OBJ_DESC");
	}
	
	public function getEngine() {
		return $this->myEngine;
	}
	
	public function getTheme() {
		if ($this->get_attribute("WAVESIDE_THEME") !== 0 && $this->get_attribute("WAVESIDE_THEME") !== "") {
			$waveSideThemeName = $this->get_attribute("WAVESIDE_THEME");
			return $this->getEngine()->getTheme($waveSideThemeName);
		} else {
			return $this->getEngine()->getTheme(WAVE_STANDARDTHEME);
		}
	}
	
	public function getFooter() {
		$footerObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $this->object->get_path() . "/footer.html");
		if (isset($footerObject) && ($footerObject instanceof \steam_document)) {
			return $footerObject->get_content();
		} else {
			return "";
		}
	}
	
	public function getSidebarTitle() {
		$sidebarObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $this->object->get_path() . "/sidebar.html");
		if (isset($sidebarObject) && ($sidebarObject instanceof \steam_document)) {
			return $sidebarObject->get_attribute("OBJ_DESC");
		} else {
			return "";
		}
	}
	
	public function getSidebar() {
		$sidebarObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $this->object->get_path() . "/sidebar.html");
		if (isset($sidebarObject) && ($sidebarObject instanceof \steam_document)) {
			return $sidebarObject->get_content();
		} else {
			return "";
		}
	}
	
}
?>