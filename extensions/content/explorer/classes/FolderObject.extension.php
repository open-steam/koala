<?php
class FolderObject extends AbstractExtension implements IObjectExtension {
	
	public function getName() {
		return "FolderObject";
	}
	
	public function getDesciption() {
		return "Extension for container object.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function getId() {
		return "Explorer";
	}
	
	public function getObjectReadableName() {
		return "Ordner";
	}
	
	public function getObjectReadableDescription() {
		return "Mit Ordnern können Sie Ihre Objekte strukturieren.";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/folder.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Explorer\Commands\NewFolderForm();
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$object = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		$type = getObjectType($object);
		if (array_search($type, array("referenceFolder", "userHome", "groupWorkroom", "room", "container")) !== false) {
			return new \Explorer\Commands\Index();
		}
	}
	
	public function getPriority() {
		return 7;
	}
}
?>