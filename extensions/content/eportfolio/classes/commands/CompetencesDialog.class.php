<?php
namespace Portfolio\Commands;

class CompetencesDialog extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $entry;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: null;
			isset($this->params["env"]) ? $env = $this->params["env"]: null;
			isset($this->params["type"]) ? $type = $this->params["type"]: null;
		}
		if (isset($env)) {
			$portfolioInstance = \Portfolio\Model\Portfolios::getInstanceByRoom($env);
			$this->entry = $portfolioInstance->createEntry($type);
		} else {
			$room = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			if ($room instanceof \steam_room) {
				$this->entry = \Portfolio\Model\Entry::getEntryByRoom($room);
			}
		}
		$this->id = $this->entry->get_id();
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$entry = $this->entry;

		$competences = \Portfolio\Model\Competence::getCompetences();
		$jobs = \Portfolio\Model\Competence::getJobs();
		$activities = \Portfolio\Model\Competence::getActivityFieldsDistinct();
		$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle($entry::getEntryTypeEditDescription());
		$dialog->setDescription($entry::getEntryTypeEditInfo());

		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);
		$dialog->setForceReload(true);

		$html =
		'<br><div align="right">
		Beruf:
		<select id="jobs"><option value="undefined">Alle</option>';
		foreach ($jobs as $job) {
			$html .= '<option value="' . $job->name . '">' . $job->name .": ". $job->description .  '</option>';
		}
		$html .=
		"</select> ";
		$html .=
		'Tätigkeitsfeld:
		<select style="width:175px;" id="activities">';
		$html .= '<option value="undefined">Alle</option>';
		foreach ($activities as $activity) {
			$html .=
			'<option value="' . $activity->index . '">' . $activity->index .": ". $activity->name . '</option>';
		}
		$html .=
		'</select>
		</div>
		<div id="items" style="max-height:400px;overflow-y:auto">
		<table width=100% class="grid">';
		$competencesStrings = $entry->getCompetencesStrings();
		foreach ($activities as $activity){
			if (isset($this->activity) && $this->activity != $activity->index)
				continue;
			$html .=
			'<tr>
			<th id="headline" colspan=2>Tätigkeitsfeld ' . $activity->index .': '. $activity->name . '</td>
			</tr>';
			$currentCompetences = \Portfolio\Model\Competence::getCompetences(null, $activity->index);
			foreach ($currentCompetences as $competence) {
				$checked = in_array($competence->short, $competencesStrings) ? " checked=\"true\"" : "";
				$html .=
				"<tr short=\"{$competence->short}\" job=\"{$competence->job}\" activity=\"{$competence->activity}\">
				<td><input type=\"checkbox\" value=\"{$competence->short}\" {$checked}>
				<br><div style=\"font-size:80%\">{$competence->short}</div></td>
				<td>{$competence->name} (Niveau {$competence->niveau})</td>
				</tr>";
			}
		}
		$html .= 
		'</table>
		</div>';
		$html .= <<<END
		<script type="text/javascript">
			function filter(){
				$("#items").fadeOut();
				activity = $('select[id="activities"] option:selected').val();
				job = $('select[id="jobs"] option:selected').val();
				$("#items").find("tr").show();
				if (job != "undefined"){
					$("#items").find('tr[job!='+job+']').hide();
				}
				if (activity != "undefined"){
					$("#items").find('tr[activity!='+activity+']').hide();
				}
				$("#items").find('tr[id="headline"]').show();
				$("#items").fadeIn();
			}
			
			$('select[id="jobs"]').change(function() {
				filter();
			});
			$('select[id="activities"]').change(function() {
				filter();
			});
	
			<!-- listener for each checkbox -->
			$(':checkbox').change(function() {
				sendRequest("toggleCompetences", {"id": "{$this->id}", "competence": $(this).val(), "value": $(this).prop("checked")}, "", "data", function() {}, function() {});
			});
		</script>
END
;
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$dialog->addWidget($rawHtml);

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
	}
}
?>