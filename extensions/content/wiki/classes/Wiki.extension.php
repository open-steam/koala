<?php

class Wiki extends AbstractExtension {
	
	public function getName() {
		return "Wiki";
	}
	
	public function getDesciption() {
		return "Extension for wiki view.";
	}

	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] =array( new Person("Niroshan", "Thillainathan", "n.thillainathan@campus.uni-paderborn.de")
							, new Person("Christoph", "Sens", "csens@mail.upb.de"));
		return $result;
	}
}
?>