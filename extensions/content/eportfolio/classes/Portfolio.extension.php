<?php
class Portfolio extends AbstractExtension implements IObjectExtension {

	public static function getActionBar(){
		$actionBar = new \Widgets\ActionBar();
		$actionBar->setActions(array(
				//array("name"=>"Bildungsbiographie", "ajax"=>array("onclick"=>array("command"=>"properties", "params"=>array("id"=>"1"), "requestType"=>"popup"))),
				//array("name"=>"Kompetenzübersicht", "link"=>self::getInstance()->getExtensionUrl() . "competences/"),
				//array("name"=>"Kommentare", "ajax"=>array("onclick"=>array("command"=>"newElement", "params"=>array("id"=>"1"), "requestType"=>"popup"))),
				//array("name"=>"Kompetenzmodell", "ajax"=>array("onclick"=>array("command"=>"newElement", "params"=>array("id"=>"1"), "requestType"=>"popup"))),
				array("name"=>"Import der Belege",
						"onclick"=>"alert('Diese Funktion steht im Moment nicht zur Verfügung.');return false;"),
				array("name"=>"Export der Belege",
						"onclick"=>"alert('Diese Funktion steht im Moment nicht zur Verfügung.');return false;"),
				array("name"=>"Drucken",
						"onclick"=>"window.print()")
		));
		return $actionBar;
	}

	public static function getTabBar($userName){
		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(
				array(
						array("name"=>"Kompetenzportfolio", "link"=>\Portfolio::getInstance()->getExtensionUrl() . "/index/" . $userName),
						array("name"=>"Bildungsbiographie", "link"=>\Portfolio::getInstance()->getExtensionUrl() . "biography/" . $userName),
						array("name"=>"Kompetenzübersicht", "link"=>\Portfolio::getInstance()->getExtensionUrl() . "achieved/" . $userName),
						array("name"=>"Kommentare", "link"=>\Portfolio::getInstance()->getExtensionUrl() . "comments/" . $userName),
						array("name"=>"Kompetenzmodell", "link"=>\Portfolio::getInstance()->getExtensionUrl() . "competences/" . $userName),
						array("name"=>"Beschreibungen", "link"=>\Portfolio::getInstance()->getExtensionUrl() . "descriptions/" . $userName . "/?job=CK")
				));
		return $tabBar;
	}

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