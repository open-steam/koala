<?php
class Map extends AbstractExtension implements IObjectExtension, IObjectModelExtension{

	public function getName() {
		return "Map";
	}

	public function getDesciption() {
		return "Extension to view maps.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Christoph", "Sens", "csens@mail.uni-paderborn.de");
		return $result;
	}

	public function getObjectReadableName() {
		return "Map";
	}

	public function getObjectReadableDescription() {
		return "Anzeige von Karteninhalten.";
	}

	public function getObjectIconUrl() {
		//TODO:Update Icon
		return null;
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return null;
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		return false; //quick fix TODO: test object for trashbin
		return new \Map\Commands\Index();
	}

	public function getObjectModels() {
		$objectModels = array();
		$objectModels[] = "\Map\Model\Map";
		return $objectModels;
	}

	public function getCurrentObject(UrlRequestObject $urlRequestObject) {
		$params = $urlRequestObject->getParams();
		$id = $params[0];
		if (isset($id)) {
			if (!isset($GLOBALS["STEAM"])) {
				return null;
			}
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
			if (!($object instanceof steam_object)) {
				return null;
			}
			return $object;
		}
		return null;
	}
	
	public function getPriority() {
		return 100;
	}
}
?>
