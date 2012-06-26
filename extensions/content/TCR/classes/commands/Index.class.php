<?php
namespace TCR\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$TCR = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$TCRExtension = \TCR::getInstance();
		$TCRExtension->addCSS();
		$TCRExtension->addJS();
		$content = $TCRExtension->loadTemplate("tcr_index.template.html");
		
		// display actionbar
		$actionbar = new \Widgets\Actionbar();
		$admins = $TCR->get_attribute("TCR_ADMINS");
		if (in_array($user->get_id(), $admins)) {
			$actions = array(
				array("name" => "Konfiguration" , "link" => $TCRExtension->getExtensionUrl() . "configuration/" . $this->id),
				array("name" => "Rundmail erstellen" , "link" => $TCRExtension->getExtensionUrl() . "mail/" . $this->id),
				array("name" => "Private Dokumente" , "link" => $TCRExtension->getExtensionUrl() . "privateDocuments/" . $this->id),
				array("name" => "Übersicht" , "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
				array("name" => "Alle Dokumente" , "link" => $TCRExtension->getExtensionUrl() . "documents/" . $this->id));
		} else {
			$actions = array(
				array("name" => "Private Dokumente" , "link" => $TCRExtension->getExtensionUrl() . "privateDocuments/" . $this->id),
				array("name" => "Übersicht" , "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
				array("name" => "Alle Dokumente" , "link" => $TCRExtension->getExtensionUrl() . "documents/" . $this->id));
		}
		$actionbar->setActions($actions);
		$frameResponseObject->addWidget($actionbar);
		
		// create array structure of the theses (identified by writer of the thesis and writer of the review)
		$theses_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $TCR->get_path() . "/theses");
		$theses_inventory = $theses_container->get_inventory();
		$theses = array();
		$rounds = $TCR->get_attribute("TCR_ROUNDS");
		$members = $TCR->get_attribute("TCR_USERS");
		sort($members);
		foreach ($members as $member) {
			$theses[$member] = array();
			foreach ($members as $member2) {
				$theses[$member][$member2] = array();
			}	
		}
		
		// display message if there are no members specified
		if (count($members) == 0) {
			$content->setCurrentBlock("BLOCK_OVERVIEW_TABLE");
			$content->setVariable("TCR_TITLE", $TCR->get_attribute("OBJ_DESC"));
			$content->setVariable("TCR_NO_USERS", "Keine Teilnehmer festgelegt. Bitte in der Konfiguration die teilnehmenden Personen auswählen.");
			$content->setVariable("DISPLAY_MANY", "none");
			$content->setVariable("DISPLAY_TABLE", "none");
			$content->parse("BLOCK_OVERVIEW_TABLE");
			$rawWidget = new \Widgets\RawHtml();
			$rawWidget->setHtml($content->get());
			$frameResponseObject->addWidget($rawWidget);
			$frameResponseObject->setHeadline(array( 
				array("name" => "Thesen-Kritik-Replik-Verfahren")
			));
			return $frameResponseObject;
		}
		
		// shortform of the table is displayed if amount of members is > 4
		$shortform = 0;
		if (count($members) > 4 && count($members) < 9) {
			$shortform = 1;
		} else if (count($members) >= 9) {
			$help = round(count($members) / 8);
			if ((count($members) / 8) > $help) {
				$shortform = $help+1;
			} else {
				$shortform = $help;
			}
		}
		if ($shortform > 1) {
			$divide = $shortform;
		} else {
			$divide = 1;
		}
		$area = 0;
		if (isset($this->params[1])) {
			$area = $this->params[1];
		}
		
		// fill array data structure
		foreach ($theses_inventory as $thesis) {
			$creator = $thesis->get_creator();
			$released = $thesis->get_attribute("TCR_RELEASED");
			$round = $thesis->get_attribute("TCR_ROUND");
			if ($released != 0) {
				$critic_array = $thesis->get_attribute("TCR_REVIEWS");
				foreach ($critic_array as $critic => $review) {
					$theses[$creator->get_id()][$critic][$round] = $thesis->get_id();
				}
			}
		}
		
		// display document table
		$content->setCurrentBlock("BLOCK_OVERVIEW_TABLE");
		$content->setVariable("TCR_TITLE", $TCR->get_attribute("OBJ_DESC"));
		$content->setVariable("INFOTEXT", nl2br($TCR->get_attribute("TCR_DESC")));
		if ($divide == 1) {
			$content->setVariable("DISPLAY_MANY", "none");
		} else {
			// if there are > 8 members only display 8 on a page and create navigation for the rest
			if ($area != 0) {
				$content->setVariable("PREVIOUS_CRITICS", "Zeige " . (($area-1)*8+1) . " bis " . (($area-1)*8+8));
				$content->setVariable("PREVIOUS_URL", $TCRExtension->getExtensionUrl() . "Index/" . $this->id . "/" . ($area-1));
			}
			if (count($members) <= $area*8+8) {
				$content->setVariable("CURRENT_CRITICS", "Aktuell angezeigt: Kritiker " . ($area*8+1) . " bis " . count($members) . " (von " . count($members) . ")");
			} else {
				$content->setVariable("CURRENT_CRITICS", "Aktuell angezeigt: Kritiker " . ($area*8+1) . " bis " . ($area*8+8) . " (von " . count($members) . ")");
			}
			if (count($members) > $area*8+8) {
				if (count($members) >= ($area+1)*8+8) {
					$content->setVariable("NEXT_CRITICS", "Zeige " . (($area+1)*8+1) . " bis " . (($area+1)*8+8));
				} else {
					$content->setVariable("NEXT_CRITICS", "Zeige " . (($area+1)*8+1) . " bis " . count($members));
				}
				$content->setVariable("NEXT_URL", $TCRExtension->getExtensionUrl() . "Index/" . $this->id . "/" . ($area+1));
			}
		}
		$content->setVariable("TABLE_LABEL", "Autoren \ Kritiker");
		$countmembers = 0;
		// create user columns
		for ($count = $area*8; $count < min(count($members), ($area+1)*8); $count++) {
			$content->setCurrentBlock("BLOCK_USER_TH");
			$member_object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $members[$count]);
			$pic_id = $member_object->get_attribute("OBJ_ICON")->get_id();
			$pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/15/20";
			$content->setVariable("USER_NAME", "<img style='vertical-align:middle;' src=" . $pic_link . ">&nbsp<a href=" . PATH_URL . "user/index/" . $member_object->get_name() . ">" . $member_object->get_full_name() . "</a>");
			//$content->setVariable("USER_NAME", $member_object->get_name());
			if (count($members) < 8) {
				$content->setVariable("USER_WIDTH", round(80 / count($members)) . "%");
			} else {
				$content->setVariable("USER_WIDTH", "11%");
			}
			$content->setVariable("USER_TH_ID", "th" . $member_object->get_id());
			$content->parse("BLOCK_USER_TH");
		}
		if (count($members) > 8) {
			$tablewidth = 12 + round(min(count($members), ($area+1)*8) - ($area*8))*11;
		} else {
			$tablewidth = 100;
		}
		$content->setVariable("TABLE_WIDTH", $tablewidth . "%");
		
		// create content of the table
		for ($count = 0; $count < count($members); $count++) {
			$content->setCurrentBlock("BLOCK_TABLE_ROW");
			$creator = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $members[$count]);
			$pic_id = $creator->get_attribute("OBJ_ICON")->get_id();
			$pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/15/20";
			$content->setVariable("USER_NAME2", "<img style='vertical-align:middle;' src=" . $pic_link . ">&nbsp<a href=" . PATH_URL . "user/index/" . $creator->get_name() . ">" . $creator->get_full_name() . "</a>");
			$content->setVariable("USER_TD_ID_LABEL", "td" . $creator->get_id());
			for ($count2 = $area*8; $count2 < min(count($members), ($area+1)*8); $count2++) {
				$content->setCurrentBlock("BLOCK_TABLE_COLUMN");
				$current_critic = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $members[$count2]);
				$content->setVariable("USER_TD_ID", "td" . $creator->get_id());
				$content->setVariable("USER_TH_ID", "th" . $current_critic->get_id());
				$content->setVariable("COLUMN_TITLE", "Autor: " . $creator->get_full_name() . " Kritiker: " . $current_critic->get_full_name());
				for ($count3 = 1; $count3 <= $rounds; $count3++) {
					if (isset($theses[$members[$count]][$members[$count2]][$count3])) {
						$current_thesis = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $theses[$members[$count]][$members[$count2]][$count3]);
						$current_critics = $current_thesis->get_attribute("TCR_REVIEWS");
						$content->setCurrentBlock("BLOCK_TABLE_ELEMENT");
						if ($shortform == 0) {
							$content->setVariable("ROUND_ROW", "<td width='25%'>R" . $count3 . ":</td>");
							$content->setVariable("OTHER_WIDTH", "25");
							$content->setVariable("THESIS_LABEL", "These");
						} else {
							$content->setVariable("THESIS_LABEL", "T" . $count3);
							$content->setVariable("OTHER_WIDTH", "33");
						}
						$content->setVariable("THESIS_URL", $TCRExtension->getExtensionUrl() . "view/" . $theses[$members[$count]][$members[$count2]][$count3]);
						$current_review = $current_critics[$members[$count2]];
						if ($current_review != 0) {
							$review = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $current_review);
							$released = $review->get_attribute("TCR_RELEASED");
							if ($released != 0) {
								if ($shortform == 0) {
									$content->setVariable("REVIEW_LABEL", "Kritik");
								} else {
									$content->setVariable("REVIEW_LABEL", "K" . $count3);
								}
								$content->setVariable("REVIEW_URL", $TCRExtension->getExtensionUrl() . "view/" . $current_review);
							}
							$current_response = $review->get_attribute("TCR_RESPONSE");
							if ($current_response != 0) {
								$response = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $current_response);
								$released = $response->get_attribute("TCR_RELEASED");
								if ($released != 0) {
									if ($shortform == 0) {
										$content->setVariable("RESPONSE_LABEL", "Replik");
									} else {
										$content->setVariable("RESPONSE_LABEL", "R" . $count3);
									}
									$content->setVariable("RESPONSE_URL", $TCRExtension->getExtensionUrl() . "view/" . $current_response);
								}
							}
						}
						$content->parse("BLOCK_TABLE_ELEMENT");
					} else {
						$content->setCurrentBlock("BLOCK_TABLE_ELEMENT");
						$content->setVariable("ROUND_ROW", "");
						$content->parse("BLOCK_TABLE_ELEMENT");
					}
				}
				$content->parse("BLOCK_TABLE_COLUMN");
			}
			$content->parse("BLOCK_TABLE_ROW");
		}
		$content->parse("BLOCK_OVERVIEW_TABLE");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => "Thesen-Kritik-Replik-Verfahren")
		));
		return $frameResponseObject;
	}
}
?>