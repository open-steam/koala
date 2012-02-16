<?php
class Portfolio extends AbstractExtension implements IObjectExtension {

	public function getName() {
		return "Portfolio";
	}

	public function getDesciption() {
		return "Extension for portfolio view.";
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
		return "Portfolio";
	}

	public function getObjectReadableDescription() {
		return "Portfolio.";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/folder.png";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Portfolio\Commands\NewPortfolioForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		return new \Portfolio\Commands\ViewPortfolio();
	}

	public function getPriority() {
		return -20;
	}
}
?>