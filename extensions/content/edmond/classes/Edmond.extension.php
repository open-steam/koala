<?php
class Edmond extends AbstractExtension implements IObjectExtension, IObjectModelExtension{

	public function getName() {
		return "Edmond";
	}

	public function getDesciption() {
		return "Extension to view Edmond media.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Rolf", "Wilhelm", "party@uni-paderborn.de");
		return $result;
	}

	public function getObjectReadableName() {
		return "Edmond";
	}

	public function getObjectReadableDescription() {
		return "Anzeige von Edmond Medieninhalten.";
	}

	public function getObjectIconUrl() {
		return null;
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return null;
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		return new \Map\Commands\Index();
	}
	

	public function getObjectModels() {
		$objectModels = array();
		$objectModels[] = "\Edmond\Model\Edmond";
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