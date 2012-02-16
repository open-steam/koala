<?php
class Worksheet extends AbstractExtension implements IObjectExtension {
	
	public function getName() {
		return "Worksheet";
	}
	
	public function getDesciption() {
		return "Extension for worksheets.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Tobias", "Kempkensteffen", "tobias.kempkensteffen@gmail.com");
		return $result;
	}
	
	public function getObjectReadableName() {
		return "Arbeitsblatt";
	}
	
	public function getObjectReadableDescription() {
		return "digitales Arbeitsblatt";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/worksheet.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Worksheet\Commands\NewWorksheetForm();
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject) {
		$obj = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		$status = $obj->get_attribute("worksheet_valid");
		if ($status === 1) {
			return new \Worksheet\Commands\Index();
		}
		return null;
	}
	
	public function getPriority() {
		return 8;
	}
}
?>