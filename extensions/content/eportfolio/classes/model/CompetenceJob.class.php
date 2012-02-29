<?php
namespace Portfolio\Model;
class CompetenceJob {
	public $name;
	public $description;

	public function __construct($name, $description) {
		$this->name = $name;
		$this->description = $description;
	}

	public function getJobDescriptionHtml(){
		return "<div style=\"font-size:80%\">{$this->description}</div>";
	}
}
?>