<?php
class Wiki extends AbstractExtension implements IObjectExtension{

	public function getName() {
		return "Wiki";
	}

	public function getDesciption() {
		return "Extension for Wiki View.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Petertonkoker", "Jan", "janp@mail.uni-paderborn.de");
		return $result;
	}

	public function getObjectReadableName() {
		return "Wiki/Glossar/A-Z Liste";
	}

	public function getObjectReadableDescription() {
		return "In Wikis können Benutzer gemeinsam an einem Projekt arbeiten. Die Versionsverwaltung ermöglicht es, den Entstehungsprozess jedes Eintrags nachvollziehen zu können.";
	}

	public function getObjectIconUrl() {
		return $this->getAssetUrl() . "icons/wiki.png";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Wiki\Commands\NewWikiForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$wikiObject = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		$objectType = $wikiObject->get_attribute("OBJ_TYPE");
		if ($objectType != "0" && $objectType == "container_wiki_koala") {
			return new \Wiki\Commands\Index();
		}
		return null;
	}

	public function getPriority() {
		return 8;
	}
}
?>
