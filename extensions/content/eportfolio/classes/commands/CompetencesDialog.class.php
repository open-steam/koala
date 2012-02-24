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
		<select id="jobs"><option value="">Alle</option>';
		foreach ($jobs as $job) {
			$html .= '<option value="' . $job->name . '">' . $job->name .": ". $job->description .  '</option>';
		}
		$html .= 
		"</select> ";
		$html .=
		'Tätigkeitsfeld:
		 <select style="width:175px;" id="activities">';
		$html .= '<option value="">Alle</option>';
		foreach ($activities as $activity) {
			$html .=
			'<option value="' . $activity->index . '">' . $activity->index .": ". $activity->name . '</option>';
		}
		$html .=
		'</select>
		</div>
		<div id="items" style="max-height:400px;overflow-y:auto"><table width=100% class="grid">';
		foreach ($activities as $activity){
			if (isset($this->activity) && $this->activity != $activity->index)
				continue;
			$html .=
			'<tr>
			<th colspan=2>Tätigkeitsfeld ' . $activity->index .': '. $activity->name . '</td>
			</tr>';
			$currentCompetences = \Portfolio\Model\Competence::getCompetences("CL", "");
			foreach ($currentCompetences as $competence) {
				$html .=
				"<tr>
				<td>{$competence->short}</td>
				<td>{$competence->name}</td>
				</tr>";
			}
		}
		$html .= <<<END
</table>
</div>
<script type="text/javascript">

$('select[id="jobs"]').change(function() {
    job = $('select[id="jobs"] option:selected').val();
    activity = $('select[id="activities"] option:selected').val();
});
$('select[id="activities"]').change(function() {
    job = $('select[id="jobs"] option:selected').val();
    activity = $('select[id="activities"] option:selected').val();
});
</script>
END;
		
                $rawHtml = new \Widgets\RawHtml();
                $rawHtml->setHtml($html);
                $dialog->addWidget($rawHtml);
		
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
	}
}
?>