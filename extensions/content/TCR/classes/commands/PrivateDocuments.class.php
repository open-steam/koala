<?php
namespace TCR\Commands;
class PrivateDocuments extends \AbstractCommand implements \IFrameCommand {

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
		
		// determine kind of documents of the current user to display (0 = theses, 1 = reviews, 2 = responses)
		$kindOfDocument = 0;
		if (isset($this->params[1])) {
			$kindOfDocument = $this->params[1];
		}
		
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

		$content = $TCRExtension->loadTemplate("tcr_privatedocuments.template.html");
		// display a message if current user is not a user of this tcr
		$members = $TCR->get_attribute("TCR_USERS");
		if (!in_array($user->get_id(), $members)) {
			$content->setCurrentBlock("BLOCK_DOCUMENTS_TABLE");
			$content->setVariable("DISPLAY_TABLE", "none");
			$content->setVariable("NOT_USER", "Sie sind nicht als Teilnehmer dieses Thesen-Kritik-Replik-Verfahrens eingetragen. Wenden Sie sich an einen Administrator.");
			$content->parse("BLOCK_DOCUMENTS_TABLE");
			
			$rawWidget = new \Widgets\RawHtml();
			$rawWidget->setHtml($content->get());
			$frameResponseObject->addWidget($rawWidget);
			$frameResponseObject->setHeadline(array(
				array("name" => "Thesen-Kritik-Replik-Verfahren", "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
				array("name" => "Private Dokumente")
			));
			return $frameResponseObject;
		}
		
		// display tabbar
		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(array(
			array("name"=>"Thesen", "link"=>$TCRExtension->getExtensionUrl() . "privateDocuments/" . $this->id), 
			array("name"=>"Kritiken", "link"=>$TCRExtension->getExtensionUrl() . "privateDocuments/" . $this->id . "/1"), 
			array("name"=>"Repliken", "link"=>$TCRExtension->getExtensionUrl() . "privateDocuments/" . $this->id . "/2")));
		$tabBar->setActiveTab($kindOfDocument);
		$frameResponseObject->addWidget($tabBar);
		
		// create array structure and add theses for their round
		$rounds = $TCR->get_attribute("TCR_ROUNDS");
		$theses_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $TCR->get_path() . "/theses");
		$reviews_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $TCR->get_path() . "/reviews");
		$responses_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $TCR->get_path() . "/responses");
		$theses_inventory = $theses_container->get_inventory();
		$theses = array();
		$theses_response = array();
		foreach ($theses_inventory as $thesis) {
			if (!($thesis instanceof \steam_container)) {
				$current_round = $thesis->get_attribute("TCR_ROUND");
				if ($thesis->get_creator()->get_id() == $user->get_id()) {
					$theses[$current_round] = $thesis;
				}
				$critics = $thesis->get_attribute("TCR_REVIEWS");
				if (is_array($critics)) {
					if (array_key_exists($user->get_id(), $critics)) {
						$theses_response[$current_round] = $thesis;
					}
				}
			}
		}
		
		// display private documents table
		$content->setCurrentBlock("BLOCK_DOCUMENTS_TABLE");
		if ($kindOfDocument == 0 || $kindOfDocument == 2) {
			$content->setVariable("THESES_LABEL", "Erstellte Thesen");
			$content->setVariable("REVIEWS_LABEL", "Erhaltene Kritiken");
			$content->setVariable("RESPONSES_LABEL", "Erstellte Repliken");
			if ($kindOfDocument == 0) {
				// thesis view
				$first = true;
				for ($count = 1; $count <= $rounds; $count++) {
					$content->setCurrentBlock("BLOCK_DOCUMENTS_TABLE_ELEMENT");
					$content->setVariable("ROUND_VALUE", "Runde " . $count);
					if (!array_key_exists($count, $theses)) {
						$new_thesis = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $user->get_id() . "_thesis_round" . $count, "", "text/plain", $theses_container, "These Runde " . $count);
						$new_thesis->set_attribute("TCR_ROUND", $count);
						$new_thesis->set_attribute("TCR_REVIEWS", array());
						$new_thesis->set_attribute("TCR_RELEASED", 0);
						$theses[$count] = $new_thesis;
					}
					$current_critics = $theses[$count]->get_attribute("TCR_REVIEWS");
					if (count($current_critics) > 0) {
						$content->setVariable("THESIS_NAME", "<font style='font-size:13px;'>" . $theses[$count]->get_attribute("OBJ_DESC") . "</font><br>");
						$content->setVariable("CREATE_THESIS", "Anzeigen");
						$content->setVariable("THESIS_URL", $TCRExtension->getExtensionUrl() . "view/" . $theses[$count]->get_id());
						foreach ($current_critics as $critic => $review) {
							if ($review == 0) {
								$content->setVariable("DISPLAY_REVIEW", "none");
								$content->setVariable("DISPLAY_RESPONSE", "none");
								$review_released = 0;
							} else {
								$current_review = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $review);
								$review_released = $current_review->get_attribute("TCR_RELEASED");
							}
							if ($review_released != 0) {
								$author = $current_review->get_creator();
								$pic_id = $author->get_attribute("OBJ_ICON")->get_id();
								$pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/15/20";
								$content->setVariable("REVIEW_NAME", "<font style='font-size:13px;'>" . $current_review->get_attribute("OBJ_DESC") . "</font><br>von <img style='vertical-align:middle;' src=" . $pic_link . ">&nbsp<a href=" . PATH_URL . "user/index/" . $author->get_name() . ">" . $author->get_full_name() . "</a><br>");
								$content->setVariable("CREATE_REVIEW", "Anzeigen");
								$content->setVariable("REVIEW_URL", $TCRExtension->getExtensionUrl() . "view/" . $current_review->get_id());
								$responseID = $current_review->get_attribute("TCR_RESPONSE");
								if ($responseID == 0) {
									$response_released = 0;
								} else {
									$response_element = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $responseID);
									$response_released = $response_element->get_attribute("TCR_RELEASED");
								}
								if ($response_released == 0) {
									$content->setVariable("DISPLAY_RESPONSE", "none");
								} else {
									$content->setVariable("RESPONSE_NAME", "<font style='font-size:13px;'>" . $response_element->get_attribute("OBJ_DESC") . "<br>");
									$content->setVariable("CREATE_RESPONSE", "Anzeigen");
									$content->setVariable("RESPONSE_URL", $TCRExtension->getExtensionUrl() . "view/" . $response_element->get_id());
								}
							} else {
								$content->setVariable("DISPLAY_REVIEW", "none");
								$content->setVariable("DISPLAY_RESPONSE", "none");
							}
						}
					} else if ($first) {
						$content->setVariable("THESIS_NAME", "<font style='font-size:13px;'>" . $theses[$count]->get_attribute("OBJ_DESC") . "</font><br>");
						$content->setVariable("THESIS_BACKGROUND", "background: #DEEAAA");
						$content->setVariable("CREATE_THESIS", "Anzeigen und bearbeiten");
						$content->setVariable("THESIS_URL", $TCRExtension->getExtensionUrl() . "view/" . $theses[$count]->get_id());
						$content->setVariable("DISPLAY_REVIEW", "none");
						$content->setVariable("DISPLAY_RESPONSE", "none");
						$first = false;
					} else {
						$content->setVariable("DISPLAY_THESIS", "none");
						$content->setVariable("DISPLAY_REVIEW", "none");
						$content->setVariable("DISPLAY_RESPONSE", "none");
					}
					$content->parse("BLOCK_DOCUMENTS_TABLE_ELEMENT");
				}
			// response view
			} else {
				for ($count = 1; $count <= $rounds; $count++) {
					$content->setCurrentBlock("BLOCK_DOCUMENTS_TABLE_ELEMENT");
					$content->setVariable("ROUND_VALUE", "Runde " . $count);
					if (!array_key_exists($count, $theses)) {
						$content->setVariable("DISPLAY_THESIS", "none");
						$content->setVariable("DISPLAY_REVIEW", "none");
						$content->setVariable("DISPLAY_RESPONSE", "none");
					} else {
						$current_critics = $theses[$count]->get_attribute("TCR_REVIEWS");
						if (count($current_critics) > 0) {
							$content->setVariable("THESIS_NAME", "<font style='font-size:13px;'>" . $theses[$count]->get_attribute("OBJ_DESC") . "</font><br>");
							$content->setVariable("CREATE_THESIS", "Anzeigen");
							$content->setVariable("THESIS_URL", $TCRExtension->getExtensionUrl() . "view/" . $theses[$count]->get_id());
							foreach ($current_critics as $critic => $review) {
								if ($review == 0) {
									$review_released = 0;
								} else {
									$current_review = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $review);
									$review_released = $current_review->get_attribute("TCR_RELEASED");
								}
								if ($review_released != 0) {
									$author = $current_review->get_creator();
									$pic_id = $author->get_attribute("OBJ_ICON")->get_id();
									$pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/15/20";
									$content->setVariable("REVIEW_NAME", "<font style='font-size:13px;'>" . $current_review->get_attribute("OBJ_DESC") . "</font><br>von <img style='vertical-align:middle;' src=" . $pic_link . ">&nbsp<a href=" . PATH_URL . "user/index/" . $author->get_name() . ">" . $author->get_full_name() . "</a><br>");
									$content->setVariable("CREATE_REVIEW", "Anzeigen");
									$content->setVariable("REVIEW_URL", $TCRExtension->getExtensionUrl() . "view/" . $current_review->get_id());
									$responseID = $current_review->get_attribute("TCR_RESPONSE");
									if ($responseID == 0) {
										$new_response = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $user->get_id() . "_response_round" . $count, "", "text/plain", $responses_container, "Replik Runde " . $count);
										$new_response->set_attribute("TCR RELEASED", 0);
										$responseID = $new_response->get_id();
										$current_review->set_attribute("TCR_RESPONSE", $responseID);
									}
									$response_element = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $responseID);
									$response_released = $response_element->get_attribute("TCR_RELEASED");
									$content->setVariable("RESPONSE_NAME", "<font style='font-size:13px;'>" . $response_element->get_attribute("OBJ_DESC") . "</font><br>");
									if ($response_released == 0) {
										$content->setVariable("RESPONSE_BACKGROUND", "background: #DEEAAA");
										$content->setVariable("CREATE_RESPONSE", "Anzeigen und bearbeiten");
										$content->setVariable("RESPONSE_URL", $TCRExtension->getExtensionUrl() . "view/" . $response_element->get_id());
									} else {
										$content->setVariable("CREATE_RESPONSE", "Anzeigen");
										$content->setVariable("RESPONSE_URL", $TCRExtension->getExtensionUrl() . "view/" . $response_element->get_id());
									}
								} else {
									$content->setVariable("DISPLAY_REVIEW", "none");
									$content->setVariable("DISPLAY_RESPONSE", "none");
								}
							}
						} else {
							$content->setVariable("DISPLAY_THESIS", "none");
							$content->setVariable("DISPLAY_REVIEW", "none");
							$content->setVariable("DISPLAY_RESPONSE", "none");
						}
					}
					$content->setVariable("ASSETURL", $TCRExtension->getAssetUrl());
					$content->parse("BLOCK_DOCUMENTS_TABLE_ELEMENT");
				}
			}
		// review view
		} else {
			$content->setVariable("THESES_LABEL", "Erhaltene Thesen");
			$content->setVariable("REVIEWS_LABEL", "Erstellte Kritiken");
			$content->setVariable("RESPONSES_LABEL", "Erhaltene Repliken");
			for ($count = 1; $count <= $rounds; $count++) {
				$content->setCurrentBlock("BLOCK_DOCUMENTS_TABLE_ELEMENT");
				$content->setVariable("ROUND_VALUE", "Runde " . $count);
				if (!array_key_exists($count, $theses_response)) {
					$content->setVariable("DISPLAY_THESIS", "none");
					$content->setVariable("DISPLAY_REVIEW", "none");
					$content->setVariable("DISPLAY_RESPONSE", "none");
				} else {
					$author = $theses_response[$count]->get_creator();
					$pic_id = $author->get_attribute("OBJ_ICON")->get_id();
					$pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/15/20";
					$content->setVariable("THESIS_NAME", "<font style='font-size:13px;'>" . $theses_response[$count]->get_attribute("OBJ_DESC") . "</font><br>von <img style='vertical-align:middle;' src=" . $pic_link . ">&nbsp<a href=" . PATH_URL . "user/index/" . $author->get_name() . ">" . $author->get_full_name() . "</a><br>");				
					$content->setVariable("CREATE_THESIS", "Anzeigen");
					$content->setVariable("THESIS_URL", $TCRExtension->getExtensionUrl() . "view/" . $theses_response[$count]->get_id());
					$critics_array = $theses_response[$count]->get_attribute("TCR_REVIEWS");
					if ($critics_array[$user->get_id()] == 0) {
						$new_review = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $user->get_id() . "_review_round" . $count, "", "text/plain", $reviews_container, "Kritik Runde " . $count);
						$new_review->set_attribute("TCR_RELEASED", 0);
						$new_review->set_attribute("TCR_RESPONSE", 0);
						$critics_array[$user->get_id()] = $new_review->get_id();				
						$theses_response[$count]->set_attribute("TCR_REVIEWS", $critics_array);
					}
					$review = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $critics_array[$user->get_id()]);
					$released = $review->get_attribute("TCR_RELEASED");
					$content->setVariable("REVIEW_NAME", "<font style='font-size:13px;'>" . $review->get_attribute("OBJ_DESC") . "</font><br>");				
					if ($released == 0) {
						$content->setVariable("CREATE_REVIEW", "Anzeigen und bearbeiten");
						$content->setVariable("REVIEW_BACKGROUND", "background: #DEEAAA");
						$content->setVariable("REVIEW_URL", $TCRExtension->getExtensionUrl() . "view/" . $review->get_id());
						$content->setVariable("DISPLAY_RESPONSE", "none");
					} else {
						$content->setVariable("CREATE_REVIEW", "Anzeigen");
						$content->setVariable("REVIEW_URL", $TCRExtension->getExtensionUrl() . "view/" . $review->get_id());
						$responseID = $review->get_attribute("TCR_RESPONSE");
						if ($responseID == 0) {
							$response_released = 0;
						} else {
							$response_element = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $responseID);
							$response_released = $response_element->get_attribute("TCR_RELEASED");
						}
						if ($response_released == 0) {
							$content->setVariable("DISPLAY_RESPONSE", "none");
						} else {
							$author = $response_element->get_creator();
							$pic_id = $author->get_attribute("OBJ_ICON")->get_id();
							$pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/15/20";
							$content->setVariable("RESPONSE_NAME", "<font style='font-size:13px;'>" . $response_element->get_attribute("OBJ_DESC") . "</font><br>von <img style='vertical-align:middle;' src=" . $pic_link . ">&nbsp<a href=" . PATH_URL . "user/index/" . $author->get_name() . ">" . $author->get_full_name() . "</a><br>");				
							$content->setVariable("CREATE_RESPONSE", "Anzeigen");
							$content->setVariable("RESPONSE_URL", $TCRExtension->getExtensionUrl() . "edit/" . $response_element->get_id());
						}
					}
				}
				$content->setVariable("ASSETURL", $TCRExtension->getAssetUrl());
				$content->parse("BLOCK_DOCUMENTS_TABLE_ELEMENT");
			}
		}
		$content->parse("BLOCK_DOCUMENTS_TABLE");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => "Thesen-Kritik-Replik-Verfahren", "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
			array("name" => "Private Dokumente")
		));
		return $frameResponseObject;
	}
}
?>