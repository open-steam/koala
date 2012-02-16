<?php 
class SchoolHome extends AbstractExtension implements IHomeExtension {
	
	public function getName() {
		return "SchoolHome";
	}
	
	public function getDesciption() {
		return "Home Extension for school bookmarks.";
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
		$box->setTitle("Meine Schule");
		$box->setTitleLink(PATH_URL . "school/");
		$box->setCustomStyle("width: 375px; height: 215px; float: left; clear: none");
		$loader = new \Widgets\DivLoader();
		$loader->setWrapperId("schoolBookmarksWrapper");
		$loader->setMessage("Lade meine Schule ...");
		$loader->setCommand("loadRecentSchoolBookmarks");
		$loader->setNamespace("School");
		$object = \School\Model\FolderSchoolBookmark::getSchoolBookmarkFolderObject();
		$loader->setParams(array("id"=>$object->get_id()));
		$loader->setElementId("schoolBookmarksWrapper");
		$loader->setType("updater");
		$box->addWidget($loader);
		$box->setContent($loader->getHtml());
		$box->setContentMoreLink(PATH_URL . "school/");
		return $box;
	}
}
?>