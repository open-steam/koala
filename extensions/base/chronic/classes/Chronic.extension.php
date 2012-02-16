<?php
class Chronic extends AbstractExtension implements IMenuExtension {
	
	private static $currentObject;
	
	public function getName() {
		return "Chronic";
	}
	
	public function getDesciption() {
		return "Extension for chronic handling.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function getMenuEntries() {
		$chronic = $this->loadChronic();
		$length = count($chronic);
		$result = array(array("name" => "Chronik", "menu" => array(
													$this->getBackEntry(),
													$this->getParentEntry(), 
											 )));
		array_pop($chronic);
		$chronic = array_reverse($chronic);
		$chronicEntries = array();
		foreach ($chronic as $item) {
			$chronicEntries[] = $this->getChronicEntry($item);
		}
		if (count($chronicEntries) > 1) {
			$menuArray = $result[0]["menu"];
			$menuArray[] = array("name" => "SEPARATOR");
			foreach ($chronicEntries as $entry) {
				$menuArray[] = $entry;
			}
			$result[0]["menu"] = $menuArray;
		}
		return $result;
	}
	
	public function setCurrentObject($steamObject) {
		if ($steamObject instanceof steam_object) {
			self::$currentObject = $steamObject;
			$this->updateChronic($steamObject);
		}
	}
	
	private function getBackEntry() {
		$chronic = $this->loadChronic();
		$length = count($chronic);
		if ($length > 1) {
			$steam_object = $chronic[$length-2];
			return array("name" => "zurück", "link" => \ExtensionMaster::getInstance()->getUrlForObjectId($steam_object->get_id(), "view"));
		}
		return "";
	}
	
	private function getParentEntry() {
		$type = getObjectType(self::$currentObject);
		if (array_search($type, array("forum", "referenceFolder", "trashbin", "gallery", "portal", "room", "container")) !== false) {
			$steam_object = self::$currentObject->get_environment();
			return array("name" => "nach oben ( <img src=\"".PATH_URL."explorer/asset/icons/mimetype/".deriveIcon($steam_object)."\"></img> " . getCleanName($steam_object, 20) . " )", "link" => \ExtensionMaster::getInstance()->getUrlForObjectId($steam_object->get_id(), "view"));
		}
		return "";
	}
	
	private function getChronicEntry($steam_object) {
		$result = array("name" => "<img src=\"".PATH_URL."explorer/asset/icons/mimetype/".deriveIcon($steam_object)."\"></img> " . getCleanName($steam_object, 20), "link" => \ExtensionMaster::getInstance()->getUrlForObjectId($steam_object->get_id(), "view"));
		return $result;
	}
	
	private function updateChronic($steamObject) {
		$type = getObjectType($steamObject);
		if (array_search($type, array("document", "forum", "referenceFolder", "user", "trashbin", "gallery", "portal", "userHome", "groupWorkroom", "room", "container")) !== false) {
			$user = lms_steam::get_current_user();
			$chronic = $this->loadChronic();
			$pos = array_search($steamObject, $chronic);
			if ($pos === false) {
				$chronic[] = $steamObject;
			} else {
				unset($chronic[$pos]);
				$chronic = array_values($chronic);
				$chronic[] = $steamObject;
			}
			if (count($chronic) > CHRONIC_LENGTH) {
				$chronic = array_slice($chronic, count($chronic) - CHRONIC_LENGTH, CHRONIC_LENGTH);
			}
			$user->set_attribute("USER_CHRONIC", $chronic);
		}
	}
	
	private function loadChronic() {
		$ids = array();
		$user = lms_steam::get_current_user();
		$chronic = $user->get_attribute("USER_CHRONIC");
		$result = array();
		if (is_array($chronic)) {
			foreach ($chronic as $item) {
				if ($item instanceof steam_object) {
					$env = $item->get_environment();
					if (!($env instanceof steam_trashbin)) {
						$id = $item->get_id();
						if (array_search($id, $ids) === false) {
							$result[] = $item;
							$ids[] = $id;
						}
					}
				}
			}
		}
		return $result;
	}
}
?>