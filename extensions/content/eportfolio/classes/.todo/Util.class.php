<?php
class Util {
/*
 * All retrieved Data is delivered in an array indexed by timestamp
 */
	
	public function getLatestInvites(){
		
	}
	
	public function getAllLatest($count = 10){
		$new = array();
		$portfolios = PortfolioModel::getLatestPortfolios();
		foreach ($portfolios as $item) {
			$timestamp = $item->get_attribute("OBJ_LAST_CHANGED");
			$new[$timestamp] = $item;
		}
		$artefacts = Artefacts::getLatestArtefacts();
		foreach ($artefacts as $item) {
			$timestamp = $item->get_attribute("OBJ_LAST_CHANGED");
			$new[$timestamp] = $item;
		}
		
	}
	
	public function getLatestChangesAtFriends(){
		
	}
	
}
?>