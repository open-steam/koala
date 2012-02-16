<?php
class LinkObject extends AbstractExtension implements IObjectExtension {
	
	public function getName() {
		return "LinkObject";
	}
	
	public function getDesciption() {
		return "Extension for link object.";
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
		return "Mit Hilfe von Verknüpfungen können Sie schnell zu Seiten auf dieser Plattform oder zu anderen Seiten im Web navigieren.";
	}
	
	public function getObjectIconUrl() {
		return null;
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return null;
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$object = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		if ($object instanceof steam_link) {
			return new \Explorer\Commands\Index();
		}
	}
	
	public function getPriority() {
		return -20;
	}
}
?>