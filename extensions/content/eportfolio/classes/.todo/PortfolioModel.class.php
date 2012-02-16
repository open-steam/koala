<?php
class PortfolioModel extends PortfolioExtensionModel {

	public static function getPortfoliosContainer() {
		if (!array_key_exists(PORTFOLIO_PREFIX . "PortfoliosContainer", $_SESSION)){
			$user = lms_steam::get_current_user();
			$_SESSION[ PORTFOLIO_PREFIX . "PortfoliosContainer" ] = $user->get_workroom()->get_object_by_name("portfolio")->get_object_by_name("portfolios");
		}
		return $_SESSION[ PORTFOLIO_PREFIX . "PortfoliosContainer" ];
	}
	
	public static function getMyPortfolios() {
		$portfolios = array();
		$user = lms_steam::get_current_user();
		$workroom = $user->get_workroom();
		if (!($workroom->get_object_by_name("portfolio") instanceof steam_room)){
			error_log("portfolio room is noch steam_room object");
		}
		$moduleRoom = $workroom->get_object_by_name("portfolio");
		$portfoliosRoom = $moduleRoom->get_object_by_name("portfolios");
		$portfolios = $portfoliosRoom->get_inventory(CLASS_ROOM);
		$portfolioObjectsArray = array();
		foreach ($portfolios as $portfolio) {
			$portfolioObjectsArray[]=new PortfolioModel($portfolio);
		}
		return $portfolioObjectsArray;
	}
	
	public static function getLatestPortfolios($count = 10) {
		$portfolios = array();
		$user = lms_steam::get_current_user();
		$workroom = $user->get_workroom();
		if (!($workroom->get_object_by_name("portfolio") instanceof steam_room)){
			error_log("portfolio room is noch steam_room object");
		}
		$moduleRoom = $workroom->get_object_by_name("portfolio");
		$portfoliosRoom = $moduleRoom->get_object_by_name("portfolios");
		$portfolios = $portfoliosRoom->get_inventory_filtered(
		    array(array( '+', 'class', CLASS_ROOM )),
		    array(
		    	array( '>', 'attribute', 'OBJ_CREATION_TIME' ),
		    	array( '>', 'attribute', 'OBJ_LAST_CHANGED' )
		  		)
		    );
		$portfolioObjectsArray = array();
		$i = 0;
		foreach ($portfolios as $portfolio) {
			$i++;
			if ($i > $count)
				break;
			$portfolioObjectsArray[]=new PortfolioModel($portfolio);
		}
		return $portfolioObjectsArray;
	}
		
	public static function create($name) {

		//Create Room
		$newPortfolio = steam_factory::create_room(
			$GLOBALS[ "STEAM" ]->get_id(),
			$name,
			self::getPortfoliosContainer(),
			$name
		);
		$newPortfolio->set_attribute(PORTFOLIO_PREFIX . "TYPE", "PORTFOLIO");
		$newPortfolio->set_attribute("OBJ_TYPE", PORTFOLIO_PREFIX . "PORTFOLIO");
		$newPortfolio = new PortfolioModel($newPortfolio);
		//Create Forum
		$newPortfolio->createForum();
		
		//Create Groups
//		$newPortfolio->createGroups();
		
		return $newPortfolio;
	}

	public static function getById($id){
		$steamObject = steam_factory::get_object(
			$GLOBALS[ "STEAM" ]->get_id(),
			$id
		);
		return new PortfolioModel($steamObject);
	}
	
	public function addArtefact($artefact){
		$artefact->createLinkObject($this->getRoom());
	}
	
	public function removeArtefact($artefact){
		$artefactsLinks = $this->getRoom()->get_inventory(CLASS_LINK);
		$artefact_id = $artefact->get_id();
		foreach ($artefactsLinks as $artefactLink) {
			if ($artefact_id == $artefactLink->get_destination()->get_id()){
				$artefactLink->delete();
			}
		}
	}

	public function getArtefacts(){
		$artefactLinks = $this->getRoom()->get_inventory(CLASS_LINK);
		$artefacts = array();
		foreach ($artefactLinks as $link) {
			$artefacts []= new ArtefactModel($link->get_link_object());
		}
		return $artefacts;
	}
	

	public function setGoal($goal){
		$this->getRoom()->set_attribute(PORTFOLIO_PREFIX . "GOAL", $goal);
	}

	public function getGoal(){
		$this->getRoom()->get_attribute(PORTFOLIO_PREFIX . "GOAL");
	}
	
//	public function getForum(){
//		return $this->forum; 
//	}

	public function getTypeString(){
		return "portfolios";
	}
	
	public function delete(){
		$artefacts = $this->getArtefacts();
		$room = $this->getRoom();
		foreach ($artefacts as $artefactLink) {
			$artefact = new ArtefactModel($artefactLink->get_link_object());
			$artefact->removeLinkedLocation($room);
		}
		$objectAuthorizationsGroup = $this->getAuthorizeGroupParent();
		if ($objectAuthorizationsGroup != false)
			$objectAuthorizationsGroup->delete();
		$this->getRoom()->delete();
	}
	
	public function count(){
		return $this->getRoom()->count_inventory();
	}
	
	
}
?>