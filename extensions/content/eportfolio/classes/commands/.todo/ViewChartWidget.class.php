<?php
namespace Portfolio\Commands;
class ViewChart1 extends \AbstractCommand implements \IFrameCommand {

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {

	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name"=>\Portfolio::getInstance()->getText("My Competences"))));
		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(array(array("name"=>\Portfolio::getInstance()->getText("Dashboard"), "link"=>$this->getextension()->getExtensionUrl()."/"), array("name"=>\Portfolio::getInstance()->getText("Competences"), "link"=>$this->getExtension()->getExtensionUrl() . "ViewChart/"), array("name"=>\Portfolio::getInstance()->getText("Artefacts"), "link"=>$this->getExtension()->getExtensionUrl() . "ManageArtefacts/")));
		$tabBar->setActiveTab(1);

		$jobs = \CompetenceRaster::getJobs();
		$facets = \CompetenceRaster::getFacets();

		
		$json_chart = array(
			
		);
		
		
		$html = <<< END
	    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">
		google.load('visualization', '1', {packages: ['corechart']});
		</script>
END;
		foreach ($jobs as $job) {
			$name = $job->name;
			$html .= <<< END
			<script type="text/javascript">
			function drawVisualization{$name}() {
END;
			$data =  "[['Activity', '1', '2', '3', '4', '5', '6', '7'],";
			foreach ($facets as $facetObject) {
				$facet = $facetObject->short;
				$data .= "[\"$facet\", ";
				for ($i = 0; $i < 7; $i++) {
					$data .= count(\CompetenceRaster::getCompetences($job->name, $i, $facet)) .",";
				}
				$data .= "],";
			}
			$data .= "]";
			//var_dump($data);
			$html .= <<< END
				// Some raw data (not necessarily accurate)
				var data{$name} = google.visualization.arrayToDataTable({$data});
	
				// Create and draw the visualization.
				var comboChart{$name} = new google.visualization.ComboChart(document.getElementById('chart_div{$name}'));
				comboChart{$name}.draw(data{$name}, {
					title : 'Kompetenzen mit Ausbildungsziel {$job->description}',
					vAxis: {title: "Anzahl"},
					hAxis: {title: "Facetten"},
					 
					seriesType: "bars",
					//series: {4: {type: "line"}}
				});
				 
				google.visualization.events.addListener(comboChart{$name}, 'select' , function(){
					var row = comboChart{$name}.getSelection()[0].row;
					var column = comboChart{$name}.getSelection()[0].column
					var url = "/portfolio/ViewCompetence/?";
					var activity = 1 + row; //TODO
					switch (row) { //TODO
						case 0:
							facet = "W";
							break;
	
						case 1:
							facet = "F";
							break;
							 
						case 2:
							facet = "K";
							break;
							 
						case 3:
							facet = "M";
							break;
	
	
					}
				    url = url + "job={$name}" + "&activity=" + activity + "&facet=" + facet;
				    window.location = url;
				});
			}
			google.setOnLoadCallback(drawVisualization{$name});
			</script>
			<div id="chart_div{$name}" style="width:940px; height: 400px;"></div>		
END;
//break;
		}
		$frameResponseObject->setTitle("Discuss View");
		$rawHtml = new \Widgets\RawHtml();
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget($tabBar);
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>