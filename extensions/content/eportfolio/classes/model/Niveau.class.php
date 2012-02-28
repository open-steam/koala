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
		$this->job = $description;
		$this->activityObject = $activityObject;
	}
}
?>