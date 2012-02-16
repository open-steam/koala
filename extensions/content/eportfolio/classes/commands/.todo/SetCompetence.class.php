<?php
/*
 * wizard like setting competences for artefacts
 */
namespace Portfolio\Commands;
class SetCompetence extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;
	private $artefactId;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->artefactId = $this->params[0]: "";
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		if (!$this->artefactId){
			print "no object id given!";
			exit;
		}
		$artefact = \Artefacts::getById($this->artefactId);

		$actionBar = new \Widgets\ActionBar();
		$actionBar->setActions(array(
		array("link" => "javascript:history.back()","name" => "zurück"))
		);

		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(
		array(
		array("name"=>\Portfolio::getInstance()->getText("Dashboard"), "link"=>$this->getextension()->getExtensionUrl()."/"),
		array("name"=>\Portfolio::getInstance()->getText("Competences"), "link"=>$this->getExtension()->getExtensionUrl() . "ViewChart/"),
		array("name"=>\Portfolio::getInstance()->getText("Artefacts"), "link"=>$this->getExtension()->getExtensionUrl() . "ManageArtefacts/"),
		array("name"=>$artefact->getName(), "link"=>"#")));
		$tabBar->setActiveTab(3);

		$competences = \CompetenceRaster::getCompetences();
		$jobs = \CompetenceRaster::getJobs();
		$facets = \CompetenceRaster::getFacets();
		$activities = \CompetenceRaster::getActivityFields();
		$html = "<br>";
		$htmlPre = "";
		$htmlPost = "";

		//		$jswrapper = new \Widgets\JSWrapper();
		$htmlPost .= <<<END
		<br>
    <h1>Weitere Kompetenzen zuordnen</h1>
	Suche: <input id="search" type="text" value="" title="Suche mit Enter-Taste bestätigen."/>
    <select id="jobs">
END
		;
		$htmlPost .= '<option value="undefined">Job Auswahl</option>';
		foreach ($jobs as $job) {
			$htmlPost .= '<option value="' . $job->name . '">' . $job->name .": ". $job->description .  '</option>';
		}
		$htmlPost .= "</select>";
		//		$htmlPost .= <<<END
		//
		//    </select>
		//    <select id="facets">
		//END
		//		;
		//
		//		$htmlPost .= '<option value="undefined">Facette Auswahl</option>';
		//		foreach ($facets as $facet) {
		//			$htmlPost .= '<option value="' . $facet->short . '">' . $facet->short .": ". $facet->name .  '</option>';
		//		}
		$htmlPost .= <<<END
    
    <select style="width:175px;" id="activities">
END
		;
		$htmlPost .= '<option value="undefined">Fertigkeiten Auswahl</option>';
		foreach ($activities as $activity) {
			$htmlPost .= '<option value="' . $activity->index . '">' . $activity->index .": ". $activity->name . '</option>';
		}
		$htmlPost .= <<<END
    
    </select>
END
		;

		$htmlPre .= '<h1>Zugeordnete Kompetenzen</h1><div id="itemsChecked"><table id="checkedRows" width=100% class="grid">';
		$htmlPre .= '<tr id="headline">
				<td>Index</td>
				<td>Beschreibung</td>
				<td></td>
			</tr>';
		//				<td>Activity</td>
		//				<td>Facet</td>
		//				<td>Index</td>
		//				<td>Description</td>
		$htmlPost .= '<div id="items"><table id="uncheckedRows" width=100% class="grid">';
		$htmlPost .= '<tr id="headline">
				<td>Index</td>
				<td>Beschreibung</td>
				<td></td>
			</tr>';
		foreach ($competences as $competence) {
			$checked = key_exists($competence->short, $artefact->getCompetencesStrings()) ? " checked=\"true\"" : "";
			$tmp = "<tr short=\"{$competence->short}\" job=\"{$competence->job}\" facet=\"{$competence->facet}\" activity=\"{$competence->activity}\">
						<td>{$competence->short}</td>
						<td>{$competence->name}</td>
						<td><input value=\"{$competence->short}\" type=\"checkbox\" name=\"selected[]\"{$checked}></td>
						</tr>";
			//						<td>{$competence->job}</td>
			//						<td>{$competence->activity}</td>
			//						<td>{$competence->facet}</td>
			//						<td>{$competence->index}</td>
			//						<td>{$competence->description}</td>
			if ($checked != ""){
				$htmlPre .=	 $tmp;
			} else {
				$htmlPost .= $tmp;
			}
		}
		$htmlPre .= '</table>';
		$htmlPost .= '<tr id="nothing">
				<td align="center">Keine Kompetenzen gefunden welche den Kriterien entsprechen</td>
			</tr>';
		$html .= $htmlPre . $htmlPost;
		$html .= <<<END
</table>
</div>
<script type="text/javascript">
jQuery.expr[':'].Contains = function(a, i, m) { 
  return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0; 
};

$("#items").fadeOut();

function filter(){
	$("#items").fadeOut();
	activity = $('select[id="activities"] option:selected').val();
    job = $('select[id="jobs"] option:selected').val();
    facet = $('select[id="facets"] option:selected').val();
    search = $('input[id="search"]').val();
    $("#items").find("tr").show();
    if (job != "undefined"){
    	$("#items").find('tr[job!='+job+']').hide();
    	}
    if (activity != "undefined"){
    	$("#items").find('tr[activity!='+activity+']').hide();
    	}
//    if (facet != "undefined"){
//    	$("#items").find('tr[facet!='+facet+']').hide();
//    	}
    if (search != ""){
    	$("#items").find("tr").not('tr:Contains('+search+')').hide();
    	}
    if ($("#items").find('tr:visible').length == 0){
    	$("#items").find('tr[id="nothing"]').show();
	} else {
    	$("#items").find('tr[id="headline"]').show();
	}
    
    $("#items").fadeIn();
}

$("input[type='text']").keyup( function(e) {
    if (e.keyCode == 13) {
		filter();
    }
});
$('select[id="jobs"]').change(function() {
	filter();
});
$('select[id="activities"]').change(function() {
	filter();
});
//$('select[id="facets"]').change(function() {
//	filter();
//});

<!-- listener for each checkbox -->
$(':checkbox').change(function() {
   sendRequest("UpdateCompetence", {"artefactId": "{$this->artefactId}", "competence": $(this).val(), "checked": $(this).prop("checked")}, "", "data");
   if ($(this).prop("checked")){
	   row = $('tr[short="'+$(this).val()+'"]').fadeOut().detach();
	   row.appendTo($('#checkedRows > tbody:last')).fadeIn();
	} else {
	   row = $('tr[short="'+$(this).val()+'"]').fadeOut().detach();
	   row.prependTo($('#uncheckedRows > tbody:first')).fadeIn();
   }
   
});

//<!-- for a save button -->
//var data = { 'selected[][]' : []};
//$("#items").find(":checked").each(function() {
//  data['selected[][]'].push($(this).val());
//});
//sendRequest("UpdateCompetence", {"artefactid": "{$this->id}", "competence": $(this).val(), "checked": data}, "", "data");
</script>
END
		;
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		//		$frameResponseObject->addWidget($jswrapper);
		$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($tabBar);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}

?>
