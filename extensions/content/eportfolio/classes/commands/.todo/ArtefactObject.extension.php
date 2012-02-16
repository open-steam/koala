<?php
class ArtefactObject extends AbstractExtension implements IObjectExtension {

	public function getName() {
		return "ArtefactObject";
	}

	public function getDesciption() {
		return "Extension for artefact object.";
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
		return "Portfolio";
	}

	public function getObjectReadableName() {
		return "Artefakt";
	}

	public function getObjectReadableDescription() {
		return "Belege für Portfolios.";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/folder.png";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Portfolio\Commands\NewArtefactForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		/*
		 $object = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		 if ($object instanceof steam_container) {
			return new \Explorer\Commands\Index();
			}

		 */
		return new \Portfolio\Commands\ViewArtefact();
	}

	public function getPriority() {
		return -20;
	}
}
?>