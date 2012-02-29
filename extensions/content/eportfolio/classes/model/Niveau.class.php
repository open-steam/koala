<?php
namespace Portfolio\Model;
class Niveau {
	public $niveau;
	public $description;
	public $job;
	public $activity;
	public $activityObject;
	
	public function __construct($activity, $niveau, $job, $description, $activityObject) {
		$this->activity = $activity;
		$this->niveau = $niveau;
		$this->description = $description;
		$this->job = $job;
		$this->activityObject = $activityObject;
	}
	
	public function getHtml(){
 		return "<span title=\"Niveaubeschreibung: {$this->description}\" style=\"border-bottom:dotted 2px;\">{$this->niveau}</span>";
	}
}
?>