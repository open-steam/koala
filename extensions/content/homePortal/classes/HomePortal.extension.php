<?php 
class HomePortal extends AbstractExtension {
	
	public function getName() {
		return "HomePortal";
	}
	
	public function getDesciption() {
		return "Extension for home portal.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Jan", "Petertonkoker", "janp@mail.uni-paderborn.de");
		return $result;
	}
	
	public function getUrlNamespaces() {
		return array(strtolower($this->getName()), "desktop");
	}
}
?>