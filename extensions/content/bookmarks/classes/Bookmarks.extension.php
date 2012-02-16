<?php 
class Bookmarks extends AbstractExtension implements IObjectModelExtension {
	
	public function getName() {
		return "Bookmarks";
	}
	
	public function getDesciption() {
		return "Extension for Bookmarks.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function getObjectModels() {
		$objectModels = array();
		$objectModels[] = "\Bookmarks\Model\Bookmark";
		$objectModels[] = "\Bookmarks\Model\FolderBookmark";
		return $objectModels;
	}
}
?>