<?php
class Spreadsheets extends AbstractExtension implements IObjectExtension {
	public function getName() {
		return "Spreadsheets";
	}
	
	public function getDesciption() {
		return "Extension for creating and editing spreadsheets.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Raphael", "Schroiff", "raphaels@mail.uni-paderborn.de");
		return $result;
	}

	public function getObjectReadableName() {
		return "Tabelle";
	}
	
	public function getObjectReadableDescription() {
		return "Erstellen und gemeinsames bearbeiten von Tabellen.";
	}
	
	public function getObjectIconUrl() {
		return $this->getAssetUrl() . "sheet_get_range.png";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Spreadsheets\Commands\NewSpreadsheetForm();
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$galleryObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		$galleryType = $galleryObject->get_attribute("OBJ_TYPE");
		if ($galleryType==="document_spreadsheet") {
			return new \Spreadsheets\Commands\Index();
		}
		return false;
	}
              
        public function getPriority() {
		return 20;
	}
  
}
?>