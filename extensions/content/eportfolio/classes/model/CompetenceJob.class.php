<?php
namespace Portfolio\Model;
class CompetenceJob {
	public $name;
	public $description;

	public function __construct($name, $description) {
		$this->name = $name;
		$this->description = $description;
	}

	public static function getJobByName($name){
		$jobs = CompetenceRaster::getJobs();
		foreach ($jobs as $job){
			if ($job->name == $name)
				return $job;
		}
		return null;
	}
}
?>