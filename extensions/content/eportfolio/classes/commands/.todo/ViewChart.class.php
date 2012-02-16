<?php
namespace Portfolio\Commands;
class ViewChart extends \AbstractCommand implements \IFrameCommand {

	private $activities;
	private $jobs;
	private $facets;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->jobs = isset($_GET["jobs"]) ? explode(",",$_GET["jobs"]) : explode(",","BA,CL,CT,CK,IC");
		$this->facets = isset($_GET["facets"]) ? explode(",",$_GET["facets"]) : explode(",","W,F,K,M");
		$this->activities = isset($_GET["activities"]) ? explode(",",$_GET["activities"]) : explode(",","1,2,3,4,5,6,7");
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name"=>\Portfolio::getInstance()->getText("My Competences"))));
		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(array(array("name"=>\Portfolio::getInstance()->getText("Dashboard"), "link"=>$this->getextension()->getExtensionUrl()."/"), array("name"=>\Portfolio::getInstance()->getText("Competences"), "link"=>$this->getExtension()->getExtensionUrl() . "ViewChart/"), array("name"=>\Portfolio::getInstance()->getText("Artefacts"), "link"=>$this->getExtension()->getExtensionUrl() . "ManageArtefacts/")));
		$tabBar->setActiveTab(1);
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget($tabBar);

		$jobs = \CompetenceRaster::getJobs();

		$html = '<div align="right"><select id="jobs"><option value="all">Beruf wählen</option><option value="all">Alle</option>';
		foreach ($jobs as $job) {
			$html .= '<option value="' . $job->name . '">' . $job->name .": ". $job->description .  '</option>';
		}

		$html .= <<<END
		</select></div>
		<script>
		function filter(){
			$(".chart").hide();
		    job = $('select[id="jobs"] option:selected').val();
		    if (job == "all"){
				$(".chart").fadeIn();
			}
	    	$("#div_" + job + "_1").fadeIn();
	    	$('#div_' + job ).fadeIn();
		}
		$('select[id="jobs"]').change(function() {
			filter();
		});
		</script>
END;
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($rawHtml);

		$arrayArtefactCompetencesCount = \CompetenceRaster::getCollectedCompetences();
		foreach ($this->jobs as $job){
//			$urlEventArray = array();
//			$row = 0;
			$baseUrl = "/portfolio/ViewCompetence/?job=" . $job;
//
			$jobObject = \CompetenceJob::getJobByName($job);
//			$jsonChart = array();
//			$chartWidget = new \Widgets\Chart();
//			$headerActivitiesArray = array("Aktivitäten");
//			foreach ($this->activities as $activity){
//				$headerActivitiesArray []= "Tätigkeitsfeld " . $activity;
//			}
//			$jsonChart []= $headerActivitiesArray;
//			foreach ($this->facets as $facet) {
//				$facetArray = array($facet);
//				$column = 0;
//				foreach ($this->activities as $activity){
//					$facetArray []= isset($arrayArtefactCompetencesCount[$job . $activity . $facet]) ? $arrayArtefactCompetencesCount[$job . $activity . $facet] : 0;
//					$urlEventArray [$row][$column]= $baseUrl . "&activity=" . $column . "&facet=" . $facet . "&sc=1";
//					$column++;
//				}
//				$jsonChart []= $facetArray;
//				$row++;
//			}
//
//			$chartWidget->setData(json_encode($jsonChart));
//			$chartWidget->setUrlData(json_encode($urlEventArray));
//			$chartWidget->setDescription($job);
//			$chartWidget->setVAxisTitle("Facetten");
//			$chartWidget->setHAxisTitle("Anzahl");
//			$chartWidget->setId($job);
//			$chartWidget->setTitle($jobObject->description . " - Absolute Anzahl an Kompetenzen");
//
//			$frameResponseObject->addWidget($chartWidget);
			$urlEventArray = array();
			$row = 0;

			$arrayCompetencesQuantity = \CompetenceRaster::getCompetencesQuantity();

			$jsonChart = array();
			$chartWidget = new \Widgets\Chart();
			$headerActivitiesArray = array("Aktivitäten");
			$headerActivitiesArray []= "IST";
			$headerActivitiesArray []= "SOLL";
			$jsonChart []= $headerActivitiesArray;
			foreach ($this->activities as $activity) {
				$activityArray = array($activity);
				$quantityAll = isset($arrayCompetencesQuantity[$job . $activity]) ? $arrayCompetencesQuantity[$job . $activity] : 0 ;
				$absoluteCredit = isset($arrayArtefactCompetencesCount[$job . $activity]) ? $arrayArtefactCompetencesCount[$job . $activity] : 0 ;
				$absoluteDebit = $quantityAll - $absoluteCredit;
				$percentCredit = ($absoluteDebit == 0) ? 0 : round(($absoluteCredit / $quantityAll) * 100, 2);
				$percentDebit = ($absoluteDebit == 0) ? 0 : - 100 + $percentCredit;
				$activityArray []= $absoluteCredit;
				$activityArray []= $absoluteDebit;
				$urlEventArray [$row]= array(1 => $baseUrl . "&activity=" . ($activity) . "&sc=1" , 2 => $baseUrl . "&activity=" . ($activity) . "&sc=0");
				$row++;
				$jsonChart []= $activityArray;
			}

			$chartWidget->setData(json_encode($jsonChart));
			$chartWidget->setUrlData(json_encode($urlEventArray));
			$chartWidget->setDescription($job);
			$chartWidget->setVAxisTitle("Tätigkeitsfelder");
			$chartWidget->setHAxisTitle("Anzahl erreicht / noch zu erledigen");
			$chartWidget->setId($job);
			$chartWidget->setTitle($jobObject->description . " - Absolut");

			$frameResponseObject->addWidget($chartWidget);
			
			######################################
			$urlEventArray1 = array();
			$row = 0;

			$arrayCompetencesQuantity = \CompetenceRaster::getCompetencesQuantity();

			$jsonChart1 = array();
			$chartWidget1 = new \Widgets\Chart();
			$headerActivitiesArray1 = array("Aktivitäten");
			$headerActivitiesArray1 []= "IST";
			$headerActivitiesArray1 []= "SOLL";
			$jsonChart1 []= $headerActivitiesArray1;
			foreach ($this->activities as $activity) {
				$activityArray1 = array($activity);
				$quantityAll = isset($arrayCompetencesQuantity[$job . $activity]) ? $arrayCompetencesQuantity[$job . $activity] : 0 ;
				$absoluteCredit = isset($arrayArtefactCompetencesCount[$job . $activity]) ? $arrayArtefactCompetencesCount[$job . $activity] : 0 ;
				$absoluteDebit = $quantityAll - $absoluteCredit;
				$percentCredit = ($absoluteDebit == 0) ? 0 : round(($absoluteCredit / $quantityAll) * 100, 2);
				$percentDebit = ($absoluteDebit == 0) ? 0 : - 100 + $percentCredit;
				$activityArray1 []= $percentCredit;
				$activityArray1 []= $percentDebit;
				$urlEventArray1 [$row]= array(1 => $baseUrl . "&activity=" . ($activity) . "&sc=1" , 2 => $baseUrl . "&activity=" . ($activity) . "&sc=0");
				$row++;
				$jsonChart1 []= $activityArray1;
			}

			$chartWidget1->setData(json_encode($jsonChart1));
			$chartWidget1->setUrlData(json_encode($urlEventArray1));
			$chartWidget1->setDescription($job);
			$chartWidget1->setVAxisTitle("Tätigkeitsfelder");
			$chartWidget1->setHAxisTitle("Prozent erreicht / noch zu erledigen");
			$chartWidget1->setId($job . "_1");
			$chartWidget1->setTitle($jobObject->description . " - Prozentual");

			$frameResponseObject->addWidget($chartWidget1);

		}
		$rawHtml1 = new \Widgets\RawHtml();
		$rawHtml1->setHtml("<script>$(\".chart\").fadeOut();$('#div_CL_1').fadeIn();$('#div_CL').fadeIn();
		</script>");
		$frameResponseObject->addWidget($rawHtml1);

		return $frameResponseObject;
	}
}
?>