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
                    if (\Portfolio\Model\Portfolios::isManager() || \Portfolio\Model\Portfolios::isViewer() || \lms_steam::get_current_user()->get_name() === $params[0]) {
                        $this->user = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $params[0]);
                    }
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

		$entries = $portfolio->getAllEntries();
		$achievedEntries = array();
		foreach ($entries as $entry){
			$competencesStrings = $entry->getCompetencesStrings();
			foreach ($competencesStrings as $competenceString){
				$achievedEntries [$competenceString] []= $entry;
			}
		}

		$html =
		'<div id="items"><table width=100% class="grid">';
		foreach ($activities as $activity){
			if (isset($this->activity) && $this->activity != $activity->index)
				continue;
			$html .=
			'<tr>
			<th colspan=4>' . $activity->getDescriptionHtml() . '</td>
			</tr>';
			$currentCompetences = array();
			foreach ($competences as $competence){
				if ($competence->activity == $activity->index)
					$currentCompetences []= $competence;
			}
			foreach ($currentCompetences as $competence) {
				$competencesLinks = "";
				$uri = \Portfolio::getInstance()->getExtensionUrl() . "index/" . $this->user->get_name() . "/";
				foreach ($achievedEntries[$competence->short] as $entry)
					$competencesLinks .=
					"<div style=\"border: 1px dotted lightblue; font-size:80%\"><a href={$uri}#{$entry->getRoom()->get_id()}>{$entry::$entryTypeDescription}</a></div>
					";
				$html .=
				"<tr>
				<td>{$competence->getJobObject()->getDescriptionHtml()}{$competence->getShortHtml()}</td>
				<td>{$competence->name}</td>
				<td>{$competence->getNiveauObject()->getHtml()}</td>
				<td>$competencesLinks</td>
				</tr>";
			}
		}
		$html .=
		"</table></div>";
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget(\Portfolio::getActionBar());
		$tabbar = \Portfolio::getTabBar($this->user->get_name());
		$tabbar->setActiveTab(2);
		$frameResponseObject->addWidget($tabbar);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>
