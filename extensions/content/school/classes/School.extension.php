<?php 
class School extends AbstractExtension implements IObjectModelExtension {
	
	public function getName() {
		return "School";
	}
	
	public function getDesciption() {
		return "Extension for schools.";
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
		$objectModels[] = "\School\Model\SchoolBookmark";
		$objectModels[] = "\School\Model\FolderSchoolBookmark";
		return $objectModels;
	}
}
?>