<?php
class Wiki extends AbstractExtension implements IObjectExtension, IIconBarExtension {

	public function getName() {
		return "Wiki";
	}

	public function getDesciption() {
		return "Extension for Wiki View.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Petertonkoker", "Jan", "janp@mail.uni-paderborn.de");
		return $result;
	}

	public function getObjectReadableName() {
		return "Wiki/Glossar/A-Z Liste";
	}

	public function getObjectReadableDescription() {
		return "In Wikis können Benutzer gemeinsam an einem Projekt arbeiten. Die Versionsverwaltung ermöglicht es, den Entstehungsprozess jedes Eintrags nachvollziehen zu können.";
	}

	public function getObjectIconUrl() {
		return $this->getAssetUrl() . "icons/wiki.png";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Wiki\Commands\NewWikiForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$wikiObject = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		$objectType = $wikiObject->get_attribute("OBJ_TYPE");
		if ($objectType != "0" && $objectType == "container_wiki_koala") {
			return new \Wiki\Commands\Index();
		}
		return null;
	}

	public function getPriority() {
		return 8;
	}

	public function getIconBarEntries() {
		$array = array();
		$path = strtolower($_SERVER["REQUEST_URI"]);
		if(strpos($path, "wiki") !== false){
			$pathArray = explode("/", $path);
			$currentObjectID = "";
			for ($count = 0; $count < count($pathArray); $count++) {
					if (intval($pathArray[$count]) !== 0) {
							$currentObjectID = $pathArray[$count];
							break;
					}
			}
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentObjectID);
			$env = $object->get_environment();
			$array[] = array("name" => "<img title=\"Aufwärts\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/arrow_up_white.png\">", "onclick"=>"location.href='" . PATH_URL . "explorer/index/{$env->get_id()}/'");
			return $array;
		}
	}
}
?>
