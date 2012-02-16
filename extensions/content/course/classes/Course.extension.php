<?php
class Course extends AbstractExtension {
	
	public function getName() {
		return "Course";
	}
	
	public function getDesciption() {
		return "Extension for course view.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Christoph", "Sens", "csens@mail.upb.de");
		return $result;
	}
}
?>