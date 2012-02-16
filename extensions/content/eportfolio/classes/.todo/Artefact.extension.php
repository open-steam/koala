<?php
class Artefact extends AbstractExtension implements IObjectExtension {

	public function getName() {
		return "Artefact";
	}

	public function getDesciption() {
		return "Extension for artefact view.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		$result[] = new Person("Rolf", "Wilhelm", "party@uni-paderborn.de");
		$result[] = new Person("Ashish", "Chopra", "ashish@mail.uni-paderborn.de");
		return $result;
	}

	public function getId() {
		return "Portfolio";
	}

	public function getObjectReadableName() {
		return "Artefact";
	}

	public function getObjectReadableDescription() {
		return "Artefacts for Portfolios.";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/folder.png";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Portfolio\Commands\NewArtefactForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject, $method = "view"){
		$object = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		if ($object instanceof steam_room && $object->get_attribute("OBJ_TYPE") == PORTFOLIO_PREFIX . "ARTEFACT") {
			if ($method == "competences"){
				return new \Portfolio\Commands\SetCompetence();
			}
			return new \Portfolio\Commands\ViewArtefact();
		}

	}

	public function getPriority() {
		return 20;
	}
}
?>