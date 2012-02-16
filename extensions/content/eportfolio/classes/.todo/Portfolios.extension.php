<?php
class Portfolios extends AbstractExtension implements IObjectExtension {
	
	public function getName() {
		return "Portfolios";
	}
	
	public function getDesciption() {
		return "Extension for portfolio view.";
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject, $method = "view"){
		$object = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		if ($method == "competences"){
			return new \Portfolio\Commands\SetCompetence();
		}
		if ($object instanceof steam_room && $object->get_attribute(PORTFOLIO_PREFIX . "TYPE") === "PORTFOLIO") {
			return new \Portfolio\Commands\ViewPortfolio();
		}
		else if ($object instanceof steam_room && $object->get_attribute(PORTFOLIO_PREFIX . "TYPE") === "ARTEFACT") {
			return new \Portfolio\Commands\ViewArtefact();
		}
	}

	/*
	public static function getTabBar() {
		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(array(array("name"=>gettext("Dashboard"), "link"=>$this->getextension()->getExtensionUrl()."/"), array("name"=>gettext("Portfolio"), "link"=>$this->getExtension()->getExtensionUrl() . "myportfolio/"), array("name"=>gettext("Shared Portfolios"), "link"=>$this->getExtension()->getExtensionUrl() . "SharedProfiles/")));
		return $tabBar;
	}
	*/
	
	public function getObjectReadableName() {
		return null;
	}
	
	public function getObjectReadableDescription() {
		return null;
	}
	
	public function getObjectIconUrl() {
		return null;
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return null;
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
}
?>