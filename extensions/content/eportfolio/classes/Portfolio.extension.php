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
						"ajax"=>
						array("onclick"=>
								array("command"=>"Sanctions", "params"=>array("id"=>"1"), "requestType"=>"popup"))),
				array("name"=>"Export der Belege",
						"ajax"=>
						array("onclick"=>
								array("command"=>"Sanctions", "params"=>array("id"=>"1"), "requestType"=>"popup"))),
				array("name"=>"Drucken",
						"ajax"=>
						array("onclick"=>
								array("command"=>"Sanctions", "params"=>array("id"=>"1"), "requestType"=>"popup")))
		));
		return $actionBar;
	}

	public static function getTabBar(){
		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(
				array(
						array("name"=>"Kompetenzportfolio", "link"=>\Portfolio::getInstance()->getExtensionUrl() . "/"),
						array("name"=>"Bildungbiographie", "link"=>\Portfolio::getInstance()->getExtensionUrl() . "biography/"),
						array("name"=>"Kompetenzübersicht", "link"=>\Portfolio::getInstance()->getExtensionUrl() . "achieved/"),
						array("name"=>"Kommentare", "link"=>\Portfolio::getInstance()->getExtensionUrl() . "comments/"),
						array("name"=>"Kompetenzmodell", "link"=>\Portfolio::getInstance()->getExtensionUrl() . "competences/")
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