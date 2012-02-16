<?php
namespace Portfolio\Commands;
use Widgets\Checkbox;

class ViewCompetence extends \AbstractCommand implements \IFrameCommand {

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	private $jobs;
	private $facet;
	private $activity;



	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->jobs = $this->params[0]: "";
		isset($this->params[1]) ? $this->activity = $this->params[1]: "";
		isset($this->params[2]) ? $this->facet = $this->params[2]: "";
	}


	public function frameResponse(\FrameResponseObject $frameResponseObject) {

		\CompetenceRaster::initReadCompetences();
		$comptetences = \CompetenceRaster::$competences;
		$comptetences = \CompetenceRaster::getCompetences($this->jobs,$this->facet,$this->activity);
			

		$jobs = \CompetenceRaster::getJobs();
		//$activity = \CompetenceRaster::getActivityFields();
		$facets = \CompetenceRaster::getFacets();
		$html = "";
		foreach ($jobs as $job) {
			if (!($this->jobs == "" || $this->jobs == $job->name))
			continue;
			$html .= "<h2>" . $job->name . "</h2>" ;
			$html .= '<table  class="grid">';
			//foreach ($activity as $activity) {
				//if (!($this->activity == "" || $this->activity == $activity->name))
					//continue;
			foreach ($facets as $facet) {
				if (!($this->facet == "" || $this->facet == $facet->name))
					continue;
				$html .= "<tr><td colspan=7><h4>" . $facet->name . "</h4></td><tr>" ;
				$comptetences = \CompetenceRaster::getCompetences($job->name, $facet->short);
				//$comptetences = \CompetenceRaster::getCompetences();
				foreach ($comptetences as $competence) {
					//$html .= '<table  class="grid">';
					$html .= "<tr><td>" . $competence->job . "</td>" ;
					$html .= "<td>" . $competence->facet . "</td>" ;
					$html .= "<td>" . $competence->activity . "</td>" ;
					$html .= "<td >" . $competence->index . "</td>" ;
					$html .= "<td>" . $competence->name . "</td>" ;
					//$html .= "<td>" . $competence->description . "</td>" ;
					//$html .= "<td>" . $competence->niveau . "</td>" ;
					$html .= "<td>" . "<input type=checkbox>" . "</td>" ;
					$html .= "<td>" . "......" . "</td></tr>" ;
				}
			
			}
		
	
			

			$html .= "</table>";
				
		}
		

			
		$frameResponseObject->setTitle("Competence View");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		//$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($rawHtml);

		return $frameResponseObject;
	
	}
}

?>