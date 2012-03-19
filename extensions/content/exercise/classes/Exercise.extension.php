<?php
class Exercise extends AbstractExtension implements IObjectExtension{

	public function getName() {
		return "Exercise";
	}

	public function getDesciption() {
		return "Exercise Extension";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Kevin", "Eysert", "eysert@gmail.com");
		return $result;
	}
	
	public function getObjectReadableName() {
		return "Exercise";
	}
	
	public function getObjectReadableDescription() {
		return "Übungsmanagement";
	}
	
	public function getObjectIconUrl() {
		return $this->getAssetUrl() . "icons/widget_kl.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Exercise\Commands\NewExerciseForm();
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$galleryObject = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		$galleryType = $galleryObject->get_attribute("OBJ_TYPE");
		if ($galleryType==="container_exercise") {
			return new \Exercise\Commands\Index();
		}
		return null;
	}
        
        public function getPriority() {
		return 5;
	}
}
?>