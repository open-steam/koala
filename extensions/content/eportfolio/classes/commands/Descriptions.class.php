<?php
namespace Portfolio\Commands;
class Descriptions extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $job;
	private $activity;
	private $index;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$competences = \Portfolio\Model\Competence::getCompetences();
		$jobs = \Portfolio\Model\Competence::getJobs();
		$activities = \Portfolio\Model\Competence::getActivityFields();

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

		$sortedActivities = array();
		foreach ($activities as $activity){
			$sortedActivities[$activity->index] []= $activity;
		}
		$html =
		'<div id="items">
		<table width=100% class="grid">';
		foreach ($sortedActivities as $activityItem){
			$html .=
			'<tr>
			<th colspan=3>Tätigkeitsfeld ' . $activityItem[0]->index .': '. $activityItem[0]->name . '</td>
			</tr>
			<tr>
			<td colspan=3>Tätigkeitsbeschreibung: ' . $activityItem[0]->description . '</td>
			</tr>
			<tr>
			<td><i>Beruf</i></td>
			<td><i>Niveau</i></td>
			<td><i>Niveaubeschreibung</i></td>
			</tr>';
			foreach ($activityItem as $activity){
				$jobName = \Portfolio\Model\Competence::getJobByName($activity->job)->getJobDescriptionHtml();
				$niveau = \Portfolio\Model\Competence::getNiveau($activity->index, $activity->job)->getHtml();
				$html .=
				"<tr>
				<td>{$jobName}</td>
				<td>{$niveau}</td>
				<td>{$activity->niveauDescription}</td>
				</tr>";
			}
		}
		$html .= 
		"</table></div>";
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget(\Portfolio::getActionBar());
		$tabbar = \Portfolio::getTabBar();
		$tabbar->setActiveTab(5);
		$frameResponseObject->addWidget($tabbar);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>
