<?php 
include_once PATH_LIB . "steam_handling.inc.php";
class SignIn  extends AbstractExtension {
	
	public function getName() {
		return "SignIn";
	}
	
	public function getDesciption() {
		return "Extension for sign-in and sign-out handling.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function getDefaultCommandName($urlNamespace) {
		if ($urlNamespace == "signout") {
			return "SignOut";
		}
		return "SignIn";
	}
	
	public function getUrlNamespaces() {
		return array("signin", "signout");
	}
}
?>