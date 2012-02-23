<?php
namespace Portfolio\Commands;
class Competences extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $job;
	private $activity;
	private $index;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->job = (isset($_GET["job"]) && $_GET["job"] != "") ? $_GET["job"] : null;
		$this->activity = (isset($_GET["activity"]) && $_GET["activity"] != "") ? $_GET["activity"] : null;
		$this->params = array(
				"job" => $this->job,
				"activity" => $this->activity,
		);
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$competences = \Portfolio\Model\Competence::getCompetences();
		$jobs = \Portfolio\Model\Competence::getJobs();
		$activities = \Portfolio\Model\Competence::getActivityFields();

		$portfolioExtension = \Portfolio::getInstance();
		$content = $portfolioExtension->loadTemplate("portfolio.template.html");
		
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name"=>"Kompetenzportfolio")));
		
		$html = 
		'<div align="right">
		Berufsfeld: 
		<select id="jobs"><option value="">keine Auswahl</option>';
		foreach ($jobs as $job) {
			$selected = (strtolower($this->job) == strtolower($job->name)) ? "selected" : "";
			$html .= 
			'<option '. $selected .' value="' . $job->name . '">' . $job->name .": ". $job->description .  '</option>';
		}
		$html .= 
		"</select> ";
		$html .=
		'Fertigkeiten:
		 <select style="width:175px;" id="activities">';
		$html .= '<option value="">keine Auswahl</option>';
		$present = array();
		foreach ($activities as $activity) {
			if (in_array($activity->name, $present))
				continue;
			$present []= $activity->name;
			$selected = ($this->activity == $activity->index) ? "selected" : "";
			$html .=
			'<option ' . $selected . ' value="' . $activity->index . '">' . $activity->index .": ". $activity->name . '</option>';
		}
		$html .=
		'</select>
		</div>
		<div id="items"><table width=100% class="grid">';
		foreach ($activities as $activity){
			if (isset($this->activity) && $this->activity != $activity->index)
				continue;
			if (isset($this->job) && strtolower($this->job) != strtolower($activity->job))
				continue;			
			$html .=
			'<tr>
			<th colspan=2>' . $activity->name . '</td>
			</tr>
			<tr>
			<td>Index</td>
			<td>Beschreibung</td>
			</tr>';
			$currentCompetences = \Portfolio\Model\Competence::getCompetences($this->job, $activity->index);
			foreach ($currentCompetences as $competence) {
				$html .=
				"<tr>
				<td>{$competence->short}</td>
				<td>{$competence->name}</td>
				</tr>";
			}
		}
		$competencesPath = \Portfolio::getInstance()->getExtensionUrl() . "/competences/?job=";
		$html .= <<<END
</table>
</div>
<script type="text/javascript">

$('select[id="jobs"]').change(function() {
    job = $('select[id="jobs"] option:selected').val();
    activity = $('select[id="activities"] option:selected').val();
    window.location = "$competencesPath" + job + "&activity=" + activity;
});
$('select[id="activities"]').change(function() {
    job = $('select[id="jobs"] option:selected').val();
    activity = $('select[id="activities"] option:selected').val();
    window.location = "$competencesPath" + job + "&activity=" + activity;
});
</script>
END;
	$rawHtml = new \Widgets\RawHtml();
	$rawHtml->setHtml($html);
	$frameResponseObject->addWidget($breadcrumb);
	$actionBar = new \Widgets\ActionBar();
	$actionBar->setActions(\Portfolio::getActionBarArray());
	$frameResponseObject->addWidget($actionBar);
	$frameResponseObject->addWidget($rawHtml);
	return $frameResponseObject;
	}
}
?>
