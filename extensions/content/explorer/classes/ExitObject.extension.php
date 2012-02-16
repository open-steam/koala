<?php
class ExitObject extends AbstractExtension implements IObjectExtension {
	
	public function getName() {
		return "ExitObject";
	}
	
	public function getDesciption() {
		return "Extension for exit object.";
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
		return "Verknüpfung";
	}
	
	public function getObjectReadableDescription() {
		return "Verknüpfung";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/exit.gif";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return null;
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$object = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		if ($object instanceof steam_exit) {
			return new \Explorer\Commands\Index();
		}
	}
	
	public function getPriority() {
		return -20;
	}
}
?>