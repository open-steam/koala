<?php
namespace Portfolio\Commands;
class Comments extends \AbstractCommand implements \IFrameCommand {

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
			header("location: " . \Portfolio::getInstance()->getExtensionUrl() . "comments/" .  \lms_steam::get_current_user()->get_name());
			exit;
		} else {
		    if (\Portfolio\Model\Portfolios::isManager() || \Portfolio\Model\Portfolios::isViewer() || \lms_steam::get_current_user()->get_name() === $params[0]) {
                        $this->user = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $params[0]);
                    }
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

		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
                $breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name"=>"Kommentare")));
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget(\Portfolio::getActionBar());
		$tabbar = \Portfolio::getTabBar($this->user->get_name());
		$tabbar->setActiveTab(3);
		$frameResponseObject->addWidget($tabbar);
		$frameResponseObject->addWidget($rawHtml);
                
                $entries = $portfolio->getAllEntries();
                foreach ($entries as $entry) {
                    if ($entry->getCommentsCount() > 0) {
                        $raw = new \Widgets\RawHtml();
                        $raw->setHtml("<b>Kommentare zu:</b><br>".$entry->getEntryTableHtml());
                        $frameResponseObject->addWidget($raw);
                        
                        $raw = new \Widgets\RawHtml();
                        $threads = $entry->get_annotations();
                        $discussion = $threads[0];
                        $chat = new \Widgets\Chat();
                        $chat->setData($discussion);
                        $raw->addWidget($chat);
                        $raw->setHtml("<div style=\"width:500px; border: 2px dotted; padding: 5px\">" . $chat->getHtml() . "</div>");
                        
                        $frameResponseObject->addWidget($raw);
                    }
                    $competences = $entry->getCompetences();
                    foreach($competences as $oid => $competence) {
                        $room = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $oid);
                        $threads = $room->get_annotations();
                        if (isset($threads[0])) {
                            $raw = new \Widgets\RawHtml();
                            $raw->setHtml("<b>Kommentare zu:</b><br>".$competence->short . " " . $competence->name);
                            $frameResponseObject->addWidget($raw);
                        
                            $raw = new \Widgets\RawHtml();
                            $discussion = $threads[0];
                            $chat = new \Widgets\Chat();
                            $chat->setData($discussion);
                            $raw->addWidget($chat);
                            $raw->setHtml("<div style=\"width:500px; border: 2px dotted; padding: 5px\">" . $chat->getHtml() . "</div>");
                        
                            $frameResponseObject->addWidget($raw);
                        }
                    }
                }
                
		return $frameResponseObject;
	}
}
?>
