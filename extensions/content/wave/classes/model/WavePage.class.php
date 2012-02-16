<?php
namespace Wave\Model;
/**
 * Wheel model object for a wave page. It's a object with following attributes:
 * OBJ_TYPE: "container_wavepage"
 * OBJ_NAME: "<page name string>"
 * WAVEPAGE_THEME: "<theme name string>"
 * WAVEPAGE_TYPE: "<type name string>"
 * WAVEPAGE_TYPE_CONFIG : "<type configuration map>"
 * WAVEPAGE_HIDDEN: "true" or "false"
 * WAVEPAGE_MODULE_SSP_ALBUM_NO: "<no of ssp album>"
 * @author Dominik Niehus <nicke@uni-paderborn.de>
 *
 */
class WavePage extends WaveObject{
	private $mySide;
	private static $instances = array();
	
	private function __construct($objectId, $mySide) {
		$this->mySide = $mySide;
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		if (!$this->object || !($this->object instanceof \steam_container)) {
			throw new Exception("Wave side not found.");
		}
		//$controlObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $this->object->get_path() . "/control.xml");
		if (isset($controlObject) && $controlObject instanceof \steam_document) {
			$xmlStr = $controlObject->get_content();
			$xml = simplexml_load_string($xmlStr);
			$children = $xml->children();
			foreach($children as $child) {
				if ($child->getName() == "OBJ_DESC") {
					$this->object->set_attribute(OBJ_DESC, $child->asXML());
				} else if ($child->getName() == "steamweb_type") {
					$this->object->set_attribute("WAVEPAGE_TYPE", $child->asXML());
				} else if ($child->getName() == "steamweb_type_config") {
					$subChildren = $child->children();
					$map = XmlHelper::xml_to_array($subChildren[0]);
					$this->object->set_attribute("WAVEPAGE_TYPE_CONFIG", $map[0]);
				} else if ($child->getName() == "steamweb_module_ssp") {
					$subChildren = $child->children();
					$this->object->set_attribute("WAVEPAGE_MODULE_SSP_ALBUM_NO", $subChildren[0]->asXML());
				}
			}
		}
	}
	
	public static function getInstanceFor($objectId, $mySide) {
		if (array_key_exists($objectId, self::$instances)) {
			return self::$instances[$objectId];
		} else {
			$instance = new self($objectId, $mySide);
			self::$instances[$objectId] = $instance;
			return $instance;
		}
	}
	
	public function getHtml() {
		$theme = $this->getTheme();
		//$theme->setHeader($header);
		$theme->setTitle($this->getTitle());
		//$theme->setStyleVariations($styleVariations);
		//$theme->setUserStyles($userStyles);
		//$theme->setUserJavascript($userJavascript);
		//$theme->setPluginHeader($pluginHeader);
		//$theme->setUserHeader($userHeader);
		$theme->setToolbar($this->mySide->getEngine()->getFullMenuAsHtml());
		//$theme->setLogo($logo);
		$theme->setSiteTitle($this->mySide->getSideName());
		$theme->setSiteSlogan($this->mySide->getSideSlogan());
		$theme->setSidebarTitle($this->mySide->getSidebarTitle());
		$theme->setSidebar($this->mySide->getSidebar());
		//$theme->setPluginSidebar($pluginSidebar);
		//$theme->setBreadcrumb($breadcrumb);
		$theme->setContent($this->getContent());
		$theme->setFooter($this->mySide->getFooter());
		$theme->setPrevChapter("<a href=\"\">zurück</a>");
		//$theme->setChapterMenu("");
		$theme->setNextChapter("<a href=\"\">weiter</a>");
		$html = WaveTags::processContent($theme->getHtml(), $this);
		return $html;
	}
	
	public function getPageName() {
		$objDesc = trim($this->object->get_attribute(OBJ_DESC));
		if ($objDesc !== 0 && $objDesc !== "") {
			return $objDesc;
		} else {
			return $this->object->get_name();
		}
	}
	
	public function setPageName($pageName) {
		$this->object->set_attribute("OBJ_NAME", $pageName);
	}
	
	public function getTitle() {
		return $this->mySide->getSideName() . " - " . $this->getPageName();
	}
	
	public function getContent() {
		$contentObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $this->object->get_path() . "/start.html");
		if (isset($contentObject) && $contentObject instanceof \steam_document) {
// 			$encoding = $contentObject->get_attribute(DOC_ENCODING);
// 			if ($encoding === 0) {
// 				if (mb_detect_encoding($contentObject) == "UTF-8") {
// 					echo "found utf-8";
// 					$encoding = "utf-8";
// 					$contentObject->set_attribute(DOC_ENCODING, $encoding);
// 				} else {
// 					echo "found " . mb_detect_encoding($contentObject);
// 				}
// 			}
			return $contentObject->get_content();
		} else {
			return "Content object is missing!";
		}
	}
	
	public function getTheme() {
		if ($this->get_attribute("WAVEPAGE_THEME") !== 0 && $this->get_attribute("WAVEPAGE_THEME") !== "") {
			$wavePageThemeName = $this->get_attribute("WAVEPAGE_THEME");
			return $this->mySide->getEngine()->getTheme($wavePageThemeName);
		} else {
			return $this->mySide->getTheme();
		}
	}
	
	public function getSubPages($showHidden) {
		$result = array();
		$subRooms = $this->object->get_inventory(CLASS_ROOM);
		foreach ($subRooms as $subRoom) {
			$result[] = WavePage::getInstanceFor($subRoom->get_Id(), $this->mySide);
		}
		return $result;
	}
	
	public function getPageUrl() {
		return $this->mySide->getEngine()->getSideUrl() . str_replace($this->mySide->get_path() ."/", "", $this->get_path() . "/");
	}
	
	public function getSide() {
		return $this->mySide;
	}
}
?>