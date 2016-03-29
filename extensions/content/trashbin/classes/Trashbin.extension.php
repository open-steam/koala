<?php
class Trashbin extends AbstractExtension implements IObjectExtension, IObjectModelExtension{

	public function getName() {
		return "Trashbin";
	}

	public function getDesciption() {
		return "Extension to manage files.";
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
		return "Trashbin";
	}

	public function getObjectReadableDescription() {
		return "Verwaltung von Daten, welche gelöscht werden können.";
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
		return new \Trashbin\Commands\Index();
	}


	public function getObjectModels() {
		$objectModels = array();
		$objectModels[] = "\Trashbin\Model\Trashbin";
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
			//$type = getObjectType($object);
			//if (array_search($type, array("calendar")) !== false) {
			return $object;
			//}
		}
		return null;
	}

	public function getPriority() {
		return 6;
	}
}
?>
