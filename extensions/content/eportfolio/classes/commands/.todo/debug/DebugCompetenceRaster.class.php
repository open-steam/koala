<?php
namespace Portfolio\Commands;
class DebugCompetenceRaster extends \AbstractCommand implements \IFrameCommand {

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$activities = \CompetenceRaster::getActivityFields();		
		$jobs = \CompetenceRaster::getJobs();		
		$facets = \CompetenceRaster::getFacets();		
		$listViewer = new \Widgets\ListViewer();
		
		\CompetenceRaster::initReadCompetences();
		
		$steamUser = $GLOBALS["STEAM"]->get_current_steam_user();		

		//print("<pre>".print_r($activities,true)."</pre>");
		//print("<pre>".print_r(\CompetenceRaster::$competences,true)."</pre>");
		$html_text = "";
		$html_text .= "<h2>Bereiche</h2><br>";
		foreach ($activities as $activity){
			$html_text .= $activity->name .":<br>". $activity->description ."<br><br>";
		}
		$html_text .= "<h2>Jobs</h2><br>";
		foreach ($jobs as $activity){
			$html_text .= $activity->name .":<br>". $activity->description ."<br><br>";
		}
		$html_text .= "<h2>Aspekte</h2><br>";
		foreach ($facets as $activity){
			$html_text .= $activity->name .":<br>". $activity->description ."<br><br>";
		}
		$html_text .= "<h2>Kompetenzen</h2><br>";
		
		
		foreach (\CompetenceRaster::getCompetences() as $activity){
		//foreach (\CompetenceRaster::$competences as $activity){
			$html_text .= "Name: " . $activity->name .
							"<br>ID: " . $activity->job .
							"<br>facet: " . $activity->facet . 
							"<br>activity: " . $activity->activity . 
							"<br>index: " . $activity->index . "<br>".
							"Beschreibung: " . $activity->description ."<br><br>";
		}
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html_text);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>