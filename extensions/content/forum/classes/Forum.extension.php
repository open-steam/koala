<?php
class Forum extends AbstractExtension implements IObjectExtension, IObjectModelExtension  {

	public function getName() {
		return "Forum";
	}

	public function getDesciption() {
		return "Extension for forum view.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Niroshan", "Thillainathan", "n.thillainathan@campus.uni-paderborn.de");
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}

	public function getObjectReadableName() {
		return "Forum";
	}

	public function getObjectReadableDescription() {
		return "In Foren können Benutzer miteinander diskutieren.";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/forum.svg";;
	}

	public function getHelpUrl(){
		return "";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Forum\Commands\NewForum();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$forumObject = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());
		if($forumObject instanceof steam_messageboard) {
			return new \Forum\Commands\Index();
		}
	}

	public function getObjectModels() {
		$objectModels = array();
		$objectModels[] = "\Forum\Model\Forum";
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
			$type = getObjectType($object);
			if (array_search($type, array("forum")) !== false) {
				return $object;
			}
		}
		return null;
	}

	public function getPriority() {
		return 6;
	}
}
?>
