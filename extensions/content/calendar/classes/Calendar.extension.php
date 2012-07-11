<?php
class Calendar extends AbstractExtension implements IObjectExtension, IObjectModelExtension{

	public function getName() {
		return "Calendar";
	}

	public function getDesciption() {
		return "Extension to manage events.";
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
		return "Calendar";
	}
	
	public function getObjectReadableDescription() {
		return "Verwaltung von Terminen.";
	}
	
	public function getObjectIconUrl() {
		//TODO:Update Icon
		return PATH_URL. "gallery/asset/icons/mimetype/gallery.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Calendar\Commands\NewCalendar();
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
                return false; //TODO: test if object is a valid calendar, if not return false
		return new \Calendar\Commands\Index();
	}
	
	public function getObjectModels() {
		$objectModels = array();
		$objectModels[] = "\Calendar\Model\Calendar";
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
		return 500;
	}
}
?>