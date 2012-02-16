<?php
class PortfolioObject extends AbstractExtension implements IObjectExtension {

	public function getName() {
		return "PortfolioObject";
	}

	public function getDesciption() {
		return "Extension for portfolio object.";
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
		/*
		 $object = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		 if ($object instanceof steam_container) {
			return new \Explorer\Commands\Index();
			}

		 */
		return new \Portfolio\Commands\ViewPortfolio();
	}

	public function getPriority() {
		return -20;
	}
}
?>