<?php 
class LastVisitedHome extends AbstractExtension implements IHomeExtension {
	
	public function getName() {
		return "LastVisitedHome";
	}
	
	public function getDesciption() {
		return "Home Extension for last visited containers.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function getWidget() {
		$box = new \Widgets\Box();
		$box->setId(\BookmarksHome::getInstance()->getId());
		$box->setTitle("zuletzt besuchte Ordner");
		$box->setTitleLink(PATH_URL . "explorer/");
		$loader = new \Widgets\DivLoader();
		$loader->setWrapperId("lastVisitedWrapper");
		$loader->setMessage("Lade Ordner ...");
		$loader->setCommand("loadLastVisited");
		$loader->setNamespace("Explorer");
		$loader->setParams(array("id"=>\lms_steam::get_current_user()->get_id()));
		$loader->setElementId("lastVisitedWrapper");
		$loader->setType("updater");
		$box->addWidget($loader);
		$box->setContent($loader->getHtml());
		$box->setContentMoreLink(PATH_URL . "explorer/");
		return $box;
	}
}
?>