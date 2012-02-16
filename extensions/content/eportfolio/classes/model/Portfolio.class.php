<?php
namespace Portfolio\Model;
class Portfolio {
	
	private $user;
	private $portfolioContainer;
	private $entryContainer;
	
	private function __construct($user, $portfolioContainer, $entryContainer){
		$this->user = $user;
		$this->portfolioContainer = $portfolioContainer;
		$this->entryContainer = $entryContainer;
	}
	
	public function getAllEntries(){
		$all = $this->getEntriesContainer()->get_inventory_filtered(
				array(array( '+', 'class', CLASS_ROOM )));
		$allEntries = array();
		foreach ($all as $room) {
			$allEntries[] = Entry::getEntryByRoom($room);
		}
		return $allEntries;
	}
	
	public function getEntriesByClass($className) {
		$result = array();
		foreach($this->getAllEntries() as $entry) {
			if ($entry instanceof $className) {
				$result[] = $entry;
			}
		}
		return $result;
	}
	
	public function getLatestEntries($count = 10){
		$all = $this->getEntriesContainer()->get_inventory_filtered(array(
				array( '+', 'class', CLASS_ROOM )),
				array(
						array( '>', 'attribute', 'OBJ_CREATION_TIME' ),
						array( '>', 'attribute', 'OBJ_LAST_CHANGED' )
				)
		);
	
		$allEntries = array();
		$i = 0;
		foreach ($all as $room) {
			$i++;
			if ($i > $count)
				break;
			$allEntries[] = Entry::getEntryByRoom($room);
		}
		return $allEntries;
	}
	
	public function getEntriesByCompetence($job = null, $facet = null, $activity = null){
		$all = $this->getAllEntries();
		$filtered = array();
		foreach ($all as $entry) {
			$competences = $entry->getCompetences();
			foreach ($competences as $competence){
				if ($job == null || $competence->getJob() == $job)
					if ($activity == null || $competence->getActivity() == $activity)
					if ($facet == null || $competence->getFacet() == $facet)
					$filtered [$entry->getId()]= $entry;
			}
		}
		return $filtered;
	}
	
	public function getEntriesContainer() {
		//		if (!array_key_exists(PORTFOLIO_PREFIX . "EntriesContainer", $_SESSION)){
		//			//$user = lms_steam::get_current_user();
		//			$_SESSION[ PORTFOLIO_PREFIX . "EntriesContainer" ] = $this->user->get_workroom()->get_object_by_name("portfolio")->get_object_by_name("entries");
		//		}
		//		return $_SESSION[ PORTFOLIO_PREFIX . "EntriesContainer" ];
		return $this->entryContainer;
	}
	
	public function getId(){
		return $this->portfolioContainer->get_id();
	}
	
	public function createEntry($entryClass) {
		$newEntry = \steam_factory::create_room(
				$GLOBALS[ "STEAM" ]->get_id(),
				$entryClass,
				$this->entryContainer,
				"Entry: " + $entryClass
		);
		$newEntry->set_attribute(PORTFOLIO_PREFIX . "TYPE", "ENTRY");
		$newEntry->set_attribute(PORTFOLIO_PREFIX . "ENTRYCLASS", $entryClass);
		$newEntry->set_attribute("OBJ_TYPE", PORTFOLIO_PREFIX . "ENTRY");
	
		\steam_factory::create_room(
				$GLOBALS[ "STEAM" ]->get_id(),
				"artefacts",
				$newEntry,
				"room for artefacts"
		);
		$newEntryObject = Entry::getEntryByRoom($newEntry);
	
		return $newEntryObject;
	}
	
	public static function getInstanceForUser($user = null){
		if (!isset($user)) {
			$user = \lms_steam::get_current_user();
		}
		$elements = $user->get_workroom()->get_inventory();
		foreach ($elements as $element) {
			if ($element->get_attribute("OBJ_TYPE") === PORTFOLIO_PREFIX . "PORTFOLIOCONTAINER") {
				$portfolioContainer = $element;
				break;
			}
		}
		if (isset($portfolioContainer)){
			$entryContainer = $portfolioContainer->get_object_by_name("entries");
		} else {
			return self::init($user);
		}
		return new self($user, $portfolioContainer, $entryContainer);
	}
	
	public static function getInstanceByRoom($room){
		$portfolioContainer = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $room);
		$entryContainer = $portfolioContainer->get_object_by_name("entries");
		$user = $portfolioContainer->get_environment()->get_creator();
		return new self($user, $portfolioContainer, $entryContainer);
	}
	
	private static function init($user) {
		$workroom = $user->get_workroom();
		//create rooms
		$portfolioContainer = \steam_factory::create_room(
				$GLOBALS[ "STEAM" ]->get_id(),
				"portfolio",
				$workroom,
				"room for portfolio module"
		);
		$portfolioContainer->set_attribute("OBJ_TYPE", PORTFOLIO_PREFIX . "PORTFOLIOCONTAINER");
	
		$entryContainer = \steam_factory::create_room(
				$GLOBALS[ "STEAM" ]->get_id(),
				"entries",
				$portfolioContainer,
				"room for artefacts for portfolios"
		);
		$entryContainer->set_attribute("OBJ_TYPE", PORTFOLIO_PREFIX . "ENTRYCONTAINER");
		return new self($user, $portfolioContainer, $entryContainer);
	}
}
?>