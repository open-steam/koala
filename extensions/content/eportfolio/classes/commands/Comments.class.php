<?php
namespace Portfolio\Commands;
class Comments extends \AbstractCommand implements \IFrameCommand {

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
			header("location: " . \Portfolio::getInstance()->getExtensionUrl() . "comments/" .  \lms_steam::get_current_user()->get_name());
			exit;
		} else {
			$this->user = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $params[0]);
		}
		if (!isset($this->user) || !($this->user instanceof \steam_user)) {
			header("location: " . \Portfolio::getInstance()->getExtensionUrl() . "comments/" .  \lms_steam::get_current_user()->get_name());
			exit;
		}
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$portfolioExtension = \Portfolio::getInstance();
		$content = $portfolioExtension->loadTemplate("portfolio.template.html");
		$portfolio = \Portfolio\Model\Portfolios::getInstanceForUser($this->user);
		$competences = $portfolio->getAllEntries();
		$jobs = \Portfolio\Model\Competence::getJobs();
		$activities = \Portfolio\Model\Competence::getActivityFieldsDistinct();

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

		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());

		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name"=>"Kommentare")));
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget(\Portfolio::getActionBar());
		$tabbar = \Portfolio::getTabBar();
		$tabbar->setActiveTab(3);
		$frameResponseObject->addWidget($tabbar);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>
