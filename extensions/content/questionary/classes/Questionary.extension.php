<?php
class Questionary extends AbstractExtension implements IObjectExtension {

	public function getName() {
		return "Questionary";
	}

	public function getDesciption() {
		return "Extension for questionary view.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "dominik.niehus@coactum.de");
		return $result;
	}

	public function getObjectReadableName() {
		return "Fragebogen (alt)";
	}

	public function getObjectReadableDescription() {
		return "Fragebogen (alt)";
	}

	public function getObjectIconUrl() {
		return null;
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return null;
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$object = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
        $type = $object->get_attribute("bid:doctype");
        if ($type === "questionary") {
            return new \Questionary\Commands\Index();
        }
        return null;
	}

    public function getPriority() {
        return 7.5;
    }
}
?>