<?php
class Mplme extends AbstractExtension implements IObjectExtension{

	public function getName() {
		return "Mplme";
	}

	public function getDesciption() {
		return "Extension for Mplme.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function getObjectReadableName() {
		return "Mplme";
	}
	
	public function getObjectReadableDescription() {
		return "Darstellung von Mplme Daten.";
	}
	
	public function getObjectIconUrl() {
		return $this->getAssetUrl() . "icons/widget_kl.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		//return new \Mplme\Commands\NewMplmeForm();
		return null;
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$galleryObject = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		$galleryType = $galleryObject->get_attribute("OBJ_TYPE");
		if ($galleryType==="container_mplme") {
			return new \Mplme\Commands\Index();
		}
		return null;
	}
	
	public function getPriority() {
		return 7.5;
	}
}
?>