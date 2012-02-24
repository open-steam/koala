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
		$params = $requestObject->getParams();
		if(!isset($params[0])) {
			header("location: " . \Portfolio::getInstance()->getExtensionUrl() . "competences/" .  \lms_steam::get_current_user()->get_name());
			exit;
		} else {
			$this->user = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $params[0]);
		}
		if (!isset($this->user) || !($this->user instanceof \steam_user)) {
			header("location: " . \Portfolio::getInstance()->getExtensionUrl() . "competences/" .  \lms_steam::get_current_user()->get_name());
			exit;
		}
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
		$activities = \Portfolio\Model\Competence::getActivityFieldsDistinct();

		$portfolioExtension = \Portfolio::getInstance();
		$content = $portfolioExtension->loadTemplate("portfolio.template.html");
		
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name"=>"Kompetenzmodell")));

		$infobar = new \Widgets\InfoBar();
		$infobar->setHeadline("");
		$infobar->addParagraph('
				Mithilfe dieses Kompetenzportfolio-Systems können  zentrale chemieberufliche
				Kompetenzen zur Bilanzierung gesichtet, bestimmt, geordnet und dokumentiert werden.<br><br>
				Das Kompetenzportfolio ist durch seine Bilanzierungs- und Dokumentationsfunktionen
				dafür geeignet, Ausbilder, Dozenten, Auszubildende, Schüler, Personalreferenten oder
				Angestellte von Berufen der chemischen Industrie bei Fragestellungen der Aus- und
				Weiterbildungseignung/-vorbereitung , der Anrechnung von Aus- und Weiterbildungszielen,
				der Personalauswahl, der Personalentwicklung, der Berufswahl sowie bei der Bewerbung zu unterstützen.
				'
		);
		$content->setVariable("INFOBAR", $infobar->getHtml());
		
		$html = 
		'<br><div align="right">
		Beruf: 
		<select id="jobs"><option value="">Alle</option>';
		foreach ($jobs as $job) {
			$selected = (strtolower($this->job) == strtolower($job->name)) ? "selected" : "";
			$html .= 
			'<option '. $selected .' value="' . $job->name . '">' . $job->name .": ". $job->description .  '</option>';
		}
		$html .= 
		"</select> ";
		$html .=
		'Tätigkeitsfeld:
		 <select style="width:175px;" id="activities">';
		$html .= '<option value="">Alle</option>';
		foreach ($activities as $activity) {
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
			$html .=
			'<tr>
			<th colspan=2>Tätigkeitsfeld ' . $activity->index .': '. $activity->name . '</td>
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
		$competencesPath = \Portfolio::getInstance()->getExtensionUrl() . "competences/" . $this->user->get_name() . "/?job=";
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
	$frameResponseObject->addWidget(\Portfolio::getActionBar());
	$tabbar = \Portfolio::getTabBar();
	$tabbar->setActiveTab(4);
	$frameResponseObject->addWidget($tabbar);
	$frameResponseObject->addWidget($rawHtml);
	return $frameResponseObject;
	}
}
?>
