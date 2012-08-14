<?php
class Profile extends AbstractExtension {

	public function getName() {
		return "Profile";
	}

	public function getDesciption() {
		return "Extension for profile view.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Christoph", "Sens", "csens@mail.upb.de");
		return $result;
	}
	
	public function getUrlNamespaces() {
		return array(strtolower($this->getName()), "profile", "user");
	}
}
?>