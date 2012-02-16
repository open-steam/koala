<?php
namespace Portfolio\Commands;

class ViewCompetence extends \AbstractCommand implements \IFrameCommand {

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	private $job;
	private $facet;
	private $activity;
	private $showCollected;



	public function processData(\IRequestObject $requestObject) {

		$this->job = isset($_GET["job"]) ? $_GET["job"] : null;
		$this->facet = isset($_GET["facet"]) ? $_GET["facet"] : null;
		$this->activity = isset($_GET["activity"]) ? $_GET["activity"] : null;
		$this->index = isset($_GET["index"]) ? $_GET["index"] : null;
		$this->showCollected = isset($_GET["sc"]) ? $_GET["sc"] : null;
		$this->params = array(
			"job" => $this->job,
			"facet" => $this->facet,
			"activity" => $this->activity,
			"index" => $this->index,
			"sc" => $this->showCollected
		);
	}


	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(array(array("name"=>\Portfolio::getInstance()->getText("Dashboard"), "link"=>$this->getextension()->getExtensionUrl()."/"), array("name"=>\Portfolio::getInstance()->getText("Competences"), "link"=>$this->getExtension()->getExtensionUrl() . "ViewChart/"), array("name"=>\Portfolio::getInstance()->getText("Artefacts"), "link"=>$this->getExtension()->getExtensionUrl() . "ManageArtefacts/")));
		$tabBar->setActiveTab(1);

		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name"=>\Portfolio::getInstance()->getText("Chart"),"link"=>$this->getExtension()->getExtensionUrl() . "ViewChart/"),array("name"=>\Portfolio::getInstance()->getText("View Competance"))));

		\CompetenceRaster::initReadCompetences();
		$comptetences = \CompetenceRaster::$competences;
			
		$collected = \CompetenceRaster::getCollectedCompetences();

		$jobs = \CompetenceRaster::getJobs();
		$activities = \CompetenceRaster::getActivityFields();
		$facets = \CompetenceRaster::getFacets();
		$html = "<br>";
		if ($this->showCollected) {
			$html .= "<h1>Gesammelte Kompetenzen</h1>";
		} else {
			$html .= "<h1>Offene Kompetenzen</h1>";
		}
		foreach ($jobs as $job) {
			if (!($this->job == "" || $this->job == $job->name))
			continue;
			$html .= "<h2>" . $job->description . "</h2>" ;
			$html .= '<table  class="grid">';
			foreach ($activities as $activity) {
				if (!($this->activity == "" || $this->activity == $activity->short))
				continue;
				$html .= "<tr><td colspan=7><h4>" . $activity->name . "</h4></td><tr>" ;
				foreach ($facets as $facet) {
					if (!($this->facet == "" || $this->facet == $facet->short))
					continue;
					$comptetences = \CompetenceRaster::getCompetences($job->name, $activity->short, $facet->short);
					foreach ($comptetences as $competence) {
						$url = "#";
						$text = "";
						if (!$this->showCollected && isset($collected[$job->name . $activity->short . $facet->short . $competence->getIndex()])){
							continue;
						} elseif ($this->showCollected && !isset($collected[$job->name . $activity->short . $facet->short . $competence->getIndex()])){
							//$artefact = Artefacts::getById($collected[$job->name . $activity->short . $facet->short . $competence->getIndex() . "ID"]);
							continue;
						}
						if ($this->showCollected && isset($collected[$job->name . $activity->short . $facet->short . $competence->getIndex()])){
							$artefactId = $collected[$job->name . $activity->short . $facet->short . $competence->getIndex() . "ID"];
							$text = "Artefakt";
							$url = \ExtensionMaster::getInstance()->getUrlForObjectId($artefactId, "view");
						}
						$checked = isset($collected[$job->name . $activity->short . $facet->short . $competence->getIndex()]) ? "checked=true" : "";
						$html .= "<tr><td>" . $competence->job . "</td>" ;
						$html .= "<td>" . $competence->activity . "</td>" ;
						$html .= "<td>" . $competence->facet . "</td>" ;
						$html .= "<td >" . $competence->index . "</td>" ;
						$html .= "<td>" . $competence->name . "</td>" ;
						$html .= "<td>" . "<input type=checkbox disabled=true " . $checked. "</td>" ;
						$html .= "<td><a href=\"" . $url . "\">" . $text . "</a></td></tr>" ;
					}
				}
			}
			$html .= "</table>";
		}



		$frameResponseObject->setTitle("Competence View");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget($tabBar);
		$frameResponseObject->addWidget($rawHtml);

		return $frameResponseObject;

	}
}

?>