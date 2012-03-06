<?php
namespace TCR\Commands;
class Documents extends \AbstractCommand implements \IFrameCommand {

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
		$members = $TCR->get_attribute("TCR_USERS");
		
		// determine which kind of elements is displayed (0 = theses, 1 = reviews, 2 = responses, 3 = all)
		if (isset($this->params[1])) {
			$kind = $this->params[1];
		} else {
			$kind = 3;
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
		
		// display tabbar
		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(array(
			array("name"=>"Alle", "link"=>$TCRExtension->getExtensionUrl() . "documents/" . $this->id), 
			array("name"=>"Thesen", "link"=>$TCRExtension->getExtensionUrl() . "documents/" . $this->id . "/0"), 
			array("name"=>"Kritiken", "link"=>$TCRExtension->getExtensionUrl() . "documents/" . $this->id . "/1"), 
			array("name"=>"Repliken", "link"=>$TCRExtension->getExtensionUrl() . "documents/" . $this->id . "/2")));
		if ($kind == 3) {
			$tabBar->setActiveTab(0);
		} else {
			$tabBar->setActiveTab($kind + 1);
		}
		$frameResponseObject->addWidget($tabBar);
		
		// create arrays for every round and add the theses to these arrays
		$rounds = $TCR->get_attribute("TCR_ROUNDS");
		$theses_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $TCR->get_path() . "/theses");
		$theses_inventory = $theses_container->get_inventory();
		$theses = array();
		for ($count = 1; $count <= $rounds; $count++) {
			$theses[$count] = array();
		}
		foreach ($theses_inventory as $thesis) {
			$current_round = $thesis->get_attribute("TCR_ROUND");
			if ($current_round <= $rounds) {
				$critics = $thesis->get_attribute("TCR_REVIEWS");
				if (count($critics) > 0 && in_array($thesis->get_creator()->get_id(), $members) && !($thesis instanceof \steam_container)) {
					array_push($theses[$current_round], $thesis);
				}
			}
		}
		
		// display documents table
		$content = $TCRExtension->loadTemplate("tcr_documents.template.html");
		$content->setCurrentBlock("BLOCK_DOCUMENTS_TABLE");
		$content->setVariable("ROUND_LABEL", "Runde");
		$content->setVariable("DOCUMENT_KIND_LABEL", "Art");
		$content->setVariable("CONTENT_LABEL", "Inhalt");
		$content->setVariable("AUTHOR_LABEL", "Autor");
		$content->setVariable("DATE_LABEL", "Veröffentlicht am");
		$content->setVariable("COMMENTS_LABEL", "Kommentare");
		for ($count = 1; $count <= $rounds; $count++) {
			$content->setCurrentBlock("BLOCK_DOCUMENTS_ROUND");
			$content->setVariable("CURRENT_ROUND", $count . ". Runde");
			$content->setVariable("CURRENT_ROUND_VALUE", $count);
			// display every thesis in the corresponding round
			foreach ($theses[$count] as $current_thesis) {
				if ($kind == 3 || $kind == 0) {
					$creator = $current_thesis->get_creator();
					$date = $current_thesis->get_attribute("TCR_RELEASED");
					$content->setCurrentBlock("BLOCK_DOCUMENTS_ELEMENT");
					$content->setVariable("ASSETURL", $TCRExtension->getAssetUrl());
					$content->setVariable("DOCUMENT_KIND", "These");
					$content->setVariable("ELEMENT_URL", $TCRExtension->getExtensionUrl() . "view/" . $current_thesis->get_id());
					$content->setVariable("VIEW_ELEMENT", "Inhalt anzeigen");
					$content->setVariable("ASSETURL", $TCRExtension->getAssetUrl());
					$pic_id = $creator->get_attribute("OBJ_ICON")->get_id();
					$pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/20/30";
					$content->setVariable("AUTHOR_VALUE", "<img style='vertical-align:middle;' src=" . $pic_link . ">&nbsp<a href=" . PATH_URL . "user/index/" . $creator->get_name() . ">" . $creator->get_full_name() . "</a>");
					$content->setVariable("DATE_VALUE", date("d.m.Y H:i:s",$date));
					if (count($current_thesis->get_annotations()) == 1) {
						$content->setVariable("COMMENTS_VALUE", "(1 Kommentar)");
					} else {
						$content->setVariable("COMMENTS_VALUE", "(" .count($current_thesis->get_annotations()) . " Kommentare)");
					}
					$content->setVariable("COMMENTS_URL", $TCRExtension->getExtensionUrl() . "view/" . $current_thesis->get_id());
					$content->parse("BLOCK_DOCUMENTS_ELEMENT");
				}
				
				$critics = $current_thesis->get_attribute("TCR_REVIEWS");
				// display reviews to the thesis if there are any released ones
				foreach ($critics as $critic => $review) {
					if ($review != 0) {
						$review_object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $review);
						$review_released = $review_object->get_attribute("TCR_RELEASED");
						if ($review_released != 0 && in_array($critic, $members)) {
							if ($kind == 3 || $kind == 1) {
								$creator = $review_object->get_creator();
								$date = $review_object->get_attribute("TCR_RELEASED");
								$content->setCurrentBlock("BLOCK_DOCUMENTS_ELEMENT");
								$content->setVariable("ASSETURL", $TCRExtension->getAssetUrl());
								$content->setVariable("DOCUMENT_KIND", "Kritik");
								$content->setVariable("ELEMENT_URL", $TCRExtension->getExtensionUrl() . "view/" . $review_object->get_id());
								$content->setVariable("VIEW_ELEMENT", "Inhalt anzeigen");
								$content->setVariable("ASSETURL", $TCRExtension->getAssetUrl());
								$pic_id = $creator->get_attribute("OBJ_ICON")->get_id();
								$pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/20/30";
								$content->setVariable("AUTHOR_VALUE", "<img style='vertical-align:middle;' src=" . $pic_link . ">&nbsp<a href=" . PATH_URL . "user/index/" . $creator->get_name() . ">" . $creator->get_full_name() . "</a>");
								$content->setVariable("DATE_VALUE", date("d.m.Y H:i:s",$date));
								if (count($review_object->get_annotations()) == 1) {
									$content->setVariable("COMMENTS_VALUE", "(1 Kommentar)");
								} else {
									$content->setVariable("COMMENTS_VALUE", "(" .count($review_object->get_annotations()) . " Kommentare)");
								}
								$content->setVariable("COMMENTS_URL", $TCRExtension->getExtensionUrl() . "view/" . $review_object->get_id());
								$content->parse("BLOCK_DOCUMENTS_ELEMENT");
							}
							
							// display response if it exists and is released
							$response = $review_object->get_attribute("TCR_RESPONSE");
							if ($response != 0) {
								$response_object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $response);
								$response_released = $response_object->get_attribute("TCR_RELEASED");
								if ($response_released != 0) {
									if ($kind == 3 || $kind == 2) {
										$creator = $response_object->get_creator();
										$date = $response_object->get_attribute("TCR_RELEASED");
										$content->setCurrentBlock("BLOCK_DOCUMENTS_ELEMENT");
										$content->setVariable("ASSETURL", $TCRExtension->getAssetUrl());
										$content->setVariable("DOCUMENT_KIND", "Replik");
										$content->setVariable("ELEMENT_URL", $TCRExtension->getExtensionUrl() . "view/" . $response_object->get_id());
										$content->setVariable("VIEW_ELEMENT", "Inhalt anzeigen");
										$content->setVariable("ASSETURL", $TCRExtension->getAssetUrl());
										$pic_id = $creator->get_attribute("OBJ_ICON")->get_id();
										$pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/20/30";
										$content->setVariable("AUTHOR_VALUE", "<img style='vertical-align:middle;' src=" . $pic_link . ">&nbsp<a href=" . PATH_URL . "user/index/" . $creator->get_name() . ">" . $creator->get_full_name() . "</a>");
										$content->setVariable("DATE_VALUE", date("d.m.Y H:i:s",$date));
										if (count($response_object->get_annotations()) == 1) {
											$content->setVariable("COMMENTS_VALUE", "(1 Kommentar)");
										} else {
											$content->setVariable("COMMENTS_VALUE", "(" .count($response_object->get_annotations()) . " Kommentare)");
										}
										$content->setVariable("COMMENTS_URL", $TCRExtension->getExtensionUrl() . "view/" . $response_object->get_id());
										$content->parse("BLOCK_DOCUMENTS_ELEMENT");
									}
								}
							}
						}
					}
				}
			}
			$content->parse("BLOCK_DOCUMENTS_ROUND");
		}
		
		// display filter
		$content->setVariable("FILTER_LABEL", "Filter:");
		$content->setCurrentBlock("BLOCK_DOCUMENTS_DROPDOWN");
		$content->setVariable("OPTION_VALUE", 0);
		$content->setVariable("OPTION_NAME", "Alle Runden");
		$content->setVariable("ROUNDS_VALUE", $rounds);
		$content->parse("BLOCK_DOCUMENTS_DROPDOWN");
		for ($count = 1; $count <= $rounds; $count++) {
			$content->setCurrentBlock("BLOCK_DOCUMENTS_DROPDOWN");
			$content->setVariable("OPTION_VALUE", $count);
			$content->setVariable("OPTION_NAME", $count . ". Runde");
			$content->setVariable("ROUNDS_VALUE", $rounds);
			$content->parse("BLOCK_DOCUMENTS_DROPDOWN");
		}
		$content->parse("BLOCK_DOCUMENTS_TABLE");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => "Thesen-Kritik-Replik-Verfahren", "link" => $TCRExtension->getExtensionUrl() . "Index/" . $TCR->get_id()),
			array("name" => "Alle Dokumente")
		));
		return $frameResponseObject;
	}
}
?>