<?php 
class DocumentsHome extends AbstractExtension implements IHomeExtension {
	
	public function getName() {
		return "DocumentsHome";
	}
	
	public function getDesciption() {
		return "Home Extension for documents.";
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
		$box->setTitle("Meine Dokumente");
		$box->setTitleLink(PATH_URL . "explorer/");
		$loader = new \Widgets\DivLoader();
		$loader->setWrapperId("documentsWrapper");
		$loader->setMessage("Lade Dokumente ...");
		$loader->setCommand("loadRecentDocuments");
		$loader->setNamespace("Explorer");
		$loader->setParams(array("id"=>$GLOBALS["STEAM"]->get_current_steam_user()->get_workroom()->get_id()));
		$loader->setElementId("documentsWrapper");
		$loader->setType("updater");
		$box->addWidget($loader);
		$box->setContent($loader->getHtml());
		$box->setContentMoreLink(PATH_URL . "explorer/");
		return $box;
	}
}
?>