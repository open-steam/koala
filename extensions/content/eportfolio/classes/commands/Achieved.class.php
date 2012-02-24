<?php
namespace Portfolio\Commands;
class Achieved extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $job;
	private $activity;
	private $index;
	private $user;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$params = $requestObject->getParams();
		if(!isset($params[0])) {
			header("location: " . \Portfolio::getInstance()->getExtensionUrl() . "achieved/" .  \lms_steam::get_current_user()->get_name());
			exit;
		} else {
			$this->user = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $params[0]);
		}
		if (!isset($this->user) || !($this->user instanceof \steam_user)) {
			header("location: " . \Portfolio::getInstance()->getExtensionUrl() . "achieved/" .  \lms_steam::get_current_user()->get_name());
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
		$portfolio = \Portfolio\Model\Portfolios::getInstanceForUser($this->user);
		$competences = $portfolio->getAchievedCompetences();
		$jobs = \Portfolio\Model\Competence::getJobs();
		$activities = \Portfolio\Model\Competence::getActivityFieldsDistinct();

		$portfolioExtension = \Portfolio::getInstance();
		$content = $portfolioExtension->loadTemplate("portfolio.template.html");
		
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name"=>"Kompetenzportfolio")));
		
		$html =
		'<div id="items"><table width=100% class="grid">';
		foreach ($activities as $activity){
			if (isset($this->activity) && $this->activity != $activity->index)
				continue;
			$html .=
			'<tr>
			<th colspan=2>Tätigkeitsfeld ' . $activity->index .': '. $activity->name . '</td>
			</tr>';
			$currentCompetences = array();
			foreach ($competences as $competence){
				if ($competence->activity == $activity->index)
					$currentCompetences []= $competence;
			}
			foreach ($currentCompetences as $competence) {
				$html .=
				"<tr>
				<td>{$competence->short}</td>
				<td>{$competence->name}</td>
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
	$tabbar->setActiveTab(2);
	$frameResponseObject->addWidget($tabbar);
	$frameResponseObject->addWidget($rawHtml);
	return $frameResponseObject;
	}
}
?>
