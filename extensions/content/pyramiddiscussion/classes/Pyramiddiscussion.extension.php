<?php
class Pyramiddiscussion extends AbstractExtension implements IObjectExtension{

	public function getName() {
		return "Pyramiddiscussion";
	}

	public function getDesciption() {
		return "Extension for Pyramiddiscussions.";
	}

	public function getVersion() {
		return "v1.0.0";
		/* 
		possible extensions in future versions:
 		- pictures in positions
 		- anonymous pyramid
		- editing state on position in pyramid view
		- polling to test for pyramid state changes
		 */
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Petertonkoker", "Jan", "janp@mail.uni-paderborn.de");
		return $result;
	}
	
	public function getObjectReadableName() {
		return "Pyramidendiskussion";
	}
	
	public function getObjectReadableDescription() {
		return "Darstellung von Pyramidendiskussionen.";
	}
	
	public function getObjectIconUrl() {
		return $this->getAssetUrl() . "icons/pyramiddiscussion.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Pyramiddiscussion\Commands\NewPyramiddiscussionForm();
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$pyramidObject = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		$pyramidType = $pyramidObject->get_attribute("OBJ_TYPE");
		if ($pyramidType != "0" && strStartsWith($pyramidType, "container_pyramiddiscussion")) {
			return new \Pyramiddiscussion\Commands\Index();
		}
		return null;
	}
	
	public function getPriority() {
		return 8;
	}
}
?>